const puppeteer = require("puppeteer-core");
const IPHONE = "Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1";
(async () => {
  const browser = await puppeteer.launch({ executablePath: "C:/Program Files/Google/Chrome/Application/chrome.exe", headless: "new", args: ["--no-sandbox", "--disable-gpu", "--hide-scrollbars"] });
  for (const [ua, tag] of [[null, "PC"], [IPHONE, "iPhone"]]) {
    const page = await browser.newPage();
    if (ua) await page.setUserAgent(ua);
    await page.setViewport({ width: 390, height: 844, deviceScaleFactor: 2 });
    await page.goto("http://localhost:5173/?page=parktop", { waitUntil: "networkidle2", timeout: 90000 });
    await new Promise((r) => setTimeout(r, 6000));
    const box = await page.evaluate(() => { const b = document.querySelector("[class^='_box_']"); const r = b.getBoundingClientRect(); return { x: r.x + r.width / 2, y: r.y + r.height / 2 }; });
    const snap = () => page.evaluate(() => {
      const c = [...document.querySelectorAll("[class*='winCell']")].find((x) => !/winCellOff/.test(x.className) && x.querySelector("img[src*='Coin_']"));
      if (!c) return null;
      const idx = [...c.parentElement.children].indexOf(c);
      const stack = c.querySelector("[class*='twistStack']");
      const im = [...stack.querySelectorAll("img")].find((x) => getComputedStyle(x).visibility === "visible");
      const w = im.getBoundingClientRect();
      const cell = [...document.querySelectorAll("[class*='cell_']")][idx];
      const cr = cell.getBoundingClientRect();
      const cy = cr.top + cr.height / 2;
      const reel = [...cell.querySelectorAll("img")]
        .filter((x) => /Icn_Coin_Front/.test(x.src) && !/iconBlur/.test(x.className) && x.getBoundingClientRect().height > 4)
        .map((x) => x.getBoundingClientRect())
        .sort((a, b) => Math.abs(a.top + a.height / 2 - cy) - Math.abs(b.top + b.height / 2 - cy))[0];
      const m = getComputedStyle(stack).transform.match(/matrix\(([\d.-]+)/);
      return {
        колонка: idx,
        масштаб: m ? +(+m[1]).toFixed(2) : 1,
        win_центр: +(w.top + w.height / 2).toFixed(1),
        барабан_центр: reel ? +(reel.top + reel.height / 2).toFixed(1) : null,
        Δ: reel ? +((w.top + w.height / 2) - (reel.top + reel.height / 2)).toFixed(1) : null,
      };
    });
    let appear = null, parked = null;
    for (let spin = 1; spin <= 8 && !appear; spin += 1) {
      await page.mouse.click(box.x, box.y);
      for (let i = 0; i < 100; i += 1) {
        const st = await snap();
        if (st && !appear && st.масштаб > 1.1) appear = st;
        if (st && st.масштаб < 0.85) { parked = st; break; }
        await new Promise((r) => setTimeout(r, 70));
      }
      if (!appear) await new Promise((r) => setTimeout(r, 400));
    }
    console.log(tag + ":");
    console.log("  при появлении: " + JSON.stringify(appear));
    console.log("  после сжатия : " + JSON.stringify(parked));
    await page.close();
  }
  await browser.close();
})();
