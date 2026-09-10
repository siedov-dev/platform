const puppeteer = require("puppeteer-core");
(async () => {
  const browser = await puppeteer.launch({
    executablePath: "C:/Program Files/Google/Chrome/Application/chrome.exe",
    headless: "new",
    args: ["--no-sandbox", "--disable-gpu", "--hide-scrollbars"],
  });
  const page = await browser.newPage();
  await page.setViewport({ width: 390, height: 844, deviceScaleFactor: 1 });
  await page.goto("http://localhost:5173/?page=bonuspark", { waitUntil: "domcontentloaded", timeout: 60000 });
  await new Promise((r) => setTimeout(r, 2500));
  await page.evaluate(() => {
    const b = [...document.querySelectorAll("button")].find((x) => /Park/i.test(x.textContent || ""));
    if (b) b.click();
  });
  await new Promise((r) => setTimeout(r, 6500));
  await page.evaluate(() => {
    const b = [...document.querySelectorAll("button")].find((x) => /auto/i.test(x.textContent || ""));
    if (b) b.click();
  });
  await page.evaluate(() => {
    window.__log = [];
    const strips = [...document.querySelectorAll("[class*='strip']")];
    let last = strips.map((s) => s.style.transform);
    let moving = false;
    let t0 = performance.now();
    const tick = () => {
      const now = performance.now();
      const cur = strips.map((s) => s.style.transform);
      const changed = cur.some((v, i) => v !== last[i]);
      last = cur;
      if (changed !== moving) {
        window.__log.push({ state: changed ? "движение" : "покой", at: Math.round(now - t0) });
        moving = changed;
      }
      requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  });
  await new Promise((r) => setTimeout(r, 32000));
  const log = await page.evaluate(() => window.__log);
  const segs = [];
  for (let i = 1; i < log.length; i += 1) segs.push({ what: log[i - 1].state, ms: log[i].at - log[i - 1].at });
  console.log("фазы подряд (мс):");
  segs.forEach((s, i) => console.log("  " + String(i + 1).padStart(2) + "  " + s.what.padEnd(9) + String(s.ms).padStart(6)));
  const cycles = [];
  for (let i = 0; i + 1 < segs.length; i += 2) if (segs[i].what === "движение") cycles.push(segs[i].ms + segs[i + 1].ms);
  console.log("\nполные циклы (мс): " + cycles.join(", "));
  await browser.close();
})();
