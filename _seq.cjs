const puppeteer = require("puppeteer-core");
(async () => {
  const browser = await puppeteer.launch({ executablePath: "C:/Program Files/Google/Chrome/Application/chrome.exe", headless: "new", args: ["--no-sandbox", "--disable-gpu", "--hide-scrollbars"] });
  const page = await browser.newPage();
  const logs = [];
  page.on("console", (m) => { const t = m.text(); if (/\[slot/.test(t)) logs.push(t.replace("[slot demo] ", "")); });
  await page.setViewport({ width: 390, height: 844, deviceScaleFactor: 1 });
  await page.goto("http://localhost:5173/?page=parktop", { waitUntil: "networkidle2", timeout: 90000 });
  await new Promise((r) => setTimeout(r, 6500));
  await page.evaluate(() => { const b = [...document.querySelectorAll("button")].find((x) => /auto/i.test(x.textContent || "")); if (b) b.click(); });
  await new Promise((r) => setTimeout(r, 115000));
  console.log("выпавшие комбинации:");
  logs.slice(0, 22).forEach((l, i) => console.log("  " + String(i + 1).padStart(2) + ". " + l));
  const lit = await page.evaluate(() => document.querySelectorAll("[class*='crystalOn'], [class*='crOn_']").length);
  await page.screenshot({ path: "C:/Users/zssss/shots/seq_end.png" });
  await browser.close();
})();
