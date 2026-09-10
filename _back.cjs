const fs = require("fs");
const p = "src/components/pages/MonitorScreen.jsx";
const rep = (s, x, y) => { if (!s.includes(x)) throw new Error("miss " + x.slice(0, 60)); return s.replace(x, y); };
let s = fs.readFileSync(p, "utf8");

s = rep(s,
  '{ key: "crystal", img: "ezgif-frame_Base.png", dir: CRYSTAL, h: 115, bh: 115, noFx: true, noSway: true, wm: 1, wh: 117, lockPos: true, wfix: -11.27 }',
  '{ key: "crystal", img: "ezgif-frame_Base.png", dir: CRYSTAL, h: 115, bh: 115, noFx: true, noSway: true, wm: 1.5, wh: 117, lockPos: true, wfix: -11.27, glowFx: "Fx_Light_5.png", glowK: 1.95 }');

s = rep(s,
  '{ list: CRYSTAL_FRAMES, turns: 1, turnMs: 620, cross: true, crossMs: 190, still: true, frameDy: "3px", final: `${CRYSTAL}/ezgif-frame_Base.png` }',
  '{ list: CRYSTAL_FRAMES, turns: 1, turnMs: 620, pop: true, popMs: 520, popDelay: 230, growUp: true, growUpK: 0.62, cross: true, crossMs: 190, still: true, settle: true, noBack: true, final: `${CRYSTAL}/ezgif-frame_Base.png` }');

s = rep(s,
  'className={`${styles.winIcon} ${ic.key === "crystal" ? styles.winStatic : ""}`}',
  'className={styles.winIcon}');

fs.writeFileSync(p, s);
console.log("ok");
