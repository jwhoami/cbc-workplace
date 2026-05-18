# Toolchain — Pipeline reproducible de generación de guías

**Objetivo:** dado un conjunto de archivos Markdown bajo `docs/guides/<guide>/`,
producir tres `.docx` profesionales en una sola invocación de `make guides`.

---

## 1. Pipeline en una vista

```
docs/guides/<admin|impl|user>/*.md
        │
        ▼
[1] markdownlint        ← linter de Markdown
        │
        ▼
[2] captures.mjs        ← Playwright + Sharp (solo si screenshots ausentes)
        │
        ▼
[3] annotate.mjs        ← anotaciones rojas + caja + sombra
        │
        ▼
[4] concat & pandoc     ← une .md y aplica reference template
        │
        ▼
docs/guides/build/cbc-workplace-<guide>.docx
        │
        ▼
[5] libreoffice --convert-to pdf  (opcional)
        │
        ▼
docs/guides/build/cbc-workplace-<guide>.pdf
```

---

## 2. Dependencias del entorno

### 2.1 Sistema (Linux / macOS)

| Herramienta | Versión mínima | Verificación |
|---|---|---|
| GNU Make | 4.0 | `make --version` |
| Pandoc | 3.1 | `pandoc --version` |
| Python | 3.10 | `python3 --version` |
| Node.js | 20 LTS | `node --version` |
| LibreOffice (opcional, para PDF) | 24.x | `libreoffice --version` |
| Git | 2.40 | `git --version` |
| pngquant | 2.18 | `pngquant --version` |

### 2.2 Python — para construir la plantilla de referencia

```bash
python3 -m pip install --user python-docx==1.1.2
```

Versión pineada para reproducibilidad.

### 2.3 Node — para capturas y anotaciones

```bash
cd docs/guides
npm install
```

`package.json` (`docs/guides/package.json`):

```json
{
  "name": "cbc-workplace-guides",
  "version": "1.0.0",
  "private": true,
  "type": "module",
  "scripts": {
    "captures": "node scripts/captures.mjs",
    "annotate": "node scripts/annotate.mjs",
    "verify": "node scripts/verify-captures.mjs"
  },
  "dependencies": {
    "playwright": "^1.46.0",
    "sharp": "^0.33.5",
    "yargs": "^17.7.2"
  }
}
```

Tras `npm install`, ejecutar una vez:

```bash
npx playwright install chromium
```

---

## 3. Makefile (raíz del proyecto)

El Makefile vive en la raíz del repo para que `make guides` funcione desde
cualquier rama. Su contenido se entrega en este commit.

### 3.1 Targets

| Target | Hace |
|---|---|
| `make guides` | Genera los tres `.docx` |
| `make guides-pdf` | Genera los tres `.pdf` (depende de `guides`) |
| `make captures` | Ejecuta el pipeline completo de capturas |
| `make captures-only SLUG=<slug>` | Regenera una sola captura |
| `make verify-captures` | Valida integridad del set |
| `make reference-docx` | Regenera `templates/cbc-reference.docx` |
| `make lint` | Lintea Markdown con `markdownlint` |
| `make clean` | Borra `docs/guides/build/` y screenshots cacheadas |
| `make help` | Muestra los targets |

### 3.2 Variables configurables

```makefile
GUIDE ?= all          # all | admin | impl | user
LANG  ?= es
DATE  ?= $(shell date +"%Y-%m")
TEMPLATE ?= docs/guides/templates/cbc-reference.docx
BUILD ?= docs/guides/build
```

Uso:

```bash
make guides GUIDE=admin       # solo admin
make guides DATE=2026-06      # forzar fecha en footer
```

---

## 4. Comando Pandoc canónico

Para cada guía, Pandoc se invoca así (esto está dentro del Makefile):

```bash
pandoc \
  --from markdown+yaml_metadata_block+pipe_tables+raw_html \
  --to docx \
  --reference-doc=docs/guides/templates/cbc-reference.docx \
  --toc \
  --toc-depth=3 \
  --number-sections \
  --metadata-file=docs/guides/<guide>/metadata.yaml \
  --resource-path=.:docs/guides:docs/guides/<guide> \
  --top-level-division=chapter \
  --lua-filter=docs/guides/scripts/callout-filter.lua \
  --output=docs/guides/build/cbc-workplace-<guide>.docx \
  $(ls docs/guides/<guide>/*.md | sort)
```

### 4.1 Metadata YAML por guía

`docs/guides/admin/metadata.yaml` (ejemplo):

```yaml
---
title: "Guía de Administración"
subtitle: "CBC Workplace · Bolsa de Trabajo"
author: "Juan Carlos Hooper"
date: "Mayo 2026"
version: "v1.0"
lang: es
toc-title: "Índice"
---
```

### 4.2 Filtro Lua para callouts

`docs/guides/scripts/callout-filter.lua` transforma blockquotes que empiezan
con `**Nota.**`, `**Atención.**`, `**Importante.**`, `**Buena práctica.**`,
o `**Solo administradores.**` en párrafos con el estilo nombrado
correspondiente del reference template (`Callout-Note`, `Callout-Warn`,
`Callout-Danger`, `Callout-Success`, `Callout-AdminOnly`).

El archivo se entrega en este commit.

---

## 5. Plantilla de referencia (Pandoc)

### 5.1 Cómo se construye

`docs/guides/scripts/build-reference-docx.py` genera
`docs/guides/templates/cbc-reference.docx` programáticamente con
`python-docx`. Esto evita guardar un binario en el repo con configuración
manual frágil; cualquier cambio de estilo se hace en código Python.

### 5.2 Estilos nombrados que define

| Estilo | Tipo | Uso |
|---|---|---|
| `Title` | paragraph | Portada |
| `Subtitle` | paragraph | Subtítulo de portada |
| `Heading 1`–`Heading 4` | paragraph | Jerarquía de encabezados |
| `Body Text` | paragraph | Cuerpo |
| `Source Code` | character | Code inline |
| `Verbatim Char` | character | Code inline alternativo (Pandoc) |
| `Code` | paragraph | Bloque de código |
| `Caption` | paragraph | Pie de figura / tabla |
| `Quote` | paragraph | Cita |
| `Callout-Note` | paragraph | Callout informativo |
| `Callout-Warn` | paragraph | Callout de atención |
| `Callout-Danger` | paragraph | Callout importante |
| `Callout-Success` | paragraph | Callout buena práctica |
| `Callout-AdminOnly` | paragraph | Callout solo admins |
| `TOC 1`–`TOC 3` | paragraph | Tabla de contenidos |

### 5.3 Regenerar la plantilla

```bash
make reference-docx
# equivale a:
python3 docs/guides/scripts/build-reference-docx.py
```

> **Atención.** Editar `cbc-reference.docx` a mano en Word funciona, pero
> los cambios se pierden al volver a correr el script. La fuente de verdad
> son los tokens en el Python.

---

## 6. Pipeline de capturas

### 6.1 captures.mjs

`docs/guides/scripts/captures.mjs` lee el inventario JSON, lanza Playwright,
ejecuta los pre-actions, espera selectores, captura PNG, opcionalmente
recorta, comprime con Sharp / pngquant, y guarda en `docs/guides/screenshots/`.

Flags:

- `--guide <admin|impl|user>` — limita el alcance
- `--only <slug>` — una sola captura
- `--list` — lista los slugs disponibles sin ejecutar
- `--headed` — abre browser visible (debug)
- `--base-url <url>` — sobreescribe `http://localhost`

### 6.2 annotate.mjs + annotations.mjs

**Arquitectura post-Fase 2:** la resolución de selectores → coordenadas se
hace **durante** la captura, no después. `captures.mjs` resuelve cada
`annotation.selector` vía `page.locator().boundingBox()` mientras la página
está renderizada en el estado correcto, compone el overlay SVG con Sharp,
sobrescribe el PNG y persiste las coords en un sidecar `<slug>.coords.json`
junto al PNG.

`annotate.mjs` se convierte en una herramienta **standalone** para
re-aplicar el overlay sin relanzar Playwright (útil para cambiar tokens
visuales o reparar PNGs sin el browser corriendo). Lee coords desde:

1. Sidecar `<png-path>.coords.json` (generado por `captures.mjs`)
2. Inline en el descriptor (`annotations[].x/y/w/h` ya calculadas)

El núcleo compartido vive en `scripts/annotations.mjs` (`resolveAnnotations`,
`buildAnnotationSvg`, `applyAnnotations`).

**Schema soportado:**

```jsonc
"annotations": [
  // Caja roja alrededor de un elemento
  { "type": "box", "selector": ".fi-modal", "padding": 8 },

  // Círculo numerado en una esquina del elemento (tl/tr/bl/br/center)
  { "type": "circle", "selector": "button[type=submit]", "id": 1, "position": "tr" },

  // Flecha de un selector a otro
  { "type": "arrow", "from": ".fi-btn-suspender", "to": ".fi-modal" }
]
```

**Estilos rendereados:**

- Cajas: rectángulo rojo (`#DC2626`) sin relleno, stroke 3px, bordes redondeados
- Círculos: rojo sólido, número blanco en Inter bold 14px
- Flechas: línea roja + cabeza triangular

### 6.3 verify-captures.mjs

Valida que para cada descriptor en `captures.json` existe un PNG, que pesa
< 350 kB y que tiene 1440px de ancho (± 5px).

```bash
make verify-captures
```

---

## 7. Generación de PDF (opcional)

```bash
make guides-pdf
# equivale a:
libreoffice --headless --convert-to pdf \
  --outdir docs/guides/build \
  docs/guides/build/cbc-workplace-admin.docx \
  docs/guides/build/cbc-workplace-impl.docx \
  docs/guides/build/cbc-workplace-user.docx
```

> **Nota.** LibreOffice respeta la mayoría de estilos del `.docx`, pero
> las anotaciones aplicadas como imágenes preservan calidad perfecta. Si
> aparecen diferencias de fuente, instalar las familias `Inter` y
> `JetBrains Mono` en el sistema antes de exportar.

---

## 8. Integración continua (futuro)

Recomendado para una versión v1.1:

```yaml
# .github/workflows/guides.yml (boceto)
name: guides

on:
  pull_request:
    paths:
      - 'docs/guides/**'

jobs:
  build:
    runs-on: ubuntu-22.04
    steps:
      - uses: actions/checkout@v4
      - run: sudo apt-get install -y pandoc libreoffice pngquant
      - uses: actions/setup-python@v5
        with: { python-version: '3.11' }
      - run: pip install python-docx==1.1.2
      - uses: actions/setup-node@v4
        with: { node-version: '20' }
      - run: cd docs/guides && npm ci
      - run: make reference-docx
      - run: make lint
      - run: make guides
      - uses: actions/upload-artifact@v4
        with:
          name: guides
          path: docs/guides/build/*.docx
```

Hasta entonces, la generación es manual local.

---

## 9. Convenciones de gitignore

`docs/guides/.gitignore`:

```
build/
screenshots/
node_modules/
package-lock.json
```

Solo se versiona:

- `*.md`
- `templates/cbc-reference.docx` (¡sí se versiona; aunque sea generable!)
- `scripts/**`
- `package.json`
- `Makefile` (en raíz, ya bajo git)

**Razón para versionar `cbc-reference.docx`:** evita que cada reviewer tenga
que correr el script Python para previsualizar. El script + el `.docx` deben
mantenerse en sync; CI puede validar que regenerar produce un diff binario
mínimo o despreciar la validación porque `python-docx` no es determinista
byte-a-byte.

---

## 10. Procedimiento de generación end-to-end

Para un mantenedor que clona el repo y quiere las tres guías:

```bash
# 1. Setup local
./vendor/bin/sail up -d
sail artisan migrate:fresh --seed
sail artisan db:seed --class=Spec009DemoSeeder
sail artisan db:seed --class=JobAlertSeeder

# 2. Cola y scheduler para que digests/alertas existan
sail artisan queue:work --queue=instant,default &

# 3. Dependencias de toolchain
cd docs/guides && npm install && npx playwright install chromium && cd ../..
pip3 install --user python-docx==1.1.2

# 4. Capturas (40-60 min)
make captures

# 5. Generar .docx
make guides

# 6. (Opcional) generar .pdf
make guides-pdf
```

Resultado:

```
docs/guides/build/
├── cbc-workplace-administracion.docx
├── cbc-workplace-implementacion.docx
└── cbc-workplace-usuario.docx
```

---

## 11. Troubleshooting

| Problema | Causa probable | Solución |
|---|---|---|
| `pandoc: command not found` | Pandoc no instalado | `apt install pandoc` / `brew install pandoc` |
| `python-docx: ModuleNotFoundError` | venv equivocado o pip no global | `pip3 install --user python-docx==1.1.2` |
| Tipografías default (Calibri) en .docx | Fuentes Inter/JetBrains Mono no presentes | Instalar fuentes; reabrir Word; o aceptar fallback |
| Capturas con Debugbar | `APP_DEBUG=true` | `sed -i 's/APP_DEBUG=true/APP_DEBUG=false/' .env && sail artisan optimize:clear` |
| Capturas con emails reales | `.env` con `MAIL_FROM_ADDRESS` real | Cambiar a `no-reply@example.com` antes de capturar |
| Playwright timeout | Sail no responde en `localhost` | `sail ps`; revisar puerto; o usar `--base-url http://laravel.test` |
| PDF con tipografías cuadradas | LibreOffice sin font cache | `fc-cache -fv` |
| `.docx` sin tabla de contenidos | Word no regenera ToC al abrir | F9 sobre el campo ToC en Word, o regenerar con `--toc` (ya está) |

---

## 12. Bitácora de cambios

| Fecha | Cambio | Por |
|---|---|---|
| 2026-05-17 | Creación inicial del pipeline | Juan Carlos Hooper |
