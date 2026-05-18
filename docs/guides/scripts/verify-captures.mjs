#!/usr/bin/env node
/**
 * verify-captures.mjs — Valida que cada descriptor en captures.json tiene
 * su PNG en disco, que pesa < 350 kB y que tiene ~1440px de ancho.
 *
 * Falla con exit 1 si encuentra problemas. Pensado para correrse en CI.
 */
import sharp from "sharp";
import { fileURLToPath } from "url";
import path from "path";
import fs from "fs/promises";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const GUIDES_DIR = path.resolve(__dirname, "..");
const REPO_ROOT = path.resolve(GUIDES_DIR, "..", "..");

const MAX_BYTES = 350 * 1024;
const TARGET_WIDTH = 1440;
const WIDTH_TOLERANCE = 5;

async function main() {
  const jsonPath = path.join(GUIDES_DIR, "captures.json");
  const captures = JSON.parse(await fs.readFile(jsonPath, "utf8"));

  let failures = 0;
  for (const c of captures) {
    const p = path.resolve(REPO_ROOT, c.outputPath);
    try {
      const stat = await fs.stat(p);
      if (stat.size > MAX_BYTES) {
        console.error(
          `[fail] ${c.slug}: ${stat.size} bytes > ${MAX_BYTES} (recomprima)`,
        );
        failures++;
      }
      const meta = await sharp(p).metadata();
      if (Math.abs(meta.width - TARGET_WIDTH) > WIDTH_TOLERANCE) {
        console.error(
          `[fail] ${c.slug}: width=${meta.width}, esperado ~${TARGET_WIDTH}`,
        );
        failures++;
      }
    } catch (err) {
      if (err.code === "ENOENT") {
        console.error(`[fail] ${c.slug}: PNG no existe en ${c.outputPath}`);
        failures++;
      } else {
        throw err;
      }
    }
  }

  if (failures > 0) {
    console.error(`\n[verify-captures] ${failures} fallas`);
    process.exit(1);
  }
  console.log(`[ok] verify-captures: ${captures.length} OK`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
