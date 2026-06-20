# Sub-prompt Fase 6 — Ensamblaje, QA y PR final

> **Cómo usar:** copie todo el bloque siguiente en una nueva sesión de
> Claude Code, una vez que las Fases 3, 4 y 5 estén mergeadas a `main` (o
> a una rama de integración). Rama sugerida: `docs/guides-fase-6-release`.

---

## Misión

Ensamblar los tres `.docx` finales (Admin, Implementación, Usuario), pasar
QA cruzado, generar PDFs derivados opcionales, y abrir el PR de release
v1.0 de las guías oficiales de CBC Workplace.

## Contexto previo

- Fases 1–5 mergeadas. Estructura completa:
  ```
  docs/guides/
    00-blueprint.md
    00-style-guide.md
    00-screenshot-inventory.md
    00-toolchain.md
    00-subprompts/
    templates/cbc-reference.docx
    scripts/
    admin/01-... 11-...  + metadata.yaml + apendices
    impl/01-... 12-...   + metadata.yaml + apendices
    user/01-... 10-...   + metadata.yaml + apendice
    screenshots/admin/  (45 PNGs)
    screenshots/impl/   (27 PNGs)
    screenshots/user/   (38 PNGs)
  Makefile               (raíz)
  ```

CBC = **Crossroads Bible Church**. Atribución: solo Juan Carlos Hooper.

## Entregables

1. **Tres `.docx` generados** en `docs/guides/build/`:
   - `cbc-workplace-administracion.docx`
   - `cbc-workplace-implementacion.docx`
   - `cbc-workplace-usuario.docx`

2. **Tres `.pdf` derivados** (opcional pero recomendado) en
   `docs/guides/build/`.

3. **Reporte de QA** en `docs/guides/build/QA-REPORT.md` con:
   - Conteo de páginas por guía
   - Tamaño en MB
   - Resultado de cada checklist (ver §3)
   - Bugs encontrados y corregidos en este pass
   - Bugs deferidos (con justificación)

4. **PR final** que mergea las tres guías a `main` con el changelog
   apropiado.

## Procedimiento

### Paso 1. Setup y validación de entorno

```bash
git checkout main
git pull
git checkout -b docs/guides-fase-6-release

# Sail + seeders frescos (por si hay que regenerar capturas)
./vendor/bin/sail up -d
sail artisan migrate:fresh --seed
sail artisan db:seed --class=Spec009DemoSeeder
sail artisan db:seed --class=JobAlertSeeder

# Toolchain
cd docs/guides && npm install && cd ../..
pip3 install --user python-docx==1.1.2

# Plantilla actualizada
make reference-docx
```

### Paso 2. Generación

```bash
make clean
make guides
```

Esperar a que termine. Salida esperada:

```
[ok] Guías generadas: docs/guides/build/cbc-workplace-admin.docx
                       docs/guides/build/cbc-workplace-impl.docx
                       docs/guides/build/cbc-workplace-user.docx
```

### Paso 3. QA cruzado — checklist

Para CADA `.docx`:

- [ ] Abre en LibreOffice sin errores
- [ ] Abre en Microsoft Word sin warnings
- [ ] Portada con título correcto, autor "Juan Carlos Hooper", fecha
  "Mayo 2026", versión "v1.0"
- [ ] Tabla de contenidos completa y clickeable
- [ ] Numeración de capítulos secuencial
- [ ] Numeración de figuras secuencial (Figura X.Y formato)
- [ ] Callouts renderizados con colores correctos
- [ ] Capturas alineadas y con caption debajo
- [ ] Bloques de código en JetBrains Mono o fallback
- [ ] Footer "CBC Workplace · Guías oficiales · v1.0 — Mayo 2026"
- [ ] Sin TODO / FIXME / XXX en el cuerpo
- [ ] Sin menciones a "Caribbean Business Coalition" (debe ser
  "Crossroads Bible Church")
- [ ] Sin emails reales (solo `*@example.com`)
- [ ] Sin teléfonos reales
- [ ] Sin paths absolutos de desarrollo (`/home/juanca/...`)

Cross-guide:

- [ ] Glosario canónico respetado en las tres
- [ ] No hay contradicciones entre lo que dice Admin y lo que dice
  Usuario sobre el mismo flujo
- [ ] Implementación cita el código real (verificación file:line por
  muestreo: 5 claims aleatorios verificados con `Read`)

### Paso 4. Sanitización por grep

```bash
# No debe imprimir nada:
grep -rn "TODO\|FIXME\|XXX" docs/guides/ --include="*.md"
grep -rn "crossroads\.cl\|@gmail\|@yahoo" docs/guides/ --include="*.md"
grep -rn "Caribbean Business" docs/guides/ --include="*.md"
grep -rn "/home/juanca\|/Users/" docs/guides/ --include="*.md"
grep -rn "password.*=.*['\"]" docs/guides/ --include="*.md" | grep -v Spec009DemoSeeder
```

Cualquier hit es bloqueante.

### Paso 5. (Opcional) PDFs

```bash
make guides-pdf
```

Validar que los PDFs:
- Pesan < 15 MB cada uno
- Preservan colores de callouts
- Preservan capturas con anotaciones

### Paso 6. Reporte de QA

Crear `docs/guides/build/QA-REPORT.md` con:

```markdown
# Reporte de QA — Guías CBC Workplace v1.0

Fecha: 2026-MM-DD
Revisor: Juan Carlos Hooper

## Resumen

| Guía | Páginas | Tamaño .docx | Capturas | Estado |
|---|---|---|---|---|
| Administración | NN | X MB | 45 | ✓ |
| Implementación | NN | X MB | 27 | ✓ |
| Usuario | NN | X MB | 38 | ✓ |

## Checklist por guía
(pegar el checklist completo del Paso 3)

## Sanitización por grep
(pegar output de los greps del Paso 4)

## Bugs encontrados y corregidos
- [Capítulo X.Y de Guía Z] descripción del bug → commit que lo arregla

## Bugs deferidos
- ninguno / justificación

## Decisiones tomadas en este pass
- ...
```

### Paso 7. Commit y PR

```bash
git add docs/guides/build/  # incluir los .docx finales
git commit -m "docs: release v1.0 de guías oficiales (admin + impl + usuario)"
git push -u origin docs/guides-fase-6-release

gh pr create \
  --repo hooperits/cbc-workplace \
  --base main \
  --head docs/guides-fase-6-release \
  --title "docs: release v1.0 — guías oficiales (Admin + Impl + Usuario)" \
  --body "$(cat <<'EOF'
## Resumen
- Tres guías profesionales en .docx (Administración, Implementación, Usuario)
- 110 capturas anotadas
- Pipeline reproducible via \`make guides\`
- QA-REPORT.md adjunto

## Plan de test
- [x] make guides corre sin errores
- [x] Tres .docx abren correctamente en Word y LibreOffice
- [x] Checklist QA pasado para cada guía
- [x] Sanitización por grep limpia
- [ ] Revisión humana de muestreo (5 capítulos al azar)
EOF
)"
```

## Definición de "hecho"

- PR abierto en `hooperits/cbc-workplace`.
- QA-REPORT.md committeado.
- Tres `.docx` finales committeados a `docs/guides/build/`.
- Las tres guías abren limpiamente en Word y LibreOffice.
- Cero hits en los greps de sanitización.

## Cierre

Cuando el PR sea aprobado y mergeado:

1. Crear un tag `guides-v1.0` apuntando al commit de merge.
2. Anunciar internamente a CBC con los tres `.docx` adjuntos.
3. Actualizar memoria del proyecto creando
   `~/.claude/projects/-home-juanca-proys-cbc-workplace/memory/project_guides_v1.md`
   con: fecha de release, PR ID, ruta a los binarios en `docs/guides/build/`,
   pendientes para v1.1 (branding oficial, traducción inglés si aplica, CI).
