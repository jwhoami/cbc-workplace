# Blueprint: Tres Guías Profesionales — CBC Workplace

**Proyecto:** CBC Workplace (Crossroads Bible Church)
**Repositorio:** `hooperits/cbc-workplace`
**Idioma de las guías:** Español latinoamericano neutro
**Versión:** v1.0 — Mayo 2026
**Autor responsable:** Juan Carlos Hooper
**Estado de este documento:** Fase 1 — Fundamentos (no contiene contenido editorial; sólo plan de ejecución)

---

## 1. Objetivo

Producir tres documentos profesionales en formato **MS Word (.docx)** que cubran
el ciclo completo de uso, despliegue y administración del job board **CBC
Workplace**, especs 002–009 ya mergeadas en `main`.

| Guía | Audiencia | Páginas estimadas | Profundidad |
|---|---|---|---|
| Administración | Super-admins de `/admin` | 40–60 | Referencia exhaustiva |
| Implementación | Desarrolladores y DevOps | 50–80 | Referencia exhaustiva con file:line |
| Usuario final | Organizaciones publicando + candidatos buscando | 30–50 | Tutorial paso a paso |

---

## 2. Decisiones de alcance asumidas

Tomadas en ausencia de directrices contradictorias del usuario; cada una
queda `// TODO: confirmar antes de v1.0` en los entregables.

| Tema | Decisión asumida | Origen |
|---|---|---|
| Branding | Plantilla limpia profesional con paleta sobria (carbón #1F2937, azul #2563EB, fondo #F9FAFB); reemplazar cuando llegue la oficial | Sin assets CBC oficiales en `public/` ni en `resources/` |
| Entorno capturas | Sail local + `php artisan db:seed --class=Spec009DemoSeeder` + Spec 008 data | `database/seeders/Spec009DemoSeeder.php:28-65` documenta credenciales reproducibles |
| Profundidad | Referencia exhaustiva (no quick-start) | Petición explícita del usuario |
| Formato salida | `.docx` primario; PDF derivado vía LibreOffice headless | Restricción operativa pedida |
| Identidad | "Crossroads Bible Church" — NO "Caribbean Business Coalition" | `memory/project_cbc_acronym.md` |
| Atribución | Solo Juan Carlos Hooper; sin `Co-Authored-By: Claude` | `memory/feedback_authorship.md` |
| Tono | Formal con "usted" para Administración e Implementación; cercano con "tú" para Usuario | Convención latinoamericana profesional |

---

## 3. Glosario de términos canónicos

Términos que DEBEN usarse de manera consistente en las tres guías. Definidos
contra el código verificado para evitar alucinaciones.

| Término | Definición canónica |
|---|---|
| **Bolsa de Trabajo** | Módulo job board completo (especs 002–009). Nombre oficial del grupo de navegación en `/admin` (`AdminPanelProvider.php:47-56`) |
| **Panel Admin** | Filament panel en `/admin`, guard `admin`, color amber; super-admins (`AdminPanelProvider.php:25-93`) |
| **Panel Member** | Filament panel en `/member`, guard `member`; organizaciones y candidatos (`MemberPanelProvider.php:35-152`) |
| **Panel Venture** | Filament panel en `/app`, panel por defecto, sin auth middleware; público (`VenturePanelProvider.php:23-92`) |
| **Portal público** | Rutas en `routes/public.php` sin sesión (`/bolsa-de-trabajo`, `/sitemap.xml`); más `/bolsa-de-trabajo/{slug}` en `routes/web.php` |
| **Organización** | Modelo `Organization`; pertenece 1:1 a un Member vía `member_id`; tiene `verification_state` (PENDING / VERIFIED) y flags ortogonales de suspensión (`suspended_at`, `suspended_by`, `suspension_reason`) |
| **Suspensión** | Flag ortogonal sobre `Organization`, NO un estado de verificación. Implementada en `SuspendOrganization::run()` (`app/Actions/Admin/SuspendOrganization.php`); `OrganizationVerificationState::SUSPENDED` está deprecado (PR #26) |
| **Reactivación** | Inverso de suspensión vía `ReactivateOrganization::run()`; preserva `verification_state`, NO reactiva ofertas cerradas en cascada |
| **Empleo** / **Oferta** | Modelo `JobListing`; estados via `JobListingState` (DRAFT/PENDING/ACTIVE/REJECTED/CLOSED/EXPIRED) |
| **Aplicación** / **Postulación** | Modelo `Application`; estados via `ApplicationStatus` (RECEIVED/IN_REVIEW/INTERVIEW/REJECTED/ACCEPTED) |
| **Alerta de empleo** | Modelo `JobAlert`; frecuencias `Daily` (1), `Weekly` (2), `Instant` (3) — `JobAlertFrequency` |
| **Digest** | Email agrupador de ofertas que cumplen criterios de un `JobAlert`; armado por `BuildDigestForAlertAction`, despachado por `DispatchDailyDigestAction` / `DispatchWeeklyDigestAction` |
| **Categoría de empleo** | Reutiliza tabla `categories` con `scope="JobListing"` (decisión arquitectónica spec 002); slug + icon añadidos en migración `2026_03_23_000001_add_slug_icon_to_categories_table.php` |
| **Candidato** | `Member` con `CandidateProfile` 1:1; no es una tabla de usuarios separada (decisión spec 004) |
| **Action** | Clase bajo `app/Actions/` que aplica patrón `lorisleiva/laravel-actions` (`AsAction`/`AsJob`/`AsListener`) |
| **Widget de dashboard** | Filament widget bajo `app/Filament/Admin/Widgets/`; los introducidos por spec 009: `JobBoardStatsOverview`, `PendingJobListingApprovalsWidget`, `PendingOrganizationVerificationsWidget`, `RecentApplicationsWidget` |

> Cualquier término técnico que no esté en este glosario y aparezca en las
> guías debe verificarse contra el código antes de fijarlo.

---

## 4. Grafo de dependencias y paralelizable

```
                   Fase 1 (esta sesión)
                   └── Fundamentos
                       ├── 00-blueprint.md
                       ├── 00-style-guide.md
                       ├── 00-screenshot-inventory.md
                       ├── 00-toolchain.md
                       ├── templates/cbc-reference.docx
                       └── Makefile + scripts/

                   Fase 2 (sesión nueva, secuencial)
                   └── Pipeline de capturas funcional
                       ├── scripts/captures.mjs configurado
                       ├── 100+ screenshots generadas en docs/guides/screenshots/
                       └── Anotaciones aplicadas vía scripts/annotate.mjs

  ┌──────────────────────┼──────────────────────┐
  │                      │                      │
  Fase 3                  Fase 4                  Fase 5  (PARALELIZABLES)
  Guía de Administración  Guía de Implementación  Guía del Usuario
  docs/guides/admin/      docs/guides/impl/       docs/guides/user/
  ├── 01-intro.md         ├── 01-arquitectura.md  ├── 01-bienvenida.md
  ├── 02-acceso.md        ├── 02-setup-local.md   ├── 02-registro.md
  ├── 03-dashboard.md     ├── 03-paneles.md       ├── 03-perfil-org.md
  ├── 04-orgs.md          ├── 04-actions.md       ├── 04-publicar-empleo.md
  ├── 05-empleos.md       ├── 05-alertas.md       ├── 05-gestionar-app.md
  ├── 06-categorias.md    ├── 06-policies.md      ├── 06-busqueda.md
  ├── 07-usuarios.md      ├── 07-public-routes.md ├── 07-alertas.md
  ├── 08-alertas.md       ├── 08-deploy.md        ├── 08-suspension.md
  ├── 09-auditoria.md     ├── 09-extender.md      ├── 09-cuenta.md
  ├── 10-troubleshoot.md  ├── 10-troubleshoot.md  └── 10-faq.md
  └── apendices/          ├── 11-changelog.md
                          └── apendices/
  │                      │                      │
  └──────────────────────┼──────────────────────┘
                         │
                   Fase 6 (sesión final, secuencial)
                   └── Ensamblaje y QA
                       ├── make guides → 3 .docx
                       ├── make pdf → 3 .pdf (opcional)
                       ├── Revisión cruzada de glosario
                       ├── Verificación de file:line claims
                       └── Sanitización de credenciales/PII
```

### 4.1 Dependencias estrictas

- **Fase 2 depende de Fase 1** (necesita plantilla y style guide para que las anotaciones sean coherentes).
- **Fases 3, 4, 5 dependen de Fase 2** (las guías referencian screenshots que deben existir).
- **Fase 6 depende de 3, 4, 5** (no se ensambla hasta tener contenido en las tres).
- Las **Fases 3, 4, 5 son paralelizables entre sí**: cada una toca su propio subdirectorio y comparte solo el glosario (read-only) y la plantilla (read-only).

### 4.2 Paralelización recomendada

Usar `superpowers:dispatching-parallel-agents` para Fase 2→3→4→5:

- Despachar **3 agentes en paralelo** en una sola llamada (uno por guía).
- Cada agente recibe su sub-prompt auto-contenido (ver §7).
- Cada agente solo escribe bajo `docs/guides/<su-prefijo>/`.
- Coordinación: el agente padre verifica al cierre que ningún archivo haya sido tocado por dos agentes.

---

## 5. Pasos atómicos (cada uno ejecutable por agente fresco)

Cada paso lleva un **brief de contexto** auto-contenido que permite a un
agente sin memoria previa ejecutarlo. Los briefs completos viven en §7.

| # | Paso | Tipo | Depende de | Brief en |
|---|---|---|---|---|
| F1.1 | Crear `00-blueprint.md` | Doc | — | (este archivo) |
| F1.2 | Crear `00-style-guide.md` | Doc | F1.1 | (esta sesión) |
| F1.3 | Crear `00-screenshot-inventory.md` | Doc | F1.1 | (esta sesión) |
| F1.4 | Crear `00-toolchain.md` | Doc | F1.1 | (esta sesión) |
| F1.5 | Generar `templates/cbc-reference.docx` | Tool | F1.2 | (esta sesión) |
| F1.6 | Crear `Makefile` + `scripts/captures.mjs` | Tool | F1.2, F1.4 | (esta sesión) |
| F2.1 | Configurar Playwright + ejecutar capturas | Tool | F1.6 | §7.1 |
| F2.2 | Anotar capturas (flechas, números) | Tool | F2.1 | §7.1 |
| F3.x | Escribir capítulos de Guía Admin | Doc | F2.2 | §7.2 |
| F4.x | Escribir capítulos de Guía Impl | Doc | F2.2 | §7.3 |
| F5.x | Escribir capítulos de Guía Usuario | Doc | F2.2 | §7.4 |
| F6.1 | Ejecutar `make guides` y revisar `.docx` | QA | F3+F4+F5 | §7.5 |
| F6.2 | QA cruzado de glosario, file:line, PII | QA | F6.1 | §7.5 |
| F6.3 | Generar PDF y abrir PR final | Release | F6.2 | §7.5 |

---

## 6. Riesgos identificados (top 5)

1. **Drift entre guías y código** — Las especs 002–009 ya están en `main`, pero
   futuros cambios pueden desactualizar las guías. **Mitigación:** cada claim
   técnico lleva `file:line` para facilitar la auditoría futura; añadir un
   check de CI que valide que `docs/guides/**` no menciona archivos
   inexistentes.

2. **Credenciales en capturas** — `Spec009DemoSeeder` usa `admin@example.com`
   / `password` reproducibles. Si una captura muestra el formulario con
   credenciales reales escritas, leak en .docx. **Mitigación:** §6.3 del
   style guide; checklist obligatoria en Fase 6.2 (grep de cadenas
   sensibles).

3. **Inconsistencia de tono entre guías paralelizadas** — Tres agentes
   escribiendo en paralelo pueden divergir en voz, formato de pasos, etc.
   **Mitigación:** el style guide es la única fuente de verdad; QA cruzado
   en Fase 6.2 con checklist explícito.

4. **Branding placeholder vs. oficial** — La plantilla v1.0 usa paleta
   genérica. Cuando CBC entregue su brand kit, hay que regenerar `.docx`
   completas. **Mitigación:** todos los colores y logos viven en el
   reference template; reemplazar el `.docx` y volver a correr
   `make guides` regenera todo.

5. **Volumen de capturas (~100)** — Playwright contra `php artisan serve`
   más anotaciones puede tomar 30–60 min de ejecución; un fallo a mitad
   de camino deja un set inconsistente. **Mitigación:** el script de
   capturas debe ser **idempotente** (cada captura tiene su propio target
   reproducible) y soportar `--only <slug>` para regenerar individuales.

---

## 7. Sub-prompts auto-contenidos (para Fases 2–6)

Los sub-prompts completos viven en `docs/guides/00-subprompts/`. Aquí solo
el índice:

| ID | Archivo | Sesión a ejecutar |
|---|---|---|
| §7.1 | `docs/guides/00-subprompts/fase-2-capturas.md` | Generación de capturas Playwright + anotaciones |
| §7.2 | `docs/guides/00-subprompts/fase-3-admin.md` | Guía de Administración (40–60 pág) |
| §7.3 | `docs/guides/00-subprompts/fase-4-impl.md` | Guía de Implementación (50–80 pág) |
| §7.4 | `docs/guides/00-subprompts/fase-5-usuario.md` | Guía del Usuario (30–50 pág) |
| §7.5 | `docs/guides/00-subprompts/fase-6-ensamblaje.md` | Ensamblaje, QA y PR final |

Cada uno es un bloque copiable diseñado para pegarse en una sesión nueva
de Claude Code sin contexto previo.

---

## 8. Convenciones de paths

```
docs/guides/
├── 00-blueprint.md              (este archivo)
├── 00-style-guide.md            (entregable 2)
├── 00-screenshot-inventory.md   (entregable 3)
├── 00-toolchain.md              (entregable 4)
├── 00-subprompts/               (sub-prompts para fases 2-6)
│   ├── fase-2-capturas.md
│   ├── fase-3-admin.md
│   ├── fase-4-impl.md
│   ├── fase-5-usuario.md
│   └── fase-6-ensamblaje.md
├── templates/
│   ├── cbc-reference.docx       (Pandoc reference-doc)
│   └── README.md                (cómo regenerar)
├── scripts/
│   ├── build-reference-docx.py  (genera templates/cbc-reference.docx)
│   ├── captures.mjs             (orquestador Playwright)
│   └── annotate.mjs             (anotador de PNGs)
├── screenshots/                 (generado en Fase 2, gitignored)
│   ├── admin/
│   ├── impl/
│   └── user/
├── admin/                       (Fase 3)
│   ├── 01-intro.md
│   ├── 02-acceso.md
│   └── ...
├── impl/                        (Fase 4)
│   ├── 01-arquitectura.md
│   └── ...
├── user/                        (Fase 5)
│   ├── 01-bienvenida.md
│   └── ...
└── build/                       (generado por make guides, gitignored)
    ├── cbc-workplace-administracion.docx
    ├── cbc-workplace-implementacion.docx
    └── cbc-workplace-usuario.docx
```

---

## 9. Mutación del plan

Si durante Fases 2–6 cualquier descubrimiento contradice este blueprint,
actualizar este archivo en el mismo commit que el cambio y registrar la
razón en una sección `## 10. Bitácora de cambios`. No editar las fases ya
ejecutadas — añadir un nuevo paso `F<n>.<m>-fix`.

---

## 10. Bitácora de cambios

| Fecha | Cambio | Por |
|---|---|---|
| 2026-05-17 | Creación inicial del blueprint | Juan Carlos Hooper |
