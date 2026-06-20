import { chromium } from "playwright";

const url = process.argv[2] || "http://localhost/";
const path = process.argv[3] || "docs/guides/screenshots/custom_page.png";

(async () => {
    const browser = await chromium.launch();
    const page = await browser.newPage();
    await page.setViewportSize({ width: 1280, height: 800 });
    await page.goto(url, { waitUntil: "networkidle" });
    await page.screenshot({ path });
    console.log(`[ok] Screenshot of ${url} saved to ${path}`);
    await browser.close();
})();
