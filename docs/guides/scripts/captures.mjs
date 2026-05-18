#!/usr/bin/env node
/**
 * captures.mjs — Orquestador Playwright para generar las ~110 capturas del
 * inventario `docs/guides/00-screenshot-inventory.md`.
 *
 * Lee `docs/guides/captures.json` (generado a partir del inventario) y
 * produce PNGs en `docs/guides/screenshots/<guide>/<slug>.png`.
 *
 * Uso:
 *   node scripts/captures.mjs                          # todo
 *   node scripts/captures.mjs --guide admin
 *   node scripts/captures.mjs --only admin-org-suspend-modal
 *   node scripts/captures.mjs --list                   # imprime slugs
 *   node scripts/captures.mjs --headed                 # browser visible
 *   node scripts/captures.mjs --base-url http://laravel.test
 *
 * Requiere: playwright + sharp (ver docs/guides/package.json)
 *
 * Estado: ESQUELETO INICIAL — se complementa en Fase 2 con el JSON descriptor
 * completo derivado del inventario. La Fase 1 solo entrega el scaffolding.
 */
import { chromium } from "playwright";
import { fileURLToPath } from "url";
import path from "path";
import fs from "fs/promises";
import yargs from "yargs";
import { hideBin } from "yargs/helpers";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const GUIDES_DIR = path.resolve(__dirname, "..");
const REPO_ROOT = path.resolve(GUIDES_DIR, "..", "..");

// ---------------------------------------------------------------------------
// Argumentos
// ---------------------------------------------------------------------------
const argv = yargs(hideBin(process.argv))
  .option("guide", {
    choices: ["all", "admin", "impl", "user"],
    default: "all",
    describe: "Limita la generación a una guía",
  })
  .option("only", { type: "string", describe: "Genera sólo el slug dado" })
  .option("list", { type: "boolean", describe: "Lista los slugs y sale" })
  .option("headed", { type: "boolean", describe: "Browser visible (debug)" })
  .option("base-url", {
    type: "string",
    default: process.env.CAPTURE_BASE_URL || "http://localhost",
  })
  .help()
  .parse();

// ---------------------------------------------------------------------------
// Credenciales seedeadas — sincronizadas con Spec009DemoSeeder
// ---------------------------------------------------------------------------
const ACCOUNTS = {
  "admin@example.com":            { password: "password", panel: "/admin"  },
  "moderator@example.com":        { password: "password", panel: "/admin"  },
  "org-verified-a@example.com":   { password: "password", panel: "/member" },
  "org-verified-b@example.com":   { password: "password", panel: "/member" },
  "org-suspend-target@example.com": { password: "password", panel: "/member" },
  "candidate-1@example.com":      { password: "password", panel: "/member" },
  "candidate-2@example.com":      { password: "password", panel: "/member" },
};

// ---------------------------------------------------------------------------
// Carga del inventario JSON
// ---------------------------------------------------------------------------
async function loadCaptures() {
  const jsonPath = path.join(GUIDES_DIR, "captures.json");
  try {
    const raw = await fs.readFile(jsonPath, "utf8");
    return JSON.parse(raw);
  } catch (err) {
    if (err.code === "ENOENT") {
      console.error(
        `[error] No existe ${jsonPath}.\n` +
          `        Genérelo a partir de docs/guides/00-screenshot-inventory.md` +
          ` (Fase 2 del blueprint).`,
      );
      process.exit(1);
    }
    throw err;
  }
}

// ---------------------------------------------------------------------------
// Login Filament (solo si la captura lo requiere)
// ---------------------------------------------------------------------------
async function login(page, baseUrl, email) {
  const account = ACCOUNTS[email];
  if (!account) throw new Error(`Cuenta no seedeada: ${email}`);

  await page.goto(`${baseUrl}${account.panel}/login`);
  await page.fill('input[name="email"]', email);
  await page.fill('input[name="password"]', account.password);
  await page.click('button[type="submit"]');
  await page.waitForURL(new RegExp(`${account.panel}(?!/login)`));
}

// ---------------------------------------------------------------------------
// Captura individual
// ---------------------------------------------------------------------------
async function capture(browser, baseUrl, descriptor) {
  const ctx = await browser.newContext({
    viewport: descriptor.viewport ?? { width: 1440, height: 900 },
    deviceScaleFactor: 1,
    locale: "es-ES",
    timezoneId: "America/Santiago",
    colorScheme: "light",
  });
  const page = await ctx.newPage();

  try {
    if (descriptor.auth) {
      await login(page, baseUrl, descriptor.auth);
    }

    if (descriptor.url) {
      await page.goto(`${baseUrl}${descriptor.url}`, {
        waitUntil: "networkidle",
      });
    }

    for (const action of descriptor.preActions ?? []) {
      switch (action.type) {
        case "click":
          await page.click(action.selector);
          break;
        case "fill":
          await page.fill(action.selector, action.value);
          break;
        case "wait":
          await page.waitForTimeout(action.ms ?? 250);
          break;
      }
    }

    for (const wait of descriptor.wait ?? []) {
      if (wait.type === "selector") {
        await page.waitForSelector(wait.value, { timeout: 8000 });
      } else if (wait.type === "timeout") {
        await page.waitForTimeout(wait.value);
      }
    }

    const outputPath = path.resolve(REPO_ROOT, descriptor.outputPath);
    await fs.mkdir(path.dirname(outputPath), { recursive: true });
    await page.screenshot({
      path: outputPath,
      type: "png",
      fullPage: descriptor.fullPage ?? false,
    });
    console.log(`[ok] ${descriptor.slug} -> ${descriptor.outputPath}`);
  } catch (err) {
    console.error(`[error] ${descriptor.slug}: ${err.message}`);
  } finally {
    await ctx.close();
  }
}

// ---------------------------------------------------------------------------
// Entrypoint
// ---------------------------------------------------------------------------
async function main() {
  const captures = await loadCaptures();

  if (argv.list) {
    captures.forEach((c) => console.log(c.slug));
    return;
  }

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

  const browser = await chromium.launch({ headless: !argv.headed });
  try {
    for (const descriptor of filtered) {
      await capture(browser, argv["base-url"], descriptor);
    }
  } finally {
    await browser.close();
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
