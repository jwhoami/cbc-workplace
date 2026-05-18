# Sub-prompt Fase 4 — Guía de Implementación

> **Cómo usar:** copie todo el bloque siguiente en una nueva sesión de
> Claude Code, dentro del repo `cbc-workplace` en una rama nueva
> (sugerido: `docs/guides-fase-4-impl`). Auto-contenido.

---

## Misión

Escribir la **Guía de Implementación** completa (50–80 páginas) para
desarrolladores y DevOps que despliegan, mantienen o extienden CBC
Workplace. Los 12 capítulos viven bajo `docs/guides/impl/` y se ensamblan
con `make guides GUIDE=impl`.

## Audiencia

Desarrolladores con experiencia básica en Laravel y Filament; DevOps
desplegando a producción. Asume conocimiento de Composer, Sail/Docker,
Pest, queue workers, cron, MySQL.

Pronombre: **usted**, registro técnico.

## Contexto previo (no necesita lectura adicional)

Archivos a leer antes de escribir:

- `docs/guides/00-blueprint.md`
- `docs/guides/00-style-guide.md`
- `docs/guides/00-screenshot-inventory.md`
- `docs/guides/impl/metadata.yaml`
- Memorias relevantes:
  - `project_bolsa_de_trabajo.md`
  - `project_spec_007_shipped.md`
  - `project_spec_008_shipped.md`
  - `project_spec_009_shipped.md`
  - `feedback_laravel_actions_testing.md`
  - `feedback_test_coverage_pitfalls.md`

CBC = **Crossroads Bible Church**. Atribución: solo Juan Carlos Hooper.

## Estructura de capítulos

Crear estos archivos bajo `docs/guides/impl/`:

| Archivo | Capítulo | Capturas / diagramas |
|---|---|---|
| `01-arquitectura.md` | Stack, capas, paneles, decisiones rechazadas (Scout, Spatie Permission, etc.) | `impl-arch-overview`, `impl-arch-panels` |
| `02-setup-local.md` | Sail, requisitos, migrate, seeders, queue worker, schedule | `impl-terminal-*`, `impl-mailpit-inbox` |
| `03-paneles-filament.md` | Los 3 PanelProviders, navigation groups, render hooks, login | (sin captura — código + texto) |
| `04-actions-pattern.md` | lorisleiva/laravel-actions: AsAction, AsJob, AsListener — ejemplos del repo | (sin captura) |
| `05-modelos-y-relaciones.md` | Cada modelo de Bolsa de Trabajo con ER textual + scopes destacados | `impl-tree-models` |
| `06-policies-y-autorizacion.md` | Las 4 policies de Bolsa de Trabajo + suspensión cascade | `impl-arch-suspension-cascade` |
| `07-rutas-publicas-y-seo.md` | routes/public.php, sitemap, cookie-free middleware, SEO | `impl-sitemap-xml` |
| `08-alertas-y-digests.md` | Pipeline completo: instant + daily + weekly + dedup | `impl-arch-alerts-pipeline`, `impl-arch-daily-digest` |
| `09-deploy-y-operacion.md` | Producción: queue worker, cron, supervisor, nginx, .env | `impl-supervisor-config`, `impl-nginx-config-snippet`, `impl-deploy-checklist-diagram` |
| `10-observabilidad-y-tests.md` | activitylog, laravel.log, Pest, coverage | `impl-pest-run-*`, `impl-coverage-report`, `impl-activitylog-query` |
| `11-extender-el-sistema.md` | Cómo añadir un widget, una policy, una action, una categoría | (sin captura) |
| `12-changelog-y-versiones.md` | Especs 002–009 con dependencias y referencias a tags/commits | (sin captura) |
| `apendice-a-glosario.md` | Términos técnicos extendidos | — |
| `apendice-b-variables-env.md` | Todas las claves `.env` agrupadas con default y rango | — |

## Reglas de contenido

### Verificación obligatoria con file:line

**Toda afirmación técnica debe llevar referencia file:line.** El reviewer
debe poder validar con un `Read` en segundos.

Áreas a verificar:

- `app/Providers/Filament/*.php` — IDs, paths, guards, brand color, nav groups
- `app/Filament/Admin/Widgets/*.php` — clase, tipo, polling
- `app/Actions/**/*.php` — traits, signature de `handle()`
- `app/Models/*.php` — relaciones, scopes, casts de enum
- `app/Policies/*.php` — métodos públicos, condiciones
- `app/Enums/*.php` — cases con valor numérico
- `routes/*.php` — rutas, middleware, nombres
- `app/Console/Kernel.php` — schedule entries
- `database/migrations/*.php` — orden cronológico
- `composer.json` — versiones de dependencias
- `config/alerts.php` — claves de configuración
- `database/seeders/Spec009DemoSeeder.php` — credenciales seedeadas

### Snippets de código

Mostrar código del repo real, no inventado. Acompañar con file:line
debajo del bloque:

```php
public function update(Member $member, Organization $organization): bool
{
    if ($this->organizationFrozenForMember($member)) {
        return false;
    }
    // ...
}
```

> Fuente: `app/Policies/OrganizationPolicy.php:15-26`

### Diagramas Mermaid

Capítulo 1, 6, 8: incluir bloques Mermaid renderizados como PNG en
`docs/guides/screenshots/impl/`. La fuente Mermaid vive embebida en el
`.md` y se renderiza por `mermaid-cli` (`mmdc`) durante la Fase 2 del
pipeline de capturas.

### Tono y estilo

- Pronombre **usted**, técnico.
- Acepta jerga: "queue worker", "cron", "middleware", "scopes", "fakes".
- Cada capítulo abre con "Resumen ejecutivo" de 5 líneas y un índice.
- Procedimientos numerados.
- Callouts según `00-style-guide.md §4`. Usar **Importante** para
  acciones destructivas (migrate:fresh en prod, etc.).
- En `09-deploy-y-operacion.md` debe haber una checklist de despliegue
  explícita.

### Glosario

Respetar el glosario canónico del blueprint (§3). Extender con
terminología técnica (Action, Policy, Pest, Sail, Pandoc) en el apéndice A.

## Entregables

14 archivos `.md` totalizando 50–80 páginas en `.docx`. Capítulos densos
(4-deploy, 8-alertas, 11-extender) pueden llegar a 10–12 páginas; el
resto 4–6.

## Definición de "hecho"

```bash
make guides GUIDE=impl
# produce: docs/guides/build/cbc-workplace-impl.docx
# tamaño: 1–4 MB con capturas + diagramas

# Validar en Word / LibreOffice:
#   - Portada
#   - TOC con 12 capítulos + apéndices
#   - Diagramas Mermaid renderizados correctamente
#   - Bloques de código con tipografía monoespaciada
#   - file:line citas presentes en TODOS los claims técnicos
#   - Checklist de despliegue completo en capítulo 9
```

Ejecutar:

```bash
grep -rn 'TODO\|FIXME\|XXX' docs/guides/impl/   # vacío
grep -rn 'crossroads\.cl' docs/guides/impl/      # vacío
grep -rn 'Caribbean Business' docs/guides/impl/  # vacío
```

## Cierre

- Commit:
  `feat(docs): guía de implementación completa (12 capítulos + apéndices)`
- Push a `hooperits/cbc-workplace`.
- PR contra `main` o rama de coordinación.
