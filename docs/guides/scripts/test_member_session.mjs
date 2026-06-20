import { chromium } from "playwright";

(async () => {
    const browser = await chromium.launch();
    const context = await browser.newContext();
    const page = await context.newPage();
    await page.setViewportSize({ width: 1280, height: 800 });

    // 1. Login
    console.log("Logging in as candidate@example.com...");
    await page.goto("http://localhost/member/login", { waitUntil: "networkidle" });
    await page.locator("#data\\.email").fill("candidate@example.com");
    await page.locator("#data\\.password").fill("password");
    await page.click('button[type="submit"]');
    await page.waitForURL("**/member");
    await page.waitForLoadState("networkidle");
    await page.screenshot({ path: "docs/guides/screenshots/member_dashboard.png" });
    console.log("[ok] Dashboard screenshot saved.");

    // 1.5 Go to Buscar Empleo
    console.log("Navigating to Buscar Empleo page...");
    await page.goto("http://localhost/member/browse-jobs", { waitUntil: "networkidle" });
    await page.screenshot({ path: "docs/guides/screenshots/member_browse_jobs.png" });
    console.log("[ok] Logged-in Browse Jobs screenshot saved.");

    // 2. Go to Bolsa de Trabajo
    console.log("Navigating to Bolsa de Trabajo...");
    await page.goto("http://localhost/bolsa-de-trabajo", { waitUntil: "networkidle" });
    await page.screenshot({ path: "docs/guides/screenshots/logged_in_job_board.png" });
    console.log("[ok] Logged-in Job Board screenshot saved.");

    // 3. Go to Job Offer Detail
    console.log("Navigating to coordinators job offer...");
    await page.goto("http://localhost/bolsa-de-trabajo/coordinador-de-eventos", { waitUntil: "networkidle" });
    await page.screenshot({ path: "docs/guides/screenshots/logged_in_offer_detail.png" });
    console.log("[ok] Logged-in Offer Detail screenshot saved.");

    await browser.close();
})();
