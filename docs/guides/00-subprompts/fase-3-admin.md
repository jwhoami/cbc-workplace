# Sub-prompt Fase 3 — Guía de Administración

> **Cómo usar:** copie todo el bloque siguiente en una nueva sesión de
> Claude Code, dentro del repo `cbc-workplace` en una rama nueva
> (sugerido: `docs/guides-fase-3-admin`). Auto-contenido.

---

## Misión

Escribir la **Guía de Administración** completa (40–60 páginas) para
super-admins del panel `/admin` de CBC Workplace. Los 11 capítulos viven
bajo `docs/guides/admin/` y se ensamblan con `make guides GUIDE=admin`.

## Audiencia

Super-administradores y moderadores que operan el panel `/admin`. Asumen
familiaridad con Filament a nivel de usuario, no técnico. El registro es
formal con pronombre **usted**.

## Contexto previo (no necesita lectura adicional, sólo abrir estos archivos)

- `docs/guides/00-blueprint.md` — visión global y glosario canónico (§3)
- `docs/guides/00-style-guide.md` — convenciones editoriales completas
- `docs/guides/00-screenshot-inventory.md` — capturas disponibles bajo
  `docs/guides/screenshots/admin/`
- `docs/guides/admin/metadata.yaml` — metadata Pandoc ya creada
- Memorias relevantes en `~/.claude/projects/-home-juanca-proys-cbc-workplace/memory/`:
  - `project_bolsa_de_trabajo.md`
  - `project_spec_009_shipped.md`
  - `project_cbc_acronym.md`

CBC = **Crossroads Bible Church**. Atribución: solo Juan Carlos Hooper.

## Estructura de capítulos

Crear estos archivos bajo `docs/guides/admin/`:

| Archivo | Capítulo | Capturas que referencia |
|---|---|---|
| `01-introduccion.md` | Bienvenida, alcance del panel admin, glosario | `admin-dashboard-full` |
| `02-acceso-y-sesion.md` | Login, cambio de contraseña, cierre de sesión, badge de rol | `admin-login-form`, `admin-global-search-role-badge` |
| `03-dashboard-y-widgets.md` | Los 4 widgets de spec 009 explicados uno por uno | `admin-dashboard-*-widget` |
| `04-organizaciones.md` | Listado, verificación, suspensión, reactivación | Todo el bloque `admin-org-*` y `admin-flow-suspend-*` y `admin-flow-reactivate-*` |
| `05-empleos.md` | Aprobación / rechazo de ofertas, ciclo de vida | `admin-job-*`, `admin-flow-approve-job-*` |
| `06-categorias.md` | Gestión de la taxonomía JobListing | `admin-category-*` |
| `07-usuarios-y-roles.md` | Matriz de permisos, alta/baja de admins | `admin-users-list`, `admin-roles-manage` |
| `08-postulaciones-y-candidatos.md` | Visualización de aplicaciones y perfiles | `admin-applications-list`, `admin-application-view`, `admin-candidates-list` |
| `09-alertas-de-empleo.md` | Cómo funciona el sistema de alertas; qué controlar | (sin captura admin específica, referencia conceptual) |
| `10-auditoria.md` | Activitylog, qué eventos se registran, cómo investigar | `admin-activitylog-org-suspended` |
| `11-troubleshooting.md` | Síntomas y soluciones para issues comunes | (sin capturas) |
| `apendice-a-glosario.md` | Glosario completo (extender el canónico) | — |
| `apendice-b-comandos-utiles.md` | Comandos artisan que el admin puede pedir al equipo dev | — |

## Reglas de contenido

### Verificación obligatoria

**Cada afirmación técnica debe estar verificada contra el código.** Usar
`Read` + `Grep` sobre `app/Filament/Admin/`, `app/Actions/Admin/`,
`app/Policies/`, `app/Models/Organization.php`. Cada claim con `file:line`.

Ejemplos a verificar:

- "La suspensión envía un correo a los administradores de la
  organización" → verificar en `app/Actions/Admin/SuspendOrganization.php`
- "Los widgets se actualizan automáticamente cada N segundos" → grep
  `getPollingInterval` en `app/Filament/Admin/Widgets/`
- "El badge de rol aparece junto a la búsqueda" → verificar en
  `app/Providers/Filament/AdminPanelProvider.php` el render hook
  `GLOBAL_SEARCH_AFTER`

### Tono y estilo

- Pronombre **usted**.
- Procedimientos numerados explícitos: `1.`, `2.`, `3.`.
- Cada capítulo abre con un resumen de 3–5 líneas y un índice local.
- Cada procedimiento cierra con un párrafo "**Qué esperar después.**"
- Callouts según `00-style-guide.md §4`. Usar **Importante** para
  acciones destructivas (suspensión, rechazo).

### Capturas

Insertar con `![Caption](../screenshots/admin/admin-<slug>.png)` y caption
inmediatamente debajo en cursiva: `*Figura 4.6 — ...*`.

### Glosario

Respetar el glosario canónico del blueprint (§3). NO redefinir términos.
El apéndice A puede añadir términos específicos del rol admin pero NO
contradecir.

## Entregables

13 archivos `.md` totalizando 40–60 páginas equivalentes en `.docx`.
Aproximadamente 4–6 páginas por capítulo, 8–12 páginas para los capítulos
más densos (organizaciones, troubleshooting).

## Definición de "hecho"

```bash
make guides GUIDE=admin
# produce: docs/guides/build/cbc-workplace-admin.docx
# tamaño esperado: 800 kB - 3 MB con capturas

# Abrir el .docx en Word / LibreOffice y verificar:
#   - Portada con título, subtítulo, autor, fecha
#   - Tabla de contenidos con 11 capítulos + apéndices
#   - Capturas insertadas con caption numerado
#   - Callouts con sus colores correctos
#   - No hay TODOs
#   - No hay credenciales reales
#   - No hay menciones a "Caribbean Business Coalition"
```

Ejecutar la checklist editorial pre-merge del `00-style-guide.md §13`
sobre cada capítulo.

## Cierre

- Commit convencional en español:
  `feat(docs): guía de administración completa (11 capítulos + apéndices)`
- Push a `hooperits/cbc-workplace`.
- PR contra `main` (o contra rama de coordinación si se está corriendo en
  paralelo con Fases 4 y 5).
