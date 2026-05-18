# Capítulo 9 — Despliegue y operación

**Resumen ejecutivo.** Producción ejecuta CBC Workplace contra **PHP-FPM + nginx + MariaDB + Redis**. El worker de cola corre bajo **Supervisor** (típicamente dos workers: `default` para correos transaccionales y `instant` para alertas spec 008). El scheduler corre via cron con un único entry de Laravel. Este capítulo enumera los componentes de infraestructura, ofrece snippets representativos de configuración y termina con la **checklist de despliegue** que el responsable debe verificar antes de promover una versión a producción.

## 9.1 Infraestructura mínima

| Componente | Versión mínima | Propósito |
|---|---|---|
| PHP | 8.3 | Runtime de la aplicación |
| PHP-FPM | (matched a PHP) | Pool de workers que sirven HTTP |
| nginx | 1.20+ | Reverse proxy, archivos estáticos |
| MariaDB | 10.6+ | Base de datos (compatible con migraciones MySQL 8 del proyecto) |
| Redis | 7+ | Caché, sesión, cola, locks de scheduler |
| Supervisor | 4+ | Mantener vivos los workers de cola |
| Cloudflare (opcional) | — | CDN delante de nginx para portal público |
| Mail provider | — | SMTP autenticado (ej. Amazon SES, Postmark, Mailgun) |

> **Buena práctica.** Use Redis para sesión y cola incluso en despliegues modestos. Evita lock contention en MySQL y desacopla el escalado de workers del de la base de datos.

## 9.2 Variables de entorno críticas para producción

Listado abreviado (el detalle completo está en el **Apéndice B**):

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<dominio>

# Base de datos
DB_CONNECTION=mysql
DB_HOST=<host-mariadb>
DB_PORT=3306
DB_DATABASE=<db>
DB_USERNAME=<user>
DB_PASSWORD=<password>

# Cache + sesión + cola en Redis
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=<host-redis>
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=<smtp-host>
MAIL_PORT=587
MAIL_USERNAME=<smtp-user>
MAIL_PASSWORD=<smtp-pass>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@<dominio>

# Alertas (overrides opcionales)
DAILY_DISPATCH_HOUR=7
WEEKLY_DISPATCH_DAY=monday
WEEKLY_DISPATCH_HOUR=7
INSTANT_ALERT_WINDOW_SECONDS=300
MAX_ALERTS_PER_MEMBER=10
```

> **Importante.** `APP_DEBUG=true` en producción es un leak masivo de información (stack traces, variables, paths). Verifique después de cada deploy que `APP_DEBUG=false` y que `APP_ENV=production`.

## 9.3 nginx — configuración de virtual host

Patrón de referencia (ajustar paths, certificados y dominios):

```nginx
server {
    listen 443 ssl http2;
    server_name <dominio>;

    root /var/www/cbc-workplace/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/<dominio>/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/<dominio>/privkey.pem;

    # Archivos estáticos: largo TTL para inmutables
    location ~* \.(css|js|woff2?|png|jpg|svg|ico)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # Sitemap servido como archivo plano (precomputado por scheduler)
    location = /sitemap.xml {
        try_files /sitemap.xml @php;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location @php {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root/index.php;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Bloquear acceso a archivos sensibles
    location ~ /\.(?!well-known).* { deny all; }
    location ~ \.(env|log)$ { deny all; }
}

server {
    listen 80;
    server_name <dominio>;
    return 301 https://$server_name$request_uri;
}
```

<!-- TODO captura: impl-nginx-config-snippet — captura del archivo en /etc/nginx/sites-available/. -->

> **Atención.** Si Cloudflare sirve el portal público con caché, asegúrese de que las cabeceras `Cache-Control: public, max-age=60, stale-while-revalidate=600` que emite el middleware `PublicNoSessionCookie` (capítulo 7) lleguen intactas al edge. Las page rules de Cloudflare pueden sobreescribirlas.

## 9.4 Supervisor — workers de cola

`/etc/supervisor/conf.d/cbc-workplace-worker.conf`:

```ini
[program:cbc-workplace-worker-default]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/cbc-workplace/artisan queue:work redis --queue=default --tries=3 --max-time=3600 --sleep=3
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/cbc-workplace/worker-default.log
stopwaitsecs=3600

[program:cbc-workplace-worker-instant]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/cbc-workplace/artisan queue:work redis --queue=instant --tries=3 --max-time=3600 --sleep=1
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/cbc-workplace/worker-instant.log
stopwaitsecs=3600
```

<!-- TODO captura: impl-supervisor-config — captura del archivo Supervisor. -->

Justificación:

- **Dos pools separados** (`default` y `instant`): permite reservar capacidad para el listener spec 008 (`EvaluateInstantJobAlerts`, capítulo 8 sección 8.2.2) sin que correos transaccionales lo bloqueen.
- **`--max-time=3600`** y **`stopwaitsecs=3600`**: cada worker se recicla cada hora para liberar memoria, y Supervisor da hasta una hora a un worker activo para terminar antes de matarlo. Evita OOM y kills abruptos durante un envío.
- **`--tries=3`**: cada job tiene tres oportunidades antes de pasar a `failed_jobs`.

Tras editar:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

## 9.5 Cron — el scheduler

Un único entry en `/etc/cron.d/cbc-workplace`:

```cron
* * * * * www-data cd /var/www/cbc-workplace && php artisan schedule:run >> /dev/null 2>&1
```

Esto invoca a Laravel cada minuto; Laravel decide internamente qué tareas ejecutar según `app/Console/Kernel.php`. Las tres tareas relevantes para Bolsa de Trabajo son:

| Comando | Frecuencia |
|---|---|
| `app:generate-sitemap` | Cada hora |
| `alerts:dispatch-daily` | Diario 07:00 |
| `alerts:dispatch-weekly` | Lunes 07:00 |

> **Importante.** `onOneServer()` requiere driver de caché compartido (Redis típicamente). En un cluster con múltiples nodos, el cron debe estar activo en al menos uno de ellos pero el lock de Redis garantiza ejecución única.

## 9.6 Procedimiento de despliegue

### Procedimiento: Promover una nueva versión a producción

1. **Pre-deploy: verificación local del PR**
   ```bash
   git checkout main && git pull
   sail artisan test
   sail bin pint --test
   sail bin pest --coverage
   ```

2. **Tag de versión** (semver o fecha):
   ```bash
   git tag v1.X.Y
   git push origin v1.X.Y
   ```

3. **En el servidor de producción**, como el usuario `www-data` o equivalente:
   ```bash
   cd /var/www/cbc-workplace
   php artisan down --refresh=15 --secret="<TOKEN_BYPASS>"
   git fetch --tags && git checkout v1.X.Y
   composer install --no-dev --optimize-autoloader --no-interaction
   php artisan migrate --force
   php artisan optimize:clear
   php artisan optimize
   php artisan filament:optimize
   php artisan view:cache
   ```

4. **Reiniciar workers** para que carguen el código nuevo:
   ```bash
   sudo supervisorctl restart all
   ```

5. **Levantar la app**:
   ```bash
   php artisan up
   ```

6. **Post-deploy: smoke tests** desde un host externo:
   ```bash
   curl -sf https://<dominio>/                       # 200
   curl -sf https://<dominio>/bolsa-de-trabajo        # 200
   curl -sf https://<dominio>/sitemap.xml             # 200 o 503 (backstop)
   curl -sf https://<dominio>/admin/login             # 200
   ```

7. **Verificar workers procesando**:
   ```bash
   sudo supervisorctl status
   ```
   Salida esperada: todos los workers en estado `RUNNING`.

> **Importante.** `php artisan migrate --force` es necesario en producción (sin `--force` el comando pregunta confirmación, y los procesos automatizados no pueden responder). **Pero** si su PR contiene migraciones destructivas (drop column, rename, etc.), considere ejecutar la migración fuera del flujo automatizado tras revisar el plan de rollback.

## 9.7 Plan de rollback

Si tras el deploy detecta un problema crítico:

1. **Activar modo mantenimiento**:
   ```bash
   php artisan down --secret="<TOKEN_BYPASS>"
   ```

2. **Volver al tag previo**:
   ```bash
   git checkout v1.X.(Y-1)
   composer install --no-dev --optimize-autoloader
   php artisan optimize:clear
   php artisan optimize
   ```

3. **Migraciones**: si la versión nueva añadió columnas nullable o tablas nuevas, **no es necesario** revertir las migraciones; las viejas las ignoran. Si añadió cambios destructivos, ejecute la migración inversa:
   ```bash
   php artisan migrate:rollback --step=1 --force
   ```

4. **Reiniciar workers y levantar**:
   ```bash
   sudo supervisorctl restart all
   php artisan up
   ```

> **Atención.** Las migraciones destructivas (drop, rename) deben evitarse o anunciarse. Una migración añade-y-mantiene-nullable acompañada de código que tolere ambos esquemas es la estrategia que permite rollbacks limpios. Ver patrón "expand/contract migrations".

## 9.8 Checklist de despliegue

**Antes del deploy:**

- [ ] Branch mergeado a `main` con CI verde.
- [ ] Migraciones revisadas: ¿alguna destructiva? ¿plan de rollback?
- [ ] `composer.lock` commiteado.
- [ ] `.env.example` actualizado si hay variables nuevas.
- [ ] Tag de versión creado y pusheado.
- [ ] Aviso al equipo de operación con ventana de tiempo y descripción del cambio.

**Durante el deploy:**

- [ ] Modo mantenimiento activado con `--secret` para bypass del operador.
- [ ] `git checkout` al tag exacto.
- [ ] `composer install --no-dev --optimize-autoloader`.
- [ ] `php artisan migrate --force` sin errores.
- [ ] `php artisan optimize` + `filament:optimize` + `view:cache`.
- [ ] `supervisorctl restart all`.

**Después del deploy:**

- [ ] `php artisan up` ejecutado.
- [ ] Smoke tests externos pasan (`/`, `/bolsa-de-trabajo`, `/admin/login`, `/sitemap.xml`).
- [ ] `supervisorctl status` muestra workers `RUNNING`.
- [ ] Login en `/admin` exitoso con cuenta de prueba.
- [ ] `tail -f storage/logs/laravel.log` durante 5 minutos sin errores nuevos.
- [ ] Un correo de prueba enviado a través del flujo de aprobación de oferta llega correctamente.

<!-- TODO captura: impl-deploy-checklist-diagram — diagrama mermaid del flujo de deploy completo. -->

## 9.9 Backups

- **Base de datos**: dump diario completo + binlog incremental para point-in-time recovery. Retención mínima 30 días.
- **Almacenamiento público** (`storage/app/public/`): backup diario; los CVs de candidatos viven aquí.
- **`.env`**: no se backupea con el resto; vive en el secret manager.

```bash
# Ejemplo de dump diario via cron
mysqldump --single-transaction --quick --databases cbc_workplace \
  | gzip > /backups/db/cbc-$(date +%F).sql.gz
```

> **Importante.** Pruebe sus backups periódicamente restaurándolos en un entorno aislado. Un backup nunca probado no es un backup.

## 9.10 Métricas operacionales mínimas

Monitorice al menos:

- **Latencia P95** de las rutas públicas (objetivo SC-001 spec 007: 2 segundos).
- **Tasa de error 5xx** en nginx.
- **Lag de la cola Redis** (`queue:size`). Si crece sostenidamente, el worker no procesa lo suficiente.
- **Conteo de jobs fallidos** (`failed_jobs` table). Investigue cualquier crecimiento.
- **Espacio en disco** del filesystem que aloja `storage/`.
- **Uso de memoria** del proceso PHP-FPM.

Herramientas habituales: Grafana + Prometheus, o servicios alojados (NewRelic, Datadog) si presupuesto lo permite.

## 9.11 Comandos artisan útiles en producción

```bash
php artisan queue:size                              # tamaño actual de la cola
php artisan queue:failed                            # jobs en failed_jobs
php artisan queue:retry all                         # reintentar todos los failed
php artisan schedule:list                           # próxima ejecución de cada tarea
php artisan filament:optimize                       # cachea componentes de Filament
php artisan optimize:clear                          # invalida toda la caché
php artisan tinker                                  # REPL para consultas ad-hoc
```

> **Importante.** Evite `php artisan migrate:fresh` o `php artisan db:seed` en producción salvo en escenarios de bootstrap explícito de un entorno nuevo. **Nunca** ejecute seeders de demostración (`Spec009DemoSeeder`, `GuidesDemoSeeder`) en producción.

## 9.12 Resumen

| Pregunta | Respuesta |
|---|---|
| ¿Cómo se mantienen vivos los workers? | Supervisor con 2 pools: `default` y `instant`. |
| ¿Cómo corre el scheduler? | Cron de un minuto invocando `php artisan schedule:run`. |
| ¿Dónde está el sitemap? | `public/sitemap.xml`; regenerado horario por scheduler. |
| ¿Cómo se hace un deploy seguro? | `down` → `checkout tag` → `composer/migrate/optimize` → `supervisorctl restart` → `up`. |
| ¿Qué hago si el deploy falla? | Volver al tag previo + `composer install` + `supervisorctl restart` + `up`. |
| ¿Cómo evito que workers se queden colgados? | `--max-time=3600` + `stopwaitsecs=3600` en Supervisor. |

El próximo capítulo (10) trata la observabilidad y los patrones de testing.
