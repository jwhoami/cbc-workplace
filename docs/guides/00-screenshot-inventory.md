# Inventario de Capturas — Guías CBC Workplace

**Total planificado:** ~110 capturas (Admin 45 + Implementación 27 + Usuario 38)
**Entorno fuente:** Sail local en `http://localhost` + seeders `Spec009DemoSeeder` y datos de spec 008
**Pipeline:** `scripts/captures.mjs` (Playwright, resuelve selectores y compone overlay) + `scripts/annotate.mjs` (re-aplica overlay desde sidecar `.coords.json`)

> Cada fila de las tablas es un descriptor reproducible. El campo **`slug`**
> es el ID único usado por el script de capturas; permite regenerar una sola
> imagen con `node scripts/captures.mjs --only <slug>`.

---

## 1. Entorno reproducible

### 1.1 Bootstrap del entorno

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail artisan db:seed --class=Spec009DemoSeeder
./vendor/bin/sail artisan db:seed --class=JobAlertSeeder
./vendor/bin/sail artisan queue:work --queue=instant,default --tries=3 --max-time=3600 &
```

### 1.2 Credenciales seedeadas

Todas verificadas contra `database/seeders/Spec009DemoSeeder.php:44-65`.

| Rol | Email | Password | Panel |
|---|---|---|---|
| Super-admin | `admin@example.com` | `password` | `/admin` |
| Moderador | `moderator@example.com` | `password` | `/admin` |
| Org verificada A | `org-verified-a@example.com` | `password` | `/member` |
| Org verificada B | `org-verified-b@example.com` | `password` | `/member` |
| Org suspendible | `org-suspend-target@example.com` | `password` | `/member` |
| Candidato 1 | `candidate-1@example.com` | `password` | `/member` |
| Candidato 2 | `candidate-2@example.com` | `password` | `/member` |

> Si las credenciales reales del seeder cambian, **regenerar esta tabla**
> ejecutando `php artisan tinker --execute='dump(Member::all(["email"]))'`
> y revisar `Spec009DemoSeeder.php`.

### 1.3 Configuración de Playwright

```javascript
// scripts/captures.mjs (extracto)
const config = {
  baseURL: 'http://localhost',
  viewport: { width: 1440, height: 900 },
  deviceScaleFactor: 1,
  locale: 'es-ES',
  timezoneId: 'America/Santiago',
  colorScheme: 'light',
};
```

### 1.4 Sanitización pre-captura

Antes de cada sesión de captura:

- [ ] `APP_DEBUG=false` en `.env` para ocultar Debugbar
- [ ] Mailpit limpio: `curl -X DELETE http://localhost:8025/api/v1/messages`
- [ ] Cache limpia: `sail artisan optimize:clear`
- [ ] Browser sin extensiones
- [ ] Reloj del sistema con la fecha demo deseada (sino, capturas con timestamps inconsistentes)

---

## 2. Inventario — Guía de Administración

**Total: 45 capturas.** Destino: `docs/guides/screenshots/admin/<slug>.png`.

### 2.1 Acceso y dashboard

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `admin-login-form` | `/admin/login` | Logout previo; capturar form vacío | 1: campo email; 2: campo password; 3: botón "Iniciar sesión" |
| `admin-dashboard-full` | `/admin` | Login como `admin@example.com`; capturar vista completa | 1: sidebar; 2: encabezado con rol; 3: widget de stats; 4: widget de aprobaciones |
| `admin-dashboard-stats-widget` | `/admin` | Recorte del widget `JobBoardStatsOverview` (`app/Filament/Admin/Widgets/JobBoardStatsOverview.php`) | 1–6: cada métrica |
| `admin-dashboard-pending-approvals` | `/admin` | Recorte del widget `PendingJobListingApprovalsWidget` | 1: contador de pendientes; 2: link "Ver todas" |
| `admin-dashboard-pending-verifications` | `/admin` | Recorte del widget `PendingOrganizationVerificationsWidget` | 1: contador; 2: lista de orgs |
| `admin-dashboard-recent-applications` | `/admin` | Recorte del widget `RecentApplicationsWidget` (tabla 10 filas) | 1: columna candidato; 2: columna oferta; 3: timestamp |
| `admin-nav-bolsa-de-trabajo` | `/admin` | Expandir grupo "Bolsa de Trabajo" en sidebar | 1: Organizaciones; 2: Empleos; 3: Categorías; 4: Postulaciones; 5: Candidatos |
| `admin-global-search-role-badge` | `/admin` | Hover sobre la barra de búsqueda global | 1: badge `ADMIN - <role>` rendido por hook `GLOBAL_SEARCH_AFTER` (`AdminPanelProvider.php:71-74`) |

### 2.2 Gestión de organizaciones

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `admin-org-list` | `/admin/organizations` | Capturar tabla completa | 1: filtros por estado; 2: badge de verificación; 3: badge de suspensión |
| `admin-org-list-filter-pending` | `/admin/organizations?tableFilters[verification_state][value]=0` | Aplicar filtro "Pendiente" | 1: filtro activo; 2: 7 orgs pending del seeder |
| `admin-org-list-filter-verified` | `/admin/organizations` | Aplicar filtro "Verificada" | 1: filtro activo; 2: 2 orgs verificadas |
| `admin-org-list-filter-suspended` | `/admin/organizations` | Aplicar filtro "Suspendida" | 1: filtro activo; 2: 1 org suspendible |
| `admin-org-view-pending` | `/admin/organizations/{id}` | Org pendiente | 1: header con nombre; 2: header actions Verificar/Rechazar; 3: campos del perfil |
| `admin-org-view-verified` | `/admin/organizations/{id}` | Org verificada | 1: badge verde; 2: action Suspender visible |
| `admin-org-suspend-modal` | `/admin/organizations/{id}` | Pulsar acción "Suspender" | 1: modal; 2: textarea de razón; 3: botón confirmar |
| `admin-org-suspend-confirm-toast` | `/admin/organizations/{id}` | Confirmar suspensión | 1: toast verde "Organización suspendida"; 2: nuevo badge de suspensión |
| `admin-org-suspended-view` | `/admin/organizations/{id}` | Vista post-suspensión | 1: badge rojo "Suspendida"; 2: header action Reactivar; 3: razón visible (solo admin) |
| `admin-org-reactivate-modal` | `/admin/organizations/{id}` | Pulsar "Reactivar" | 1: modal de confirmación |
| `admin-org-reactivate-toast` | `/admin/organizations/{id}` | Confirmar reactivación | 1: toast; 2: badge verde restaurado |
| `admin-org-verification-history` | `/admin/organizations/{id}` | Scroll a la sección de actividad (activitylog) | 1: timeline de eventos |

### 2.3 Gestión de empleos

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `admin-job-list-pending` | `/admin/job-listings` | Filtrar por estado PENDING | 1: tabla; 2: badge "Pendiente" |
| `admin-job-view-pending` | `/admin/job-listings/{id}` | Vista de oferta pendiente | 1: campos; 2: header actions Aprobar / Rechazar |
| `admin-job-approve-modal` | `/admin/job-listings/{id}` | Acción "Aprobar" | 1: modal |
| `admin-job-reject-modal` | `/admin/job-listings/{id}` | Acción "Rechazar" | 1: modal; 2: textarea de motivo |
| `admin-job-active-view` | `/admin/job-listings/{id}` | Oferta ACTIVE | 1: badge "Activa"; 2: contador de postulaciones |
| `admin-job-list-active` | `/admin/job-listings` | Filtrar ACTIVE | 1: total; 2: ordenamiento por fecha |

### 2.4 Categorías y configuración

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `admin-category-list` | `/admin/categories` | Tabla de categorías | 1: scope JobListing; 2: slug; 3: icon |
| `admin-category-edit` | `/admin/categories/{id}/edit` | Form de edición | 1: campo nombre; 2: campo slug; 3: campo icon |

### 2.5 Usuarios y roles

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `admin-users-list` | `/admin/users` | Tabla de usuarios | 1: rol; 2: estado |
| `admin-roles-manage` | `/admin/roles` | Vista de roles + matriz de permisos | 1: tabla; 2: matriz |

### 2.6 Postulaciones y candidatos

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `admin-applications-list` | `/admin/applications` | Tabla de postulaciones | 1: estado; 2: candidato; 3: oferta |
| `admin-application-view` | `/admin/applications/{id}` | Vista detallada | 1: datos del candidato; 2: CV snapshot |
| `admin-candidates-list` | `/admin/candidate-profiles` | Tabla de candidatos | 1: foto; 2: educaciones; 3: experiencias |

### 2.7 Auditoría y banners

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `admin-activitylog-org-suspended` | `/admin/organizations/{id}` | Scroll al timeline post-suspensión | 1: evento `mail-suspension-dispatch-enqueued`; 2: actor admin |
| `admin-banner-member-frozen` | `/member` | Login como `org-suspend-target@example.com` tras suspensión | 1: banner rojo "Organización suspendida" (rendido por hook `CONTENT_START`) |

### 2.8 Procedimientos compuestos (con flecha guiada)

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `admin-flow-suspend-1` | `/admin/organizations` | Paso 1 del flujo de suspensión | 1: clic en fila |
| `admin-flow-suspend-2` | `/admin/organizations/{id}` | Paso 2 | 1: header action Suspender |
| `admin-flow-suspend-3` | `/admin/organizations/{id}` | Paso 3 | 1: modal con razón rellena |
| `admin-flow-suspend-4` | `/admin/organizations/{id}` | Paso 4 | 1: estado final |
| `admin-flow-reactivate-1` | `/admin/organizations/{id}` | Paso 1 del flujo de reactivación | 1: header action Reactivar |
| `admin-flow-reactivate-2` | `/admin/organizations/{id}` | Paso 2 | 1: modal; 2: estado final |
| `admin-flow-approve-job-1` | `/admin/job-listings` | Filtrar PENDING | 1: tabla |
| `admin-flow-approve-job-2` | `/admin/job-listings/{id}` | Vista de la oferta | 1: header action Aprobar |
| `admin-flow-approve-job-3` | `/admin/job-listings/{id}` | Modal de aprobación | 1: confirmación |
| `admin-flow-approve-job-4` | `/admin/job-listings/{id}` | Estado final ACTIVE | 1: badge |

---

## 3. Inventario — Guía de Implementación

**Total: 27 capturas.** Predominan diagramas y terminales. Destino:
`docs/guides/screenshots/impl/<slug>.png`.

### 3.1 Arquitectura (diagramas Mermaid renderizados)

| Slug | Origen | Anotaciones |
|---|---|---|
| `impl-arch-overview` | Mermaid `flowchart` | Capas: Public Routes → Filament Panels → Actions → Models → DB |
| `impl-arch-panels` | Mermaid | 3 paneles: admin / member / app + auth guards |
| `impl-arch-alerts-pipeline` | Mermaid `sequenceDiagram` | Event `JobListingApproved` → Listener → Action `DispatchInstantAlertAction` → Mailable |
| `impl-arch-daily-digest` | Mermaid | Cron → `alerts:dispatch-daily` → Action → resolve offers → Mailable |
| `impl-arch-suspension-cascade` | Mermaid | `SuspendOrganization::run` → DB transaction → cascade ACTIVE→CLOSED → enqueue mail post-commit |
| `impl-arch-search-pipeline` | Mermaid | Public route → `SearchPublicOffersAction` → eloquent scopes (folded columns) → Blade |

### 3.2 Setup local

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `impl-terminal-clone` | shell | `git clone` + `cd cbc-workplace` | output esperado |
| `impl-terminal-sail-up` | shell | `./vendor/bin/sail up -d` | logs `Ready in Xs` |
| `impl-terminal-migrate-seed` | shell | `sail artisan migrate:fresh --seed` | resumen `Migrated: N` |
| `impl-terminal-seed-spec009` | shell | `sail artisan db:seed --class=Spec009DemoSeeder` | output esperado |
| `impl-terminal-queue-worker` | shell | `sail artisan queue:work --queue=instant,default` | logs de procesado |
| `impl-terminal-schedule-work` | shell | `sail artisan schedule:work` | tabla de comandos programados |
| `impl-mailpit-inbox` | `http://localhost:8025` | Mailpit con un digest | 1: email del digest; 2: subject; 3: link unsubscribe |

### 3.3 Estructura de archivos

| Slug | Origen | Anotaciones |
|---|---|---|
| `impl-tree-actions` | shell `tree app/Actions/ -L 2` | Subdirectorios Admin / Alerts / Member / Public |
| `impl-tree-filament` | shell `tree app/Filament/ -L 3 \| head -60` | Admin / Member / Venture |
| `impl-tree-models` | shell `tree app/Models/ -L 1` | Listado de modelos |
| `impl-tree-policies` | shell `tree app/Policies/` | Lista |
| `impl-tree-mail` | shell `tree app/Mail/` | Lista |

### 3.4 Operaciones y observabilidad

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `impl-activitylog-query` | shell tinker | `ActivityLog::query()->latest()->limit(5)->get()` | tabla de eventos |
| `impl-laravel-log-tail` | shell | `tail -50 storage/logs/laravel.log` | sin errores tras el flujo |
| `impl-mysql-orgs-table` | shell mysql | `DESCRIBE organizations;` | columnas incluyendo `suspended_at` |
| `impl-mysql-job-alerts` | shell mysql | `SELECT * FROM job_alerts LIMIT 5;` | filas seedeadas |
| `impl-sitemap-xml` | `http://localhost/sitemap.xml` | Vista XML | 1: `<urlset>`; 2: nodos `<url>` |

### 3.5 Tests

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `impl-pest-run-all` | shell | `sail artisan test` | output con `345 passed` |
| `impl-pest-run-alerts` | shell | `sail artisan test --filter=Alerts` | output filtrado |
| `impl-coverage-report` | shell | `sail artisan test --coverage` (opcional) | porcentaje por clase |

### 3.6 CI / Despliegue

| Slug | Origen | Anotaciones |
|---|---|---|
| `impl-deploy-checklist-diagram` | Mermaid | pasos pre/post deploy |
| `impl-supervisor-config` | archivo `supervisor.conf` ejemplo | bloque resaltado |
| `impl-nginx-config-snippet` | archivo `nginx.conf` ejemplo | bloque resaltado |

---

## 4. Inventario — Guía del Usuario

**Total: 38 capturas.** Destino: `docs/guides/screenshots/user/<slug>.png`.

### 4.1 Portal público (candidato anónimo)

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `user-public-home` | `/bolsa-de-trabajo` | Listado de ofertas | 1: header; 2: campo de búsqueda; 3: filtros; 4: tarjeta de oferta |
| `user-public-search-keyword` | `/bolsa-de-trabajo?q=desarrollador` | Buscar palabra clave | 1: query input; 2: resultados; 3: contador |
| `user-public-filters-open` | `/bolsa-de-trabajo` | Abrir panel de filtros | 1: filtro categoría; 2: filtro ciudad; 3: contrato; 4: modalidad |
| `user-public-filter-category` | `/bolsa-de-trabajo?category=desarrollo` | Aplicar filtro | 1: chip activo; 2: clear |
| `user-public-filter-city` | `/bolsa-de-trabajo?city=santiago` | Filtro de ciudad | 1: chip; 2: resultados filtrados |
| `user-public-empty-state` | `/bolsa-de-trabajo?q=zzzzzz` | Sin resultados | 1: mensaje friendly; 2: CTA "Limpiar filtros" |
| `user-public-pagination` | `/bolsa-de-trabajo?page=2` | Página 2 | 1: paginador; 2: indicador de página |
| `user-public-offer-detail` | `/bolsa-de-trabajo/{slug}` | Detalle de oferta | 1: título; 2: org; 3: descripción; 4: CTA postular |
| `user-public-offer-detail-expired` | `/bolsa-de-trabajo/{slug}` | Oferta expirada | 1: banner expirada; 2: CTA deshabilitado |
| `user-public-cta-anonymous` | `/bolsa-de-trabajo/{slug}` | CTA variant Anonymous | 1: botón "Crear cuenta" |
| `user-public-error-state-400` | `/bolsa-de-trabajo?q=<invalid>` | Error 400 friendly | 1: mensaje; 2: link al home |

### 4.2 Registro y onboarding

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `user-register-form` | `/member/register-with-invitation-code` | Form de registro | 1: nombre; 2: email; 3: password; 4: código de invitación |
| `user-register-success` | `/member/welcome` | Tras registro | 1: bienvenida; 2: CTA "Verificar email" |
| `user-verify-email-mailpit` | `http://localhost:8025` | Email de verificación | 1: subject; 2: botón verificar |
| `user-tos-page` | `/member/tos` | Términos de servicio | 1: contenido; 2: scroll |

### 4.3 Perfil de organización (member)

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `user-member-home` | `/member` | Dashboard del member | 1: nav; 2: tarjetas |
| `user-org-create-form` | `/member/organizations/create` | Form de creación | 1: tipo; 2: nombre; 3: descripción; 4: contacto |
| `user-org-edit` | `/member/organizations/{id}/edit` | Editar org | 1: campos; 2: botón Guardar |
| `user-org-pending-state` | `/member/organizations/{id}` | Estado PENDING | 1: badge; 2: aviso "esperando verificación" |
| `user-org-verified-state` | `/member/organizations/{id}` | Estado VERIFIED | 1: badge verde |
| `user-org-suspension-banner` | `/member` | Login como org suspendida | 1: banner rojo en CONTENT_START |

### 4.4 Publicar y gestionar empleos (org)

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `user-job-list-empty` | `/member/job-listings` | Sin ofertas | 1: CTA "Crear oferta" |
| `user-job-create-step1` | `/member/job-listings/create` | Wizard paso 1 | 1: título; 2: descripción |
| `user-job-create-step2` | `/member/job-listings/create` | Paso 2 | 1: categoría; 2: contrato; 3: modalidad; 4: ciudad |
| `user-job-create-step3` | `/member/job-listings/create` | Paso 3 | 1: fechas; 2: deadline |
| `user-job-list-active` | `/member/job-listings` | Lista con ofertas | 1: badge ACTIVE; 2: acción Cerrar |
| `user-job-view-active` | `/member/job-listings/{id}` | Detalle de oferta | 1: stats; 2: lista de postulaciones |
| `user-job-applications-relmgr` | `/member/job-listings/{id}` | RelationManager Applications | 1: tabla; 2: estado por postulación |

### 4.5 Postulaciones desde el lado de la organización

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `user-application-list` | `/member/applications` | Postulaciones recibidas | 1: filtro estado; 2: candidato |
| `user-application-view` | `/member/applications/{id}` | Detalle | 1: candidato; 2: CV; 3: cambiar estado |
| `user-application-status-change` | `/member/applications/{id}` | Acción cambiar estado | 1: dropdown; 2: confirm |
| `user-application-note` | `/member/applications/{id}` | Agregar nota interna | 1: textarea; 2: lista |

### 4.6 Postulaciones desde el lado del candidato

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `user-candidate-profile-create` | `/member/candidate-profile/create` | Form de perfil | 1: foto; 2: bio |
| `user-candidate-experience-add` | `/member/candidate-profile/{id}/edit` | RelationManager WorkExperiences | 1: form; 2: lista |
| `user-candidate-apply-cta-anon-vs-auth` | `/bolsa-de-trabajo/{slug}` | Variant difference | 1: variant `MemberCandidate` con botón "Postularme" |
| `user-candidate-submit-confirm` | `/bolsa-de-trabajo/{slug}` | Tras postular | 1: toast; 2: link a mis postulaciones |

### 4.7 Alertas de empleo

| Slug | Ruta | Acción | Anotaciones |
|---|---|---|---|
| `user-alerts-list-empty` | `/member/job-alerts` | Sin alertas | 1: CTA crear |
| `user-alert-create-form` | `/member/job-alerts/create` | Form | 1: nombre; 2: keyword; 3: ciudad; 4: frecuencia |
| `user-alert-list-with-items` | `/member/job-alerts` | Con 3 alertas creadas | 1: tabla; 2: estado activo/inactivo |
| `user-alert-edit` | `/member/job-alerts/{id}/edit` | Editar | 1: campos |
| `user-alert-toggle-disable` | `/member/job-alerts` | Toggle | 1: switch off |
| `user-mailpit-digest-daily` | `http://localhost:8025` | Email digest diario | 1: subject; 2: lista de ofertas; 3: footer con unsubscribe |
| `user-unsubscribe-landing` | `/alerts/unsubscribe/{member}/{alert}?signature=...` | Vista de unsubscribe | 1: confirmación; 2: aria-live region |

---

## 5. Formato del descriptor JSON (uso interno del script)

Cada captura se materializa como entrada en `docs/guides/captures.json`,
generado a partir de las tablas anteriores. Ejemplo:

```json
{
  "slug": "admin-org-suspend-modal",
  "guide": "admin",
  "url": "/admin/organizations/3",
  "auth": "admin@example.com",
  "preActions": [
    { "type": "click", "selector": "button[aria-label='Suspender']" }
  ],
  "wait": [
    { "type": "selector", "value": ".fi-modal" }
  ],
  "viewport": { "width": 1440, "height": 900 },
  "annotations": [
    { "type": "box", "selector": ".fi-modal", "padding": 8 },
    { "type": "circle", "id": 1, "selector": "textarea[name='reason']", "position": "tl" },
    { "type": "circle", "id": 2, "selector": "button[type='submit']", "position": "tr" }
  ],
  "caption": "Figura 4.6 — Modal de suspensión de organización con campo de razón.",
  "outputPath": "docs/guides/screenshots/admin/admin-org-suspend-modal.png"
}
```

El script de Fase 2 (ver `00-toolchain.md`) consume este JSON.

---

## 6. Procedimiento operativo

### 6.1 Captura completa (primera vez)

```bash
make captures               # ejecuta todo el inventario
```

Tiempo estimado: 40–60 minutos en hardware moderno.

### 6.2 Re-captura individual

```bash
node docs/guides/scripts/captures.mjs --only admin-org-suspend-modal
```

### 6.3 Re-captura por guía

```bash
node docs/guides/scripts/captures.mjs --guide admin
node docs/guides/scripts/captures.mjs --guide impl
node docs/guides/scripts/captures.mjs --guide user
```

### 6.4 Verificación post-captura

```bash
make verify-captures        # ejecuta scripts/verify-captures.mjs
```

Que valida:

- Cada slug del JSON tiene su PNG correspondiente
- Cada PNG pesa < 350 kB
- Cada PNG es 1440px de ancho ± 5px
- No hay PNGs huérfanos (sin entrada en JSON)

---

## 7. Bitácora de cambios

| Fecha | Cambio | Por |
|---|---|---|
| 2026-05-17 | Creación inicial; 110 capturas planificadas | Juan Carlos Hooper |
