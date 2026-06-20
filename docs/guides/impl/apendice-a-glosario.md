# Apéndice A — Glosario técnico

Este apéndice consolida los términos técnicos usados a lo largo de la *Guía de Implementación*. Extiende el glosario canónico del blueprint del proyecto ([`docs/guides/00-blueprint.md`](../00-blueprint.md), sección 3) con terminología específica del stack: paquetes, conceptos de framework, herramientas de operación.

## A

**Action** (patrón `lorisleiva/laravel-actions`)
:   Clase única bajo `app/Actions/` que encapsula una operación de negocio con un método `handle()`. Invocable estáticamente (`::run()`), despachable como job (`::dispatch()` con trait `AsJob`) y registrable como listener (con `AsListener`). Capítulo 4.

**Activitylog**
:   Bitácora persistente del paquete `spatie/laravel-activitylog`. Tabla `activity_log`. Capítulo 1 sección 1.8 y capítulo 10.

**Active set**
:   Conjunto de ofertas visibles en el portal público y en el sitemap. Definido por `SearchPublicOffersAction::baseActiveQuery()`. Capítulo 7 sección 7.4.1.

**AsAction / AsJob / AsListener**
:   Los tres traits de `lorisleiva/laravel-actions` que dotan a una clase de capacidad de invocación síncrona, dispatch a cola y suscripción a eventos. Capítulo 4 sección 4.2.

## B

**Backstop on-demand**
:   Patrón usado por `SitemapController`: si el archivo precomputado no existe, encola un job para regenerarlo y responde 503 Retry-After. Evita bloqueos en el request. Capítulo 7 sección 7.4.

**BasePolicy**
:   Clase base de la que heredan todas las policies del proyecto. Implementa `before()` para admin global, y método `prefix()` para generar la clave de permiso `Panel.Model.method`. Capítulo 6.

**Blueprint**
:   En este proyecto, referencia a `docs/guides/00-blueprint.md` (el documento de planificación). En el ecosistema PHP, `laravel-shift/blueprint` es un paquete distinto (generador de código), incluido en `require-dev`.

## C

**Cache-Control**
:   Header HTTP que controla caché en navegadores e intermediarios. El middleware `PublicNoSessionCookie` setea `public, max-age=60, stale-while-revalidate=600` en respuestas del portal público.

**Cascade (cierre en cascada)**
:   Efecto colateral de suspender una organización: todas sus ofertas activas pasan a `CLOSED` automáticamente, dentro de la misma transacción de `SuspendOrganization::handle()`. Capítulo 4 (en *Guía de Admin*) y capítulo 6 sección 6.5 (cascade-freeze de operaciones del miembro).

**Causer**
:   Usuario que originó un evento de auditoría. Campo `causer_*` en `activity_log`.

**Coalesce (en alertas)**
:   Acción `CoalesceInstantMatchAction` que decide si una `(alert, listing)` debe dispatcharse, consultando `JobAlertDispatchLog` para dedup. Capítulo 8 sección 8.2.3.

**Cookie-free response**
:   Respuesta HTTP sin `Set-Cookie`. Pre-requisito para ser cacheable en CDNs como Cloudflare. Capítulo 7.

**Coverage**
:   Porcentaje de líneas de código ejecutado por la suite de tests. Medido por `pcov` o `Xdebug`. Objetivo del proyecto: 80% mínimo, 90%+ en áreas críticas. Capítulo 10 sección 10.6.

## D

**Dedup (en alertas)**
:   Mecanismo para evitar enviar la misma oferta a la misma alerta dos veces. Implementado vía `JobAlertDispatchLog` para frecuencia `instant`. Capítulo 8 sección 8.2.3.

**Digest**
:   Correo electrónico agrupador de ofertas de empleo entregado a un candidato suscrito a una alerta. Tres variantes: instantánea, diaria, semanal.

**`DispatchDecision`**
:   Enum en `app/Enums/DispatchDecision.php`. Valores: `DISPATCHED`, `ABSORBED_DEDUP`, `SKIPPED_INACTIVE`. Se registra en `JobAlertDispatchLog.decision` para análisis posterior.

**DTO (Data Transfer Object)**
:   Objeto con datos sin lógica, usado para pasar resultados estructurados entre capas. Ejemplo del producto: `SuspendOrganizationResult`. Capítulo 4 sección 4.4.

## E

**Edge cache**
:   Caché en el CDN (Cloudflare, etc.) cercano al visitante. Sirve respuestas sin tocar el servidor de origen. Habilitado para el portal público gracias a cookies-free + Cache-Control public.

**Email verification**
:   Flujo nativo de Laravel para confirmar correos. Activo en el panel `/member` (`emailVerification()` en `MemberPanelProvider.php:76`). Personalizado para enviar texto en español.

**Enum (PHP 8.1+)**
:   Tipo nativo de enumeración. El producto usa enums con `int` (no `string`) para todos los estados (JobListingState, ApplicationStatus, etc.). Capítulo 5 sección 5.11.

**`expand/contract migrations`**
:   Patrón de despliegue con migraciones aditivas en una versión + contracción en la siguiente. Evita ventanas de downtime. Capítulo 9 sección 9.7.

## F

**Fake (en tests)**
:   `Queue::fake()`, `Mail::fake()`, etc. Sustituye el componente real para inspeccionar el comportamiento sin side effects. Capítulo 10 sección 10.7.

**Filament**
:   Framework de paneles administrativos construido sobre Laravel + Livewire. Versión usada: `^3.3`. Capítulo 3.

**Folded column**
:   Columna generada en MySQL/MariaDB con texto sin acentos. Usada para búsqueda acento-insensible. Columnas `*_folded` en `job_listings`. Capítulo 7 sección 7.5.

## G

**Generated column**
:   Columna calculada en la base, mantenida por el motor. Requiere MySQL 8 o MariaDB 10.5+.

**Guard**
:   Mecanismo de autenticación de Laravel. El producto usa tres guards: `admin`, `member`, `web` (default).

## H

**Header action**
:   Acción de Filament que aparece en la cabecera de una vista de detalle. Capítulo 4 sección 4.6.

**`hasPermission`**
:   Método en `User` y `Member` que verifica si el rol del usuario tiene el permiso pedido. Llamado desde policies con clave `Panel.Model.method`. Capítulo 6 sección 6.2.

## I

**Idempotente** (operación HTTP)
:   Operación que produce el mismo efecto si se ejecuta una o varias veces. GET es idempotente; POST no lo es. Las rutas en `routes/public.php` son todas GET idempotentes.

**`onOneServer()`**
:   Modifier de tarea programada que garantiza ejecución única en cluster multi-nodo. Requiere driver de caché compartido (Redis). Capítulo 9 sección 9.5.

**Instant pipeline**
:   Flujo síncrono+asíncrono que entrega correos instantáneos cuando una oferta nueva matchea una alerta. Capítulo 8 sección 8.2.

## J

**`JobAlertDispatchLog`**
:   Tabla append-only que registra cada decisión del pipeline de alertas. Sostiene el dedup instantáneo y permite métricas operacionales. Capítulo 5 sección 5.9 y capítulo 8 sección 8.9.

**`JobDecorator`**
:   Clase del paquete `lorisleiva/laravel-actions` que envuelve una Action cuando se despacha vía `::dispatch()`. **Importante para tests**: `Queue::assertPushed(JobDecorator::class, fn ($j) => $j->decorated instanceof MyAction)`. Capítulo 4 sección 4.7.2.

## L

**Listener**
:   Suscriptor a un evento de Laravel. En el producto, los listeners de eventos críticos implementan `ShouldQueue` para procesarse en background. Ejemplo: `EvaluateInstantJobAlerts`.

**Livewire**
:   Framework PHP para componentes dinámicos en Blade, base de Filament. Tests Livewire usan el plugin Pest `pestphp/pest-plugin-livewire`.

**`LogsActivity`**
:   Trait del paquete `spatie/laravel-activitylog` que automatiza el registro en `activity_log` para cambios en un modelo. Usado en `Organization`, `JobListing`, etc.

## M

**Mailable**
:   Clase que representa un correo en Laravel. Patrón: `Mail::to($recipient)->queue(new MyMailable($context))`. Capítulo 11 sección 11.9.

**MariaDB**
:   Base de datos en producción del proyecto. Compatible con migraciones MySQL 8 del repositorio.

**Member**
:   Modelo `App\Models\Member` que representa al usuario del panel `/member` (organizaciones publicadoras y candidatos). Distinto de `User` (panel admin).

**Middleware**
:   Capa intermedia que procesa request/response. El producto define tres custom middleware: `PublicNoSessionCookie`, `ThrottleOnQuery`, y el `Authenticate` estándar.

**Middleware mínimo (en rutas públicas)**
:   Stack reducido que excluye `StartSession`, `VerifyCsrfToken` y `EncryptCookies` para que las respuestas sean cacheables.

**Morph polymorphic**
:   Relación Eloquent que puede apuntar a múltiples modelos según `*_type`. Usado por `Comments`, `categories` (vía pivot), etc.

## N

**`navigationGroups()`**
:   Método de `PanelProvider` que declara los grupos del sidebar. Admin tiene 4: Sistema, Administración, Bolsa de Trabajo, Emprendimientos.

**Nullable column**
:   Columna que admite `NULL`. Preferida en migraciones de producción para evitar backfills bloqueantes.

## P

**Panel** (Filament)
:   Aplicación independiente Filament. El producto tiene tres: admin, member, venture.

**Pandoc**
:   Convertidor universal de documentos. Usado en el toolchain de las guías para generar `.docx` desde Markdown. Capítulo de la *Guía de Toolchain*.

**`PanelsRenderHook`**
:   Enum de Filament con puntos de inyección de HTML/Livewire en el layout. Usado en el producto para `GLOBAL_SEARCH_AFTER` (badge de rol) y `CONTENT_START` (banner de suspensión).

**`pcov`**
:   Extensión PHP de cobertura, más rápida que Xdebug. Recomendada para CI.

**`Pest`**
:   Framework de testing con sintaxis funcional sobre PHPUnit. Versión usada: `^2.34`.

**`PHP-CS-Fixer` / `Laravel Pint`**
:   Linters de formato. El proyecto usa Pint (`laravel/pint`).

**Policy**
:   Clase de autorización por modelo. Capítulo 6.

**`prefix()` (de BasePolicy)**
:   Método estático que construye la clave de permiso `Panel.Model.method` inspeccionando el stack trace. Capítulo 6 sección 6.2.

## Q

**Queue (cola)**
:   Sistema de jobs diferidos de Laravel. Driver recomendado en producción: Redis. Driver en local/Sail: database. Workers procesados por Supervisor.

## R

**`RateLimiter::for(...)`**
:   API de Laravel para definir limiters nombrados. El producto define `public-search` para el portal.

**Redis**
:   Almacén key-value usado en producción para cache, sesión, cola y locks de scheduler.

**Render hook**
:   Punto de extensión de Filament donde inyectar HTML. Capítulo 3 sección 3.2/3.3.

**`Resource` (Filament)**
:   Clase que expone un modelo Eloquent al panel: formulario, tabla, páginas. Capítulo 11 sección 11.7.

**`RouteServiceProvider`**
:   Provider de Laravel que carga los archivos de rutas. El producto modifica el método `boot()` para cargar `routes/public.php` con su stack mínimo.

## S

**Sail**
:   Wrapper de Docker Compose para Laravel local. Provee `mysql`, `mailpit`, `redis` (opcional). Capítulo 2.

**Schedule (Laravel)**
:   Sistema de tareas programadas declarado en `app/Console/Kernel.php::schedule()`. Invocado por cron cada minuto.

**Scope (en Eloquent)**
:   Método del modelo que añade restricciones a una query. Ejemplo: `JobListing::active()`. Capítulo 5.

**Scope (en categorías)**
:   Campo de la tabla `categories` que discrimina contextos: `JobListing`, `Venture`, etc. Capítulo 6 (en *Guía de Admin*).

**Signed URL**
:   URL con firma criptográfica de Laravel para invalidar manipulación. Usado para desuscripción de alertas. `URL::signedRoute(..., absoluteExpiresAt: null)`.

**Sitemap**
:   `public/sitemap.xml`. Generado por `app:generate-sitemap` cada hora. Patrón precomputado + backstop. Capítulo 7 sección 7.4.

**Supervisor**
:   Process manager para mantener vivos los workers de cola en producción. Capítulo 9 sección 9.4.

**Suspension flag**
:   Trío de columnas (`suspended_at`, `suspended_by`, `suspension_reason`) que indican operacionalmente que una organización está congelada. **Ortogonal** al `verification_state`. Spec 009.

## T

**ThrottleRequests**
:   Middleware estándar de Laravel para rate limiting. El producto lo envuelve en `ThrottleOnQuery` para aplicación condicional.

**TopNavigation**
:   Modo de navegación de Filament donde el sidebar es horizontal en la parte superior. Activado en los tres paneles.

## U

**`Util::isPanelActive(string $id)`**
:   Helper para distinguir desde qué panel se invoca una policy. Capítulo 6 sección 6.7.

**`Util::run(callable $fn)`**
:   Helper que envuelve una invocación con manejo uniforme de excepciones y notificaciones de Filament. Capítulo 4 sección 4.6.

## V

**Variant CTA**
:   Comportamiento del detalle de oferta donde el call-to-action cambia según el estado de autenticación del visitante (anónimo vs. autenticado vs. candidato sin postular). Spec 007 FR-019.

**`VerifyCsrfToken`**
:   Middleware estándar de Laravel que valida el token CSRF. **Excluido** del grupo `routes/public.php` porque las rutas son GET-only y sin sesión.

## W

**Webhook**
:   No usado actualmente en el producto, pero contemplado para integraciones futuras (12.14).

**Widget (Filament)**
:   Componente del dashboard. El producto tiene 4 widgets de spec 009 bajo `app/Filament/Admin/Widgets/`.

**Worker (de cola)**
:   Proceso que procesa jobs de la cola. En producción corre bajo Supervisor con dos pools: `default` e `instant`.

## X

**Xdebug**
:   Extensión PHP para debug y cobertura. Reemplazada por `pcov` en CI por rendimiento; preferible localmente cuando se necesita step debugging.

## Y

**YAML metadata block** (Pandoc)
:   Bloque al principio de un `.md` con metadatos para Pandoc. Usado en `metadata.yaml` de cada guía. Define título, autor, fecha, etc.
