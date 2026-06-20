# Guía de Estilo — Documentación CBC Workplace

**Documento base para las tres guías:** Administración, Implementación y Usuario.

Este es el contrato editorial al que las tres guías deben adherirse. Cualquier
desviación debe documentarse en la sección final de bitácora.

---

## 1. Voz y tono

### 1.1 Pronombre por guía

| Guía | Pronombre | Ejemplo |
|---|---|---|
| Administración | **Usted** (formal) | "Para suspender una organización, **usted** debe abrir el panel..." |
| Implementación | **Usted** (formal, técnico) | "**Usted** puede regenerar el sitemap ejecutando `php artisan app:generate-sitemap`." |
| Usuario | **Tú** (cercano) | "Cuando **tú** publiques una oferta, los candidatos podrán postularse..." |

> Excepción razonable: cuando el contexto sea claramente impersonal (descripción
> de un proceso del sistema), usar formas pasivas o tercera persona. No mezclar
> "tú" y "usted" en el mismo párrafo.

### 1.2 Registros

- **Administración:** formal, directo, orientado a procedimientos. Cero coloquialismos.
- **Implementación:** formal técnico. Acepta jerga estándar (CI, queue worker,
  PostgreSQL idioms). Asume conocimiento básico de Laravel.
- **Usuario:** cercano, alentador, libre de jerga técnica. Si un término técnico
  aparece, definirlo en la primera mención y añadirlo al glosario.

### 1.3 Tiempos verbales

- Procedimientos: **presente del indicativo o imperativo neutralizado**.
  - ✅ "Haga clic en **Guardar**."
  - ✅ "Para guardar los cambios, **seleccione Guardar**."
  - ❌ "Vas a hacer clic en Guardar." (excepto guía de Usuario, donde sí va)
- Descripciones de comportamiento del sistema: **presente del indicativo**.
  - ✅ "El sistema envía un correo electrónico al administrador."

### 1.4 Pronombres de género

Usar formas neutras siempre que sea posible:
- ✅ "El equipo administrador"
- ✅ "La persona que publica la oferta"
- ❌ "Los administradores y administradoras" (excesivo)
- ❌ "Los administradores/as" (anti-estético)

---

## 2. Tipografía y jerarquía

### 2.1 Familias tipográficas

| Uso | Fuente primaria | Alternativa | Notas |
|---|---|---|---|
| Cuerpo de texto | Inter | Source Sans 3 | Sans-serif legible en pantalla y print |
| Encabezados | Inter Semi-Bold / Bold | Source Sans 3 | Mismo set que cuerpo |
| Código inline y bloques | JetBrains Mono | Cascadia Code, Fira Code | Monoespaciada con ligaduras |
| Citas y notas marginales | Inter Italic | Source Sans 3 Italic | — |

Las fuentes se cargan desde el sistema vía LibreOffice / MS Word; se asume que
están instaladas en el entorno de generación. Si no, Pandoc cae a las alternativas.

### 2.2 Tamaños y espaciado

| Estilo | Tamaño | Interlínea | Peso | Espacio sup./inf. |
|---|---|---|---|---|
| Title (portada) | 36 pt | 1.0 | Bold | — |
| Heading 1 (capítulo) | 24 pt | 1.15 | Bold | 24/12 pt |
| Heading 2 (sección) | 18 pt | 1.15 | Semi-Bold | 18/9 pt |
| Heading 3 (subsección) | 14 pt | 1.20 | Semi-Bold | 14/7 pt |
| Heading 4 (procedimiento) | 12 pt | 1.20 | Bold | 12/6 pt |
| Body | 11 pt | 1.45 | Regular | 0/6 pt |
| Caption (pie figura) | 9 pt | 1.30 | Italic | 0/12 pt |
| Code block | 10 pt | 1.30 | Regular | 8/8 pt |
| Footnote | 9 pt | 1.20 | Regular | — |

### 2.3 Jerarquía semántica

| Nivel | Uso |
|---|---|
| H1 | Título de capítulo (uno por archivo `.md`) |
| H2 | Sección dentro del capítulo |
| H3 | Subsección o concepto |
| H4 | Procedimiento numerado o paso específico |

> **Regla:** nunca saltar niveles. Un H3 debe estar bajo un H2, nunca directamente bajo un H1.

---

## 3. Paleta de colores

### 3.1 Paleta oficial (Lazos de Fe)

| Token | Hex | Uso |
|---|---|---|
| `brand-slate` | `#0F172A` | Fondo principal (Modo Oscuro Backend) |
| `brand-cyan` | `#06B6D4` | Enlaces, destacados primarios, énfasis, primary |
| `brand-amber` | `#D97706` | Advertencias, atención, warning |
| `brand-border` | `#334155` | Bordes de capturas, separadores, tablas |
| `brand-success` | `#10B981` | Callout "Buena práctica", check marks |
| `brand-danger` | `#EF4444` | Callout "Importante", anotaciones en capturas |
| `brand-muted` | `#94A3B8` | Captions, texto secundario |

### 3.2 Sincronización de Marca

El brand kit oficial utiliza una combinación elegante de Cyan y Amber sobre una base Slate para el backend y una base limpia clara para el directorio público de Emprendimientos. **No** hardcodear colores en los `.md` — todo va vía estilos nombrados de Pandoc.

---

## 4. Callouts y avisos

Cada guía dispone de cuatro tipos de callouts. En Markdown se escriben como
blockquotes con un prefijo emoji-libre estilizado por el reference template.

### 4.1 Nota informativa

```markdown
> **Nota.** Texto explicativo aclaratorio que añade contexto sin ser crítico
> para el flujo principal.
```

- **Color:** `cbc-blue` borde izquierdo, `cbc-bg` fondo
- **Cuándo usar:** información que el lector puede saltarse sin romper el flujo

### 4.2 Atención

```markdown
> **Atención.** Advertencia sobre algo que puede causar confusión, pérdida
> menor de tiempo o resultados inesperados.
```

- **Color:** `cbc-warn` borde izquierdo, fondo amarillo claro `#FEF3C7`
- **Cuándo usar:** comportamientos contraintuitivos del sistema, side-effects no obvios

### 4.3 Importante

```markdown
> **Importante.** Acción destructiva, irreversible o con impacto operativo
> serio. Leer antes de continuar.
```

- **Color:** `cbc-danger` borde izquierdo, fondo `#FEE2E2`
- **Cuándo usar:** suspensión, borrado, cambios de credenciales, despliegues a producción

### 4.4 Buena práctica

```markdown
> **Buena práctica.** Recomendación derivada de la experiencia operativa,
> no obligatoria pero altamente sugerida.
```

- **Color:** `cbc-success` borde izquierdo, fondo `#D1FAE5`
- **Cuándo usar:** tips, atajos, convenciones internas

### 4.5 Solo admin

```markdown
> **Solo administradores.** Funcionalidad disponible únicamente para usuarios
> del panel `/admin`.
```

- **Color:** `cbc-charcoal` borde izquierdo, fondo `cbc-bg`
- **Cuándo usar:** solo en Guía de Usuario; señala que cierta acción no le aplica al lector

---

## 5. Capturas de pantalla

### 5.1 Convenciones gráficas

| Elemento | Especificación |
|---|---|
| Resolución base | 1440×900 (viewport Playwright); exportar a 1× |
| Borde | 1 px sólido `#E5E7EB` |
| Sombra | `0 2px 4px rgba(0,0,0,0.08)` (sutil, no dramática) |
| Esquinas | 4 px de radio |
| Formato | PNG-24 con compresión PNG-quant (calidad 80) |
| Tamaño objetivo | < 350 kB por imagen |

### 5.2 Anotaciones

Cuando una captura necesita señalar elementos:

- **Color de marca:** `cbc-danger` (`#DC2626`)
- **Flechas:** trazo de 3 px, cabeza estilo `triangle`, ángulo 30°
- **Números:** círculo de 28 px con fondo `cbc-danger`, número blanco 14 pt bold
- **Cajas resaltadas:** rectángulo `#DC2626` 3 px sin relleno; 8 px de offset desde el elemento real

Las anotaciones se aplican vía `scripts/annotate.mjs` (ver `00-toolchain.md`),
nunca a mano sobre el PNG. La fuente de verdad es el JSON descriptor de cada
captura en `00-screenshot-inventory.md`.

### 5.3 Caption obligatorio

Toda imagen DEBE llevar caption con el formato:

> *Figura A.B — Descripción concisa. Captura tomada en el entorno demo de spec 009.*

Donde `A` es el número de capítulo y `B` el ordinal dentro del capítulo.

### 5.4 Sanitización de datos

Antes de aprobar una captura, verificar:

- [ ] No aparecen correos reales (solo `*@example.com`)
- [ ] No aparecen números de teléfono reales
- [ ] No aparecen nombres reales de personas externas al equipo
- [ ] No se ve el panel debug bar (deshabilitar `APP_DEBUG=false` o configurar Debugbar)
- [ ] No se ven datos de tarjetas, tokens, sesiones
- [ ] El idioma del navegador es español
- [ ] La URL del navegador es legible y refleja el flujo descrito

---

## 6. Convenciones de código en guías

### 6.1 Bloques de código

Usar siempre el lenguaje correcto en el fence:

```markdown
` ` `bash      → comandos de shell
` ` `php       → código Laravel / Filament
` ` `php-tmpl  → Blade templates
` ` `sql       → consultas SQL
` ` `dotenv    → fragmentos de .env
` ` `yaml      → docker-compose / GitHub Actions / config
` ` `json      → respuestas API, JSON descriptors
` ` `text      → output de comandos, logs
```

### 6.2 Referencias a archivos del repo

Formato canónico: `path/relativo/al/repo.php:LINEA`

- ✅ `app/Actions/Admin/SuspendOrganization.php:42`
- ✅ `database/seeders/Spec009DemoSeeder.php:44-54`
- ❌ `~/proys/cbc-workplace/app/Actions/...` (path absoluto)
- ❌ `SuspendOrganization.php` (sin path completo)
- ❌ `app/Actions/Admin/SuspendOrganization.php` sin línea cuando se cita comportamiento específico

**Regla:** toda afirmación técnica que dependa del código debe llevar
`file:line`. Cualquier reviewer puede entonces validarla con un `Read` en
segundos.

### 6.3 Comandos artisan

Los comandos siempre van en bloque de código, no inline, cuando ocupan más de media línea:

```bash
php artisan db:seed --class=Spec009DemoSeeder
```

Y se referencian inline cuando son cortos: ejecutar `php artisan migrate`.

### 6.4 Comandos Sail

En guías de Administración y Usuario, **no** mostrar comandos Sail (es detalle
de implementación). En Guía de Implementación, preferir Sail sobre artisan
directo cuando aplique:

- ✅ `./vendor/bin/sail artisan migrate`
- ✅ `sail artisan migrate` (si el alias `sail` está documentado)
- ❌ Mezclar `./vendor/bin/sail` y `sail` en el mismo capítulo

### 6.5 Placeholders

| Placeholder | Significado |
|---|---|
| `<URL_BASE>` | URL del despliegue (sin trailing slash) |
| `<ORG_ID>` | ID numérico de una organización |
| `<EMAIL>` | Email genérico del usuario |
| `{slug}` | Parámetro de ruta Laravel (notación oficial) |

---

## 7. Numeración y referencias cruzadas

### 7.1 Capítulos y secciones

Cada archivo `.md` representa **un capítulo**. El nombre del archivo lleva el
prefijo numérico de capítulo: `01-intro.md`, `02-acceso.md`, etc.

Dentro del archivo:

- H1: `# Capítulo N — Título`
- H2: `## N.M Título de sección`
- H3: `### N.M.P Título de subsección`
- H4: `#### Procedimiento: Descripción del procedimiento`

### 7.2 Referencias cruzadas

- A otro capítulo: "Consulte el capítulo 4 — Gestión de organizaciones."
- A una figura: "Como se muestra en la Figura 4.2..."
- A una tabla: "La Tabla 4.1 enumera los estados de verificación."
- A código en el repo: usar `file:line` (§6.2).
- A otra guía: "Para detalles del despliegue, consulte la *Guía de
  Implementación*, capítulo 8."

### 7.3 Tablas

```markdown
| Columna A | Columna B |
|---|---|
| Valor 1 | Valor 2 |
```

- Encabezados en **negrita** vía el reference template (no usar `**` en Markdown).
- Alineación: por defecto izquierda; centrar solo números puros; nunca justificar.
- Pie de tabla obligatorio cuando explique una columna:

> *Tabla 4.1 — Estados de verificación de organizaciones y su significado.*

---

## 8. Glosario compartido

Todas las guías DEBEN respetar el glosario canónico definido en
[00-blueprint.md §3](./00-blueprint.md). Cada guía añade su propio glosario
de términos locales en el apéndice **A — Glosario**, pero NO redefine los
términos canónicos.

Si un término del glosario aparece por primera vez en un capítulo, se
puede marcar en *cursiva* en esa única mención inicial.

---

## 9. Convenciones de mailing y emails de ejemplo

- Direcciones genéricas: usar dominio `@example.com` (RFC 2606).
- Nombres ficticios: usar nombres comunes en español ("María González",
  "Juan Pérez") salvo cuando el dato venga literalmente del seeder.
- **Nunca** usar el dominio real `crossroads.cl` o similar en ejemplos.

---

## 10. Listas

### 10.1 Listas no ordenadas

Usar `-` como marcador (no `*` ni `+`). Sangría de 2 espacios para anidación.

```markdown
- Primer item
- Segundo item
  - Sub-item
- Tercer item
```

### 10.2 Listas ordenadas (procedimientos)

Para procedimientos paso-a-paso, usar numeración explícita siempre `1.`, `2.`, `3.`
(no `1.`, `1.`, `1.`). Esto facilita el diff y la lectura del Markdown crudo.

```markdown
1. Abra el panel de administración.
2. Seleccione **Organizaciones**.
3. Pulse el botón **Suspender** en la fila correspondiente.
```

### 10.3 Listas de definiciones

Para glosarios, usar tablas o el patrón DL de Pandoc:

```markdown
Término
:   Definición concisa del término.

Otro término
:   Otra definición.
```

---

## 11. Énfasis y formato inline

| Convención | Uso |
|---|---|
| `**negrita**` | Nombres de botones, ítems de menú, etiquetas de UI |
| `*cursiva*` | Términos introducidos por primera vez, énfasis suave |
| `` `código` `` | Identificadores, paths, comandos cortos, claves de configuración |
| <kbd>Tecla</kbd> | Pulsaciones de teclado (HTML inline, supported by Pandoc) |

**No usar** subrayado para énfasis (se confunde con enlaces). **No usar**
mayúsculas para gritar.

---

## 12. Internacionalización y acentos

- Verificar acentos correctos siempre: organización, también, después, así, más.
- Comillas: usar comillas latinas «» en citas largas, comillas dobles "" para
  citas cortas. NO usar comillas tipográficas curvas en código.
- Símbolo de mayor / menor sólo en código; en texto, usar palabras: "mayor que",
  "menor que".
- Decimales: usar coma (1,5 segundos) en texto narrativo; punto en código y tablas técnicas.
- Miles: separar con punto en texto (1.000 organizaciones); sin separador en código.

---

## 13. Checklist editorial pre-merge

Antes de marcar un capítulo como completo:

- [ ] Una sola H1 al inicio del archivo
- [ ] Todos los procedimientos en imperativo neutralizado / 2ª persona consistente
- [ ] Toda referencia a código tiene `file:line`
- [ ] Todas las capturas tienen caption con número de figura
- [ ] No hay TODOs sin owner
- [ ] No hay datos sensibles (grep de `password`, `secret`, `token`, `@gmail`, `@crossroads`)
- [ ] El glosario canónico se respeta (no se redefine "Suspensión", "Organización", etc.)
- [ ] Bloques de código tienen su language tag
- [ ] El archivo lintea limpio con `markdownlint` (regla: `.markdownlint.json` del repo)

---

## 14. Bitácora de cambios

| Fecha | Cambio | Por |
|---|---|---|
| 2026-05-17 | Creación inicial | Juan Carlos Hooper |
