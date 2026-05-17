<p align="center">
  <img src="public/images/logo_500px.png" alt="Lazos de Fe" width="200">
</p>

<h1 align="center">Lazos de Fe</h1>

<p align="center">
  <em>Plataforma web de gestión de miembros y emprendimientos para una comunidad basada en la fe.</em>
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

Permite a los miembros registrarse, publicar emprendimientos (ideas de negocio o proyectos), y participar en un flujo de aprobación administrado. Construida con Laravel 11 y Filament 3 como panel de administración.

## 📑 Tabla de Contenidos

- [✨ Características Principales](#-características-principales)
- [🛠️ Stack Tecnológico](#️-stack-tecnológico)
- [📋 Prerequisitos](#-prerequisitos)
- [🚀 Instalación y Configuración](#-instalación-y-configuración)
- [🐳 Servicios Docker](#-servicios-docker)
- [🏗️ Arquitectura de la Aplicación](#️-arquitectura-de-la-aplicación)
- [📊 Modelo de Datos](#-modelo-de-datos)
- [🔄 Flujos de Trabajo](#-flujos-de-trabajo)
- [⌨️ Comandos Útiles](#️-comandos-útiles)
- [🧪 Testing](#-testing)
- [🌐 Despliegue en Producción](#-despliegue-en-producción)

## ✨ Características Principales

- **Gestión de Membresías** — Registro, aprobación y renovación de miembros con sistema de patrocinio (invitaciones)
- **Publicación de Emprendimientos** — Los miembros crean y envían emprendimientos para aprobación
- **Flujo de Aprobación** — Los administradores aprueban o rechazan emprendimientos con retroalimentación
- **Favoritos y Calificaciones** — Los miembros pueden marcar emprendimientos como favoritos y calificarlos
- **Comentarios** — Sistema polimórfico de comentarios sobre emprendimientos y miembros
- **Categorías Jerárquicas** — Clasificación de emprendimientos en categorías padre-hijo
- **Categorías de Empleo** — Clasificación de ofertas laborales con slug e ícono (Bolsa de Trabajo)
- **Organizaciones** — Perfil de organización para miembros empleadores con flujo de verificación administrativa
- **Bolsa de Trabajo** — Miembros de organizaciones verificadas publican ofertas de empleo con flujo de aprobación, cierre manual y expiración automática
- **Bolsa de Trabajo Pública** — Listado anónimo de ofertas activas en `/bolsa-de-trabajo` con búsqueda por palabra clave (insensible a acentos), filtros multi-select (categoría, modalidad, contrato, ciudad), paginación y detalle SEO-amigable por slug. Sirve sitemap.xml para indexación de buscadores, marcado JobPosting JSON-LD, Open Graph para redes sociales, CTA de postulación adaptada al visitante (anónimo / miembro / candidato / admin), y meta `noindex,follow` en variantes filtradas/paginadas. Conformidad WCAG 2.1 AA; rate-limit selectivo en búsqueda con palabra clave (60 req/min)
- **Perfiles de Candidato** — Perfil profesional del miembro con experiencia laboral, educación, CV y control de visibilidad
- **Postulaciones a Empleo** — Los candidatos se postulan a ofertas activas con copia inmutable del CV y carta de presentación; las organizaciones gestionan postulantes mediante un flujo de estados (recibida → en revisión → entrevista → rechazada/aceptada, con saltos permitidos) con notas internas privadas y notificaciones por email en cada cambio; los administradores tienen vista global de solo lectura. PII anonimizado al borrar la cuenta del candidato.
- **Gestión de Medios** — Adjuntar imágenes y archivos a emprendimientos
- **Registro de Actividad** — Auditoría de cambios con Spatie Activity Log
- **Control de Acceso por Roles** — Permisos personalizados para usuarios administrativos
- **Contenido Dinámico** — Textos editables para correos electrónicos y elementos de la interfaz
- **Captcha** — Protección contra bots en formularios públicos
- **Alertas de empleo** — Los miembros suscriben criterios (categoría opcional, ciudad libre opcional con normalización insensible a acentos, frecuencia diaria/semanal/instantánea, hasta 10 alertas activas/inactivas por miembro). El sistema entrega tres canales: (1) **resumen diario** a las 07:00 hora local, (2) **resumen semanal** los lunes 07:00 hora local, (3) **notificación instantánea** disparada al aprobar una oferta coincidente, con una **ventana de coalescencia de 5 minutos** (configurable vía `INSTANT_ALERT_WINDOW_SECONDS`) que agrupa aprobaciones cercanas en un solo correo. Cada correo incluye un **enlace de desuscripción firmado de larga duración** (`URL::signedRoute(..., absoluteExpiresAt: null)`) que idempotentemente desactiva la alerta. Despachos idempotentes vía `(job_alert_id, window_key)` único en `job_alert_dispatch_logs`. Toda la telemetría se emite a `public_events` con frontera PII estricta (sólo IDs, sin email/nombre/IP). Conformidad WCAG 2.1 AA en el panel del miembro y la vista anónima de desuscripción.

## 🛠️ Stack Tecnológico

| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| PHP | ^8.3 | Lenguaje del servidor |
| Laravel | ^11.0 | Framework backend |
| Filament | ^3.3 | Paneles de administración y formularios |
| MySQL | 8.0 | Base de datos (desarrollo) |
| MariaDB | jammy | Base de datos (producción) |
| Tailwind CSS | ^3.4 | Estilos del frontend |
| Vite | ^4.0 | Empaquetador de assets |
| Laravel Sail | ^1.25 | Entorno Docker para desarrollo |
| Pest | ^2.34 | Framework de pruebas |
| Caddy | 2.10 | Servidor web en producción (HTTPS automático) |

### Dependencias Destacadas

- **laravel/sanctum** — Autenticación de API
- **spatie/laravel-activitylog** — Registro de auditoría
- **lorisleiva/laravel-actions** — Patrón de acciones
- **codewithdennis/filament-select-tree** — Selección jerárquica en formularios
- **marcogermani87/filament-captcha** — Protección CAPTCHA
- **jenssegers/agent** — Detección de navegador/dispositivo

## 📋 Prerequisitos

- **Docker** y **Docker Compose** instalados
- **Git** para clonar el repositorio

> No se necesita instalar PHP, Composer, ni Node.js localmente — todo se ejecuta dentro de los contenedores Docker.

## 🚀 Instalación y Configuración

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio> lazos-de-fe
cd lazos-de-fe
```

### 2. Instalar dependencias de Composer

```bash
docker run --rm -v "$(pwd):/app" -w /app composer:latest composer install --ignore-platform-reqs
```

### 3. Configurar el entorno

```bash
cp .env.example .env
```

> Si no existe `.env.example`, crear `.env` manualmente con las siguientes variables mínimas:

```env
APP_NAME="Lazos de Fe"
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

WWWGROUP=1000
WWWUSER=1000
```

### 4. Construir y levantar los contenedores

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

Esto crea las tablas de la base de datos y ejecuta los seeders iniciales: `RoleSeeder`, `UserSeeder`, y `ConfigSeeder`.

### 7. Corregir permisos de almacenamiento

```bash
docker compose exec app chown -R sail:sail /var/www/html/storage /var/www/html/bootstrap/cache
```

### 8. Verificar la instalación

Abre [http://localhost](http://localhost) en tu navegador. La aplicación debería estar corriendo.

## 🐳 Servicios Docker

### Desarrollo (`docker-compose.yml`)

| Servicio | Contenedor | Puerto(s) | Descripción |
|----------|------------|-----------|-------------|
| App | app-lazosdefe | 80, 5173 | Aplicación Laravel con PHP 8.4 (Sail) |
| MySQL | mysql-lazosdefe | 3306 | Base de datos MySQL 8.0 |
| Mailpit | mailpit-lazosdefe | 1025 (SMTP), 8025 (UI) | Captura de correos en desarrollo |
| phpMyAdmin | phpmyadmin-lazosdefe | 8000 | Interfaz web para la base de datos |

### Producción (`docker-compose.prod.yml`)

| Servicio | Contenedor | Descripción |
|----------|------------|-------------|
| App | app-lazosdefe | PHP 8.4 FPM Alpine |
| Caddy | caddy-lazosdefe | Servidor web con HTTPS automático |
| MariaDB | mariadb-lazosdefe | Base de datos MariaDB |
| phpMyAdmin | phpmyadmin-lazosdefe | Solo con perfil `dev` |
| Mailpit | mailpit-lazosdefe | Solo con perfil `dev` |

## 🏗️ Arquitectura de la Aplicación

La aplicación tiene tres paneles construidos con Filament:

### Panel Admin (`/admin`)

Panel de administración para el equipo interno. Gestiona:

- **Dashboard** — Cuatro widgets de solo lectura con métricas de Bolsa de Trabajo: candidatos totales, organizaciones (totales y verificadas), ofertas activas, postulaciones (24h), postulaciones recientes, organizaciones pendientes de verificación y ofertas pendientes de aprobación.
- **Bolsa de Trabajo** (grupo de navegación) — Agrupa Organizaciones, Ofertas de Empleo, Postulaciones, Categorías de Empleo y Perfiles de Candidato.
- **Suspender / reactivar organización** — Acción atómica con cascada (cierra ofertas activas), notificación encolada al admin de la organización (sin exponer la razón), banner persistente de solo-lectura en el panel del miembro afectado, registro de auditoría y reactivación idempotente que preserva el estado de verificación.
- **Miembros** — Aprobación, rechazo y administración de miembros
- **Emprendimientos** — Aprobación, rechazo y gestión de contenido
- **Categorías** — Clasificación jerárquica de emprendimientos
- **Categorías de Empleo** — Gestión de categorías para la Bolsa de Trabajo (scope "JobListing")
- **Organizaciones** — Lista, detalle y verificación de organizaciones registradas por miembros (suspensión ahora es un flag ortogonal a la verificación)
- **Ofertas de Empleo** — Lista todas las ofertas, aprobación/rechazo de ofertas pendientes con notificación al miembro
- **Postulaciones** — Lista de todas las postulaciones recibidas por las organizaciones
- **Perfiles de Candidato** — Vista de solo lectura de perfiles profesionales de candidatos con experiencia laboral y educación
- **Usuarios** — Usuarios administrativos del sistema
- **Roles** — Control de acceso basado en permisos
- **Textos** — Plantillas de correo y contenido dinámico de la UI
- **Configuraciones** — Ajustes generales de la aplicación

### Panel Miembro (`/member`)

Panel para miembros registrados de la comunidad:

- **Mis Emprendimientos** — Crear, editar y ver emprendimientos propios
- **Favoritos** — Emprendimientos marcados como favoritos
- **Mi Organización** — Crear y gestionar el perfil de organización empleadora, solicitar verificación
- **Mis Ofertas de Empleo** — Crear, editar, enviar a aprobación, cerrar y ver ofertas de empleo de la organización
- **Mi Perfil Profesional** — Crear y gestionar perfil de candidato con experiencia laboral, educación, CV (PDF) y control de visibilidad
- **Perfil** — Editar información personal y de contacto
- **Registro** — Formulario de registro con términos y condiciones

### Panel Emprendimientos (`/app`)

Panel público para explorar emprendimientos:

- **Lista de Emprendimientos** — Navegar emprendimientos activos y aprobados
- **Detalle** — Ver información completa de un emprendimiento
- **Vista Previa** — Previsualizar emprendimientos con fecha de expiración

## 📊 Modelo de Datos

### Entidades Principales

```
Miembro ────┬──── crea ──────→ Emprendimiento ←──── clasificado por ──── Categoría
            │                       ↑                                       ↑
            ├──── favorito ──→ Favorito                               (jerárquica)
            │
            ├──── comenta ───→ Comentario (polimórfico)
            │
            └──── patrocinado → Invitación ← Miembro (padrino)
```

- **Miembro** — Participante de la comunidad. Tipos: visitante o miembro. Estados de membresía: indefinido, pendiente, aprobado, rechazado. Puede tener contactos, emprendimientos, favoritos y comentarios.
- **Emprendimiento** — Idea de negocio o proyecto creado por un miembro. Tiene categorías (muchos a muchos), medios, comentarios, favoritos, adjuntos, tags y contadores de vistas.
- **Categoría** — Sistema jerárquico padre-hijo para clasificar emprendimientos. Soporta múltiples ámbitos (scopes): "Venture" para emprendimientos, "JobListing" para ofertas de empleo. Las categorías de empleo incluyen campos adicionales de slug (URL amigable) e ícono.
- **Organización** — Entidad empleadora registrada por un miembro (relación 1:1). Tipos: iglesia, ministerio, ONG, empresa privada, emprendimiento. Estados de verificación: pendiente, verificada, suspendida. Flujo de verificación con notificaciones por email, log de actividad y trail de comentarios.
- **Perfil de Candidato** — Perfil profesional del miembro (relación 1:1). Incluye headline, resumen, ubicación, teléfono, foto, CV (PDF), declaración de fe y control de visibilidad. Tiene relaciones 1:N con Experiencia Laboral y Educación.
- **Experiencia Laboral** — Historial laboral del candidato. Pertenece a un perfil (relación N:1). Campos: empresa, cargo, descripción, fecha inicio/fin, indicador de trabajo actual.
- **Oferta de Empleo** — Publicación laboral creada por un miembro de una organización verificada. Estados: borrador, pendiente, activa, rechazada, cerrada, expirada. Incluye tipo de contrato, modalidad de trabajo, ubicación, rango salarial, preguntas de selección, y categorías (morphToMany). Expiración automática diaria por fecha límite.
- **Educación** — Formación académica del candidato. Pertenece a un perfil (relación N:1). Campos: institución, título, campo de estudio, año de graduación, indicador de en curso.
- **Rol** — Permisos de acceso para usuarios administrativos (array JSON de permisos).
- **Favorito** — Relación miembro-emprendimiento con calificación opcional.
- **Comentario** — Polimórfico: puede pertenecer a un emprendimiento o a un miembro.

## 🔄 Flujos de Trabajo

### Estados de Aprobación de Emprendimientos

```
Nuevo → Aprobación → Aprobado
                   ↘ Rechazado → Actualizado → Aprobación → ...
```

| Estado | Descripción |
|--------|-------------|
| Nuevo | Emprendimiento recién creado |
| Actualizado | Emprendimiento rechazado y re-enviado con cambios |
| Aprobación | En revisión por un administrador |
| Aprobado | Activo y visible públicamente |
| Rechazado | Rechazado con motivo explicado |

### Estados de Membresía

```
Indefinido → Pendiente → Aprobado
                       ↘ Rechazado
```

| Estado | Descripción |
|--------|-------------|
| Indefinido | Estado inicial al registrarse |
| Pendiente | Solicitud enviada, esperando aprobación |
| Aprobado | Miembro activo de la comunidad |
| Rechazado | Solicitud de membresía rechazada |

### Estados de Verificación de Organizaciones

```
Pendiente → Verificada
         ↘ Suspendida → Pendiente (re-solicitud) → ...
Verificada → Suspendida → Pendiente → ...
```

| Estado | Descripción |
|--------|-------------|
| Pendiente | Organización recién creada o que ha re-solicitado verificación |
| Verificada | Aprobada por un administrador, puede publicar ofertas |
| Suspendida | Suspendida por un administrador con motivo explicado |

### Estados de Ofertas de Empleo

```
Borrador → Pendiente → Activa → Cerrada (manual)
                     ↘ Rechazada → Editada → Pendiente → ...
                       Activa → Expirada (automático, fecha límite)
```

| Estado | Descripción |
|--------|-------------|
| Borrador | Oferta recién creada, editable |
| Pendiente | Enviada a aprobación por el miembro |
| Activa | Aprobada y visible (published_at) |
| Rechazada | Rechazada con motivo, editable para reenvío |
| Cerrada | Cerrada manualmente por el miembro |
| Expirada | Cerrada automáticamente al pasar la fecha límite |

## ⌨️ Comandos Útiles

### Artisan (dentro del contenedor)

```bash
# Ejecutar migraciones
docker compose exec app php artisan migrate

# Ejecutar migraciones con seeders
docker compose exec app php artisan migrate --seed

# Sembrar categorías de empleo (Bolsa de Trabajo)
docker compose exec app php artisan db:seed --class=JobCategorySeeder

# Regenerar el sitemap.xml de la bolsa de trabajo pública (corre cada hora vía scheduler)
docker compose exec app php artisan app:generate-sitemap

# Procesar la cola de jobs (requerido en producción para sitemap y alertas instantáneas)
# Prioridad: `instant` primero, luego `default` — protege la SLA de alertas instantáneas
# frente a backlogs de resúmenes diarios/semanales.
docker compose exec app php artisan queue:work --queue=instant,default --tries=3 --max-time=3600

# Despachar manualmente el resumen diario de alertas (corre 07:00 hora local vía scheduler)
docker compose exec app php artisan alerts:dispatch-daily

# Despachar manualmente el resumen semanal de alertas (corre lunes 07:00 hora local vía scheduler)
docker compose exec app php artisan alerts:dispatch-weekly

# Repoblar las columnas folded (title_folded / description_folded) usadas
# por la búsqueda insensible a acentos del listado público
docker compose exec app php artisan app:backfill-folded-columns

# Revertir última migración
docker compose exec app php artisan migrate:rollback

# Consola interactiva (Tinker)
docker compose exec app php artisan tinker

# Limpiar caché
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear

# Listar rutas
docker compose exec app php artisan route:list

# Actualizar assets de Filament
docker compose exec app php artisan filament:upgrade

# Crear usuario de Filament
docker compose exec app php artisan make:filament-user
```

### Docker

```bash
# Levantar todos los servicios
docker compose up -d

# Detener todos los servicios
docker compose down

# Ver logs de la aplicación
docker compose logs -f app

# Acceder al contenedor de la aplicación
docker compose exec app bash
```

### Frontend (dentro del contenedor)

```bash
# Instalar dependencias de Node.js
docker compose exec app npm install

# Compilar assets para desarrollo (con hot reload)
docker compose exec app npm run dev

# Compilar assets para producción
docker compose exec app npm run build
```

## 🧪 Testing

El proyecto usa [Pest](https://pestphp.com/) como framework de pruebas.

```bash
# Ejecutar todas las pruebas
docker compose exec app php artisan test

# Ejecutar pruebas con Pest directamente
docker compose exec app ./vendor/bin/pest

# Ejecutar pruebas de una carpeta específica
docker compose exec app php artisan test --testsuite=Feature
docker compose exec app php artisan test --testsuite=Unit
```

## 🌐 Despliegue en Producción

El archivo `docker-compose.prod.yml` define la configuración de producción con:

- **PHP 8.4 FPM Alpine** como servidor de aplicación
- **Caddy 2.10** como servidor web con HTTPS automático
- **MariaDB** como base de datos
- Red externa `caddynet` para comunicación entre servicios

Los servicios de desarrollo (phpMyAdmin, Mailpit) están disponibles solo con el perfil `dev`.

### Cola de Jobs (producción)

La regeneración on-demand del sitemap (`/sitemap.xml` cuando el archivo aún no existe tras un deploy en frío) **y** el despacho de alertas instantáneas dependen de un worker de cola activo. En producción se requiere:

1. `QUEUE_CONNECTION=database` (o `redis`) en `.env` — el driver `sync` por defecto bloquearía la petición HTTP / el aprobado de oferta.
2. Un proceso `php artisan queue:work --queue=instant,default --tries=3 --max-time=3600` corriendo (supervisor / systemd / Caddy worker block). La prioridad `instant,default` garantiza que un backlog de resúmenes diarios/semanales nunca demore una alerta instantánea.
3. La tabla `jobs` ya existe en migraciones; no se requiere paso adicional.

Sin un worker, los digests diarios/semanales se ejecutan en línea por el scheduler (`app/Console/Kernel.php` — 07:00 hora local / lunes 07:00) pero las alertas instantáneas quedan en cola sin procesarse.

### Configuración de alertas

`config/alerts.php` centraliza los parámetros operacionales del subsistema de alertas:

| Variable de entorno | Default | Significado |
|---------------------|---------|-------------|
| `INSTANT_ALERT_WINDOW_SECONDS` | `300` | Duración de la ventana de coalescencia para alertas instantáneas (segundos). |
| `MAX_ALERTS_PER_MEMBER` | `10` | Cuota máxima de alertas (activas + inactivas) por miembro. |
| `DAILY_DISPATCH_HOUR` | `7` | Hora local de despacho del resumen diario. |
| `WEEKLY_DISPATCH_DAY` | `monday` | Día de despacho del resumen semanal. |
| `WEEKLY_DISPATCH_HOUR` | `7` | Hora local de despacho del resumen semanal. |

```bash
# Levantar en producción
docker compose -f docker-compose.prod.yml --profile prod up -d

# Levantar con herramientas de desarrollo incluidas
docker compose -f docker-compose.prod.yml --profile prod --profile dev up -d
```

---

<p align="center">
  <strong>Lazos de Fe</strong> — Construyendo comunidad a través de la fe y el emprendimiento.
</p>
