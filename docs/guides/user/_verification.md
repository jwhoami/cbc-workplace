# Verificación técnica — Guía del Usuario

> Este archivo **no se incluye** en el `.docx` final. El Makefile lo excluye
> por el prefijo `_`. Su propósito es auditar las afirmaciones funcionales
> de la *Guía del Usuario* contra el código del producto.
>
> Cada entrada lista la afirmación tal como aparece en la guía (capítulo y
> sección), seguida del archivo y línea que la sustentan.

## Capítulo 1 — Bienvenida

- "Tres formas de participar: visitante, candidato, organización." → arquitectura general: `app/Providers/Filament/MemberPanelProvider.php:65-152` (panel member), `routes/public.php:25-36` (portal público sin sesión).

## Capítulo 2 — Buscando empleos

- "Cada tarjeta muestra título, organización, ciudad, categoría." → `app/Http/Controllers/Public/JobBoardController.php` + view `resources/views/public/job-board/index.blade.php`.
- "La búsqueda no distingue acentos." → Columnas generadas `*_folded` en `database/migrations/2026_05_07_000001_add_folded_columns_to_job_listings.php` con `utf8mb4_unicode_ci`.
- "Filtros: categoría, ciudad, modalidad, tipo de contrato." → ver query builder en `app/Actions/Public/SearchPublicOffersAction.php`.
- "Cada empleo tiene URL propia." → `routes/web.php:38-40`, rute `public.job-offer.show` con slug.

## Capítulo 3 — Registro y cuenta

- "Auto-registro habilitado." → `app/Providers/Filament/MemberPanelProvider.php:73-77` (`registration()`, `passwordReset()`, `emailVerification()`).
- "Reseteo de contraseña vía correo." → `MemberPanelProvider.php:74-75` con `authPasswordBroker('members')`.
- "Verificación de correo personalizada en español." → `MemberPanelProvider.php:39-46` `VerifyEmail::toMailUsing(...)`.

## Capítulo 4 — Perfil de organización

- "Estado pendiente tras crear." → `app/Models/Organization.php:41` enum `OrganizationVerificationState::PENDING` default; `app/Actions/Member/RequestOrganizationVerification.php`.
- "Recibe correo cuando se aprueba." → `app/Actions/Admin/OrganizationVerification.php:52` `Mail::to($organization->member)->send(new Verified($organization))`.
- "Plazo de verificación sin SLA fijo." → el código no impone plazo; decisión editorial de no comprometer fechas en la guía.
- "Solo organizaciones verificadas pueden publicar." → `app/Policies/JobListingPolicy.php:41-52` (`create()` requiere `verification_state === VERIFIED`).

## Capítulo 5 — Publicar y gestionar empleos

- "Seis estados del empleo." → `app/Enums/JobListingState.php` (DRAFT=0, PENDING=1, ACTIVE=2, REJECTED=3, CLOSED=4, EXPIRED=5).
- "Empleo enviado a aprobación queda en PENDING hasta decisión." → `app/Actions/Member/RequestJobListingApproval.php` + `app/Actions/Admin/JobListingApproval.php:21-23`.
- "Aprobación dispara correo + evento de alertas." → `JobListingApproval.php:49-53` (correo síncrono + `App\Events\JobListingApproved::dispatch`).
- "Rechazo requiere razón obligatoria." → `app/Filament/Admin/Resources/JobListingResource/Pages/ViewJobListing.php:45-47` (`requiredIf decision=REJECTED`).
- "Cerrar empleo es irreversible." → `app/Actions/Member/CloseJobListing.php` no expone método inverso; estado `CLOSED` es terminal en el enum.
- "Cierre por expiración automática." → `app/Actions/ExpireJobListings.php` (comando programado).
- "Cascade de cierre por suspensión organización." → `app/Actions/Admin/SuspendOrganization.php:42-48`.

## Capítulo 6 — Recibir y evaluar postulaciones

- "Estados de postulación." → `app/Enums/ApplicationStatus.php` (RECEIVED, IN_REVIEW, INTERVIEW, REJECTED, ACCEPTED).
- "Snapshot de perfil al postular." → `app/Actions/Member/SubmitApplication.php` (campo `profile_snapshot` JSON + `cv_snapshot_path`).
- "Notas internas privadas." → `app/Models/ApplicationNote.php` + `app/Policies/ApplicationNotePolicy.php` (no exposición al candidato).
- "Cambio de estado dispara correo al candidato (excepto IN_REVIEW)." → `app/Actions/Member/UpdateApplicationStatus.php` con condicional de notify.
- "Cierre del empleo NO cierra postulaciones existentes." → `CloseJobListing.php` solo modifica `JobListing.state`, no toca `applications`.

## Capítulo 7 — Perfil de candidato y postular

- "CandidateProfile es extensión 1:1 de Member." → `app/Models/Member.php:90-95` (`candidateProfile(): HasOne`).
- "Una sola postulación por candidato por empleo." → unique constraint en migración `database/migrations/2026_04_27_000001_create_applications_table.php` + validación temprana en `SubmitApplication.php:30-35`.
- "Pre-condición postular: estado ACTIVE + fecha límite no vencida." → `SubmitApplication.php:27-30` (`$listing->state !== JobListingState::ACTIVE || application_deadline isPast`).
- "Snapshot de perfil queda congelado." → mismo Action; copia campos al momento del submit.
- "Postulación dispara correos." → `SubmitApplication.php` con `Mail::to($member)->send(new ApplicationSubmitted)` + `Mail::to(...)->send(new ApplicationReceived)`.
- "Perfil solo visible para orgs a las que postulas." → `CandidateProfilePolicy.php` restringe `view()` a admin + own member; no hay enumeración pública.

## Capítulo 8 — Alertas

- "Hasta 10 alertas por candidato." → `config/alerts.php:18` `MAX_ALERTS_PER_MEMBER=10`, enforced en `app/Actions/Member/CreateJobAlertAction.php`.
- "Tres frecuencias: Instant (3), Daily (1), Weekly (2)." → `app/Enums/JobAlertFrequency.php`.
- "Daily a las 07:00 hora servidor." → `app/Console/Kernel.php:20-25` `dailyAt('07:00')->timezone(config('app.timezone'))`.
- "Weekly lunes 07:00." → `app/Console/Kernel.php:27-33` `mondays()->at('07:00')`.
- "Instant tras aprobación de oferta." → `app/Actions/Admin/JobListingApproval.php:53` dispatch `JobListingApproved` + `app/Listeners/EvaluateInstantJobAlerts.php`.
- "Ventana de gracia instant 5 min." → `config/alerts.php:16` `INSTANT_ALERT_WINDOW_SECONDS=300`.
- "Enlace de desuscripción firmado, long-lived." → `routes/web.php:44-46` middleware `signed` + `URL::signedRoute(..., absoluteExpiresAt: null)` documentado en `app/Actions/Member/DisableJobAlertByTokenAction.php`.
- "Desuscripción no borra: desactiva." → `DisableJobAlertByTokenAction.php` setea `enabled=false`.

## Capítulo 9 — Cuando mi organización está suspendida

- "Suspensión cierra ofertas activas en cascada." → `app/Actions/Admin/SuspendOrganization.php:42-48`.
- "Correo de notificación al admin de la org." → `SuspendOrganization.php:85-93` `Mail::to($adminMember)->queue(new Suspended($organization))`.
- "Banner rojo en panel /member." → `app/Providers/Filament/MemberPanelProvider.php:48-62` render hook `CONTENT_START` + view `resources/views/filament/member/banners/organization-suspended.blade.php`.
- "No se puede publicar empleos suspendido." → `app/Policies/JobListingPolicy.php:41-52` (`create()` check de `organizationFrozenFor`).
- "No se puede editar empleos suspendido." → `JobListingPolicy.php:54-65` (`update()`) idem.
- "Reactivación no reabre ofertas cerradas." → `app/Actions/Admin/ReactivateOrganization.php:22-29` solo nulifica las columnas de suspensión, no toca `job_listings`.
- "Postulaciones recibidas siguen visibles tras suspensión." → no se modifica `applications` en `SuspendOrganization.php`.
- "Helper compartido `organizationFrozenForMember`." → `app/Policies/OrganizationPolicy.php:56-61`.

## Capítulo 10 — Preguntas frecuentes

- Todas las afirmaciones de FAQ son derivadas de capítulos previos; verificación con sus respectivos archivos.

## Apéndice A — Glosario

- Términos definidos contra el código del producto. Referencias cruzadas a `app/Enums/`, `app/Models/`, `app/Actions/`.

---

## Cómo usar este archivo

1. Antes de un merge mayor, abrir esta lista y verificar las afirmaciones contra los archivos citados.
2. Si una afirmación deja de coincidir con el código (porque cambió la implementación), **actualizar primero** la guía y luego este archivo.
3. Si una nueva afirmación se añade a la guía, **añadirla aquí** con su `file:line`.

Este archivo es **interno del equipo de mantenimiento**. No se incluye en el `.docx` final (Makefile excluye archivos con prefijo `_`).
