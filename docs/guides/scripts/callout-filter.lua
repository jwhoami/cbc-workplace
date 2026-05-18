--[[
  callout-filter.lua — Pandoc Lua filter que transforma blockquotes que
  comienzan con un marcador "**Nota.**", "**Atención.**", "**Importante.**",
  "**Buena práctica.**" o "**Solo administradores.**" en párrafos con el
  estilo nombrado correspondiente del reference template (Callout-Note,
  Callout-Warn, Callout-Danger, Callout-Success, Callout-AdminOnly).

  Uso (vía Makefile):
    pandoc ... --lua-filter=docs/guides/scripts/callout-filter.lua ...
]]

local STYLES = {
  ["Nota"]                  = "Callout-Note",
  ["Atención"]              = "Callout-Warn",
  ["Importante"]            = "Callout-Danger",
  ["Buena práctica"]        = "Callout-Success",
  ["Solo administradores"]  = "Callout-AdminOnly",
}

-- Devuelve (style_name, sin_prefijo_inlines) o nil si no es un callout.
local function detect_callout(inlines)
  if #inlines == 0 then return nil end
  local first = inlines[1]
  if first.t ~= "Strong" then return nil end
  local txt = pandoc.utils.stringify(first.content)
  -- El marcador puede venir con o sin punto final
  local key = txt:match("^(.-)%.$") or txt
  local style = STYLES[key]
  if not style then return nil end

  -- Construir nuevos inlines sin el strong de marcador
  local rest = {}
  for i = 2, #inlines do
    table.insert(rest, inlines[i])
  end
  -- Saltar espacios iniciales tras eliminar el marcador
  while #rest > 0 and rest[1].t == "Space" do
    table.remove(rest, 1)
  end
  return style, rest
end

function BlockQuote(el)
  if #el.content == 0 then return nil end

  local first_block = el.content[1]
  if first_block.t ~= "Para" and first_block.t ~= "Plain" then
    return nil
  end

  local style, rest_inlines = detect_callout(first_block.content)
  if not style then return nil end

  -- Construir Divs con el estilo custom; Pandoc respeta `custom-style` para docx
  local new_blocks = {}
  -- Primer párrafo: usamos el resto de inlines del primer bloque + marcador en bold
  local first_para_inlines = pandoc.List({
    pandoc.Strong({ pandoc.Str(invert(style)) }),
    pandoc.Space(),
  })
  for _, inl in ipairs(rest_inlines) do
    first_para_inlines:insert(inl)
  end
  table.insert(new_blocks, pandoc.Para(first_para_inlines))

  -- Resto de bloques del blockquote, intactos
  for i = 2, #el.content do
    table.insert(new_blocks, el.content[i])
  end

  return pandoc.Div(new_blocks, { ["custom-style"] = style })
end

-- Helper: convertir "Callout-Note" -> "Nota" para reescribir el marcador
function invert(style_name)
  for k, v in pairs(STYLES) do
    if v == style_name then return k end
  end
  return style_name
end
