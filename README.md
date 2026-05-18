<p align="center">
  <img src="public/images/logo_500px.png" alt="Lazos de Fe" width="200">
</p>

<h1 align="center">cbc-workplace · Lazos de Fe</h1>

<p align="center">
  <em>Plataforma dual: comunidad de emprendimientos (Lazos de Fe) + bolsa de trabajo (Caribbean Business Coalition).</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.3+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/Filament-3.3-FDAE4B?style=flat-square&logo=laravel&logoColor=white" alt="Filament">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.4-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Docker-Sail-2496ED?style=flat-square&logo=docker&logoColor=white" alt="Docker">
</p>

---

## Tabla de Contenidos

- [Resumen ejecutivo](#resumen-ejecutivo)
- [Arquitectura general](#arquitectura-general)
- [Stack tecnológico](#stack-tecnológico)
- [Prerequisitos](#prerequisitos)
- [Instalación y configuración](#instalación-y-configuración)
- [Servicios Docker](#servicios-docker)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Núcleo (CORE)](#núcleo-core)
  - [Paneles Filament](#paneles-filament)
  - [Autenticación y autorización](#autenticación-y-autorización)
  - [Capa de Actions](#capa-de-actions)
  - [Mailables y notificaciones](#mailables-y-notificaciones)
  - [Console y Scheduler](#console-y-scheduler)
  - [Middleware compartido](#middleware-compartido)
  - [Helpers y macros](#helpers-y-macros)
- [Infraestructura compartida entre módulos](#infraestructura-compartida-entre-módulos)
- [Módulo Emprendimientos (Lazos de Fe)](#módulo-emprendimientos-lazos-de-fe)
- [Módulo Bolsa de Trabajo](#módulo-bolsa-de-trabajo)
  - [Spec 002 — Categorías](#spec-002--categorías)
  - [Spec 003 — Organizaciones y verificación](#spec-003--organizaciones-y-verificación)
  - [Spec 004 — Perfil de candidato](#spec-004--perfil-de-candidato)
  - [Spec 005 — Ofertas de empleo](#spec-005--ofertas-de-empleo)
  - [Spec 006 — Postulaciones](#spec-006--postulaciones)
  - [Spec 007 — Búsqueda pública](#spec-007--búsqueda-pública)
  - [Spec 008 — Alertas de empleo](#spec-008--alertas-de-empleo)
  - [Spec 009 — Dashboard admin + suspensión](#spec-009--dashboard-admin--suspensión)
- [Convenciones del codebase](#convenciones-del-codebase)
- [Cross-cutting concerns](#cross-cutting-concerns)
- [Testing](#testing)
- [Comandos útiles](#comandos-útiles)
- [Estado del proyecto](#estado-del-proyecto)
- [Despliegue en producción](#despliegue-en-producción)

---

## Resumen ejecutivo

**cbc-workplace** es una aplicación Laravel 11 + Filament 3.3 que aloja **dos productos distintos** sobre una misma plataforma e infraestructura:

1. **Lazos de Fe (Emprendimientos)** — módulo original. Comunidad basada en fe donde miembros publican "emprendimientos" (proyectos / ideas de negocio) que pasan por un flujo de aprobación administrativo. Incluye sistema de patrocinio por invitación (UUID + expiración 3 días), favoritos con calificación, comentarios polimórficos y exposición pública de los emprendimientos aprobados.
2. **Bolsa de Trabajo (CBC)** — módulo nuevo, construido en 8 specs incrementales (002-009). Conecta candidatos (con perfil profesional y CV) con organizaciones empleadoras previamente verificadas. Incluye listado público sin sesión, búsqueda insensible a acentos, alertas opt-in en tres frecuencias y dashboard administrativo.

Ambos productos comparten **una misma capa de infraestructura**: el modelo `Member` (authenticatable del guard `member`), el modelo `Category` con doble scope (`Venture` | `JobListing`), `Comments` polimórfico (presente en Venture, Member, Organization, JobListing, Application, JobAlert), `Media` polimórfico, sistema de roles + permisos (`Role.perm` array consumido por `BasePolicy`), contenido editable (`Text`, `Config`), helpers (`Util`, `AppMacros`), middleware (`SecurityHeaders`) y layouts/componentes Blade reutilizados en ambas superficies públicas.

**Tres paneles Filament** sirven los dos productos:

- `app` (default, `/`) — panel público de Emprendimientos. Sin auth obligatoria; el navlink "Mis Favoritos" sólo aparece para `Member` autenticado.
- `member` (`/member`) — panel autenticado del miembro: gestiona perfil de candidato, organización, ofertas, postulaciones y alertas (Bolsa) **y** sus emprendimientos (Lazos de Fe).
- `admin` (`/admin`) — panel administrativo con grupos de navegación: Sistema, Administración, **Bolsa de Trabajo**, **Emprendimientos**.

Tres principios cross-cutting sostienen la arquitectura:

1. **Curaduría administrativa** explícita en ambos productos: las organizaciones pasan por verificación ([app/Enums/OrganizationVerificationState.php](app/Enums/OrganizationVerificationState.php)), los emprendimientos pasan por aprobación ([app/Enums/VentureApprovalState.php](app/Enums/VentureApprovalState.php)). Suspensión de organizaciones **ortogonal** al estado de verificación ([app/Models/Organization.php#L63-L66](app/Models/Organization.php#L63-L66)).
2. **Capa de Actions** ([lorisleiva/laravel-actions](https://github.com/lorisleiva/laravel-actions)) encapsula toda transición de estado + efectos colaterales en clases mono-propósito reutilizables desde controllers, Filament o jobs.
3. **Scheduler defensivo** con `withoutOverlapping()` + `onOneServer()` para sitemap horario, digests diario/semanal de alertas y expiración de listings.

---

## Arquitectura general

```
+-----------------------------------------------------------------------+
|                   CAPA PUBLICA (sin sesion)                           |
|  GET /                          GET /bolsa-de-trabajo                 |
|  (panel Venture default)        JobBoardController                    |
|  emprendimientos aprobados      (throttle 60/min si q)                |
|                                 GET /bolsa-de-trabajo/{slug}          |
|                                 JobOfferController (200/410/404)      |
|                                 GET /sitemap.xml -> SitemapController |
|  Middleware: SecurityHeaders, PublicNoSessionCookie                   |
+-----------------------+-----------------------------------------------+
                        |
+-----------------------+-----------------------------------------------+
|                    PANELES FILAMENT                                   |
|  +------------------+   +-------------------+   +------------------+  |
|  | Admin /admin     |   | Member /member    |   | App  /  (default)|  |
|  | guard: admin     |   | guard: member     |   | id: 'app'        |  |
|  | NavGroups:       |   | Recursos:         |   | authMiddleware:[]|  |
|  | - Sistema        |   | - CandidateProfile|   | nav: Inicio,     |  |
|  | - Administracion |   | - Organization    |   |      Mis Favs    |  |
|  | - Bolsa de Trab. |   | - JobListing      |   | Resources:       |  |
|  | - Emprendimientos|   | - JobAlert        |   |   VentureResource|  |
|  | 4 widgets dash   |   | - Application     |   |   (publico/list) |  |
|  | suspend banner   |   | - Favorite        |   |                  |  |
|  +--------+---------+   +---------+---------+   +--------+---------+  |
+-----------+-----------------------+----------------------+------------+
            |                       |                      |
+-----------v-----------------------v----------------------v------------+
|             AUTH / AUTHORIZATION                                      |
|  guard 'admin' -> User + Role.perm[]                                  |
|  guard 'member' -> Member (Authenticatable, MustVerifyEmail)          |
|  Policies extend BasePolicy (admin bypass via before())               |
|  Member.hasPermission() -> Role.perm[] (compartido)                   |
+-----------------------------------+-----------------------------------+
                                    |
+-----------------------------------v-----------------------------------+
|        CAPA DE ACTIONS  (lorisleiva/laravel-actions)                  |
|                                                                       |
|  EMPRENDIMIENTOS                  BOLSA DE TRABAJO                    |
|  --------------                   ----------------                    |
|  Sponsor                          Admin/                              |
|  Admin/                             OrganizationVerification          |
|    VentureApproval                  SuspendOrganization               |
|    MarkVentureAsExpired             ReactivateOrganization            |
|    VentureToggleActive              JobListingApproval                |
|  Member/                            AnonymizeMemberApplications       |
|    RequestVentureApproval         Member/                             |
|                                     RequestOrganizationVerification   |
|                                     RequestJobListingApproval         |
|                                     CloseJobListing, SubmitApplic.    |
|                                   Public/                             |
|                                     GenerateSitemapAction             |
|                                     SearchPublicOffersAction          |
|                                   Alerts/                             |
|                                     CoalesceInstantMatchAction        |
|                                     DispatchInstantAlertAction        |
|                                     BuildDigestForAlertAction         |
|                                     Dispatch{Daily,Weekly}DigestAct.  |
|                                   Raiz/ ExpireJobListings             |
+-----------------------------------+-----------------------------------+
                                    |
+-----------------------------------v-----------------------------------+
|                  CAPA ELOQUENT (LogsActivity)                         |
|                                                                       |
|  COMPARTIDO                                                           |
|  Member (Authenticatable, guard 'member')                             |
|    +-- ventures()  HasMany Venture       (EMPRENDIMIENTOS)            |
|    +-- organization() HasOne Organization (BOLSA)                     |
|    +-- candidateProfile() HasOne CandidateProfile (BOLSA)             |
|    +-- favorites() HasMany Favorite      (EMPRENDIMIENTOS)            |
|    +-- comments() MorphMany Comments                                  |
|    +-- invitation() / sponsor() Invitation                            |
|                                                                       |
|  Category (scope: 'Venture' | 'JobListing', parent_id)                |
|  Comments (polymorfico: Venture|Member|Org|JobListing|App|JobAlert)   |
|  Media (polymorfico, disk='files'); Attachment (Member only)          |
|  Role + perm[] (compartido por User y Member)                         |
|  Text / Config (contenido editable via admin)                         |
|                                                                       |
|  EMPRENDIMIENTOS              BOLSA DE TRABAJO                        |
|  -------------                ----------------                        |
|  Venture                      Organization (verif_state+suspended_*)  |
|  Invitation                   JobListing (state 0..5, _folded cols)   |
|  MemberContact                Application + ApplicationNote           |
|  Favorite (member+venture+    CandidateProfile + WorkExperience       |
|            rating)                              + Education           |
|                               JobAlert + JobAlertDispatchLog          |
|                               PublicEvent (append-only telemetry)     |
+-----------------------------------+-----------------------------------+
                                    |
   +--------------------------------+--------------------------------+
   |                                |                                |
+--v-------------------+   +--------v---------+   +------------------v-+
| QUEUE / SCHEDULER    |   |   MAIL LAYER     |   |  STORAGE::public   |
| sitemap hourly       |   | Member/          |   | candidates/photos/ |
| alerts daily 07:00   |   |   Application*   |   | candidates/cvs/    |
| alerts weekly Mon    |   |   JobAlert*      |   | applications/{id}/ |
| ExpireJobListings    |   |   Venture*       |   |   cv.{ext}         |
| (withoutOverlap +    |   | Organization/    |   | ventures (file)    |
|  onOneServer)        |   |   Suspended,     |   |                    |
| Eventos:             |   |   Verified, etc. |   |                    |
|  JobListingApproved  |   | Sponsor          |   |                    |
+----------------------+   +------------------+   +--------------------+
```

### Grafo de dependencias entre módulos y specs

```
EMPRENDIMIENTOS (Lazos de Fe — modulo base, preexiste a las specs 002-009)
  +-- Member, Role, Category(scope=Venture), Comments, Media, Favorite
  +-- Venture (estados NEW/UPDATED/APPROVAL/APPROVED/REJECTED)
  +-- Sistema de patrocinio (Invitation UUID + 3 dias)
        |
        v
BOLSA DE TRABAJO  (construida sobre la misma infra compartida)
  002 (Categorias scope=JobListing)
        \
         v
  003 (Organizaciones) --> 005 (Ofertas) --> 006 (Postulaciones)
                                |                  |
                                +--> 007 (Busqueda publica + sitemap)
                                +--> 008 (Alertas: Instant/Daily/Weekly)
                                |
  003 + 005 ----------------> 009 (Dashboard admin + Nav + Suspension)
```

**Lectura del grafo**:

- Emprendimientos provee la infra compartida que Bolsa reutiliza: `Member` como pivot autenticatable, `Category` (extendido con un segundo scope), `Comments` polimórfico, `Media` polimórfico, `Role`/`BasePolicy`.
- Categorías (002) habilitan la taxonomía consumida por listings; Organizaciones (003) provee la entidad empleadora verificable; sobre ambas se construye el dominio de JobListing (005). Postulaciones (006) dependen de listings activos. La capa pública (007) y las alertas (008) consumen el flujo `ACTIVE` en paralelo. La spec 009 refactoriza 003 (suspensión ortogonal) y agrega visibilidad agregada sobre 005/006.

---

## Stack tecnológico

| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| PHP | ^8.3 | Runtime |
| Laravel | ^11.0 | Framework backend |
| Filament | ^3.3 | Paneles admin/member/app |
| MySQL | 8.0 | Base de datos (Sail) |
| MariaDB | jammy | Base de datos (producción) |
| Tailwind CSS | ^3.4 | Estilos del frontend |
| Vite | ^4.0 | Empaquetador de assets |
| Laravel Sail | ^1.25 | Entorno Docker para desarrollo |
| Pest | ^2.34 | Framework de pruebas |
| Caddy | 2.10 | Servidor web en producción |

### Dependencias destacadas

- **lorisleiva/laravel-actions** — Encapsula transiciones de estado + side effects.
- **spatie/laravel-activitylog** — Auditoría declarativa en modelos críticos.
- **spatie/laravel-sitemap** — Generación del sitemap (justificado en spec 007 FR-023).
- **laravel/sanctum** — Autenticación de API.
- **codewithdennis/filament-select-tree** — Selección jerárquica de categorías en formularios.
- **marcogermani87/filament-captcha** — Protección CAPTCHA en registro.
- **jenssegers/agent** — Detección de dispositivo (analytics públicos).

---

## Prerequisitos

- **Docker** y **Docker Compose** instalados
- **Git** para clonar el repositorio

> No se necesita instalar PHP, Composer ni Node.js localmente — todo se ejecuta dentro de los contenedores.

---

## Instalación y configuración

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio> cbc-workplace
cd cbc-workplace
```

### 2. Instalar dependencias de Composer

```bash
docker run --rm -v "$(pwd):/app" -w /app composer:latest composer install --ignore-platform-reqs
```

### 3. Configurar el entorno

```bash
cp .env.example .env
```

Variables mínimas:

```env
APP_NAME="cbc-workplace"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025

QUEUE_CONNECTION=database

WWWGROUP=1000
WWWUSER=1000
```

### 4. Construir y levantar contenedores

```bash
docker compose build
docker compose up -d
```

### 5. Generar la clave de la aplicación

```bash
docker compose exec app php artisan key:generate
```

### 6. Ejecutar migraciones y seeders

```bash
docker compose exec app php artisan migrate --seed
```

Esto crea las tablas y ejecuta los seeders iniciales (`RoleSeeder`, `UserSeeder`, `ConfigSeeder`, `JobCategorySeeder`).

### 7. Corregir permisos de almacenamiento

```bash
docker compose exec app chown -R sail:sail /var/www/html/storage /var/www/html/bootstrap/cache
```

### 8. Verificar la instalación

- Frontend público: <http://localhost/bolsa-de-trabajo>
- Panel admin: <http://localhost/admin>
- Panel member: <http://localhost/member>
- Mailpit (correos en dev): <http://localhost:8025>
- phpMyAdmin: <http://localhost:8000>

---

## Servicios Docker

### Desarrollo (`docker-compose.yml`)

| Servicio | Contenedor | Puerto(s) | Descripción |
|----------|------------|-----------|-------------|
| App | app-lazosdefe | 80, 5173 | Aplicación Laravel con PHP 8.4 (Sail) |
| MySQL | mysql-lazosdefe | 3306 | Base de datos MySQL 8.0 |
| Mailpit | mailpit-lazosdefe | 1025 (SMTP), 8025 (UI) | Captura de correos en desarrollo |
| phpMyAdmin | phpmyadmin-lazosdefe | 8000 | Interfaz web para la base de datos |

### Producción (`docker-compose.prod.yml`)

Usa Caddy 2.10 como reverse proxy con HTTPS automático y MariaDB jammy.

---

## Estructura del proyecto

```
app/
├── Actions/                     # Casos de uso (lorisleiva/laravel-actions)
│   ├── Admin/                   # Admin: aprobacion ventures, suspension orgs, etc.
│   ├── Member/                  # Member: aplicar, solicitar verificacion, etc.
│   ├── Public/                  # Capa publica (Bolsa)
│   ├── Alerts/                  # Pipeline de alertas de empleo (Bolsa)
│   ├── ExpireJobListings.php    # Job scheduled (Bolsa)
│   └── Sponsor.php              # Patrocinio / invitaciones (Emprendimientos)
├── Console/
│   ├── Commands/                # alerts:dispatch-daily/-weekly, app:generate-sitemap
│   └── Kernel.php               # Scheduler
├── Enums/                       # VentureApprovalState, OrganizationVerificationState,
│                                # ApplicationStatus, JobListingState, JobAlertFrequency,
│                                # DispatchDecision, MembershipState, MemberType, ...
├── Events/                      # JobListingApproved, eventos publicos
├── Filament/
│   ├── Admin/                   # Recursos y widgets del panel admin (ambos modulos)
│   ├── Member/                  # Recursos del panel member (Bolsa + perfil)
│   ├── Venture/                 # Resources del panel publico /  (Emprendimientos)
│   └── Shared/                  # BaseJobListingResource, BaseVentureResource
├── Helpers/                     # AppMacros, Util, DiacriticFolder
├── Http/
│   ├── Controllers/Public/      # JobBoardController, JobOfferController, SitemapController
│   ├── Controllers/Member/      # UnsubscribeAlertController
│   ├── Middleware/              # SecurityHeaders, PublicNoSessionCookie, ThrottleOnQuery
│   ├── Requests/Public/         # SearchOffersRequest
│   └── Kernel.php
├── Lib/Traits/                  # HasFiles
├── Listeners/                   # EvaluateInstantJobAlerts
├── Mail/
│   ├── Member/                  # Application*, JobAlert*, Venture* (Bolsa + Emprendi.)
│   ├── Organization/            # Suspended, Verified, VerificationRequested, ApplicationReceived
│   ├── Dynamic.php              # Mail con template tomado de Text
│   └── Sponsor.php              # Invitacion al patrocinado (Emprendimientos)
├── Models/                      # Eloquent + Traits
├── Observers/                   # JobListingObserver (folded columns + cache bust)
├── Policies/                    # BasePolicy + concretas
├── Providers/                   # AppServiceProvider, AuthServiceProvider, Filament/* providers
├── Rules/                       # JobListingCategory
└── View/                        # Componentes Blade

database/
├── factories/
├── migrations/                  # ventures (2024_03), members (2024_02), categories (2024_08),
│                                # comments, media, attachments, organizations (2026_03),
│                                # job_listings, applications, candidate_profiles, job_alerts...
└── seeders/                     # RoleSeeder, UserSeeder, JobCategorySeeder, Spec009DemoSeeder

resources/
├── views/
│   ├── public/job-board.blade.php       # Listado publico (Bolsa)
│   ├── public/job-offer/show.blade.php  # Detalle publico (Bolsa)
│   ├── components/public/               # Componentes Blade compartidos
│   ├── filament/                        # Layouts y banners de Filament
│   └── mail/...                         # Templates de mailables (ambos modulos)

routes/
├── web.php                      # GET /bolsa-de-trabajo/{slug}, panel routes
├── public.php                   # GET /bolsa-de-trabajo (throttle)
├── api.php
└── console.php

specs_bolsa_de_trabajo/          # Specs incrementales 002-009 (referencia funcional)
tests/                           # Pest + PHPUnit
```

---

## Núcleo (CORE)

### Paneles Filament

La aplicación expone **tres paneles Filament** con guards y rutas independientes. Cada uno se registra mediante un `PanelProvider` propio:

- **Admin** ([app/Providers/Filament/AdminPanelProvider.php#L25-L94](app/Providers/Filament/AdminPanelProvider.php#L25-L94))
  - `id`: `admin`, `path`: `/admin`, `guard`: `admin`
  - Grupos de navegación: Sistema, Administración, **Bolsa de Trabajo**, Emprendimientos
  - El `NavigationGroup` "Bolsa de Trabajo" se define en [AdminPanelProvider.php#L53](app/Providers/Filament/AdminPanelProvider.php#L53), con label tomada de [lang/es/navigation.php#L4](lang/es/navigation.php#L4).
  - Incluye `EditProfile` y el plugin de CAPTCHA.

- **Member** ([app/Providers/Filament/MemberPanelProvider.php#L35-L153](app/Providers/Filament/MemberPanelProvider.php#L35-L153))
  - `id`: `member`, `path`: `/member`, `guard`: `member`
  - Habilita registro, recuperación de contraseña y verificación de email.
  - Registra un **render hook** `PanelsRenderHook::CONTENT_START` ([MemberPanelProvider.php#L48-L62](app/Providers/Filament/MemberPanelProvider.php#L48-L62)) que inyecta el banner de "organización suspendida" definido en [resources/views/filament/member/banners/organization-suspended.blade.php](resources/views/filament/member/banners/organization-suspended.blade.php) cuando `$organization->is_suspended()` retorna `true`.

- **App / Ventures** ([app/Providers/Filament/VenturePanelProvider.php#L23-L93](app/Providers/Filament/VenturePanelProvider.php#L23-L93))
  - Es el **panel default** (`->default()`), montado en `/app` con la ruta `/` redirigiendo al panel.
  - Navegación personalizada con páginas "Inicio" y "Mis Favoritos" para visitantes públicos / miembros.
  - **Sin auth obligatoria** (`->authMiddleware([])` en [línea 60](app/Providers/Filament/VenturePanelProvider.php#L60)) — el panel es accesible sin sesión.

### Autenticación y autorización

- **Modelos**:
  - [app/Models/User.php#L18-L129](app/Models/User.php#L18-L129) — usuario administrativo Filament. Implementa `Authenticatable`/`FilamentUser`. Su método `hasPermission()` delega en el array `perm` del rol asociado.
  - [app/Models/Role.php#L10-L79](app/Models/Role.php#L10-L79) — define roles administrativos; `LogsActivity` audita cambios en `perm`.
  - [app/Models/Member.php](app/Models/Member.php) — usuario del panel member (organizaciones, candidatos).

- **Guards** ([config/auth.php#L38-L87](config/auth.php#L38-L87)):
  - `admin` → provider `users` (modelo `User`)
  - `member` → provider `members` (modelo `Member`)

- **Policies**: todas extienden [app/Policies/BasePolicy.php#L10-L92](app/Policies/BasePolicy.php#L10-L92). El método `before()` otorga acceso total a admins; `viewAny`/`view`/`create`/`update`/`delete` delegan en `hasPermission()`.

- **Registro de policies**: [app/Providers/AuthServiceProvider.php#L15-L25](app/Providers/AuthServiceProvider.php#L15-L25) registra explícitamente `JobAlertPolicy`. El resto se autodescubre por convención de nombres.

### Capa de Actions

El proyecto usa **lorisleiva/laravel-actions** para encapsular cada caso de uso. Las Actions se organizan por contexto del invocador:

- `app/Actions/Admin/` — invocadas desde panel admin (verificación, suspensión, aprobación de ofertas, anonimización).
- `app/Actions/Member/` — invocadas desde panel member (solicitar verificación, enviar oferta a aprobación, cerrar oferta, postular, gestionar notas).
- `app/Actions/Public/` — usadas por la capa pública (generación de sitemap, búsqueda de ofertas).
- `app/Actions/Alerts/` — pipeline completo de alertas de empleo (coalescencia instantánea, digests, dispatch).
- `app/Actions/ExpireJobListings.php` — job scheduled para expirar ofertas vencidas.

Cada Action devuelve un resultado tipado cuando aplica (`SuspendOrganizationResult`, `ReactivateOrganizationResult`, `DispatchDecision`), facilitando el contrato con tests.

### Mailables y notificaciones

Todos los mailables implementan `ShouldQueue` y se agrupan por destinatario:

- `app/Mail/Member/` — correos al candidato: `ApplicationSubmitted`, `ApplicationStatusChanged`, `JobAlertInstantBatch`, `JobAlertDigest`.
- `app/Mail/Organization/` — correos a la organización: `Suspended`, `Verified`, `VerificationRequested`, `ApplicationReceived`.

Ejemplos clave:

- [app/Mail/Organization/Suspended.php#L15-L40](app/Mail/Organization/Suspended.php#L15-L40) — toma sólo el `Organization` en el constructor; **no expone `suspension_reason`**, coherente con FR-028b de spec 009.
- [app/Mail/Member/JobAlertInstantBatch.php#L18-L50](app/Mail/Member/JobAlertInstantBatch.php#L18-L50) — usa cola `instant` y un signed URL sin expiración para la desuscripción.
- [app/Mail/Member/JobAlertDigest.php#L19-L57](app/Mail/Member/JobAlertDigest.php#L19-L57) — el mismo patrón para los resúmenes diarios/semanales.

### Console y Scheduler

[app/Console/Kernel.php#L13-L34](app/Console/Kernel.php#L13-L34) declara las tareas recurrentes:

- `app:generate-sitemap` — horaria.
- `alerts:dispatch-daily` — diario a las 07:00 (timezone configurado).
- `alerts:dispatch-weekly` — lunes a las 07:00.

Las tres usan `withoutOverlapping()`, `onOneServer()` y `runInBackground()`, lo cual es necesario para entornos con múltiples workers/máquinas.

Comandos relevantes:

- [app/Console/Commands/GenerateSitemapCommand.php#L10-L26](app/Console/Commands/GenerateSitemapCommand.php#L10-L26) — invoca `GenerateSitemapAction`.
- [app/Console/Commands/DispatchDailyJobAlertsCommand.php](app/Console/Commands/DispatchDailyJobAlertsCommand.php) — invoca `DispatchDailyDigestAction`.
- [app/Console/Commands/DispatchWeeklyJobAlertsCommand.php](app/Console/Commands/DispatchWeeklyJobAlertsCommand.php) — invoca `DispatchWeeklyDigestAction`.

### Middleware compartido

[app/Http/Kernel.php#L16-L69](app/Http/Kernel.php#L16-L69) declara el stack global:

- [app/Http/Middleware/SecurityHeaders.php#L9-L25](app/Http/Middleware/SecurityHeaders.php#L9-L25) — inyecta CSP, HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy.
- [app/Http/Middleware/PublicNoSessionCookie.php#L26-L55](app/Http/Middleware/PublicNoSessionCookie.php#L26-L55) — strippa `Set-Cookie` y fija `Cache-Control: public` en rutas públicas (spec 007 FR-013).
- [app/Http/Middleware/ThrottleOnQuery.php#L30-L37](app/Http/Middleware/ThrottleOnQuery.php#L30-L37) — rate-limit de 60 req/min por IP **sólo cuando** la query string incluye `q` (spec 007 FR-022); no aplica a paginación ni cambios de filtros.

### Helpers y macros

- [app/Helpers/AppMacros.php#L14-L76](app/Helpers/AppMacros.php#L14-L76) — registra macros de Filament Actions: `hasAuthorization()`, `requiresAuthorization()`, `requiresPasswordConfirmation()`. Se invocan en Resources/widgets para consistencia.
- [app/Helpers/Util.php](app/Helpers/Util.php) — helpers misceláneos: `getActivityLog()`, `filamentNotification()`, `filamentNotifications()` (batch).
- [app/Helpers/DiacriticFolder.php#L27-L44](app/Helpers/DiacriticFolder.php#L27-L44) — normaliza UTF-8 a NFKD, remueve diacríticos y baja a minúscula. Idempotente. Usado por el observer de `JobListing` y la action de búsqueda pública.
- [app/Lib/Traits/HasFiles.php#L23-L57](app/Lib/Traits/HasFiles.php#L23-L57) — boot hook que borra archivos del disco al destruir el modelo.

---

## Infraestructura compartida entre módulos

Esta sección documenta las piezas que **ambos productos** (Emprendimientos y Bolsa de Trabajo) consumen. Su evolución es independiente y los cambios aquí afectan a los dos lados a la vez.

### Member (authenticatable compartido)

[app/Models/Member.php#L26](app/Models/Member.php#L26) — `class Member extends Authenticatable implements CanResetPassword, FilamentUser, HasAvatar, MustVerifyEmail`.

Es el authenticatable del **guard `member`** ([config/auth.php](config/auth.php)) y del panel `app` (Emprendimientos) + `member` (Bolsa). Pivota relaciones de ambos productos:

- `sponsor()` MorphOne [app/Models/Member.php#L65-L68](app/Models/Member.php#L65-L68) — apunta a `Invitation` (sistema de patrocinio de Emprendimientos).
- `contact()` HasOne [app/Models/Member.php#L70-L73](app/Models/Member.php#L70-L73) — `MemberContact`.
- `invitation()` BelongsTo [app/Models/Member.php#L75-L78](app/Models/Member.php#L75-L78) — la invitación con la que el miembro fue patrocinado.
- `comments()` MorphMany [app/Models/Member.php#L80-L83](app/Models/Member.php#L80-L83) — comentarios polimórficos.
- `favorites()` HasMany [app/Models/Member.php#L85-L88](app/Models/Member.php#L85-L88) — favoritos a `Venture` (Emprendimientos).
- `organization()` HasOne — la organización empleadora (Bolsa).
- `candidateProfile()` HasOne — perfil de candidato (Bolsa).
- `ventures()` HasMany — emprendimientos propios.

**Boot hook** ([app/Models/Member.php#L52-L63](app/Models/Member.php#L52-L63)): al destruir un `Member` ejecuta dos cleanups cross-módulo:

1. Detach categorías y borra media de sus ventures.
2. `AnonymizeMemberApplications::run($record)` — anonimiza PII (FR-023 de spec 006).

Esto es la única forma documentada de borrado de cuenta y deja al sistema en un estado consistente entre ambos productos.

### Category (doble scope)

[app/Models/Category.php](app/Models/Category.php) — un único modelo sirve a Emprendimientos y Bolsa via columna `scope`:

- `scope = 'Venture'` — categorías de emprendimientos. Gestionadas en [app/Filament/Admin/Resources/CategoryResource.php](app/Filament/Admin/Resources/CategoryResource.php).
- `scope = 'JobListing'` — categorías de ofertas. Gestionadas en [app/Filament/Admin/Resources/JobCategoryResource.php](app/Filament/Admin/Resources/JobCategoryResource.php) (filtra `scope='JobListing'` en línea 43).

Constraint `unique(scope, slug)` en [database/migrations/2026_03_23_000001_add_slug_icon_to_categories_table.php#L18](database/migrations/2026_03_23_000001_add_slug_icon_to_categories_table.php#L18) permite mismo slug en distinto scope (e.g. `tecnologia` puede existir en ambos).

La relación se materializa vía la **tabla pivote polimórfica `categorizables`** ([database/migrations/2024_08_20_210441_create_categorizables_table.php](database/migrations/2024_08_20_210441_create_categorizables_table.php)) que conecta `Venture` ([app/Models/Venture.php#L75-L78](app/Models/Venture.php#L75-L78), `morphToMany`) y `JobListing` con la misma estructura.

Regla de validación cross-módulo: [app/Rules/JobListingCategory.php#L19-L22](app/Rules/JobListingCategory.php#L19-L22) verifica que la categoría seleccionada en una oferta tenga `scope='JobListing'`.

### Comments polimórfico

[app/Models/Comments.php](app/Models/Comments.php) — modelo minimalista (15 líneas) con `commentable()` `morphTo()` ([línea 15-18](app/Models/Comments.php#L15-L18)).

Tabla [database/migrations/2024_05_23_101123_create_comments_table.php](database/migrations/2024_05_23_101123_create_comments_table.php) con campos `comment`, `comment_by` (nombre snapshot), `commentable_id`, `commentable_type`, `timestamps`.

**Usado por** (cada modelo expone `comments()` MorphMany + helper `addComment(string)`):

- `Venture` ([app/Models/Venture.php#L60-L63](app/Models/Venture.php#L60-L63))
- `Member` ([app/Models/Member.php#L80-L83](app/Models/Member.php#L80-L83))
- `Organization`
- `JobListing`
- `Application`
- `JobAlert`

Las Actions que toman decisiones administrativas (e.g. `VentureApproval`, `JobListingApproval`, `SuspendOrganization`) registran su trazabilidad humana llamando `$record->addComment("Decisión: …")`, complementando el `LogsActivity` automático.

### Media polimórfico + Attachment + HasFiles

- [app/Models/Media.php](app/Models/Media.php) — polimórfico (`ownable_id`, `ownable_type`). Disco default `'files'`. Campos: `file`, `caption`, `size`, `media_type`, `mime_type`, `is_mobile`, `is_active`. Usa traits `HasFiles` y `ScopeIsActive`. Lo consumen `Venture` y otros modelos como atributos visuales.
- [app/Models/Attachment.php](app/Models/Attachment.php) — específico de `Member` (FK directa, no polimórfico). Campos `name`, `file`, `filesize`, `disk`, `metadata`. Migración [database/migrations/2025_03_31_032042_create_attachments_table.php](database/migrations/2025_03_31_032042_create_attachments_table.php).
- [app/Lib/Traits/HasFiles.php#L23-L57](app/Lib/Traits/HasFiles.php#L23-L57) — trait con boot hook `deleting` que limpia archivos del disco al destruir el modelo. El modelo declara `$fileFields = ['disk' => 'public', 'file']` (ver [app/Models/Venture.php#L22-L26](app/Models/Venture.php#L22-L26)).

### Role / permisos

[app/Models/Role.php](app/Models/Role.php) — modelo con campo `perm` (cast array) que guarda la lista de permisos por nombre. Métodos:

- `isAuthorized($user, $request)` — autoriza vs nombre de ruta.
- `hasPermission($user, $uperm)` — verifica si un permiso está en el array.

Tanto `User` (admin guard) como `Member` (member guard) llaman a `hasPermission()` y delegan en `Role.perm[]`. La [app/Policies/BasePolicy.php](app/Policies/BasePolicy.php) genera la clave con `static::prefix()` (e.g. `"Admin.Category.view"`) usando reflection y la chequea contra `perm[]`.

Admin gestiona roles vía [app/Filament/Admin/Resources/RoleResource.php](app/Filament/Admin/Resources/RoleResource.php).

### Text / Config (contenido editable)

- [app/Models/Text.php](app/Models/Text.php) — textos editables (templates de correo, mensajes de UI). Campos `code`, `type`, `title`, `content`, `is_active`. Scopes `active()`, `latestText($code)`. Resource: [app/Filament/Admin/Resources/TextResource.php](app/Filament/Admin/Resources/TextResource.php).
- [app/Models/Config.php](app/Models/Config.php) — configuración flexible global (JSON). Campos `name`, `jsondata`, `jsonbkup`. Métodos `make($name)`, `getp($key, $default)`. Resource: [app/Filament/Admin/Resources/ConfigResource.php](app/Filament/Admin/Resources/ConfigResource.php).

Ambos con `LogsActivity` y `$guarded = []`.

### Helpers / utilities compartidas

- [app/Helpers/Util.php](app/Helpers/Util.php) — `filamentNotification()`, `filamentNotifications()` (batch), `logChange()` (canal `'changes'`), `getMessage()`, `run()` (try-catch helper), `formatUserDateAction()`, `isPanelActive()`, `getActivityLog()`.
- [app/Helpers/AppMacros.php#L14-L76](app/Helpers/AppMacros.php#L14-L76) — macros de Filament Actions: `hasAuthorization()`, `requiresAuthorization()`, `requiresPasswordConfirmation()` — registradas en `AppServiceProvider::boot()`.

### Componentes Blade públicos compartidos

[resources/views/components/public/](resources/views/components/public/):

- `layout.blade.php` — layout base para superficie pública (ambos módulos).
- `offer-card.blade.php` — tarjeta de oferta (Bolsa).
- `apply-cta.blade.php` — CTA de aplicación.
- `pagination-nav.blade.php` — paginación reutilizada.
- `empty-state.blade.php`, `error-state.blade.php`.

### Telemetría pública

[app/Models/PublicEvent.php](app/Models/PublicEvent.php) — tabla **append-only** para observabilidad de la superficie pública (spec 009 FR-031). Campos `kind` (enum `PublicEventKind`), `correlation_id`, `occurred_at`, `path`, `query_string`, `visitor_variant`, `page_number`, `payload` (array). Sin updates. La retención la posee spec 009. Hoy la consumen `JobBoardController` y `JobOfferController` (Bolsa); el módulo Emprendimientos no emite todavía.

---

## Módulo Emprendimientos (Lazos de Fe)

Módulo original del proyecto: comunidad basada en fe donde miembros publican "emprendimientos" (proyectos / ideas de negocio) para aprobación administrativa, los visitantes los descubren en el panel público (`/`) y los miembros autenticados los marcan como favoritos con calificación. Incluye un sistema de patrocinio: un miembro puede invitar a otro vía email con un UUID firmado válido por 3 días.

### Panel Filament Venture (público, default)

[app/Providers/Filament/VenturePanelProvider.php#L23-L93](app/Providers/Filament/VenturePanelProvider.php#L23-L93):

- `id`: `'app'` ([línea 28](app/Providers/Filament/VenturePanelProvider.php#L28)), `path`: `'/app'` ([línea 29](app/Providers/Filament/VenturePanelProvider.php#L29)), **es el panel default** (`->default()`, [línea 30](app/Providers/Filament/VenturePanelProvider.php#L30)).
- **Sin auth obligatoria** — `->authMiddleware([])` ([línea 60](app/Providers/Filament/VenturePanelProvider.php#L60)). El panel es navegable sin sesión.
- Navegación construida con `NavigationBuilder` ([línea 68-92](app/Providers/Filament/VenturePanelProvider.php#L68-L92)):
  - "Inicio" → `/` ([línea 70](app/Providers/Filament/VenturePanelProvider.php#L70)).
  - "Mis Favoritos" → ruta del panel member; `->visible(fn () => auth()->guard('member')->user())` ([línea 77](app/Providers/Filament/VenturePanelProvider.php#L77)) — sólo aparece para miembros autenticados.
- Widget: `FilamentInfoWidget` ([línea 46](app/Providers/Filament/VenturePanelProvider.php#L46)).
- Auto-descubrimiento de Resources/Pages/Widgets en `app/Filament/Venture/`.

### Resource público

[app/Filament/Venture/Resources/VentureResource.php](app/Filament/Venture/Resources/VentureResource.php):

- `protected static bool $shouldSkipAuthorization = true;` ([línea 26](app/Filament/Venture/Resources/VentureResource.php#L26)) — visible para visitantes anónimos.
- Page única: `ListVentures` ([app/Filament/Venture/Resources/VentureResource/Pages/ListVentures.php](app/Filament/Venture/Resources/VentureResource/Pages/ListVentures.php)) con ruta `/`.
- Infolist con sección "Contactenos" (datos de `member.contact`), título, URL, contenido markdown, media.

### Modelo Venture

[app/Models/Venture.php#L16](app/Models/Venture.php#L16) — `class Venture extends Model`.

**Campos** (acumulados a lo largo de varias migraciones):

| Campo | Tipo | Origen migración |
|-------|------|------------------|
| `member_id` | FK cascadeOnDelete | `2024_03_07_155410_create_ventures_table.php` |
| `title` | string 100 | idem |
| `content` | text | idem |
| `approval_state` | tinyint (cast a enum) | idem |
| `approval_by`, `approval_at`, `approval_reason` | string / datetime / text | idem |
| `expires_at` | datetime | idem |
| `is_expired`, `is_active` | bool | idem |
| `url` | string | `2024_08_13_130720_add_url_to_ventures_table.php` |
| `file` | string (disco `public`) | `2024_08_13_201607_*` |
| `preview_until` | datetime | `2025_03_17_220835_*` |
| `view_count`, `favorite_count` | unsignedBigInteger | `2025_03_31_032753_add_columns_to_ventures_table.php` |
| `tags` | json (array cast) | `2025_08_23_185451_*` |

**Casts**: [app/Models/Venture.php#L27-L35](app/Models/Venture.php#L27-L35) — `approval_state → VentureApprovalState`, datetimes, booleans, `tags → array`.

**Estados** ([app/Enums/VentureApprovalState.php](app/Enums/VentureApprovalState.php)):

| Valor | Estado | Significado |
|-------|--------|-------------|
| `0` | `NEW` | Recién creado por el miembro |
| `1` | `UPDATED` | Re-editado después de un `REJECTED` |
| `2` | `APPROVAL` | Enviado a revisión administrativa |
| `3` | `APPROVED` | Aprobado (visible al público) |
| `4` | `REJECTED` | Rechazado con motivo |

**Transiciones**:

```
NEW —[member.RequestVentureApproval]→ APPROVAL —[admin.VentureApproval]→ APPROVED|REJECTED
REJECTED —[member edit triggers resetApproval]→ UPDATED —[member.RequestVentureApproval]→ APPROVAL
APPROVED —[member.reedit()]→ NEW
```

Helpers en el modelo:

- `canRequestApproval()` ([app/Models/Venture.php#L92-L95](app/Models/Venture.php#L92-L95)) — true si `NEW|UPDATED|REJECTED`.
- `canEdit()` ([línea 97-100](app/Models/Venture.php#L97-L100)) — true en `APPROVAL` (el miembro puede editar mientras espera).
- `resetApproval()` ([línea 107-121](app/Models/Venture.php#L107-L121)).
- `isExpired()`, `reedit()`, `updateViewCount()`, `updateFavoriteCount()`, `updateCategories()`, `updateTags()`.

**Relaciones**:

- `member()` BelongsTo Member.
- `favorites()` HasMany Favorite.
- `comments()` MorphMany Comments.
- `media()` MorphMany Media.
- `categories()` MorphToMany Category (scope `Venture`).

**Boot hook** ([línea 37-48](app/Models/Venture.php#L37-L48)): al destruir, borra archivo `file` del disco `public`, cascada en media y detach de categorías.

### Modelos asociados de Emprendimientos

- [app/Models/Invitation.php](app/Models/Invitation.php) — sistema de patrocinio. Boot hook ([línea 22-26](app/Models/Invitation.php#L22-L26)) genera `uuid` (`Str::uuid()`) en `creating`. Relación `sponsor()` MorphTo ([línea 28-31](app/Models/Invitation.php#L28-L31)).
- [app/Models/Favorite.php](app/Models/Favorite.php) — pivote `Member ↔ Venture` con columna `rating` (`unsignedTinyInteger`, default 0) y constraint `unique(member_id, venture_id)` ([database/migrations/2025_03_31_031748_create_favorites_table.php](database/migrations/2025_03_31_031748_create_favorites_table.php)).
- [app/Models/MemberContact.php](app/Models/MemberContact.php) — datos de contacto del miembro (HasOne).

### Filament Admin (grupo "Emprendimientos")

- **VentureResource**: [app/Filament/Admin/Resources/VentureResource.php#L15](app/Filament/Admin/Resources/VentureResource.php#L15) — `protected static ?string $navigationGroup = 'Emprendimientos'`. Extiende [app/Filament/Shared/Resources/BaseVentureResource.php](app/Filament/Shared/Resources/BaseVentureResource.php).
  - Pages: `ListVentures`, `ViewVenture`, `EditVenture`, `EditCategories`, `EditTags`.
  - RelationManagers: `MediaRelationManager`, `CommentsRelationManager`.
  - Página `ListVentures` con tabs por estado (NEW / UPDATED / APPROVAL / APPROVED / REJECTED), cada uno con badge de count, persistencia de tab activa en session storage.
- **MemberResource**: [app/Filament/Admin/Resources/MemberResource.php#L32](app/Filament/Admin/Resources/MemberResource.php#L32) — `protected static ?string $navigationGroup = 'Emprendimientos'`. Infolist incluye `type`, `social_medias`, `invitation.sponsor.name`, y sección de aprobación de membresía.

### Actions

- [app/Actions/Sponsor.php](app/Actions/Sponsor.php) — crea `Invitation` con `expires_at = now()->addDays(3)` ([línea 17](app/Actions/Sponsor.php#L17)) vía `auth()->user()->sponsor()->create()` y envía `Mail\Sponsor` al invitado con UUID firmado ([línea 20-23](app/Actions/Sponsor.php#L20-L23)).
- [app/Actions/Admin/VentureApproval.php](app/Actions/Admin/VentureApproval.php) — recibe `Venture` + decisión (`APPROVED`/`REJECTED`):
  - Valida la decisión ([línea 18-23](app/Actions/Admin/VentureApproval.php#L18-L23)).
  - `approve()` (líneas 49-53) o `reject()` (líneas 55-59) muta el estado.
  - Setea `approval_by` (nombre del admin), `approval_at = now()`; si APPROVED → `is_active = true`.
  - Añade comment con la decisión y envía `VentureRequestApproved` o `VentureRequestDenied` al miembro ([línea 37-44](app/Actions/Admin/VentureApproval.php#L37-L44)).
- [app/Actions/Member/RequestVentureApproval.php](app/Actions/Member/RequestVentureApproval.php) — el miembro envía a aprobación. Setea `approval_state = APPROVAL`, añade comment, envía `VentureApprovalRequest` a admins (via `AppUtil::getVentureApprovers()`).
- `MarkVentureAsExpired` y `VentureToggleActive` — acciones administrativas adicionales.

### Policies

[app/Policies/VenturePolicy.php](app/Policies/VenturePolicy.php) — la mayoría de métodos están comentados; el archivo actualmente exporta sólo `reject()` ([línea 160-171](app/Policies/VenturePolicy.php#L160-L171)) que requiere permiso `'Venture.'` en el `User` y `approval_state === APPROVED`. El resto de la autorización fluye por la convención de `BasePolicy` (admin bypass via `before()` + `hasPermission()`) y por los `$shouldSkipAuthorization = true` en el Resource público.

### Mailables

- [app/Mail/Sponsor.php](app/Mail/Sponsor.php) — invitación al patrocinado.
- [app/Mail/Member/VentureRequestApproved.php](app/Mail/Member/VentureRequestApproved.php), [app/Mail/Member/VentureRequestDenied.php](app/Mail/Member/VentureRequestDenied.php), [app/Mail/Member/VentureApprovalRequest.php](app/Mail/Member/VentureApprovalRequest.php).

### Observaciones

- **Patrocinio asimétrico**: el invitante crea la invitación (UUID + 3 días) y el invitado se registra dentro de ese plazo. Si expira, la invitación queda dead-letter en BD pero la relación `Member.invitation_id` permite trazabilidad histórica.
- **Favoritos con rating**: la columna `rating` (0-255) permite calificación, no sólo bookmark. El `favorite_count` denormalizado en `Venture` se actualiza con `updateFavoriteCount()`.
- **Re-edición tras aprobación**: `reedit()` revierte a `NEW`, lo que implica que el venture deja de ser público hasta una nueva aprobación.

---

## Módulo Bolsa de Trabajo

El módulo de bolsa de trabajo se construyó en **8 specs incrementales** (002–009), cada una con su carpeta en [specs_bolsa_de_trabajo/](specs_bolsa_de_trabajo/) (plan, contratos, quickstart, data-model).

### Spec 002 — Categorías

**Spec**: [specs_bolsa_de_trabajo/002-job-categories-foundation.md](specs_bolsa_de_trabajo/002-job-categories-foundation.md)

Provee la taxonomía consumida por las ofertas. El modelo `Category` se reutiliza entre dos ámbitos (`JobListing` vs `Venture`) mediante un campo `scope`.

- **Modelo**: [app/Models/Category.php#L14-L101](app/Models/Category.php#L14-L101)
  - Soporta jerarquía padre/hijo (`parent_id`, `children_count`).
  - `LogsActivity` (línea 21) registra cambios en `name`, `slug`, `icon`, `scope`, `order`.
  - Boot hooks actualizan `child_count` y eliminan hijos en cascada.

- **Migraciones**:
  - [database/migrations/2024_08_12_180651_create_categories_table.php#L14-L22](database/migrations/2024_08_12_180651_create_categories_table.php#L14-L22)
  - [database/migrations/2026_03_23_000001_add_slug_icon_to_categories_table.php#L14-L19](database/migrations/2026_03_23_000001_add_slug_icon_to_categories_table.php#L14-L19) añade `slug` y `icon` con constraint `unique(scope, slug)`.

- **Policy**: [app/Policies/CategoryPolicy.php](app/Policies/CategoryPolicy.php) hereda directamente de `BasePolicy`.

- **Filament admin**: [app/Filament/Admin/Resources/JobCategoryResource.php#L16-L136](app/Filament/Admin/Resources/JobCategoryResource.php#L16-L136). Filtra `scope='JobListing'` (línea 43); las páginas de creación auto-generan slug si está vacío.

- **Seeder idempotente**: [database/seeders/JobCategorySeeder.php#L14-L34](database/seeders/JobCategorySeeder.php#L14-L34) crea 9 categorías (Administración y Finanzas, Tecnología e Informática, Educación y Docencia, Pastoral y Ministerio, Comunicación y Medios, Salud y Bienestar, Servicios Generales, Diseño y Creatividad, Voluntariado) con `firstOrCreate()`.

- **Factory**: [database/factories/CategoryFactory.php#L17-L26](database/factories/CategoryFactory.php#L17-L26) crea categorías scope `Venture` por defecto.

- **i18n**: [lang/es/models/category.php](lang/es/models/category.php) cubre labels, fields, placeholders y notifications.

- **Tests**: [tests/Feature/Admin/Resources/JobCategoryResourceTest.php#L15-L201](tests/Feature/Admin/Resources/JobCategoryResourceTest.php#L15-L201) — 11 casos: migración, seeder idempotente, CRUD, slug único por scope, activity log, filtrado por scope.

### Spec 003 — Organizaciones y verificación

**Spec**: [specs_bolsa_de_trabajo/003-organization-model-verification.md](specs_bolsa_de_trabajo/003-organization-model-verification.md)

Provee la entidad empleadora con flujo de verificación administrativa. Tras spec 009, la **suspensión es ortogonal** al estado de verificación: una organización puede estar `VERIFIED` y `suspended` simultáneamente.

- **Modelo**: [app/Models/Organization.php#L19](app/Models/Organization.php#L19)
  - Campos de perfil: `legal_name`, `display_name`, `type`, `denomination`, `description`, `culture_statement`, `logo`, `website`, `email_contact`, `phone`, `city`, `province`, `country`.
  - Verificación: `verification_state`, `verification_by`, `verified_at`, `verification_reason`, `is_active`.
  - Suspensión ortogonal: `suspended_at`, `suspended_by`, `suspension_reason` (líneas 44-45, 97-99).
  - Métodos: `is_suspended()` (línea 63 — chequea `suspended_at !== null`), `canBeSuspended()`, `canBeReactivated()`, `profileShouldHidePublicData()`, `scopeExcludingSuspended()`.

- **Enum**: [app/Enums/OrganizationVerificationState.php#L9](app/Enums/OrganizationVerificationState.php#L9) — sólo dos casos: `PENDING = 0`, `VERIFIED = 1`. El caso `SUSPENDED = 2` **fue removido** en cleanup PR #26 (commit `0176383`).

- **Migraciones**:
  - [database/migrations/2026_03_23_000002_create_organizations_table.php#L11](database/migrations/2026_03_23_000002_create_organizations_table.php#L11) — tabla base con índice en `verification_state`.
  - [database/migrations/2026_05_17_000001_add_suspension_columns_to_organizations.php#L14](database/migrations/2026_05_17_000001_add_suspension_columns_to_organizations.php#L14) — añade las tres columnas ortogonales con índice; el backfill (líneas 23-33) migra filas legacy `verification_state = 2` al nuevo flag preservando reason y timestamp.

- **Policy**: [app/Policies/OrganizationPolicy.php#L15](app/Policies/OrganizationPolicy.php#L15)
  - `update()` bloquea miembros si la organización está suspendida (línea 18).
  - `suspend()` requiere `canBeSuspended()` (línea 33).
  - `reactivate()` requiere `canBeReactivated()` (línea 42).
  - `organizationFrozenForMember()` (línea 56) es el helper que `JobListingPolicy`, `ApplicationPolicy` y otros consumen para "congelar" la organización.

- **Actions**:
  - [app/Actions/Admin/SuspendOrganization.php#L24](app/Actions/Admin/SuspendOrganization.php#L24) — transaccional. Setea las 3 columnas de suspensión, cierra en cascada las ofertas `ACTIVE → CLOSED`, normaliza el reason (trim, null si blanco), envía `Suspended` mail a cada admin, registra activity log.
  - [app/Actions/Admin/ReactivateOrganization.php#L16](app/Actions/Admin/ReactivateOrganization.php#L16) — limpia las 3 columnas a null; **no** reactiva las ofertas que fueron cerradas en cascada.
  - [app/Actions/Admin/OrganizationVerification.php#L23](app/Actions/Admin/OrganizationVerification.php#L23) — sólo acepta decisión `VERIFIED`; el comentario en las líneas 18-21 explica por qué `SUSPENDED` ya no es un caso válido.
  - [app/Actions/Member/RequestOrganizationVerification.php#L17](app/Actions/Member/RequestOrganizationVerification.php#L17) — el miembro solicita verificación; envía `VerificationRequested` a todos los admins activos.

- **Filament**:
  - Admin: [app/Filament/Admin/Resources/OrganizationResource.php#L15](app/Filament/Admin/Resources/OrganizationResource.php#L15). Infolist con cuatro secciones (general, contact, verification, suspension state). Filtros por estado y tipo. Acciones `SuspendOrganization` y `ReactivateOrganization` (línea 184).
  - Member: [app/Filament/Member/Resources/OrganizationResource.php#L15](app/Filament/Member/Resources/OrganizationResource.php#L15). Form con secciones general / contact / location. Query scoped a `member_id = auth('member')->id()`.

- **Mailables**:
  - [app/Mail/Organization/Suspended.php#L15](app/Mail/Organization/Suspended.php#L15) — template `mail.organization.suspended`.
  - [app/Mail/Organization/Verified.php#L12](app/Mail/Organization/Verified.php#L12) — template `mail.organization.verified`.
  - [app/Mail/Organization/VerificationRequested.php#L12](app/Mail/Organization/VerificationRequested.php#L12) — template `mail.organization.verification-requested`.

- **Tests**:
  - [tests/Unit/OrganizationIsSuspendedTest.php#L13](tests/Unit/OrganizationIsSuspendedTest.php#L13) — 7 casos. La línea 66 verifica explícitamente que la suspensión es ortogonal a `verification_state` (org puede estar VERIFIED + suspended simultáneamente).
  - [tests/Feature/Admin/Actions/SuspendOrganizationTest.php#L40](tests/Feature/Admin/Actions/SuspendOrganizationTest.php#L40) — cascada de cierre, preservación de `verification_state` (línea 62), normalización de reason, short-circuit si ya suspendida, resiliencia a fallo de mail, un correo por admin.
  - [tests/Feature/Admin/Actions/OrganizationVerificationTest.php#L139](tests/Feature/Admin/Actions/OrganizationVerificationTest.php#L139) — verifica que pasar el valor legacy `2` (SUSPENDED) lanza `ValueError` desde `enum::from()`.
  - [database/factories/OrganizationFactory.php#L37](database/factories/OrganizationFactory.php#L37) — factory states: `pending()`, `verified()`, `suspended()`, `verifiedSuspended()`, `pendingSuspended()`. El comentario en línea 57 deja explícito que `suspended()` no muta `verification_state`.

### Spec 004 — Perfil de candidato

**Spec**: [specs_bolsa_de_trabajo/004-candidate-profile-experience-education.md](specs_bolsa_de_trabajo/004-candidate-profile-experience-education.md)

Permite al miembro construir un perfil profesional con experiencia laboral, educación y CV.

- **Modelos**:
  - [app/Models/CandidateProfile.php#L14-L74](app/Models/CandidateProfile.php#L14-L74) — campos `headline`, `summary`, `city`, `province`, `phone`, `photo`, `cv_path`, `faith_statement`, `is_visible`. Relación 1:1 con `Member`, has-many a `WorkExperience` y `Education`. `LogsActivity` activo. Boot hook elimina los archivos del storage al destruir el perfil.
  - [app/Models/WorkExperience.php#L12-L49](app/Models/WorkExperience.php#L12-L49) — campos `company`, `position`, `description`, `start_date`, `end_date` (nullable), `is_current`.
  - [app/Models/Education.php#L12-L49](app/Models/Education.php#L12-L49) — campos `institution`, `degree`, `field_of_study`, `graduation_year` (nullable), `is_in_progress`. Tabla `educations`.

- **Migraciones**:
  - [database/migrations/2026_03_23_000003_create_candidate_profiles_table.php](database/migrations/2026_03_23_000003_create_candidate_profiles_table.php) — FK `member_id` único.
  - [database/migrations/2026_03_23_000004_create_work_experiences_table.php](database/migrations/2026_03_23_000004_create_work_experiences_table.php) — índice compuesto `(candidate_profile_id, start_date)`.
  - [database/migrations/2026_03_23_000005_create_educations_table.php](database/migrations/2026_03_23_000005_create_educations_table.php).

- **Policy**: [app/Policies/CandidateProfilePolicy.php#L9-L21](app/Policies/CandidateProfilePolicy.php#L9-L21) — `update()` permite si el usuario es Member y propietario (`member_id` coincide).

- **Filament (member panel)**: [app/Filament/Member/Resources/CandidateProfileResource.php#L15-L153](app/Filament/Member/Resources/CandidateProfileResource.php#L15-L153)
  - Form en tres secciones: Professional (headline, summary, faith_statement), Location (city, province, phone), Files (photo image/2MB, cv_path PDF/5MB, disco `public`).
  - RelationManagers para `WorkExperience` ([…/RelationManagers/WorkExperiencesRelationManager.php#L11-L86](app/Filament/Member/Resources/CandidateProfileResource/RelationManagers/WorkExperiencesRelationManager.php#L11-L86)) y `Education` ([…/RelationManagers/EducationsRelationManager.php#L11-L84](app/Filament/Member/Resources/CandidateProfileResource/RelationManagers/EducationsRelationManager.php#L11-L84)).
  - Query filtrada por `auth('member')->id()`. Página `Create` redirige si ya existe perfil.

- **Filament (admin panel)**: [app/Filament/Admin/Resources/CandidateProfileResource.php#L14-L50](app/Filament/Admin/Resources/CandidateProfileResource.php#L14-L50) — vista read-only.

- **Storage**:
  - CV → `candidates/cvs/` (disco `public`, PDF, max 5MB).
  - Foto → `candidates/photos/` (disco `public`, max 2MB).
  - Auto-limpieza en el `deleting` hook del modelo (líneas 34-44 de `CandidateProfile.php`).

- **Tests**:
  - [tests/Feature/Member/Resources/CandidateProfileResourceTest.php](tests/Feature/Member/Resources/CandidateProfileResourceTest.php) — render create, fields requeridos, redirect si ya existe, edit, RelationManagers (vía Livewire).
  - [tests/Feature/Admin/Resources/CandidateProfileResourceTest.php](tests/Feature/Admin/Resources/CandidateProfileResourceTest.php) — vista admin.

### Spec 005 — Ofertas de empleo

**Spec**: [specs_bolsa_de_trabajo/005-job-listing-management.md](specs_bolsa_de_trabajo/005-job-listing-management.md)

Encapsula el ciclo de vida completo de las ofertas: creación en borrador, envío a revisión, aprobación administrativa, expiración automática y cierre manual.

- **Estados** (`JobListingState` enum):

| Valor | Estado | Transiciones desde |
|-------|--------|--------------------|
| `0` | `DRAFT` | Origen al crear |
| `1` | `PENDING` | `DRAFT` o `REJECTED` (via `RequestJobListingApproval`) |
| `2` | `ACTIVE` | `PENDING` (admin approve) |
| `3` | `REJECTED` | `PENDING` (admin reject) |
| `4` | `CLOSED` | `ACTIVE` (cierre manual o suspensión de org en cascada) |
| `5` | `EXPIRED` | `ACTIVE` (deadline pasado, scheduled job) |

- **Modelo**: [app/Models/JobListing.php#L21-L167](app/Models/JobListing.php#L21-L167)
  - Campos: `title`, `description`, `requirements`, `contract_type`, `work_modality`, `city`, `province`, `salary_min`, `salary_max`, `currency`, `application_deadline`, `screening_questions` (max 5).
  - Relaciones: `organization`, `member`, `categories` (morphToMany), `comments` (morphMany), `applications`.
  - Scopes: `ofMember()`, `ofOrganization()`, `active()`.
  - `canEdit()` (DRAFT|REJECTED), `canSubmit()` (DRAFT|REJECTED), `isExpired()` (líneas 143-145, compara state + `application_deadline`).
  - Slug auto-generado en boot (líneas 53-56, 60-62).

- **Migraciones**:
  - [database/migrations/2026_03_23_000006_create_job_listings_table.php#L11-L44](database/migrations/2026_03_23_000006_create_job_listings_table.php#L11-L44) — incluye `view_count`, `approval_at`, `approval_reason`, `closed_at`. Índices: `state`, `(organization_id, state)`, `application_deadline`.
  - [database/migrations/2026_05_07_000001_add_folded_columns_to_job_listings.php](database/migrations/2026_05_07_000001_add_folded_columns_to_job_listings.php) — añade `title_folded`, `description_folded`, `city_folded` para búsqueda accent-insensitive (spec 007).

- **Policy** ([app/Policies/JobListingPolicy.php](app/Policies/JobListingPolicy.php)):
  - `create()` (líneas 41-52) — requiere `verification_state === VERIFIED` **y** `organizationFrozenFor()` falso (línea 48).
  - `update()` (líneas 54-65) — bloquea si la organización está suspendida (línea 57).
  - `delete()` (líneas 67-78), `close()` (líneas 80-92), `submitForApproval()` (líneas 94-105) — todos chequean suspensión.
  - `organizationFrozenFor()` (líneas 113-116) delega a `OrganizationPolicy::organizationFrozenForMember()`.

- **Filament**:
  - Base compartida: [app/Filament/Shared/Resources/BaseJobListingResource.php#L20-L305](app/Filament/Shared/Resources/BaseJobListingResource.php#L20-L305) — form, infolist, table compartidos entre admin y member.
  - Member: [app/Filament/Member/Resources/JobListingResource.php#L17-L66](app/Filament/Member/Resources/JobListingResource.php#L17-L66). La acción "close-job-listing" (líneas 24-35) sólo aparece si `state=ACTIVE` y la organización no está suspendida (línea 31: `! (auth('member')->user()?->organization?->is_suspended() ?? false)`).
  - [app/Filament/Member/Resources/JobListingResource/Pages/CreateJobListing.php#L14-L37](app/Filament/Member/Resources/JobListingResource/Pages/CreateJobListing.php#L14-L37) — en `mount()` verifica `verification_state === VERIFIED` y redirige si no.
  - [app/Filament/Member/Resources/JobListingResource/Pages/ListJobListings.php#L1-L22](app/Filament/Member/Resources/JobListingResource/Pages/ListJobListings.php#L1-L22) — `CreateAction` se oculta si la org está suspendida (línea 18).

- **Actions**:
  - [app/Actions/ExpireJobListings.php#L14-L30](app/Actions/ExpireJobListings.php#L14-L30) — busca ofertas `ACTIVE` con `application_deadline < now()`, las pasa a `EXPIRED`.
  - [app/Actions/Member/CloseJobListing.php#L14-L29](app/Actions/Member/CloseJobListing.php#L14-L29) — valida `state === ACTIVE`, setea `closed_at`.
  - [app/Actions/Member/RequestJobListingApproval.php#L18-L46](app/Actions/Member/RequestJobListingApproval.php#L18-L46) — valida `verification_state === VERIFIED`, `canSubmit()`, deadline futuro. Cambia a `PENDING`, notifica admins.
  - [app/Actions/Admin/JobListingApproval.php#L17-L72](app/Actions/Admin/JobListingApproval.php#L17-L72) — `approve()` setea `state=ACTIVE`, `published_at=now()`. `reject()` setea `state=REJECTED`. Ambas envían mail al member.

- **Tests**: `tests/Feature/Member/Resources/JobListingResourceTest.php`, `tests/Feature/Member/Actions/CloseJobListingTest.php`, `tests/Feature/Member/Actions/RequestJobListingApprovalTest.php`, `tests/Feature/Actions/ExpireJobListingsTest.php`, `tests/Feature/Admin/Resources/JobListingResourceTest.php`, `tests/Feature/Admin/Actions/JobListingApprovalTest.php`.

### Spec 006 — Postulaciones

**Spec**: [specs_bolsa_de_trabajo/006-applications.md](specs_bolsa_de_trabajo/006-applications.md)

Modela el flujo de aplicación a una oferta, con copia inmutable (snapshot) del CV y datos del candidato al momento de aplicar.

- **Estados** (`ApplicationStatus` enum, [app/Enums/ApplicationStatus.php#L7-L42](app/Enums/ApplicationStatus.php#L7-L42)):

| Valor | Estado | Terminal |
|-------|--------|----------|
| `0` | `RECEIVED` | no |
| `1` | `IN_REVIEW` | no |
| `2` | `INTERVIEW` | no |
| `3` | `REJECTED` | sí |
| `4` | `ACCEPTED` | sí |

`canTransitionTo()` sólo permite movimientos hacia adelante (no se puede volver a `RECEIVED`).

- **Modelos**:
  - [app/Models/Application.php#L17-L100](app/Models/Application.php#L17-L100) — campos `cover_letter`, `screening_answers`, `status`, `submitted_at`, `last_status_changed_at`, `last_status_changed_by`, `anonymized_at`. Relaciones a `JobListing`, `Member`, `CandidateProfile`, `ApplicationNote` y `Comments` (morphMany). `LogsActivity` audita `status`, `last_status_changed_by`, `anonymized_at`.
  - [app/Models/ApplicationNote.php#L12-L43](app/Models/ApplicationNote.php#L12-L43) — `body` (text, max 2000), `author_name_snapshot`, `author_user_id`.

- **Migraciones**:
  - [database/migrations/2026_04_27_000001_create_applications_table.php#L11-L38](database/migrations/2026_04_27_000001_create_applications_table.php#L11-L38) — campos snapshot: `cv_snapshot_path`, `cv_snapshot_filename`, `candidate_name_snapshot`, `candidate_email_snapshot`. Unique `(job_listing_id, member_id)`.
  - [database/migrations/2026_04_27_000002_create_application_notes_table.php#L11-L23](database/migrations/2026_04_27_000002_create_application_notes_table.php#L11-L23) — incluye `author_name_snapshot` para preservar el autor aunque el `User` cambie de nombre o sea borrado.

- **Policies**:
  - [app/Policies/ApplicationPolicy.php#L17-L82](app/Policies/ApplicationPolicy.php#L17-L82) — `view()` permite al candidato o al propietario del listing. `create()` requiere `CandidateProfile` y que la organización no esté frozen. `update()` (cambio de estado) sólo el propietario del listing. `delete()` está negado.
  - [app/Policies/ApplicationNotePolicy.php#L18-L102](app/Policies/ApplicationNotePolicy.php#L18-L102) — notas privadas, sólo accesibles al propietario del listing.

- **Actions**:
  - [app/Actions/Member/SubmitApplication.php#L23-L133](app/Actions/Member/SubmitApplication.php#L23-L133) — transaccional. Valida (profile, listing activo, deadline futuro, no duplicado), crea `Application` con `status=RECEIVED`, **copia el CV** a `applications/{id}/cv.{ext}` en el disco `public` (`copyCvSnapshot()`, líneas 112-132), envía `ApplicationSubmitted` al candidato y `ApplicationReceived` al dueño de la oferta.
  - [app/Actions/Member/UpdateApplicationStatus.php#L16-L46](app/Actions/Member/UpdateApplicationStatus.php#L16-L46) — valida transición vía `canTransitionTo()`, actualiza `last_status_changed_at/_by`, envía `ApplicationStatusChanged`.
  - [app/Actions/Member/AddApplicationNote.php#L15-L44](app/Actions/Member/AddApplicationNote.php#L15-L44), [app/Actions/Member/UpdateApplicationNote.php#L13-L36](app/Actions/Member/UpdateApplicationNote.php#L13-L36), [app/Actions/Member/DeleteApplicationNote.php#L13-L26](app/Actions/Member/DeleteApplicationNote.php#L13-L26).
  - [app/Actions/Admin/AnonymizeMemberApplications.php#L15-L56](app/Actions/Admin/AnonymizeMemberApplications.php#L15-L56) — al borrar la cuenta del candidato, elimina el CV del disco, nullea snapshots y `member_id`/`candidate_profile_id`, setea `anonymized_at`.

- **Filament**:
  - Admin: [app/Filament/Admin/Resources/ApplicationResource.php#L14-L50](app/Filament/Admin/Resources/ApplicationResource.php#L14-L50) — read-only para auditoría.
  - Member: [app/Filament/Member/Resources/ApplicationResource.php#L15-L60](app/Filament/Member/Resources/ApplicationResource.php#L15-L60) — vista del candidato sobre sus propias postulaciones (query filtrada por `auth('member')->id()`).

- **Mailables**:
  - [app/Mail/Member/ApplicationSubmitted.php#L12-L42](app/Mail/Member/ApplicationSubmitted.php#L12-L42) — al candidato.
  - [app/Mail/Organization/ApplicationReceived.php#L12-L43](app/Mail/Organization/ApplicationReceived.php#L12-L43) — al dueño del listing.
  - [app/Mail/Member/ApplicationStatusChanged.php#L17-L50](app/Mail/Member/ApplicationStatusChanged.php#L17-L50) — template dinámico según `current_status`.

- **Tests**: `tests/Unit/ApplicationStatusTest.php`, `tests/Feature/Member/Actions/SubmitApplicationTest.php`, `tests/Feature/Member/Actions/UpdateApplicationStatusTest.php`, `tests/Feature/Member/Actions/ApplicationNotesTest.php`, `tests/Feature/Admin/Actions/AnonymizeMemberApplicationsTest.php`.

### Spec 007 — Búsqueda pública

**Spec**: [specs_bolsa_de_trabajo/007-public-job-search-filtering.md](specs_bolsa_de_trabajo/007-public-job-search-filtering.md)

Expone el listado anónimo en `/bolsa-de-trabajo` con búsqueda insensible a acentos, filtros multi-select, paginación, sitemap y rate-limit selectivo.

- **Routes**:
  - [routes/public.php#L26-L28](routes/public.php#L26-L28) — `GET /bolsa-de-trabajo` (controller invocable `JobBoardController`) con middleware `ThrottleOnQuery`.
  - [routes/web.php#L38-L40](routes/web.php#L38-L40) — `GET /bolsa-de-trabajo/{slug}` (`JobOfferController::show`) con middleware `web` para detección de variante del CTA (FR-019).

- **Controllers**:
  - [app/Http/Controllers/Public/JobBoardController.php#L27-L54](app/Http/Controllers/Public/JobBoardController.php#L27-L54) — `__invoke()` resuelve keyword/filtros vía `SearchPublicOffersAction`, renderiza la vista con ofertas paginadas (20/página), ciudades dinámicas y categorías activas. Emite eventos públicos (`PageView`, `KeywordQuery`, `FilterChange`).
  - [app/Http/Controllers/Public/JobOfferController.php#L30-L65](app/Http/Controllers/Public/JobOfferController.php#L30-L65) — resuelve oferta por slug. Distingue:
    - `200` para `ACTIVE`,
    - `410 Gone` para `EXPIRED`/`CLOSED`,
    - `404 Not Found` si no existe.
    Emite `DetailOpen` y retorna `Cache-Control: public, max-age=300, stale-while-revalidate=3600`.

- **Vista**: [resources/views/public/job-board.blade.php#L1-L238](resources/views/public/job-board.blade.php#L1-L238)
  - Campo `q` con debounced submit de 300ms (líneas 210-237).
  - Filtros multi-select: categoría (checkboxes), modalidad (`work_mode`), tipo contrato, ciudad (scroll con `max-h-40`).
  - Sort `recent` (default) / `deadline`.
  - `noindex` meta en variantes filtradas y `page>1` (línea 11) — FR-024, FR-027.
  - `canonical` link a `/bolsa-de-trabajo` (línea 20).
  - Empty states con CTA condicional (líneas 182-194).

- **Búsqueda accent-insensitive**:
  - [app/Helpers/DiacriticFolder.php#L27-L44](app/Helpers/DiacriticFolder.php#L27-L44) — pipeline de NFKD + remove combining marks + lowercase.
  - [app/Actions/Public/SearchPublicOffersAction.php#L73-L90](app/Actions/Public/SearchPublicOffersAction.php#L73-L90) — aplica LIKE sobre `title_folded` y `description_folded` con escape de `%` y `_`.
  - [app/Observers/JobListingObserver.php#L20-L35](app/Observers/JobListingObserver.php#L20-L35) — recalcula `title_folded`, `description_folded`, `city_folded` en `saving()`.
  - [database/migrations/2026_05_07_000001_add_folded_columns_to_job_listings.php#L13-L16](database/migrations/2026_05_07_000001_add_folded_columns_to_job_listings.php#L13-L16) — añade las columnas.

- **Validación**: [app/Http/Requests/Public/SearchOffersRequest.php#L43-L86](app/Http/Requests/Public/SearchOffersRequest.php#L43-L86) — valida `q` (≤200), `category[]` (`exists:categories,id`), `work_mode[]` (enum), `contract[]` (enum), `city[]` (≤100), `sort` (`recent|deadline`), `page`. Expone `normalized()` con keyword/filters/sort/page listos para la action.

- **Sitemap**:
  - [app/Http/Controllers/Public/SitemapController.php#L36-L62](app/Http/Controllers/Public/SitemapController.php#L36-L62) — sirve `/sitemap.xml` con `Cache-Control: public, max-age=3600`. Si el archivo falta, dispara `GenerateSitemapAction` bajo lock con dedup y responde `503 Retry-After: 60`.
  - [app/Actions/Public/GenerateSitemapAction.php#L40-L66](app/Actions/Public/GenerateSitemapAction.php#L40-L66) — escribe el sitemap a un archivo temporal y hace `rename` atómico, evitando lecturas parciales.

- **Cache**:
  - [app/Observers/JobListingObserver.php#L37-L57](app/Observers/JobListingObserver.php#L37-L57) — bust de cache key `public.cities` cuando cambian `state`, `organization`, `application_deadline` o `city` (FR-010c).

- **Rate limit**:
  - [app/Http/Middleware/ThrottleOnQuery.php#L30-L37](app/Http/Middleware/ThrottleOnQuery.php#L30-L37) — sólo si `q` presente.

- **Tests**: [tests/Feature/Public/BrowseJobBoardTest.php](tests/Feature/Public/BrowseJobBoardTest.php), [tests/Feature/Public/KeywordSearchTest.php](tests/Feature/Public/KeywordSearchTest.php) (acento `diseñador=disenador`), [tests/Feature/Public/FilterTest.php](tests/Feature/Public/FilterTest.php) (OR within type, AND across types), [tests/Feature/Public/CityFilterTest.php](tests/Feature/Public/CityFilterTest.php), [tests/Feature/Public/SortTest.php](tests/Feature/Public/SortTest.php), [tests/Feature/Public/RateLimitTest.php](tests/Feature/Public/RateLimitTest.php), [tests/Feature/Public/SitemapTest.php](tests/Feature/Public/SitemapTest.php), [tests/Feature/Public/OfferDetailTest.php](tests/Feature/Public/OfferDetailTest.php), [tests/Feature/Public/PaginationTest.php](tests/Feature/Public/PaginationTest.php).

### Spec 008 — Alertas de empleo

**Spec**: [specs_bolsa_de_trabajo/008-notifications-job-alerts.md](specs_bolsa_de_trabajo/008-notifications-job-alerts.md)

Permite al miembro suscribir criterios y recibir notificaciones por email en tres frecuencias.

- **Modelos**:
  - [app/Models/JobAlert.php#L19](app/Models/JobAlert.php#L19) — `frequency` (`JobAlertFrequency` enum), `city`, `category_id`, `active` (bool). Scopes: `active()`, `ofMember()`, `withFrequency()`, `ofActiveMember()`.
  - [app/Models/JobAlertDispatchLog.php#L12](app/Models/JobAlertDispatchLog.php#L12) — registro de envíos con `window_key`, `decision` (`DispatchDecision` enum), `matched_offer_ids`, `correlation_id` (UUID). **Sin timestamps**; unique `(job_alert_id, window_key)` garantiza dedup atómico.

- **Migraciones**:
  - [database/migrations/2026_05_11_000001_create_job_alerts_table.php#L13](database/migrations/2026_05_11_000001_create_job_alerts_table.php#L13) — índices compuestos `(active, frequency, category_id, city_folded)` y unique `(member_id, category_id, city_folded, frequency)`.
  - [database/migrations/2026_05_11_000002_create_job_alert_dispatch_logs_table.php#L13](database/migrations/2026_05_11_000002_create_job_alert_dispatch_logs_table.php#L13).

- **Policy**: [app/Policies/JobAlertPolicy.php#L12](app/Policies/JobAlertPolicy.php#L12) — todas las operaciones restringidas al `Member` owner.

- **Pipeline instantáneo**:

  1. Al aprobar una oferta (`JobListingApproval::approve()`), se dispara el evento `JobListingApproved`.
  2. [app/Listeners/EvaluateInstantJobAlerts.php#L15](app/Listeners/EvaluateInstantJobAlerts.php#L15) — listener `ShouldQueue` (cola `instant`). Busca `JobAlert` con `frequency=Instant`, `active=true`, que coincidan en categoría + `city_folded`. Despacha `CoalesceInstantMatchAction` por cada alerta.
  3. [app/Actions/Alerts/CoalesceInstantMatchAction.php#L14](app/Actions/Alerts/CoalesceInstantMatchAction.php#L14) — agrupa aprobaciones cercanas en ventanas de 5 minutos (configurable vía `INSTANT_ALERT_WINDOW_SECONDS`) usando cache locks.
  4. [app/Actions/Alerts/DispatchInstantAlertAction.php#L22](app/Actions/Alerts/DispatchInstantAlertAction.php#L22) — el envío real.
     - **60 s lookback grace** ([línea 62](app/Actions/Alerts/DispatchInstantAlertAction.php#L62)) — `subSeconds(60)` para FR-022: cuando `JobListingApproval::approve()` setea `published_at` y el listener corre con cierta latencia, 60 s cubre el jitter de la cola. (Hotfix PR #23.)
     - **Dedup counter** ([líneas 95-99](app/Actions/Alerts/DispatchInstantAlertAction.php#L95-L99)) — `UniqueConstraintViolationException` al insertar en `job_alert_dispatch_logs` se traduce a `DispatchDecision::AlreadySent` (no requeue, no re-emit). El dispatcher tallía `dedup_absorbed` distinto de `sent`. (Hotfix PR #24.)

- **Pipeline de digests**:
  - [app/Actions/Alerts/DispatchDailyDigestAction.php#L16](app/Actions/Alerts/DispatchDailyDigestAction.php#L16) — itera alertas `active` con `frequency=Daily` de miembros activos. Ventana `[now-1d, now]`, windowKey `daily:YYYY-MM-DD`. Tallía `sent`/`suppressed`/`dedup_absorbed`.
  - [app/Actions/Alerts/DispatchWeeklyDigestAction.php#L16](app/Actions/Alerts/DispatchWeeklyDigestAction.php#L16) — análogo con ventana `[now-1w, now]` y windowKey `weekly:o-W` (ISO year+week).
  - [app/Actions/Alerts/BuildDigestForAlertAction.php#L19](app/Actions/Alerts/BuildDigestForAlertAction.php#L19) — resuelve los matches y crea el dispatch log. Las [líneas 50-56](app/Actions/Alerts/BuildDigestForAlertAction.php#L50-L56) implementan el mismo dedup `AlreadySent` (referencia explícita a "spec 008 T075 Finding 2 follow-up").

- **Scheduler**:
  - [app/Console/Kernel.php#L20](app/Console/Kernel.php#L20) — `alerts:dispatch-daily` a las 07:00.
  - [app/Console/Kernel.php#L27](app/Console/Kernel.php#L27) — `alerts:dispatch-weekly` lunes 07:00.

- **Mailables**:
  - [app/Mail/Member/JobAlertInstantBatch.php#L18](app/Mail/Member/JobAlertInstantBatch.php#L18) — cola `instant`; signed URL **sin expiry** (FR-028a) en líneas 44-50.
  - [app/Mail/Member/JobAlertDigest.php#L19](app/Mail/Member/JobAlertDigest.php#L19) — daily/weekly; signed URL idem en líneas 51-57.

- **Unsubscribe**: [app/Http/Controllers/Member/UnsubscribeAlertController.php#L15](app/Http/Controllers/Member/UnsubscribeAlertController.php#L15) — valida el match `(member_id, alert_id)` y desactiva la alerta de forma idempotente.

- **Filament**: [app/Filament/Member/Resources/JobAlertResource.php#L22](app/Filament/Member/Resources/JobAlertResource.php#L22) — CRUD con form (categoría, ciudad, frecuencia, toggle activo), table con badges, query scoped a `auth('member')`.

- **Tests**: `tests/Feature/Alerts/DispatchInstantAlertActionTest.php` (incluye verificación del 60 s grace y race conditions), `tests/Feature/Alerts/BuildDigestForAlertActionTest.php` (dedup), `tests/Feature/Member/CreateJobAlertActionTest.php`, `tests/Feature/Member/UnsubscribeAlertControllerTest.php`.

### Spec 009 — Dashboard admin + suspensión

**Spec**: [specs_bolsa_de_trabajo/009-admin-dashboard-widgets.md](specs_bolsa_de_trabajo/009-admin-dashboard-widgets.md)

Tres entregas en una spec:

1. **Cuatro widgets** en el dashboard del admin.
2. Reorganización de navegación con el grupo **"Bolsa de Trabajo"**.
3. Refactor de la **suspensión de organizaciones** de enum case a flags ortogonales.

#### Widgets

- [app/Filament/Admin/Widgets/JobBoardStatsOverview.php#L18-L72](app/Filament/Admin/Widgets/JobBoardStatsOverview.php#L18-L72) — `StatsOverview` con 4 KPIs: candidatos, organizaciones (total + verificadas), ofertas activas, postulaciones últimas 24 h.
- [app/Filament/Admin/Widgets/PendingOrganizationVerificationsWidget.php#L17-L44](app/Filament/Admin/Widgets/PendingOrganizationVerificationsWidget.php#L17-L44) — `TableWidget` que filtra `verification_state = PENDING` **y** `whereNull('suspended_at')` (línea 41), garantizando que el listado refleja sólo organizaciones que pueden ser verificadas hoy.
- [app/Filament/Admin/Widgets/PendingJobListingApprovalsWidget.php#L17-L42](app/Filament/Admin/Widgets/PendingJobListingApprovalsWidget.php#L17-L42) — `TableWidget` con ofertas en `PENDING`, límite 10.
- [app/Filament/Admin/Widgets/RecentApplicationsWidget.php#L16-L40](app/Filament/Admin/Widgets/RecentApplicationsWidget.php#L16-L40) — últimas 10 postulaciones ordenadas por `submitted_at DESC`.

#### Navegación

- [lang/es/navigation.php#L4](lang/es/navigation.php#L4) — `'bolsa-de-trabajo' => 'Bolsa de Trabajo'`.
- [app/Providers/Filament/AdminPanelProvider.php#L53](app/Providers/Filament/AdminPanelProvider.php#L53) — define `NavigationGroup::make()->label(__('navigation.bolsa-de-trabajo'))`.
- Los Resources del módulo se asignan al grupo vía el método override `getNavigationGroup()` (Filament v3, traducible) en vez de la propiedad estática `$navigationGroup`:
  - [app/Filament/Admin/Resources/OrganizationResource.php#L21-L23](app/Filament/Admin/Resources/OrganizationResource.php#L21-L23)
  - [app/Filament/Admin/Resources/JobListingResource.php#L13-L15](app/Filament/Admin/Resources/JobListingResource.php#L13-L15)
  - [app/Filament/Admin/Resources/ApplicationResource.php#L25-L27](app/Filament/Admin/Resources/ApplicationResource.php#L25-L27)
  - [app/Filament/Admin/Resources/JobCategoryResource.php#L26-L28](app/Filament/Admin/Resources/JobCategoryResource.php#L26-L28)
  - [app/Filament/Admin/Resources/CandidateProfileResource.php#L20-L22](app/Filament/Admin/Resources/CandidateProfileResource.php#L20-L22)

#### Suspensión ortogonal

- Migración aditiva: [database/migrations/2026_05_17_000001_add_suspension_columns_to_organizations.php#L14-L18](database/migrations/2026_05_17_000001_add_suspension_columns_to_organizations.php#L14-L18) — añade `suspended_at` (datetime nullable), `suspended_by` (string 100), `suspension_reason` (text). El backfill (líneas 23-33) migra filas con `verification_state = 2` al nuevo modelo.
- Migración de cleanup: [database/migrations/2026_05_17_000002_drop_suspended_verification_state.php#L22-L31](database/migrations/2026_05_17_000002_drop_suspended_verification_state.php#L22-L31) — neutraliza filas residuales con `UPDATE verification_state = 2 → 0` y luego falla-rápido (`throw`) si todavía quedan filas en estado legacy antes de remover el caso enum.
- Enum reducido: [app/Enums/OrganizationVerificationState.php#L10-L20](app/Enums/OrganizationVerificationState.php#L10-L20) — sólo `PENDING(0)` y `VERIFIED(1)`. El caso `SUSPENDED` fue **completamente removido** en cleanup PR #26 (commit `0176383`).
- Método modelo: [app/Models/Organization.php#L63-L66](app/Models/Organization.php#L63-L66) — `is_suspended()` ⇔ `suspended_at !== null`.

#### Action transaccional

[app/Actions/Admin/SuspendOrganization.php#L24-L110](app/Actions/Admin/SuspendOrganization.php#L24-L110):

1. Persiste las 3 columnas dentro de transacción.
2. Cascada: cierra todas las ofertas `ACTIVE → CLOSED` de la organización.
3. Normaliza `reason` (trim, null si blanco).
4. Despacha `Suspended` mailable a admins.
5. Registra activity log con properties.

#### Mailable

[app/Mail/Organization/Suspended.php#L15-L40](app/Mail/Organization/Suspended.php#L15-L40) — `extends Mailable implements ShouldQueue`. **No** acepta `suspension_reason` en el constructor (sólo `Organization`), coherente con FR-028b ("el mailable público no debe filtrar el motivo de suspensión").

#### Render hook del banner

- [app/Providers/Filament/MemberPanelProvider.php#L48-L62](app/Providers/Filament/MemberPanelProvider.php#L48-L62) — registra `PanelsRenderHook::CONTENT_START` que renderiza la vista [resources/views/filament/member/banners/organization-suspended.blade.php](resources/views/filament/member/banners/organization-suspended.blade.php) sólo si `$organization->is_suspended()` retorna `true`.

#### Enforcement en policy

- [app/Policies/OrganizationPolicy.php#L56-L61](app/Policies/OrganizationPolicy.php#L56-L61) — `organizationFrozenForMember()` retorna `(bool) $org?->is_suspended()` (línea 60).
- [app/Policies/JobListingPolicy.php#L57](app/Policies/JobListingPolicy.php#L57), [línea 70](app/Policies/JobListingPolicy.php#L70), [línea 83](app/Policies/JobListingPolicy.php#L83), [línea 97](app/Policies/JobListingPolicy.php#L97) — `update`, `delete`, `close`, `submitForApproval` llaman al freeze check.

---

## Convenciones del codebase

- **Actions por contexto**: `app/Actions/{Admin|Member|Public|Alerts}/`. Cada Action es de una sola responsabilidad y devuelve resultado tipado cuando aplica.
- **Resources separados por panel**: `app/Filament/{Admin|Member|Shared}/Resources/`. La carpeta `Shared/` aloja bases reutilizables como `BaseJobListingResource`.
- **Migraciones**: timestamp explícito + clase anónima (Laravel 11 idiom). Las nuevas columnas son nullable por defecto para permitir despliegues sin downtime.
- **Enums**: en `app/Enums/`, todos enums int para state machines (`OrganizationVerificationState`, `JobListingState`, `ApplicationStatus`, `JobAlertFrequency`, `DispatchDecision`).
- **Mailables por destinatario**: `app/Mail/{Member|Organization}/`. Todos `ShouldQueue`.
- **Snapshots inmutables**: `cv_snapshot`, `candidate_name_snapshot`, `candidate_email_snapshot`, `author_name_snapshot` capturan datos al momento del evento; el CV físico se copia a `storage/app/public/applications/{id}/cv.{ext}` en `SubmitApplication`.
- **Macros de Filament**: [app/Helpers/AppMacros.php#L14-L76](app/Helpers/AppMacros.php#L14-L76) expone `hasAuthorization`, `requiresAuthorization`, `requiresPasswordConfirmation` para acciones consistentes.
- **Scheduling defensivo**: tareas con `withoutOverlapping()` + `onOneServer()` + `runInBackground()`.
- **i18n total**: archivos en `lang/es/` para models, navigation, filament UI.

---

## Cross-cutting concerns

### Activity log

Todos los modelos críticos implementan `LogsActivity`:

- `Organization`, `JobListing`, `Application`, `ApplicationNote`, `CandidateProfile`, `WorkExperience`, `Education`, `JobAlert`, `Category`, `Role`, `User`.

El log se consulta vía [app/Helpers/Util.php](app/Helpers/Util.php) → `getActivityLog()`.

### Suspensión ortogonal

La regla clave: **la suspensión no muta `verification_state`**. Esto permite:

- Una org `VERIFIED + suspended` que recupera operatividad al reactivarse, sin necesidad de re-verificación.
- Una org `PENDING + suspended` (caso edge) que queda bloqueada con motivo explícito.

El test crítico vive en [tests/Unit/OrganizationIsSuspendedTest.php#L66](tests/Unit/OrganizationIsSuspendedTest.php#L66) y la factory documenta el invariante en [database/factories/OrganizationFactory.php#L57](database/factories/OrganizationFactory.php#L57).

### Cascada en suspensión

`SuspendOrganization` cierra todas las ofertas `ACTIVE` de la organización; `ReactivateOrganization` **no las re-abre**. El equipo de la organización debe reescalar manualmente cada oferta a través del flujo normal (DRAFT → submit → admin approval).

### Búsqueda accent-insensitive

Las columnas `*_folded` se mantienen automáticamente por el observer; siempre que toques un campo en el form o factory, el observer recalculará. **No** se debe insertar a mano valores en `title_folded` desde tests.

### Cache público sin sesión

`PublicNoSessionCookie` permite que las rutas de bolsa de trabajo sean cacheables por proxies/CDN (FR-013). Cualquier nuevo controller público debe registrarse en esa lista o respetar el contrato (no leer/escribir sesión).

### Dedup atómico en alertas

`JobAlertDispatchLog` tiene unique `(alert_id, window_key)`. La excepción `UniqueConstraintViolationException` se traduce a `DispatchDecision::AlreadySent` en `DispatchInstantAlertAction` y `BuildDigestForAlertAction`. **Nunca** convertir a `Sent`, ya que sesgaría las métricas.

---

## Testing

Framework: **Pest 2.34** + PHPUnit base.

- Base test case: [tests/TestCase.php#L8-L31](tests/TestCase.php#L8-L31) — limpia `Storage::fake()` disks en `setUp()` para prevenir residuos.
- Suites:
  - `tests/Unit/` — enums, modelos puros (e.g. `ApplicationStatusTest`, `OrganizationIsSuspendedTest`).
  - `tests/Feature/Public/` — capa pública (browse, search, filter, pagination, sitemap, rate-limit, offer detail).
  - `tests/Feature/Member/` — panel member (resources, actions).
  - `tests/Feature/Admin/` — panel admin (resources, widgets, actions).
  - `tests/Feature/Alerts/` — pipeline de alertas (dispatch, dedup, race conditions, 60 s grace).

Ejecución:

```bash
docker compose exec app php artisan test
docker compose exec app php artisan test --filter=OrganizationIsSuspendedTest
docker compose exec app vendor/bin/pest --parallel
```

Coverage objetivo: 80%+. Toda transición de estado (state machines) **debe** tener test feature.

---

## Comandos útiles

```bash
# Migraciones
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh --seed

# Seeders específicos
docker compose exec app php artisan db:seed --class=JobCategorySeeder

# Sitemap manual
docker compose exec app php artisan app:generate-sitemap

# Alertas (dispatcher manual)
docker compose exec app php artisan alerts:dispatch-daily
docker compose exec app php artisan alerts:dispatch-weekly

# Scheduler en foreground (dev)
docker compose exec app php artisan schedule:work

# Queue worker (dev)
docker compose exec app php artisan queue:listen

# Tinker
docker compose exec app php artisan tinker

# Limpieza
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan config:cache  # producción
```

---

## Estado del proyecto

- **Emprendimientos (Lazos de Fe)**: módulo base en producción. Ciclo de aprobación, patrocinio (3 días), favoritos con rating y exposición pública.
- **Bolsa de Trabajo**: 8 specs incrementales (002–009) shipped.
- **Spec 009**: 14/14 manual verification PASS (PR #25 + cleanup PR #26).
- **Cleanup PR #26**: eliminó el caso enum `OrganizationVerificationState::SUSPENDED` y las ramas muertas; la migración de seguridad `2026_05_17_000002_drop_suspended_verification_state.php` previene downgrade.
- **Tests**: ~344 tests passing (snapshot al cierre de spec 009).

### Tareas diferidas

- Spec 007: T123 Lighthouse pass, T127 perf probe.
- Spec 008: T073 a11y browser-side.
- Emprendimientos: tests de feature mínimos (existe stub vacío en `tests/Feature/Shared/VentureResourceTest.php`).

---

## Despliegue en producción

El proyecto se despliega vía `docker-compose.prod.yml`. Stack:

- **Caddy 2.10** como reverse proxy con HTTPS automático (ACME).
- **MariaDB jammy** como base de datos.
- **App PHP 8.4** corriendo bajo el mismo Dockerfile que dev.

Pasos:

1. Configurar variables `.env.production` (sin `APP_DEBUG`).
2. Activar `OPCache` y `config:cache`/`route:cache`/`view:cache`.
3. Levantar workers de queue con un supervisor (recomendado: `php artisan horizon` si se incorpora) o `queue:work` con systemd.
4. Configurar cron en el host: `* * * * * php artisan schedule:run >> /dev/null 2>&1`.
5. Verificar permisos: `storage/`, `bootstrap/cache/` writable por `www-data`.
6. Backup diario de DB + carpeta `storage/app/public/` (contiene CV snapshots).

Health checks:

- `GET /` (panel app default).
- `GET /bolsa-de-trabajo` (capa pública sin sesión).
- `GET /sitemap.xml` (puede responder 503 Retry-After durante regeneración; normal).

---

## Licencia

Privado / Caribbean Business Coalition.
