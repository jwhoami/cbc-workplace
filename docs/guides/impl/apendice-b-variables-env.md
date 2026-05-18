# Apéndice B — Variables de entorno

Este apéndice consolida todas las variables `.env` relevantes para CBC Workplace, agrupadas por área. Cada entrada incluye el **default** (cuando aplica), el **rango** o conjunto de valores válidos, y una **nota** sobre el efecto en producción.

## B.1 Aplicación

| Variable | Default | Rango / valores | Notas |
|---|---|---|---|
| `APP_NAME` | "Laravel" | string libre | Aparece en correos y en algunas vistas. |
| `APP_ENV` | `production` | `local`, `testing`, `staging`, `production` | Controla múltiples behaviors (CAPTCHA, Mailpit). **No** debe ser `local` en producción. |
| `APP_KEY` | (vacío) | base64-encoded 32 bytes | Generado con `php artisan key:generate`. **Rotarla invalida sesiones**. |
| `APP_DEBUG` | `false` | `true`/`false` | **Nunca `true` en producción**: leak masivo de información (stack traces, paths). |
| `APP_URL` | `http://localhost` | URL absoluta sin trailing slash | Usado para generar URLs absolutas en correos y sitemap. |
| `APP_TIMEZONE` | `UTC` | tz database name (e.g., `America/Santiago`) | Define la zona horaria para `now()` y schedule. |
| `APP_LOCALE` | `en` | locale code (e.g., `es`) | El producto usa `es` para UI. |

## B.2 Base de datos

| Variable | Default | Rango | Notas |
|---|---|---|---|
| `DB_CONNECTION` | `mysql` | `mysql`, `mariadb`, `sqlite`, `pgsql` | El producto usa `mysql` en Sail; `mysql` o `mariadb` en producción. |
| `DB_HOST` | `127.0.0.1` | hostname | En Sail: `mysql` (nombre del servicio). |
| `DB_PORT` | `3306` | port | Estándar MySQL/MariaDB. |
| `DB_DATABASE` | `laravel` | string | Nombre de la base. |
| `DB_USERNAME` | `root` | string | Sin espacios. |
| `DB_PASSWORD` | (vacío) | string | **Obligatorio en producción**, vacío bloquea conexión. |

> **Atención.** Las migraciones del producto usan columnas generadas (`*_folded`) que requieren MySQL 8 o MariaDB 10.5+. SQLite en memoria no las soporta de manera equivalente; los tests que dependen de búsqueda acento-insensible deben correrse contra MySQL/MariaDB.

## B.3 Caché y sesión

| Variable | Default | Rango | Notas |
|---|---|---|---|
| `CACHE_STORE` | `database` | `database`, `redis`, `memcached`, `file`, `array` | Recomendado `redis` en producción. |
| `CACHE_PREFIX` | `laravel_cache_` | string | Prefijo de claves. |
| `SESSION_DRIVER` | `database` | `database`, `redis`, `file`, `cookie` | Recomendado `redis` en producción. |
| `SESSION_LIFETIME` | `120` (minutos) | entero positivo | Tiempo de vida de la sesión. |
| `SESSION_ENCRYPT` | `false` | `true`/`false` | Solo necesario si el driver es `cookie`. |
| `SESSION_DOMAIN` | (null) | dominio | Setear cuando hay subdominios. |
| `SESSION_SECURE_COOKIE` | (null) | `true` en producción HTTPS | Garantiza que la cookie solo viaje por TLS. |
| `SESSION_SAME_SITE` | `lax` | `lax`, `strict`, `none` | `lax` es el sano default. |

## B.4 Cola

| Variable | Default | Rango | Notas |
|---|---|---|---|
| `QUEUE_CONNECTION` | `database` | `database`, `redis`, `sync`, `sqs`, `beanstalkd` | Recomendado `redis` en producción. |
| `QUEUE_FAILED_DRIVER` | `database-uuids` | `database-uuids`, `database`, `dynamodb`, `null` | `database-uuids` permite reintentos por UUID. |

## B.5 Redis

| Variable | Default | Notas |
|---|---|---|
| `REDIS_CLIENT` | `phpredis` | `phpredis` o `predis`. `phpredis` requiere extensión; `predis` es pure PHP más lento. |
| `REDIS_HOST` | `127.0.0.1` | hostname del Redis |
| `REDIS_PORT` | `6379` | port |
| `REDIS_PASSWORD` | (null) | password si está configurado en Redis |
| `REDIS_DB` | `0` | número de base (Redis soporta 0-15 por default) |
| `REDIS_CACHE_DB` | `1` | DB dedicada para caché para evitar colisiones con la cola |

## B.6 Mail

| Variable | Default | Rango | Notas |
|---|---|---|---|
| `MAIL_MAILER` | `log` | `smtp`, `ses`, `mailgun`, `postmark`, `sendmail`, `log`, `array` | En Sail local: `smtp` apuntando a Mailpit. En producción: SMTP autenticado o transactional. |
| `MAIL_HOST` | `mailpit` | hostname | En Sail: `mailpit`. En producción: el SMTP del proveedor. |
| `MAIL_PORT` | `1025` | port | Mailpit usa 1025. SMTP estándar: 587 (STARTTLS) o 465 (TLS implícito). |
| `MAIL_USERNAME` | (null) | string | Obligatorio para SMTP autenticado. |
| `MAIL_PASSWORD` | (null) | string | Idem. |
| `MAIL_ENCRYPTION` | (null) | `tls`, `ssl`, `null` | `tls` con puerto 587 es lo común. |
| `MAIL_FROM_ADDRESS` | `hello@example.com` | email | Dirección remitente de todos los correos del producto. |
| `MAIL_FROM_NAME` | `${APP_NAME}` | string | Nombre amigable del remitente. |

> **Importante.** En producción, `MAIL_FROM_ADDRESS` debe ser una dirección con SPF/DKIM/DMARC configurados sobre el dominio. Direcciones aleatorias terminan en spam.

## B.7 Sistema de alertas (spec 008)

Verificable en [`config/alerts.php`](../../../config/alerts.php).

| Variable | Default | Rango | Notas |
|---|---|---|---|
| `INSTANT_ALERT_WINDOW_SECONDS` | `300` | entero positivo | Ventana de gracia para retries del listener instant. Si excede, descarta. |
| `MAX_ALERTS_PER_MEMBER` | `10` | entero positivo | Límite por candidato. |
| `DAILY_DISPATCH_HOUR` | `7` | 0-23 | Hora local (zona de `APP_TIMEZONE`) del despacho diario. |
| `WEEKLY_DISPATCH_DAY` | `monday` | lunes-domingo en inglés | Día de la semana del despacho semanal. |
| `WEEKLY_DISPATCH_HOUR` | `7` | 0-23 | Hora local del despacho semanal. |

## B.8 CAPTCHA (`marcogermani87/filament-captcha`)

| Variable | Default | Notas |
|---|---|---|
| (configurado en el config del paquete, no en `.env` directamente) | — | Las llaves del provider de CAPTCHA viven en `config/filament-captcha.php`. |

Si el paquete espera variables específicas en `.env`, consultar su README. Por defecto el producto usa el modo "stub" en `local` y `testing` (saltado en código).

## B.9 Logging

| Variable | Default | Rango | Notas |
|---|---|---|---|
| `LOG_CHANNEL` | `stack` | `stack`, `single`, `daily`, `slack`, `stderr`, `papertrail`, `syslog` | `stack` permite combinar canales. |
| `LOG_STACK` | `single` | lista CSV de canales | Recomendado `daily,stderr` en producción. |
| `LOG_LEVEL` | `debug` | `debug`, `info`, `notice`, `warning`, `error`, `critical`, `alert`, `emergency` | En producción usar `warning` o `error` para no saturar. |
| `LOG_DAILY_DAYS` | `14` | entero positivo | Retención del canal `daily`. |
| `LOG_SLACK_WEBHOOK_URL` | (null) | URL | Si se usa el canal `slack` para alertar a un canal de incidentes. |

## B.10 Filesystem

| Variable | Default | Rango | Notas |
|---|---|---|---|
| `FILESYSTEM_DISK` | `local` | `local`, `public`, `s3` | El producto usa `public` para CVs de candidatos. |
| `AWS_*` | — | — | Solo si se usa S3 (no requerido por el producto v1.0). |

## B.11 Specs y feature flags

CBC Workplace v1.0 **no usa feature flags ni LaunchDarkly/similares**. El comportamiento se controla por código y por las migraciones que ya están aplicadas. Si en el futuro se introducen feature flags, este apéndice debe actualizarse.

## B.12 Variables solo para desarrollo

| Variable | Default | Notas |
|---|---|---|
| `TELESCOPE_ENABLED` | `false` | Si Telescope estuviera instalado (no lo está en v1.0). |
| `DEBUGBAR_ENABLED` | (auto) | Debug bar visible cuando `APP_DEBUG=true` y `APP_ENV=local`. |
| `IGNITION_OPEN_AI_KEY` | — | Si se desea integración de Ignition con AI (opcional). |

## B.13 Variables ausentes en `.env.example` pero usables

Las siguientes son variables Laravel estándar que el código no fuerza pero que el operador puede aprovechar:

| Variable | Default | Notas |
|---|---|---|
| `BCRYPT_ROUNDS` | `12` | Rondas de bcrypt para hashing de password. 12 es seguro y rápido; subir solo bajo análisis. |
| `OCTANE_SERVER` | — | Si se instala Laravel Octane (no es el caso en v1.0). |

## B.14 Plantilla de `.env` para producción

A modo de referencia consolidada, una plantilla mínima para producción:

```dotenv
APP_NAME="CBC Workplace"
APP_ENV=production
APP_KEY=<generar con artisan key:generate>
APP_DEBUG=false
APP_URL=https://<dominio>
APP_TIMEZONE=America/Santiago
APP_LOCALE=es

DB_CONNECTION=mysql
DB_HOST=<host>
DB_PORT=3306
DB_DATABASE=cbc_workplace
DB_USERNAME=<user>
DB_PASSWORD=<password>

CACHE_STORE=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
QUEUE_CONNECTION=redis
QUEUE_FAILED_DRIVER=database-uuids

REDIS_HOST=<host>
REDIS_PORT=6379
REDIS_PASSWORD=<password>
REDIS_DB=0
REDIS_CACHE_DB=1

MAIL_MAILER=smtp
MAIL_HOST=<smtp-host>
MAIL_PORT=587
MAIL_USERNAME=<user>
MAIL_PASSWORD=<password>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@<dominio>
MAIL_FROM_NAME="CBC Workplace"

INSTANT_ALERT_WINDOW_SECONDS=300
MAX_ALERTS_PER_MEMBER=10
DAILY_DISPATCH_HOUR=7
WEEKLY_DISPATCH_DAY=monday
WEEKLY_DISPATCH_HOUR=7

LOG_CHANNEL=stack
LOG_STACK=daily,stderr
LOG_LEVEL=warning
LOG_DAILY_DAYS=30

FILESYSTEM_DISK=public
```

> **Importante.** Este archivo contiene secretos. Manténgalo fuera del repositorio, en el secret manager (Vault, AWS Secrets Manager, GCP Secret Manager, etc.) y monte sólo el contenido sin permitir lectura por terceros en el filesystem del servidor.

## B.15 Cómo verificar el `.env` cargado

```bash
php artisan tinker
> config('database.connections.mysql.host');
> config('alerts.daily_dispatch_hour');
> config('app.debug');
```

Si una variable no llegó al config, ejecute `php artisan config:clear` para invalidar caché de configuración y reintente.

## B.16 Variables NO controlables por `.env`

Algunas configuraciones viven en archivos `config/*.php` y no se exponen como variables `.env` en esta versión:

- **Rutas del scheduler** (`app/Console/Kernel.php`).
- **Definición de `RateLimiter::for('public-search', ...)`** en `RouteServiceProvider`.
- **Trusted proxies** y middleware order.
- **Resource discovery paths** en los `PanelProvider`.

Si necesita modificarlas, edite los archivos directamente y reglas de despliegue aplican.
