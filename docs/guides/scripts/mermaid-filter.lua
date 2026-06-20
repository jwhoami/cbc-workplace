--[[
  mermaid-filter.lua — Suprime los fenced code blocks ```mermaid``` al construir
  el DOCX. La fuente Mermaid queda visible en el .md (GitHub renderiza nativa)
  pero el .docx no muestra el código fuente porque incluye el PNG renderizado
  por separado vía `render-mermaid.mjs`.

  Uso (vía Makefile):
    pandoc ... --lua-filter=docs/guides/scripts/mermaid-filter.lua ...
]]

function CodeBlock(el)
  for _, cls in ipairs(el.classes) do
    if cls == "mermaid" then
      return {}
    end
  end
  return nil
end
