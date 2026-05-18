# Capítulo 7 — Rutas públicas y SEO

**Resumen ejecutivo.** El portal público (`/bolsa-de-trabajo`, `/sitemap.xml`) está diseñado para ser **cacheable en Cloudflare** y servir respuestas en menos de dos segundos. Para lograrlo, las rutas se cargan con un stack de middleware mínimo que excluye sesión, CSRF y cookies, y se complementan con un middleware defensivo que limpia cualquier cookie que se haya filtrado desde el resto del pipeline. La generación del sitemap usa un patrón de archivo precomputado + backstop on-demand con job en cola para evitar bloqueos. Este capítulo describe la arquitectura completa, incluyendo el rate limiting condicional sobre búsqueda.

## 7.1 Dos archivos de rutas para tres clases de tráfico

Las rutas del producto se distribuyen en cinco archivos bajo [`routes/`](../../../routes/):

| Archivo | Middleware group | Propósito |
|---|---|---|
| `api.php` | `api` | Endpoints REST (no relevante para Bolsa de Trabajo) |
| `channels.php` | (none) | Broadcasting channels (no usados aquí) |
| `console.php` | (CLI) | Comandos artisan custom |
| `public.php` | (custom mínimo) | Listado público + sitemap (sin sesión) |
| `web.php` | `web` (default Laravel) | Detalle de oferta + páginas member-related |

El split crítico está entre `public.php` (sin sesión, cacheable) y `web.php` (con sesión, no cacheable). Detalle de cada uno en las secciones 7.2 y 7.3.

## 7.2 routes/public.php — el listado y el sitemap

Verificable en [`routes/public.php:25-36`](../../../routes/public.php):

```php
Route::name('public.')->group(function () {
    Route::get('/bolsa-de-trabajo', JobBoardController::class)
        ->middleware(ThrottleOnQuery::class)
        ->name('job-board.index');

    Route::get('/sitemap.xml', [SitemapController::class, 'show'])
        ->name('sitemap');
});
```

Las dos rutas son **GET-only, anónimas, idempotentes**. La carga las hace `RouteServiceProvider` con un stack mínimo —**sin** `StartSession`, `VerifyCsrfToken` ni `EncryptCookies`— para que las respuestas sean cookie-free y elegibles para caché en Cloudflare.

### 7.2.1 Middleware defensivo PublicNoSessionCookie

Aun excluyendo `StartSession` del grupo, Laravel puede adjuntar cookies por otros middlewares globales (XSRF token en algunos casos). El middleware [`app/Http/Middleware/PublicNoSessionCookie.php`](../../../app/Http/Middleware/PublicNoSessionCookie.php) limpia esto a la salida:

```php
foreach ($response->headers->getCookies() as $cookie) {
    $response->headers->removeCookie($cookie->getName(), $cookie->getPath(), $cookie->getDomain());
}
$response->headers->remove('Set-Cookie');

$existingCacheControl = $response->headers->get('Cache-Control', '');
if ($existingCacheControl === '' || str_contains($existingCacheControl, 'private')) {
    $response->headers->set(
        'Cache-Control',
        'public, max-age=60, stale-while-revalidate=600'
    );
}
```

> Fuente: [`PublicNoSessionCookie.php:27-50`](../../../app/Http/Middleware/PublicNoSessionCookie.php).

El middleware solo opera sobre verbos **GET/HEAD** (idempotentes). Cualquier otro verbo se pasa intacto. El header `Cache-Control` resultante es **público, 60s de TTL y 600s de stale-while-revalidate**.

> **Importante.** Si añade un endpoint nuevo en `routes/public.php`, debe garantizar que no produzca side effects (escritura, login, cookies). Cualquier mutación debe ir en `web.php` o detrás de auth.

### 7.2.2 Rate limiting condicional ThrottleOnQuery

El listado público acepta filtros de búsqueda (`q`, `category`, `city`, etc.). Solo el filtro `q` (palabra clave) se considera caro y, por lo tanto, se rate-limit. El resto (paginación, filtros estructurados, sort) no se limita para que los crawlers indexen libremente (FR-022 de spec 007).

```php
public function handle(Request $request, Closure $next, string $limiter = 'public-search'): Response
{
    if (! $request->filled('q')) {
        return $next($request);
    }
    return $this->throttle->handle($request, $next, $limiter);
}
```

> Fuente: [`ThrottleOnQuery.php:31-38`](../../../app/Http/Middleware/ThrottleOnQuery.php).

El limiter `public-search` se define en `RouteServiceProvider` con el patrón estándar de Laravel:

```php
RateLimiter::for('public-search', function (Request $request) {
    return Limit::perMinute(60)->by($request->ip())->response(function () {
        return response()->view('public.errors.too-many-requests', [], 429);
    });
});
```

Al exceder el límite, el middleware retorna una view `public.errors.too-many-requests` con status 429 y header `Retry-After`.

## 7.3 routes/web.php — el detalle de oferta y rutas con sesión

Verificable en [`routes/web.php:38-46`](../../../routes/web.php):

```php
Route::get('/bolsa-de-trabajo/{slug}', [JobOfferController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('public.job-offer.show');

Route::get('/alerts/unsubscribe/{member}/{alert}', UnsubscribeAlertController::class)
    ->middleware(['signed'])
    ->name('alerts.unsubscribe');
```

El detalle de oferta vive aquí, **no** en `public.php`, porque necesita leer estado de sesión para detectar la variante de CTA (FR-019 spec 007). Una respuesta personalizada por variante no es cacheable en cualquier caso, así que el costo de las cookies de sesión es aceptable.

El endpoint de desuscripción usa middleware `signed` (firma criptográfica de Laravel) para validar que el visitante llegó vía un link legítimo. Spec 008 define este como long-lived: `URL::signedRoute(..., absoluteExpiresAt: null)`.

## 7.4 SitemapController + GenerateSitemapAction

El sitemap se sirve con un patrón de **archivo precomputado + backstop**:

1. **Patrón normal**: el comando programado `app:generate-sitemap` (capítulo 1 sección 1.7) corre cada hora y reescribe `public/sitemap.xml` con todas las ofertas activas. La regeneración es atómica (escritura a tmp + `rename(2)`).
2. **Backstop on-demand**: si un visitante pide `/sitemap.xml` y el archivo no existe (deploy fresco, scheduler no corrió aún), `SitemapController::show()` encola un job `GenerateSitemapAction::dispatch()` y responde **503 Retry-After**.

```php
public function show(): Response
{
    $path = public_path('sitemap.xml');

    if (! is_file($path)) {
        // dispatch job behind a lock + return 503 retry-after
        // ...
    }

    return response()->file($path, ['Content-Type' => 'application/xml']);
}
```

> Esquema en [`app/Http/Controllers/Public/SitemapController.php:28-50`](../../../app/Http/Controllers/Public/SitemapController.php).

### 7.4.1 GenerateSitemapAction

Verificable en [`app/Actions/Public/GenerateSitemapAction.php`](../../../app/Actions/Public/GenerateSitemapAction.php).

```php
class GenerateSitemapAction
{
    use AsAction, AsJob;

    public function handle(?string $path = null): array
    {
        $path = $path ?? public_path('sitemap.xml');

        $sitemap = Sitemap::create()
            ->add(Url::create(url('/bolsa-de-trabajo'))->setLastModificationDate(now()));

        $count = 1;
        $this->activeOffers()->each(function (JobListing $offer) use ($sitemap, &$count): void {
            $sitemap->add(
                Url::create(url('/bolsa-de-trabajo/'.$offer->slug))
                    ->setLastModificationDate($offer->updated_at ?? now())
            );
            $count++;
        });

        // Atomic write via tmp + rename(2)
        // ...
        return ['path' => $path, 'count' => $count];
    }
}
```

Decisiones de diseño relevantes:

- **`AsAction + AsJob`**: invocable tanto desde el comando programado como desde el backstop on-demand encolado.
- **Active-set predicate compartido**: la consulta de "ofertas activas" debe coincidir con la del listado público (`SearchPublicOffersAction::baseActiveQuery()`). Si divergen, el sitemap promueve URLs que el listado no muestra.
- **Escritura atómica**: el archivo se construye en una ruta temporal y se mueve a la final con `rename(2)`, que es atómico en POSIX. Esto evita que un lector vea el archivo a medio escribir.
- **Lock para evitar dispatches duplicados**: si dos visitantes piden `/sitemap.xml` al mismo tiempo y el archivo no existe, un lock de caché impide encolar el job dos veces.

## 7.5 Búsqueda acento-insensible (spec 007)

El listado público acepta filtros sobre campos textuales. Para soportar búsqueda **acento-insensible** sin romper el orden lexicográfico, las migraciones spec 007 introducen *columnas generadas* `*_folded` en MySQL/MariaDB:

```php
$table->string('title_folded')->virtualAs("CONVERT(title USING utf8mb4)")
    ->collation('utf8mb4_unicode_ci')
    ->nullable();
```

> Migración representativa: [`database/migrations/2026_05_07_000001_add_folded_columns_to_job_listings.php`](../../../database/migrations/2026_05_07_000001_add_folded_columns_to_job_listings.php).

La búsqueda usa `WHERE title_folded LIKE ? COLLATE utf8mb4_unicode_ci` para que "ingles" matchee "Inglés" sin recurrir a un servicio externo (Meilisearch, Algolia).

> **Atención.** Las columnas generadas requieren MySQL 8 o MariaDB 10.5+. SQLite no las soporta de manera equivalente; los tests que dependan de búsqueda acento-insensible deben ejecutarse contra MySQL/MariaDB, no contra SQLite en memoria.

## 7.6 Sitemap accesible

Verificable contra el endpoint local:

```bash
curl -s http://localhost/sitemap.xml | xmllint --format -
```

Salida esperada:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc>http://localhost/bolsa-de-trabajo</loc>
    <lastmod>2026-05-18T...</lastmod>
  </url>
  <url>
    <loc>http://localhost/bolsa-de-trabajo/desarrollador-laravel</loc>
    <lastmod>2026-05-15T...</lastmod>
  </url>
  <!-- ... -->
</urlset>
```

![Sitemap XML del portal público.](../screenshots/impl/impl-sitemap-xml.png)

*Figura 7.1 — Respuesta de `GET /sitemap.xml` con namespace `sitemap/0.9` y una entrada por oferta activa.*

## 7.7 Cabeceras SEO en el detalle de oferta

El detalle de oferta (`/bolsa-de-trabajo/{slug}`) incluye en su HTML:

- `<title>` con título de la oferta + organización.
- `<meta name="description">` derivada del campo `description` truncado.
- `<link rel="canonical">` apuntando a la URL exacta para evitar duplicados.
- JSON-LD `JobPosting` con los campos requeridos por Google for Jobs (cuando el catálogo de campos lo permite).
- Open Graph y Twitter Card tags para preview en redes sociales.

> Verificable en `resources/views/public/job-offer/show.blade.php`. La JSON-LD usa el modelo `JobListing` directamente con un transformer específico.

## 7.8 robots.txt

El archivo `public/robots.txt` permite indexación del portal público. Bloquea `/admin/*`, `/member/*` y `/app/*` para evitar que crawlers gasten cuota sobre páginas internas y para reducir superficie de leak de datos.

```
User-agent: *
Disallow: /admin/
Disallow: /member/
Disallow: /app/
Allow: /

Sitemap: https://<dominio>/sitemap.xml
```

## 7.9 Resumen

| Pregunta | Respuesta |
|---|---|
| ¿Por qué hay un `routes/public.php` separado? | Para cargar las rutas sin middleware de sesión y permitir caché en Cloudflare. |
| ¿Por qué el detalle de oferta vive en `web.php`? | Porque necesita sesión para detectar variante de CTA (FR-019). |
| ¿Cómo se genera el sitemap? | Cron horario `app:generate-sitemap`; backstop on-demand vía controlador + job. |
| ¿Cómo se evita caché incorrecto? | `PublicNoSessionCookie` strip de cookies + `Cache-Control: public, max-age=60, swr=600`. |
| ¿Cómo es la búsqueda acento-insensible? | Columnas generadas `*_folded` + `utf8mb4_unicode_ci`. |
| ¿Se rate-limita todo el listado? | No: solo cuando hay `q`. Paginación y filtros estructurados son libres. |

El próximo capítulo (8) detalla el pipeline completo de alertas de empleo (spec 008): instant, daily y weekly.
