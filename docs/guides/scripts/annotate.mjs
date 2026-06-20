#!/usr/bin/env node
/**
 * annotate.mjs — Re-aplica el overlay de anotaciones a PNGs ya capturados,
 * usando coords pre-resueltas. No relanza el browser.
 *
 * Fuentes de coords (en orden de prioridad):
 *
 *   1. Sidecar `<png-path>.coords.json` (generado por captures.mjs durante la
 *      captura, contiene coords numéricas resueltas).
 *   2. Campo `annotations` del descriptor con coords numéricas inline
 *      (sin selector — sólo `x/y/w/h` ya calculados manualmente).
 *
 * Casos de uso:
 *
 *   - Cambiar tokens visuales (color, grosor) sin re-correr Playwright.
 *   - Aplicar anotaciones a PNGs producidos fuera del pipeline.
 *   - Reparar overlay corrompido / borrado.
 *
 * Para resolver SELECTORES → coords la primera vez, usar
 * `node scripts/captures.mjs --only <slug>`, que resuelve via boundingBox
 * y genera el sidecar.
 *
 * Uso:
 *   node scripts/annotate.mjs                  # todos los descriptores con coords
 *   node scripts/annotate.mjs --only <slug>
 *   node scripts/annotate.mjs --guide admin
 */
import { fileURLToPath } from "url";
import path from "path";
import fs from "fs/promises";
import yargs from "yargs";
import { hideBin } from "yargs/helpers";
import { applyAnnotations } from "./annotations.mjs";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const GUIDES_DIR = path.resolve(__dirname, "..");
const REPO_ROOT = path.resolve(GUIDES_DIR, "..", "..");

const argv = yargs(hideBin(process.argv))
  .option("only", { type: "string", describe: "Anota sólo el slug dado" })
  .option("guide", { choices: ["all", "admin", "impl", "user"], default: "all" })
  .help()
  .parse();

async function loadCaptures() {
  const jsonPath = path.join(GUIDES_DIR, "captures.json");
  return JSON.parse(await fs.readFile(jsonPath, "utf8"));
}

async function loadCoords(descriptor) {
  const pngPath = path.resolve(REPO_ROOT, descriptor.outputPath);
  const sidecarPath = pngPath.replace(/\.png$/, ".coords.json");
  try {
    const raw = await fs.readFile(sidecarPath, "utf8");
    return { source: "sidecar", coords: JSON.parse(raw) };
  } catch (err) {
    if (err.code !== "ENOENT") throw err;
  }

  if (!descriptor.annotations) return null;
  const inline = descriptor.annotations.filter(
    (a) => a.x !== undefined || a.x1 !== undefined,
  );
  if (inline.length === 0) return null;
  return { source: "descriptor", coords: inline };
}

async function annotateDescriptor(descriptor) {
  const pngPath = path.resolve(REPO_ROOT, descriptor.outputPath);
  try {
    await fs.access(pngPath);
  } catch {
    console.warn(`[skip] ${descriptor.slug}: PNG ausente`);
    return false;
  }

  const loaded = await loadCoords(descriptor);
  if (!loaded) {
    return false; // sin anotaciones — nada que hacer
  }

  await applyAnnotations(pngPath, loaded.coords);
  console.log(
    `[ok] ${descriptor.slug}: ${loaded.coords.length} anotaciones (${loaded.source})`,
  );
  return true;
}

async function main() {
  const captures = await loadCaptures();
  let filtered = captures;
  if (argv.only) {
    filtered = filtered.filter((c) => c.slug === argv.only);
    if (filtered.length === 0) {
      console.error(`[error] Slug no encontrado: ${argv.only}`);
      process.exit(1);
    }
  } else if (argv.guide !== "all") {
    filtered = filtered.filter((c) => c.guide === argv.guide);
  }

  let touched = 0;
  for (const descriptor of filtered) {
    if (await annotateDescriptor(descriptor)) touched++;
  }
  console.log(`[done] annotate: ${touched}/${filtered.length} PNGs anotadas`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
