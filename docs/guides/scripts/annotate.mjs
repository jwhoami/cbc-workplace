#!/usr/bin/env node
/**
 * annotate.mjs — Aplica anotaciones (cajas, círculos numerados, flechas) a
 * los PNGs producidos por captures.mjs, según el campo `annotations` de
 * cada descriptor en docs/guides/captures.json.
 *
 * Estado: ESQUELETO INICIAL — la lógica de resolución de selectores a
 * coordenadas se completa en Fase 2 reaprovechando Playwright. La Fase 1
 * sólo entrega el scaffolding del CLI y los tokens de estilo.
 *
 * Uso:
 *   node scripts/annotate.mjs              # anota todo lo presente
 *   node scripts/annotate.mjs --only <slug>
 */
import sharp from "sharp";
import { fileURLToPath } from "url";
import path from "path";
import fs from "fs/promises";
import yargs from "yargs";
import { hideBin } from "yargs/helpers";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const GUIDES_DIR = path.resolve(__dirname, "..");
const REPO_ROOT = path.resolve(GUIDES_DIR, "..", "..");

// Tokens (deben coincidir con docs/guides/00-style-guide.md §5.2)
const TOKENS = {
  danger: "#DC2626",
  border: "#E5E7EB",
  white: "#FFFFFF",
  strokeWidth: 3,
  circleRadius: 14,
  fontSize: 14,
  borderWidth: 1,
  shadowBlur: 4,
  shadowAlpha: 0.08,
  cornerRadius: 4,
};

const argv = yargs(hideBin(process.argv))
  .option("only", { type: "string", describe: "Anota sólo el slug dado" })
  .help()
  .parse();

async function loadCaptures() {
  const jsonPath = path.join(GUIDES_DIR, "captures.json");
  const raw = await fs.readFile(jsonPath, "utf8");
  return JSON.parse(raw);
}

/**
 * Genera un SVG con las anotaciones dadas las coordenadas resueltas.
 * En Fase 2: las coordenadas vendrán de Playwright via boundingBox().
 * Aquí solo entregamos la firma + el template SVG.
 */
function buildAnnotationSvg(width, height, annotations) {
  const elements = annotations
    .map((a, idx) => {
      switch (a.type) {
        case "box":
          return `<rect x="${a.x}" y="${a.y}" width="${a.w}" height="${a.h}"
            fill="none" stroke="${TOKENS.danger}" stroke-width="${TOKENS.strokeWidth}"/>`;
        case "circle":
          return `
            <circle cx="${a.x}" cy="${a.y}" r="${TOKENS.circleRadius}"
              fill="${TOKENS.danger}" stroke="${TOKENS.white}" stroke-width="2"/>
            <text x="${a.x}" y="${a.y + 5}" text-anchor="middle"
              fill="${TOKENS.white}" font-size="${TOKENS.fontSize}"
              font-family="Inter, sans-serif" font-weight="bold">${a.id ?? idx + 1}</text>`;
        case "arrow":
          return `<line x1="${a.x1}" y1="${a.y1}" x2="${a.x2}" y2="${a.y2}"
            stroke="${TOKENS.danger}" stroke-width="${TOKENS.strokeWidth}"
            marker-end="url(#arrow)"/>`;
        default:
          return "";
      }
    })
    .join("\n");

  return `<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}">
    <defs>
      <marker id="arrow" markerWidth="10" markerHeight="10" refX="9" refY="5"
              orient="auto">
        <polygon points="0,0 10,5 0,10" fill="${TOKENS.danger}"/>
      </marker>
    </defs>
    ${elements}
  </svg>`;
}

async function annotate(descriptor) {
  if (!descriptor.annotations || descriptor.annotations.length === 0) return;
  const pngPath = path.resolve(REPO_ROOT, descriptor.outputPath);
  try {
    await fs.access(pngPath);
  } catch {
    console.warn(`[skip] ${descriptor.slug}: PNG no existe aún`);
    return;
  }

  const meta = await sharp(pngPath).metadata();
  // TODO Fase 2: resolver `descriptor.annotations[].selector` -> coords usando
  // Playwright boundingBox. Por ahora pasamos las coords pre-calculadas si
  // existen en el descriptor.
  const resolved = descriptor.annotations
    .filter((a) => a.x !== undefined || a.x1 !== undefined)
    .map((a, i) => ({ id: a.id ?? i + 1, ...a }));

  if (resolved.length === 0) {
    console.warn(`[skip] ${descriptor.slug}: anotaciones sin coords resueltas`);
    return;
  }

  const svg = buildAnnotationSvg(meta.width, meta.height, resolved);
  const buffer = Buffer.from(svg);
  await sharp(pngPath)
    .composite([{ input: buffer, top: 0, left: 0 }])
    .toFile(pngPath + ".annot.png");

  // Reemplaza el PNG original
  await fs.rename(pngPath + ".annot.png", pngPath);
  console.log(`[ok] ${descriptor.slug}: ${resolved.length} anotaciones`);
}

async function main() {
  const captures = await loadCaptures();
  const filtered = argv.only
    ? captures.filter((c) => c.slug === argv.only)
    : captures;

  for (const descriptor of filtered) {
    await annotate(descriptor);
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
