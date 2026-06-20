# Sub-prompt Fase 2 — Pipeline de capturas funcional

> **Cómo usar:** copie todo el bloque siguiente en una nueva sesión de
> Claude Code, dentro del repo `cbc-workplace` en una rama de trabajo
> nueva (sugerido: `docs/guides-fase-2-capturas`). Es auto-contenido: el
> agente que lo reciba no necesita memoria previa.

---

## Misión

Implementar el pipeline de capturas Playwright + anotaciones para las guías
oficiales de CBC Workplace, dejando 110 PNGs anotadas listas para que las
Fases 3–5 (escritura de las guías) puedan referenciarlas.

## Contexto previo (no necesita lectura adicional)

- Repo: `hooperits/cbc-workplace`. PRs van a este fork, no a upstream.
- La Fase 1 (foundations) ya está mergeada en `main`. Léala primero:
  - `docs/guides/00-blueprint.md` — visión global
  - `docs/guides/00-style-guide.md` — convenciones gráficas (§5)
  - `docs/guides/00-screenshot-inventory.md` — el inventario de capturas
  - `docs/guides/00-toolchain.md` — el pipeline esperado
  - `docs/guides/scripts/captures.mjs` — esqueleto Playwright
  - `docs/guides/scripts/annotate.mjs` — esqueleto de anotaciones
- Atribución: Juan Carlos Hooper únicamente. Sin "Co-Authored-By: Claude".
- Idioma de los entregables de texto: español latinoamericano neutro.
- CBC = Crossroads Bible Church (no usar "Caribbean Business Coalition").

## Entregables

1. **`docs/guides/captures.json`** generado a partir del inventario
   markdown. 110 entradas con el formato documentado en
   `00-screenshot-inventory.md §5`.

2. **Setup ambiente seedeado** verificado:
   ```bash
   sail artisan migrate:fresh --seed
   sail artisan db:seed --class=Spec009DemoSeeder
   sail artisan db:seed --class=JobAlertSeeder
   sail artisan queue:work --queue=instant,default &
   ```

3. **Completar `captures.mjs`** para que `make captures` ejecute las 110
   capturas sin fallar:
   - Manejar autenticación Filament por panel (`/admin/login`, `/member/login`).
   - Implementar todos los tipos de `preActions` (click, fill, wait, hover, scroll).
   - Implementar `wait.type = selector | timeout | networkidle`.
   - Soportar `clip` (recorte) y `fullPage`.
   - Re-intentos con backoff exponencial en fallas de red.

4. **Completar `annotate.mjs`** para resolver selectores CSS a
   coordenadas mediante Playwright (boundingBox) y aplicar:
   - Cajas resaltadas (rectángulos sin relleno, rojo `#DC2626`, 3 px)
   - Círculos numerados (28 px, fondo rojo, texto blanco bold)
   - Flechas (línea + cabeza triangular)
   - Borde final de 1 px `#E5E7EB` + sombra sutil

5. **PNGs generados** en `docs/guides/screenshots/<guide>/<slug>.png`,
   todos < 350 kB, ancho 1440 px ± 5 px. Verificar con
   `make verify-captures`.

6. **README de uso operativo** en `docs/guides/screenshots/README.md`
   explicando cómo regenerar individual o por guía.

## Reglas operativas

- **Verificar antes de afirmar.** Antes de escribir un descriptor que
  use un selector `.fi-...`, hacer `grep -r "fi-modal" vendor/filament`
  para confirmar el nombre real.
- **Idempotencia.** Cada captura es independiente. Re-correr el script
  no debe corromper PNGs anteriores.
- **Compresión.** Pasar cada PNG por `sharp({ quality: 80 })` o
  `pngquant --quality=70-90`.
- **Sin Debugbar.** Antes de capturar, asegurar `APP_DEBUG=false`.
- **Sin emails reales.** Antes de capturar pantallas de email,
  `MAIL_FROM_ADDRESS=no-reply@example.com`.

## Sanitización pre-commit

- [ ] Ningún PNG muestra credenciales reales
- [ ] Ningún PNG muestra el dominio `crossroads.cl`
- [ ] Ningún PNG muestra debugbar
- [ ] Capturas con email muestran `*@example.com`

## Definición de "hecho"

```bash
make verify-captures      # exit 0
ls docs/guides/screenshots/admin/ | wc -l   # 45 (o más, si añade)
ls docs/guides/screenshots/impl/  | wc -l   # 27
ls docs/guides/screenshots/user/  | wc -l   # 38
```

## Cierre

- Commit convencional en español:
  `feat(docs): pipeline de capturas funcional con 110 PNGs anotadas`
- Push a `hooperits/cbc-workplace`.
- PR contra `main`. Etiquetar como `documentation`.
