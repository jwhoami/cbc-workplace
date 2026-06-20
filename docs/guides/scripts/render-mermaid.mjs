#!/usr/bin/env node
/**
 * render-mermaid.mjs — Pre-renderiza los diagramas Mermaid del proyecto a PNG.
 *
 * Lee cada `.mmd` bajo `docs/guides/diagrams/source/` y produce un PNG en
 * `docs/guides/screenshots/impl/<slug>.png`. El nombre del slug es el basename
 * del `.mmd` sin extensión. Se usa la paleta y la fuente del template oficial
 * (`docs/guides/diagrams/mermaid-config.json`).
 *
 * Uso:
 *   node docs/guides/scripts/render-mermaid.mjs              # todos
 *   node docs/guides/scripts/render-mermaid.mjs --only slug  # uno
 *   node docs/guides/scripts/render-mermaid.mjs --list       # lista detectada
 *
 * Pre-requisitos:
 *   - mmdc (mermaid-cli) en PATH
 *
 * Salida: ruta del PNG generado por cada `.mmd`. Falla con código !=0 si algún
 * render falla.
 */
import { fileURLToPath } from "url";
import path from "path";
import fs from "fs/promises";
import { execFile } from "child_process";
import { promisify } from "util";

const execFileP = promisify(execFile);
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const REPO_ROOT = path.resolve(__dirname, "..", "..", "..");
const SOURCE_DIR = path.join(REPO_ROOT, "docs/guides/diagrams/source");
const OUT_DIR = path.join(REPO_ROOT, "docs/guides/screenshots/impl");
const MERMAID_CONFIG = path.join(REPO_ROOT, "docs/guides/diagrams/mermaid-config.json");
const PUPPETEER_CONFIG = path.join(REPO_ROOT, "docs/guides/diagrams/puppeteer-config.json");

const args = process.argv.slice(2);
const onlyIdx = args.indexOf("--only");
const only = onlyIdx >= 0 ? args[onlyIdx + 1] : null;
const listOnly = args.includes("--list");

const PNG_WIDTH = 1600;
const BG = "transparent";

async function listSlugs() {
  const entries = await fs.readdir(SOURCE_DIR);
  return entries
    .filter((f) => f.endsWith(".mmd"))
    .map((f) => path.basename(f, ".mmd"))
    .sort();
}

async function ensureDir(p) {
  await fs.mkdir(p, { recursive: true });
}

async function renderOne(slug) {
  const input = path.join(SOURCE_DIR, `${slug}.mmd`);
  const output = path.join(OUT_DIR, `${slug}.png`);
  await ensureDir(OUT_DIR);
  const mmdc = process.env.MMDC || "mmdc";
  const cliArgs = [
    "-i", input,
    "-o", output,
    "-w", String(PNG_WIDTH),
    "-b", BG,
    "-c", MERMAID_CONFIG,
    "-p", PUPPETEER_CONFIG,
  ];
  try {
    const { stdout, stderr } = await execFileP(mmdc, cliArgs, { timeout: 90_000 });
    if (stderr && !stderr.includes("Generating single mermaid chart")) {
      // mmdc imprime el log normal en stderr; solo lo mostramos si hubo otra cosa.
      // No tratamos como error; el código de retorno manda.
    }
    return { slug, output, ok: true };
  } catch (err) {
    return { slug, output, ok: false, err: err.stderr || err.message };
  }
}

async function main() {
  const all = await listSlugs();
  if (listOnly) {
    console.log(all.join("\n"));
    return;
  }
  const targets = only ? all.filter((s) => s === only) : all;
  if (only && targets.length === 0) {
    console.error(`[mermaid] slug no encontrado: ${only}`);
    console.error(`[mermaid] disponibles: ${all.join(", ")}`);
    process.exit(2);
  }
  console.log(`[mermaid] rendering ${targets.length} diagrama(s)…`);
  let failed = 0;
  for (const slug of targets) {
    process.stdout.write(`  ${slug} … `);
    const res = await renderOne(slug);
    if (res.ok) {
      console.log(`OK -> ${path.relative(REPO_ROOT, res.output)}`);
    } else {
      failed++;
      console.log("FAIL");
      console.error(res.err);
    }
  }
  if (failed > 0) {
    console.error(`[mermaid] ${failed} fallo(s) de ${targets.length}`);
    process.exit(1);
  }
  console.log(`[mermaid] listo: ${targets.length} PNG(s) en ${path.relative(REPO_ROOT, OUT_DIR)}`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
