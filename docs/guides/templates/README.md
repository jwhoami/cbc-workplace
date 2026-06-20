# Plantilla de referencia Pandoc

`cbc-reference.docx` es la plantilla consumida por `pandoc --reference-doc=`
para producir las tres guías de CBC Workplace con tipografía, paleta y
estilos consistentes.

## Cómo regenerarla

```bash
python3 docs/guides/scripts/build-reference-docx.py
# o
make reference-docx
```

## Por qué es generable y no se edita a mano

La fuente de verdad de los tokens (colores, tamaños, fuentes, callouts)
vive en `docs/guides/scripts/build-reference-docx.py`. Editar el `.docx`
en Word funciona pero los cambios se perderán al regenerar.

Si necesita un estilo nuevo:

1. Añádalo en `build-reference-docx.py`.
2. Corra el script para regenerar el `.docx`.
3. Cite el nuevo estilo desde el filtro Lua de callouts (si aplica) o
   directamente en el Markdown vía `{custom-style="NombreEstilo"}`.

## Por qué se versiona el `.docx`

Tener el binario en git evita que cualquier reviewer tenga que correr
Python local para previsualizar la salida. El script + el binario deben
mantenerse en sync; cualquier cambio al script debe acompañarse de su
`.docx` regenerado en el mismo commit.
