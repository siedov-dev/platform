import puppeteer from "puppeteer-core";
const b = await puppeteer.launch({
  executablePath: "C:/Program Files/Google/Chrome/Application/chrome.exe",
  headless: "new", args: ["--window-size=460,900", "--force-device-scale-factor=2"],
});
try {
  const page = (await b.pages())[0];
  page.on("pageerror", (e) => console.log("PAGEERROR:", e.message));
  await page.goto("http://localhost:5173/?page=bonuspark", { waitUntil: "domcontentloaded" });
  await new Promise(r => setTimeout(r, 1200));
  const btns = await page.$$("button[class*='half']");
  if (btns[0]) await btns[0].click();
  await new Promise(r => setTimeout(r, 2600));
  const box = await page.evaluate(() => {
    const h = document.querySelector("div[class*='_host_']");
    if (!h) return null;
    const r = h.getBoundingClientRect();
    return { x: r.x - 26, y: r.y - 26, width: r.width + 52, height: r.height + 52 };
  });
  if (!box) { console.log("HOST NOT FOUND"); } else { await page.screenshot({ path: "sf.png", clip: box }); console.log("shot ok"); }
} catch (e) { console.log("ERR", e.message); } finally { await b.close(); }
