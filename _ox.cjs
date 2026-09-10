const puppeteer = require("puppeteer-core");
(async () => {
  const browser = await puppeteer.launch({ executablePath: "C:/Program Files/Google/Chrome/Application/chrome.exe", headless: "new", args: ["--no-sandbox", "--disable-gpu", "--hide-scrollbars"] });
  const page = await browser.newPage();
  await page.setViewport({ width: 610, height: 860, deviceScaleFactor: 1 });
  await page.goto("http://localhost:5173/?page=bonuspark", { waitUntil: "networkidle2", timeout: 90000 });
  await new Promise((r) => setTimeout(r, 3500));
  await page.evaluate(() => { const b = [...document.querySelectorAll("button")].find((x) => /Fields/i.test(x.textContent || "")); if (b) b.click(); });
  await new Promise((r) => setTimeout(r, 5500));
  const m = await page.evaluate(() => {
    const ph = document.querySelector("[class*='phone']");
    const cs = getComputedStyle(ph);
    const left = [...document.querySelectorAll("[class*='left_']")].map((e) => { const q = e.getBoundingClientRect(); return Math.round(q.x) + " w" + Math.round(q.width) + " cls:" + String(e.className).slice(0, 24); });
    return { overX: cs.getPropertyValue("--over-x").trim(), frT: cs.getPropertyValue("--fr-t").trim(), leftBars: left };
  });
  console.log(JSON.stringify(m, null, 1));
  await browser.close();
})();
