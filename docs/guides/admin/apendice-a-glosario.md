# Apéndice A — Glosario

Este apéndice consolida los términos técnicos y operativos utilizados a lo largo de la guía. Las definiciones están alineadas con el glosario canónico del blueprint del proyecto ([`docs/guides/00-blueprint.md`](../00-blueprint.md), sección 3); cualquier discrepancia debe resolverse a favor del blueprint.

## A

**Acción** (de Filament)
:   Botón visible en la interfaz que dispara una operación; típicamente abre un modal de confirmación o un formulario. En esta guía, "acción" refiere específicamente a las acciones de cabecera (Header Actions) en las vistas de detalle.

**Acción** (patrón `lorisleiva/laravel-actions`)
:   Clase bajo `app/Actions/` que encapsula una operación de negocio reutilizable (ej. `SuspendOrganization`, `JobListingApproval`). Cada acción tiene un método `handle()` y se invoca con `Action::run(...)`. La distinción con la acción de Filament es contextual.

**Activitylog**
:   Bitácora persistente del sistema, implementada con el paquete `spatie/laravel-activitylog`. Almacena eventos sensibles en la tabla `activity_log`. Ver capítulo 10.

**Administración** (grupo de navegación)
:   Uno de los cuatro grupos del sidebar del panel `/admin`. Contiene recursos transversales: categorías globales, textos editoriales, configuraciones.

**Alerta de empleo**
:   Suscripción de un candidato a criterios de búsqueda. Produce *digests* periódicos por correo. Modelo `JobAlert`. Frecuencias: `Daily`, `Weekly`, `Instant`. Ver capítulo 9.

**Aplicación / Postulación**
:   Candidatura enviada por un candidato a una oferta de empleo. Modelo `Application`. Cinco estados: `RECEIVED`, `IN_REVIEW`, `INTERVIEW`, `REJECTED`, `ACCEPTED`. Ver capítulo 8.

## B

**Bandera de suspensión**
:   Conjunto de tres columnas (`suspended_at`, `suspended_by`, `suspension_reason`) que indican operacionalmente que una organización está congelada. **Ortogonal** al estado de verificación. Ver capítulo 4.

**Bolsa de Trabajo**
:   Nombre comercial del módulo de empleos del producto. Coincide con el grupo de navegación principal en el panel `/admin`.

## C

**Callout**
:   Bloque destacado en el documento con borde y fondo de color, usado para resaltar Notas, Atención, Importante y Buenas prácticas. Definidos en la guía de estilo del proyecto.

**Candidato**
:   Persona física que postula a ofertas. Modelo `Member` con `CandidateProfile` asociado. No es una entidad de usuarios separada (decisión arquitectónica spec 004).

**Categoría**
:   Etiqueta para agrupar ofertas de empleo en el portal público. Tabla `categories` con `scope = JobListing`. Ver capítulo 6.

**Cierre en cascada**
:   Efecto colateral de suspender una organización: todas sus ofertas `ACTIVE` pasan a `CLOSED` automáticamente. Ver capítulo 4, sección 4.4.

**Cron**
:   Tarea programada del sistema operativo, ejecutada por el scheduler de Laravel. Los digests diarios y semanales dependen de un cron activo. Ver capítulo 9.

**Causer**
:   Usuario que originó un evento de auditoría. Campo `causer` de la tabla `activity_log`. Ver capítulo 10.

## D

**Dashboard**
:   Vista de bienvenida del panel `/admin`. Contiene los cuatro widgets descritos en el capítulo 3.

**Decisión** (acción)
:   Botón de cabecera en la vista de una oferta `PENDING` que abre el modal de aprobación o rechazo. Mapeado a `JobListingApproval::run()`. Ver capítulo 5.

**Digest**
:   Correo electrónico agrupador entregado a candidatos con alertas activas. Tres variantes según frecuencia: instantáneo, diario, semanal. Ver capítulo 9.

## E

**Empleo / Oferta**
:   Publicación de un puesto vacante en el portal. Modelo `JobListing`. Seis estados posibles. Ver capítulo 5.

**Emprendimientos** (grupo de navegación)
:   Uno de los cuatro grupos del sidebar; contiene `Member` y `Venture`. Fuera de alcance de esta guía v1.0.

**Evento**
:   Identificador del tipo de entrada en la bitácora (`organization-suspended`, `job-listing.approve`, etc.). Ver capítulo 10, sección 10.2.

## F

**Filament**
:   Framework de administración construido sobre Laravel y Livewire. Provee la base de los tres paneles del producto: `/admin`, `/member`, `/app`.

**Filtro**
:   Componente del listado de un recurso que acota los resultados visibles. Implementado vía `Tables\Filters\*` de Filament.

## J

**JobAlert**, **JobListing**, **JobCategory**
:   Modelos PHP que respaldan las entidades alerta, empleo y categoría de empleo. Ver glosario operativo en el capítulo 1 sección 1.5 para sus equivalentes en español.

## L

**Listener**
:   Suscriptor a un evento de Laravel. Los listeners que reaccionan a `JobListingApproved` evalúan las alertas instantáneas. Ver capítulo 9.

## M

**Member**
:   Modelo del usuario del panel `/member`. Distinto de `User` (usuario del panel `/admin`). Un miembro puede tener perfil de candidato (`CandidateProfile`) o ser representante de una organización.

**Moderador**
:   Rol típico con permisos para aprobar/rechazar ofertas y verificar organizaciones, sin acceso a gestión de usuarios y roles. Ver capítulo 7, sección 7.5.

## O

**Organización**
:   Entidad que publica empleos. Modelo `Organization`. Tiene `verification_state` (PENDING/VERIFIED) y bandera de suspensión ortogonal. Ver capítulo 4.

**Ortogonal** (estado)
:   Dos dimensiones de estado independientes que pueden coexistir en cualquier combinación. En esta guía aplica a la relación entre verificación y suspensión de una organización.

## P

**Panel Admin**
:   Interfaz Filament expuesta en `/admin`, autenticada con el guard `admin`, color amber. Audiencia: super-administradores y moderadores.

**Panel Member**
:   Interfaz Filament expuesta en `/member`. Audiencia: representantes de organizaciones y candidatos.

**Panel Venture**
:   Interfaz Filament expuesta en `/app`. Audiencia: usuarios del módulo de Emprendimientos. Fuera de alcance de esta guía.

**Permiso**
:   Capacidad atómica asignable a un rol (ej. "verificar organización", "aprobar oferta"). Ver capítulo 7.

**Policy**
:   Clase bajo `app/Policies/` que define qué usuarios pueden ejecutar qué acciones sobre un modelo. Cada modelo tiene su policy.

**Portal público**
:   Páginas accesibles sin sesión: `/bolsa-de-trabajo`, `/bolsa-de-trabajo/{slug}`, `/sitemap.xml`.

**Properties** (de un evento de bitácora)
:   Bloque JSON con metadatos del evento (IP, conteos, referencias). Ver capítulo 10.

## R

**Reactivación**
:   Acción inversa a la suspensión de una organización. **No** reabre las ofertas cerradas en cascada. Ver capítulo 4, sección 4.5.

**Render hook**
:   Punto de extensión de Filament para inyectar HTML/Livewire en posiciones específicas del layout. El panel admin usa `GLOBAL_SEARCH_AFTER` para mostrar el rol activo. Ver capítulo 2, sección 2.3.

**Resource** (Filament)
:   Clase bajo `app/Filament/Admin/Resources/` que define cómo Filament expone un modelo (formulario, tabla, páginas).

**Rol**
:   Agrupador de permisos. Cada `User` tiene un rol. Ver capítulo 7.

## S

**Scope**
:   Restricción de consultas Eloquent. En `categories`, el `scope` distingue categorías de empleo de las de otros módulos.

**Sistema** (grupo de navegación)
:   Uno de los cuatro grupos del sidebar; contiene `User`, `Role`, `Config`. Ver capítulo 7.

**Slug**
:   Identificador URL-friendly. Aplicable a categorías y ofertas. Cambiar un slug invalida URLs externas.

**Snapshot**
:   Copia congelada del perfil del candidato al momento de postular. Permite a la organización ver el perfil tal como era al recibir la postulación, aunque el candidato lo edite después.

**Super-administrador**
:   Rol con acceso total al panel `/admin`. Ver capítulo 7, sección 7.5.

**Suspensión**
:   Bandera operativa sobre una organización. **Ortogonal** al estado de verificación. Cierra automáticamente las ofertas activas en cascada. Ver capítulo 4, sección 4.4.

## U

**Usuario** (`User`)
:   Cuenta autenticada en el panel `/admin`. Distinto de `Member`. Cada `User` tiene `username`, contraseña y un rol asignado.

## V

**Verificación**
:   Transición de una organización de `PENDING` a `VERIFIED`. Unidireccional. Acción `OrganizationVerification::run()`. Ver capítulo 4, sección 4.3.

**Venture**
:   Entidad del módulo Emprendimientos. Fuera de alcance de esta guía v1.0.

## W

**Widget**
:   Componente Filament visible en el dashboard. La versión 1.0 incluye cuatro widgets relacionados con Bolsa de Trabajo. Ver capítulo 3.

**Worker** (de cola)
:   Proceso que procesa la cola de jobs de Laravel (incluidos los correos encolados). Si el worker está detenido, los correos encolados se acumulan sin enviarse. Diagnóstico via bitácora; resolución via equipo técnico.
