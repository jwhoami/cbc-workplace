/**
 * annotations.mjs — Núcleo de resolución y renderizado de anotaciones.
 *
 * Comparte lógica entre captures.mjs (resuelve selectores vía Playwright
 * mientras la página está renderizada) y annotate.mjs (re-aplica overlays con
 * coords pre-resueltas en el descriptor).
 *
 * Schema de anotación en captures.json:
 *
 *   { "type": "box",    "selector": "...", "padding": 4 }
 *   { "type": "circle", "selector": "...", "id": 1, "position": "tr" }
 *   { "type": "arrow",  "from": "...", "to": "...", "anchor": "edge" }
 *
 * Coords pre-resueltas (formato post-resolución):
 *
 *   { "type": "box",    "x": 10, "y": 20, "w": 100, "h": 50 }
 *   { "type": "circle", "id": 1, "x": 120, "y": 30 }
 *   { "type": "arrow",  "x1": 0, "y1": 0, "x2": 100, "y2": 50 }
 */
import sharp from "sharp";

// Tokens (deben coincidir con docs/guides/00-style-guide.md §5.2)
export const TOKENS = {
  danger: "#DC2626",
  border: "#E5E7EB",
  white: "#FFFFFF",
  strokeWidth: 3,
  circleRadius: 14,
  fontSize: 14,
  borderWidth: 1,
  cornerRadius: 4,
};

const CIRCLE_OFFSET = 8;

function circlePosition(box, position) {
  const pos = position ?? "tr";
  const r = TOKENS.circleRadius;
  switch (pos) {
    case "tl":
      return { x: box.x - CIRCLE_OFFSET, y: box.y - CIRCLE_OFFSET };
    case "tr":
      return { x: box.x + box.width + CIRCLE_OFFSET, y: box.y - CIRCLE_OFFSET };
    case "bl":
      return { x: box.x - CIRCLE_OFFSET, y: box.y + box.height + CIRCLE_OFFSET };
    case "br":
      return {
        x: box.x + box.width + CIRCLE_OFFSET,
        y: box.y + box.height + CIRCLE_OFFSET,
      };
    case "center":
      return { x: box.x + box.width / 2, y: box.y + box.height / 2 };
    default:
      return { x: box.x + box.width + CIRCLE_OFFSET, y: box.y - CIRCLE_OFFSET };
  }
}

function clamp(value, min, max) {
  return Math.max(min, Math.min(max, value));
}

async function resolveBox(page, selector) {
  const handle = page.locator(selector).first();
  const box = await handle.boundingBox();
  if (!box) throw new Error(`selector sin boundingBox: ${selector}`);
  return box;
}

/**
 * Resuelve un array de descriptores de anotación a coords numéricas.
 * Llamado desde captures.mjs con `page` activo. Devuelve sólo anotaciones
 * resueltas; las que fallan se loguean y descartan.
 */
export async function resolveAnnotations(page, annotations = [], viewport) {
  const out = [];
  let autoId = 1;
  for (const ann of annotations) {
    try {
      if (ann.type === "box") {
        const box = await resolveBox(page, ann.selector);
        const pad = ann.padding ?? 0;
        out.push({
          type: "box",
          x: Math.round(box.x - pad),
          y: Math.round(box.y - pad),
          w: Math.round(box.width + pad * 2),
          h: Math.round(box.height + pad * 2),
        });
      } else if (ann.type === "circle") {
        const box = await resolveBox(page, ann.selector);
        const { x, y } = circlePosition(box, ann.position);
        const id = ann.id ?? autoId++;
        out.push({
          type: "circle",
          id,
          x: Math.round(clamp(x, 0, viewport.width)),
          y: Math.round(clamp(y, 0, viewport.height)),
        });
      } else if (ann.type === "arrow") {
        const fromBox = await resolveBox(page, ann.from);
        const toBox = await resolveBox(page, ann.to);
        out.push({
          type: "arrow",
          x1: Math.round(fromBox.x + fromBox.width / 2),
          y1: Math.round(fromBox.y + fromBox.height / 2),
          x2: Math.round(toBox.x + toBox.width / 2),
          y2: Math.round(toBox.y + toBox.height / 2),
        });
      } else {
        console.warn(`[annot] tipo desconocido: ${ann.type}`);
      }
    } catch (err) {
      console.warn(`[annot] falló ${ann.type} (${ann.selector ?? ann.from}): ${err.message}`);
    }
  }
  return out;
}

export function buildAnnotationSvg(width, height, resolved) {
  const elements = resolved
    .map((a) => {
      switch (a.type) {
        case "box":
          return (
            `<rect x="${a.x}" y="${a.y}" width="${a.w}" height="${a.h}" ` +
            `fill="none" stroke="${TOKENS.danger}" stroke-width="${TOKENS.strokeWidth}" ` +
            `rx="${TOKENS.cornerRadius}"/>`
          );
        case "circle":
          return (
            `<circle cx="${a.x}" cy="${a.y}" r="${TOKENS.circleRadius}" ` +
            `fill="${TOKENS.danger}" stroke="${TOKENS.white}" stroke-width="2"/>` +
            `<text x="${a.x}" y="${a.y + 5}" text-anchor="middle" ` +
            `fill="${TOKENS.white}" font-size="${TOKENS.fontSize}" ` +
            `font-family="Inter, sans-serif" font-weight="bold">${a.id}</text>`
          );
        case "arrow":
          return (
            `<line x1="${a.x1}" y1="${a.y1}" x2="${a.x2}" y2="${a.y2}" ` +
            `stroke="${TOKENS.danger}" stroke-width="${TOKENS.strokeWidth}" ` +
            `marker-end="url(#cbc-arrow)"/>`
          );
        default:
          return "";
      }
    })
    .filter(Boolean)
    .join("\n");

  return (
    `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}">` +
    `<defs>` +
    `<marker id="cbc-arrow" markerWidth="10" markerHeight="10" refX="9" refY="5" orient="auto">` +
    `<polygon points="0,0 10,5 0,10" fill="${TOKENS.danger}"/>` +
    `</marker>` +
    `</defs>` +
    elements +
    `</svg>`
  );
}

/**
 * Aplica anotaciones a un PNG existente. Si `resolved` está vacío, no toca el
 * archivo. La composición se hace via Sharp; el resultado sobrescribe el PNG.
 */
export async function applyAnnotations(pngPath, resolved) {
  if (!resolved || resolved.length === 0) return false;
  const meta = await sharp(pngPath).metadata();
  const svg = buildAnnotationSvg(meta.width, meta.height, resolved);
  const tmpPath = pngPath + ".annot.tmp.png";
  await sharp(pngPath)
    .composite([{ input: Buffer.from(svg), top: 0, left: 0 }])
    .png()
    .toFile(tmpPath);
  // Reemplaza atómicamente
  const fs = await import("fs/promises");
  await fs.rename(tmpPath, pngPath);
  return true;
}
