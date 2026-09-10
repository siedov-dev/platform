const puppeteer = require("puppeteer-core");
(async () => {
  const browser = await puppeteer.launch({ executablePath: "C:/Program Files/Google/Chrome/Application/chrome.exe", headless: "new", args: ["--no-sandbox", "--disable-gpu", "--hide-scrollbars"] });
  const page = await browser.newPage();
  const errs = [];
  const bad = [];
  page.on("pageerror", (e) => errs.push(String(e).slice(0, 150)));
  page.on("response", (r) => { if (r.status() >= 400 && !/favicon/.test(r.url())) bad.push(r.url().split("/").pop() + " HTTP " + r.status()); });
  await page.setViewport({ width: 390, height: 844, deviceScaleFactor: 1 });
  await page.goto("http://localhost:5173/?page=parkmap", { waitUntil: "networkidle2", timeout: 90000 });
  await new Promise((r) => setTimeout(r, 5000));
  const map = await page.evaluate(() => ({ карта: !!document.querySelector("[class*='overview']") }));
  await page.goto("http://localhost:5173/?page=parktop", { waitUntil: "networkidle2", timeout: 90000 });
  await new Promise((r) => setTimeout(r, 7000));
  const park = await page.evaluate(() => ({
    сцена: !!document.querySelector("[class*='uparkScene']"),
    рамка: document.querySelectorAll("[class*='frame_'] [class*='bar_']").length,
    видео: [...document.querySelectorAll("video")].map((v) => (v.currentSrc || "").split("/").pop() + ":" + v.readyState),
    кристалл: [...document.querySelectorAll("img")].filter((i) => /Crystal_Fx/.test(i.src)).length,
  }));
  await page.goto("http://localhost:5173/?page=parkbot", { waitUntil: "networkidle2", timeout: 90000 });
  await new Promise((r) => setTimeout(r, 6000));
  const bot = await page.evaluate(() => ({ поля: !!document.querySelector("img[src*='Down_Bg']"), рамка: document.querySelectorAll("[class*='frame_'] [class*='bar_']").length }));
  console.log("общая карта: " + JSON.stringify(map));
  console.log("верхний парк: " + JSON.stringify(park));
  console.log("нижний парк: " + JSON.stringify(bot));
  console.log(errs.length ? "ОШИБКИ JS: " + errs.slice(0, 3).join(" | ") : "ошибок JS нет");
  console.log(bad.length ? "НЕ ЗАГРУЗИЛОСЬ: " + [...new Set(bad)].slice(0, 5).join(", ") : "все файлы загрузились");
  await browser.close();
})();
