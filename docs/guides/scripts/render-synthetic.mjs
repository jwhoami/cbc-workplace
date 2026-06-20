#!/usr/bin/env node
/**
 * render-synthetic.mjs — Renderiza capturas "sintéticas" (terminal, config
 * file, SQL/Tinker output) a PNG vía Playwright + HTML.
 *
 * Lee cada descriptor JSON bajo `docs/guides/synthetic/source/*.json`
 * y produce un PNG en la ruta `output` indicada (relativa al repo root).
 *
 * Tipos de descriptor:
 *   - "terminal": ventana con prompt en una sola sesión
 *   - "config":   contenido de archivo con header (filename) y syntax mono
 *   - "table":    resultado tabular (SQL / Tinker) con header de query
 *
 * Uso:
 *   node docs/guides/scripts/render-synthetic.mjs           # todos
 *   node docs/guides/scripts/render-synthetic.mjs --only slug
 *   node docs/guides/scripts/render-synthetic.mjs --list
 *
 * Pre-requisitos: Playwright chromium instalado.
 */
import { chromium } from "playwright";
import { fileURLToPath } from "url";
import path from "path";
import fs from "fs/promises";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const REPO_ROOT = path.resolve(__dirname, "..", "..", "..");
const SRC_DIR = path.join(REPO_ROOT, "docs/guides/synthetic/source");

const argv = process.argv.slice(2);
const only = argv.includes("--only") ? argv[argv.indexOf("--only") + 1] : null;
const listOnly = argv.includes("--list");

function esc(s) {
  return String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
}

function renderTerminal(d) {
  const title = esc(d.title || "Terminal");
  const lines = (d.commands || []).map((c) => {
    const prompt = esc(c.prompt || "$");
    const cmd = esc(c.cmd || "");
    const out = (c.output || []).map((o) => `<div class="line">${esc(o)}</div>`).join("");
    return `<div class="prompt-line"><span class="prompt">${prompt}</span> <span class="cmd">${cmd}</span></div>${out}`;
  }).join("");
  return `<!doctype html><html><head><meta charset="utf-8"><style>
    body { margin:0; background:#0a0e14; font-family:'Cascadia Mono','SF Mono','Menlo','Consolas',monospace; }
    .terminal { width: 1100px; margin: 24px auto; border-radius: 10px; overflow: hidden; box-shadow: 0 12px 40px rgba(0,0,0,0.35); border: 1px solid #1d242c; }
    .titlebar { background: linear-gradient(180deg, #2c333a, #1f262d); height: 32px; display: flex; align-items: center; padding: 0 14px; gap: 8px; }
    .dot { width: 12px; height: 12px; border-radius: 50%; }
    .dot.r { background: #ff5f57; } .dot.y { background: #ffbd2e; } .dot.g { background: #28c93f; }
    .title { color: #aab7c4; font-size: 12px; margin-left: 12px; }
    .body { background: #0d1117; color: #c9d1d9; font-size: 14px; padding: 18px 22px; line-height: 1.55; min-height: 320px; }
    .prompt-line { color: #c9d1d9; margin-top: 8px; }
    .prompt-line:first-child { margin-top: 0; }
    .prompt { color: #58a6ff; font-weight: 600; }
    .cmd { color: #f0f6fc; }
    .line { color: #8b949e; white-space: pre; }
    .ok { color: #3fb950; } .warn { color: #d29922; } .err { color: #f85149; }
  </style></head><body>
    <div class="terminal">
      <div class="titlebar"><span class="dot r"></span><span class="dot y"></span><span class="dot g"></span><span class="title">${title}</span></div>
      <div class="body">${lines}</div>
    </div>
  </body></html>`;
}

function renderConfig(d) {
  const filename = esc(d.filename || "config");
  const lang = esc(d.lang || "text");
  const body = (d.lines || []).map((l) => `<div class="line">${esc(l)}</div>`).join("");
  return `<!doctype html><html><head><meta charset="utf-8"><style>
    body { margin:0; background:#f9fafb; font-family:'Inter','Segoe UI','Helvetica Neue',sans-serif; }
    .file { width: 1100px; margin: 24px auto; border-radius: 8px; overflow: hidden; box-shadow: 0 8px 28px rgba(15,23,42,0.10); border: 1px solid #e2e8f0; }
    .filebar { background: #f1f5f9; height: 36px; display: flex; align-items: center; padding: 0 14px; border-bottom: 1px solid #e2e8f0; }
    .filename { color: #1f2937; font-size: 13px; font-weight: 600; }
    .lang-badge { margin-left: auto; padding: 2px 8px; border-radius: 4px; background: #2563eb; color: #fff; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
    .body { background: #ffffff; color: #0f172a; font-size: 14px; padding: 18px 22px; font-family: 'Cascadia Mono','SF Mono','Menlo','Consolas',monospace; line-height: 1.6; min-height: 320px; }
    .line { white-space: pre; }
    .comment { color: #64748b; }
  </style></head><body>
    <div class="file">
      <div class="filebar"><span class="filename">${filename}</span><span class="lang-badge">${lang}</span></div>
      <div class="body">${body}</div>
    </div>
  </body></html>`;
}

function renderTable(d) {
  const title = esc(d.title || "Resultado de consulta");
  const subtitle = esc(d.subtitle || "");
  const cols = (d.columns || []).map((c) => `<th>${esc(c)}</th>`).join("");
  const rows = (d.rows || []).map((r) => {
    const cells = r.map((c) => `<td>${esc(c)}</td>`).join("");
    return `<tr>${cells}</tr>`;
  }).join("");
  return `<!doctype html><html><head><meta charset="utf-8"><style>
    body { margin:0; background:#f9fafb; font-family:'Inter','Segoe UI','Helvetica Neue',sans-serif; }
    .panel { width: 1100px; margin: 24px auto; border-radius: 8px; overflow: hidden; box-shadow: 0 8px 28px rgba(15,23,42,0.10); border: 1px solid #e2e8f0; background: #ffffff; }
    .head { padding: 14px 18px; background: #1f2937; color: #fff; }
    .title { font-size: 14px; font-weight: 700; }
    .subtitle { font-size: 12px; color: #c7d2fe; font-family: 'Cascadia Mono','SF Mono','Menlo','Consolas',monospace; margin-top: 4px; }
    table { width: 100%; border-collapse: collapse; font-size: 13px; font-family: 'Cascadia Mono','SF Mono','Menlo','Consolas',monospace; }
    th, td { padding: 10px 14px; border-bottom: 1px solid #e2e8f0; text-align: left; color: #0f172a; vertical-align: top; }
    th { background: #eff6ff; color: #1e3a8a; font-weight: 700; }
    tr:hover td { background: #f9fafb; }
  </style></head><body>
    <div class="panel">
      <div class="head"><div class="title">${title}</div>${subtitle ? `<div class="subtitle">${subtitle}</div>` : ""}</div>
      <table><thead><tr>${cols}</tr></thead><tbody>${rows}</tbody></table>
    </div>
  </body></html>`;
}

function buildHtml(d) {
  switch (d.kind) {
    case "terminal": return renderTerminal(d);
    case "config":   return renderConfig(d);
    case "table":    return renderTable(d);
    default: throw new Error(`unknown kind: ${d.kind}`);
  }
}

async function listDescriptors() {
  const entries = await fs.readdir(SRC_DIR);
  return entries.filter((f) => f.endsWith(".json")).sort();
}

async function renderOne(browser, descFile) {
  const fullPath = path.join(SRC_DIR, descFile);
  const d = JSON.parse(await fs.readFile(fullPath, "utf8"));
  const html = buildHtml(d);
  const outRel = d.output;
  if (!outRel) throw new Error(`descriptor ${descFile} sin "output"`);
  const outAbs = path.resolve(REPO_ROOT, outRel);
  await fs.mkdir(path.dirname(outAbs), { recursive: true });

  const ctx = await browser.newContext({ viewport: { width: 1180, height: 800 }, deviceScaleFactor: 2 });
  const page = await ctx.newPage();
  await page.setContent(html, { waitUntil: "domcontentloaded" });
  const target = await page.$(".terminal, .file, .panel");
  if (!target) throw new Error("ningún contenedor encontrado en HTML generado");
  await target.screenshot({ path: outAbs, type: "png" });
  await ctx.close();
  return outAbs;
}

async function main() {
  let descs = await listDescriptors();
  if (listOnly) { console.log(descs.join("\n")); return; }
  if (only) descs = descs.filter((f) => path.basename(f, ".json") === only);
  if (descs.length === 0) { console.error("[synthetic] nada que renderizar"); process.exit(1); }
  console.log(`[synthetic] renderizando ${descs.length} descriptor(es)…`);
  const browser = await chromium.launch({ headless: true });
  let failed = 0;
  try {
    for (const f of descs) {
      try {
        const out = await renderOne(browser, f);
        console.log(`  ${f} -> ${path.relative(REPO_ROOT, out)}`);
      } catch (e) {
        console.error(`  ${f} FAIL: ${e.message}`);
        failed++;
      }
    }
  } finally {
    await browser.close();
  }
  if (failed > 0) process.exit(2);
  console.log("[synthetic] listo");
}

main().catch((e) => { console.error(e); process.exit(1); });
