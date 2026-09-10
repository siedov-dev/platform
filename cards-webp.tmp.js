const fs = require("fs");
const path = require("path");
const sharp = require("sharp");

const dir = "public/bonus-park/Cards_Game";
const files = fs.readdirSync(dir).filter((f) => f.endsWith(".png") && !f.startsWith("Preview"));

(async () => {
  let total = 0;
  for (const f of files) {
    const src = path.join(dir, f);
    const out = src.replace(/\.png$/, ".webp");
    const isCard = !f.startsWith("Shine") && !f.startsWith("Light");
    let img = sharp(src);
    if (isCard) img = img.resize({ width: 368 });
    const buf = await img.webp({ quality: 82, alphaQuality: 90 }).toBuffer();
    fs.writeFileSync(out, buf);
    total += buf.length;
  }
  console.log(files.length + " files -> webp, total " + Math.round(total / 1024) + " KB");
})();
