#!/usr/bin/env node
/**
 * captures.mjs — Orquestador Playwright para generar las capturas del
 * inventario `docs/guides/00-screenshot-inventory.md`, leyendo descriptores
 * desde `docs/guides/captures.json`.
 *
 * Uso:
 *   node scripts/captures.mjs                         # todo
 *   node scripts/captures.mjs --guide admin
 *   node scripts/captures.mjs --only <slug>
 *   node scripts/captures.mjs --list
 *   node scripts/captures.mjs --headed                # browser visible
 *   node scripts/captures.mjs --base-url http://localhost
 *
 * Pre-requisitos:
 *   - Sail corriendo con base seedeada (Spec009DemoSeeder + GuidesDemoSeeder)
 *   - APP_ENV=local (los Login.php saltan el captcha en local|testing)
 *   - APP_DEBUG=false (sin Debugbar en capturas)
 */
import { chromium } from "playwright";
import { fileURLToPath } from "url";
import path from "path";
import fs from "fs/promises";
import yargs from "yargs";
import { hideBin } from "yargs/helpers";
import { resolveAnnotations, applyAnnotations } from "./annotations.mjs";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const GUIDES_DIR = path.resolve(__dirname, "..");
const REPO_ROOT = path.resolve(GUIDES_DIR, "..", "..");

const argv = yargs(hideBin(process.argv))
  .option("guide", {
    choices: ["all", "admin", "impl", "user"],
    default: "all",
  })
  .option("only", { type: "string" })
  .option("list", { type: "boolean" })
  .option("headed", { type: "boolean" })
  .option("base-url", {
    type: "string",
    default: process.env.CAPTURE_BASE_URL || "http://localhost",
  })
  .help()
  .parse();

async function loadCaptures() {
  const jsonPath = path.join(GUIDES_DIR, "captures.json");
  try {
    return JSON.parse(await fs.readFile(jsonPath, "utf8"));
  } catch (err) {
    if (err.code === "ENOENT") {
      console.error(`[error] No existe ${jsonPath}`);
      process.exit(1);
    }
    throw err;
  }
}

async function loginAdmin(page, baseUrl, { username, password }) {
  await page.goto(`${baseUrl}/admin/login`, { waitUntil: "networkidle" });
  await page.locator("#data\\.username").fill(username);
  await page.locator("#data\\.password").fill(password);
  await page.click('button[type="submit"]');
  await page.waitForLoadState("networkidle", { timeout: 15000 }).catch(() => {});
  await page.waitForTimeout(500);
  if (page.url().includes("/admin/login")) {
    throw new Error(`Admin login no avanzó (url=${page.url()})`);
  }
}

async function loginMember(page, baseUrl, { email, password }) {
  await page.goto(`${baseUrl}/member/login`, { waitUntil: "networkidle" });
  await page.locator("#data\\.email").fill(email);
  await page.locator("#data\\.password").fill(password);
  await page.click('button[type="submit"]');
  await page.waitForLoadState("networkidle", { timeout: 15000 }).catch(() => {});
  await page.waitForTimeout(500);
  if (page.url().includes("/member/login")) {
    throw new Error(`Member login no avanzó (url=${page.url()})`);
  }
}

async function runPreActions(page, actions = []) {
  for (const action of actions) {
    switch (action.type) {
      case "click":
        await page.click(action.selector);
        break;
      case "fill":
        await page.fill(action.selector, action.value);
        break;
      case "hover":
        await page.hover(action.selector);
        break;
      case "scroll":
        await page.evaluate(
          (sel) => document.querySelector(sel)?.scrollIntoView({ behavior: "instant", block: "center" }),
          action.selector,
        );
        break;
      case "press":
        await page.keyboard.press(action.key);
        break;
      case "wait":
        await page.waitForTimeout(action.ms ?? 250);
        break;
    }
  }
}

async function runWaits(page, waits = []) {
  for (const w of waits) {
    if (w.type === "selector") {
      await page.waitForSelector(w.value, { timeout: 8000 });
    } else if (w.type === "timeout") {
      await page.waitForTimeout(w.value);
    } else if (w.type === "networkidle") {
      await page.waitForLoadState("networkidle", { timeout: 8000 }).catch(() => {});
    }
  }
}

// Cache de contextos por identidad de auth para evitar el rate-limit de login
// de Filament. Las claves son "anon", "admin:<username>", "member:<email>".
const contextCache = new Map();

async function getContext(browser, baseUrl, auth) {
  const key = auth
    ? `${auth.panel}:${auth.username || auth.email}`
    : "anon";
  if (contextCache.has(key)) return contextCache.get(key);

  const ctx = await browser.newContext({
    viewport: { width: 1440, height: 900 },
    deviceScaleFactor: 1,
    locale: "es-ES",
    timezoneId: "America/Santiago",
    colorScheme: "light",
  });

  if (auth?.panel === "admin") {
    const page = await ctx.newPage();
    await loginAdmin(page, baseUrl, auth);
    await page.close();
  } else if (auth?.panel === "member") {
    const page = await ctx.newPage();
    await loginMember(page, baseUrl, auth);
    await page.close();
  }

  contextCache.set(key, ctx);
  return ctx;
}

async function capture(browser, baseUrl, descriptor) {
  const ctx = await getContext(browser, baseUrl, descriptor.auth);
  const page = await ctx.newPage();
  if (descriptor.viewport) {
    await page.setViewportSize(descriptor.viewport);
  }

  const start = Date.now();
  try {
    const fullUrl = descriptor.absoluteUrl
      ? descriptor.url
      : `${baseUrl}${descriptor.url}`;
    await page.goto(fullUrl, { waitUntil: "networkidle", timeout: 20000 }).catch(async (e) => {
      console.warn(`[warn] ${descriptor.slug}: networkidle fallback (${e.message.substring(0, 60)})`);
      await page.waitForLoadState("domcontentloaded", { timeout: 8000 }).catch(() => {});
    });

    await runPreActions(page, descriptor.preActions);
    await runWaits(page, descriptor.wait);

    const outputPath = path.resolve(REPO_ROOT, descriptor.outputPath);
    await fs.mkdir(path.dirname(outputPath), { recursive: true });
    await page.screenshot({
      path: outputPath,
      type: "png",
      fullPage: descriptor.fullPage ?? false,
      clip: descriptor.clip,
    });

    let annotMsg = "";
    if (descriptor.annotations && descriptor.annotations.length > 0) {
      const viewport = page.viewportSize() ?? { width: 1440, height: 900 };
      const resolved = await resolveAnnotations(page, descriptor.annotations, viewport);
      if (resolved.length > 0) {
        await applyAnnotations(outputPath, resolved);
        const coordsPath = outputPath.replace(/\.png$/, ".coords.json");
        await fs.writeFile(coordsPath, JSON.stringify(resolved, null, 2));
        annotMsg = ` +${resolved.length} annot`;
      } else {
        annotMsg = " annot=0";
      }
    }

    const ms = Date.now() - start;
    console.log(`[ok] ${descriptor.slug} -> ${descriptor.outputPath} (${ms}ms${annotMsg})`);
  } catch (err) {
    console.error(`[error] ${descriptor.slug}: ${err.message}`);
  } finally {
    await page.close();
  }
}

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

  console.log(`[info] ${filtered.length} captura(s) en cola contra ${argv["base-url"]}`);
  const browser = await chromium.launch({ headless: !argv.headed });
  try {
    for (const descriptor of filtered) {
      await capture(browser, argv["base-url"], descriptor);
    }
  } finally {
    for (const ctx of contextCache.values()) await ctx.close();
    await browser.close();
  }
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
