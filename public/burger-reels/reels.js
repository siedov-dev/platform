import * as THREE from "three";

const W = 390;
const H = 800;
const TAU = Math.PI * 2;
const A = "/bonus-park";
const CNT = `${A}/Counter/Counter`;
const WB = `${A}/Wheel_Bg`;

const INGREDIENTS = ["bun_wheel", "salad_wheel", "tomato_wheel", "Chees_Wheel", "cutlet_wheel", "onion_wheel", "sauce_Wheel"];

const BARREL = { img: { w: 466, h: 466 }, x: 35, y: 0, w: 320, win: { x0: 100, x1: 366, y0: 88, y1: 362 }, shadow: { w: 276, h: 266 } };

const ROWS = [
  {
    img: `${CNT}/Counter_bg_Up.png`, iw: 656, ih: 337, x: 25, y: 326, w: 340, win: { y0: 114, y1: 302 }, label: "COST",
    groups: [
      { key: "costX", x0: 123, x1: 202, reels: [8], cell: "Counter_S", arc: 0.49, digit: { h: 0.84, w: 0.66 } },
      { key: "costD", x0: 254, x1: 537, reels: [10, 10, 10], cell: "Counter_L", arc: 0.49, digit: { h: 0.84, w: 0.64 } },
    ],
  },
  {
    img: `${CNT}/Counter_bg.png`, iw: 675, ih: 351, x: 25, y: 516, w: 340, win: { y0: 115, y1: 303 }, label: "REPEAT",
    groups: [
      { key: "repeatX", x0: 122, x1: 218, reels: [8], cell: "Counter_L", arc: 0.49, digit: { h: 0.84, w: 0.64 } },
      { key: "repeatD", x0: 250, x1: 555, reels: [10, 10, 10, 10], cell: "Counter_S", arc: 0.49, digit: { h: 0.84, w: 0.68 } },
    ],
  },
];

const stage = document.getElementById("stage");
const art = document.getElementById("art");
const over = document.getElementById("over");
const ui = document.getElementById("ui");
const canvas = document.getElementById("gl");
const spinBtn = document.getElementById("spin");
const resultEl = document.getElementById("result");

const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true, premultipliedAlpha: true });
renderer.setPixelRatio(Math.min(2.5, window.devicePixelRatio || 1));
renderer.localClippingEnabled = true;
renderer.outputColorSpace = THREE.SRGBColorSpace;
renderer.setClearColor(0x000000, 0);

const scene = new THREE.Scene();
const camera = new THREE.OrthographicCamera(0, W, 0, -H, -2000, 2000);
camera.position.set(0, 0, 600);
camera.lookAt(0, 0, 0);

const loadImg = (src) =>
  new Promise((res, rej) => {
    const im = new Image();
    im.onload = () => res(im);
    im.onerror = () => rej(new Error(src));
    im.src = src;
  });

function addImg(host, src, cls, style) {
  const im = document.createElement("img");
  im.src = src;
  im.className = cls;
  im.draggable = false;
  Object.assign(im.style, style);
  host.appendChild(im);
  return im;
}

function addPlus(style, group) {
  const btn = document.createElement("button");
  btn.type = "button";
  btn.className = "plus";
  btn.textContent = "+1";
  Object.assign(btn.style, style);
  btn.addEventListener("click", () => increment(group.reels, performance.now()));
  ui.appendChild(btn);
  return btn;
}

function inkStops(ref, count) {
  const c = document.createElement("canvas");
  c.width = ref.width;
  c.height = ref.height;
  const ctx = c.getContext("2d");
  ctx.drawImage(ref, 0, 0);
  const d = ctx.getImageData(0, 0, c.width, c.height).data;
  const stops = [];
  for (let k = 0; k < count; k += 1) {
    const y = Math.round((k / (count - 1)) * (c.height - 1));
    let r = 0, g = 0, b = 0, m = 0;
    for (let dy = -2; dy <= 2; dy += 1) {
      const yy = Math.min(c.height - 1, Math.max(0, y + dy));
      for (let x = 0; x < c.width; x += 1) {
        const i = (yy * c.width + x) * 4;
        if (d[i + 3] > 230) {
          r += d[i];
          g += d[i + 1];
          b += d[i + 2];
          m += 1;
        }
      }
    }
    if (m) stops.push([k / (count - 1), `rgb(${Math.round(r / m)}, ${Math.round(g / m)}, ${Math.round(b / m)})`]);
  }
  return stops;
}

function zeroImage(w, h, ref) {
  const big = document.createElement("canvas");
  big.width = w * 2;
  big.height = h * 2;
  const bc = big.getContext("2d");
  bc.font = `900 ${h * 1.3}px "Times New Roman", Georgia, serif`;
  bc.textAlign = "center";
  bc.textBaseline = "middle";
  bc.fillStyle = "#000";
  bc.fillText("0", w, h);
  const data = bc.getImageData(0, 0, big.width, big.height).data;
  let x0 = big.width, x1 = 0, y0 = big.height, y1 = 0;
  for (let y = 0; y < big.height; y += 1) {
    for (let x = 0; x < big.width; x += 1) {
      if (data[(y * big.width + x) * 4 + 3] > 40) {
        if (x < x0) x0 = x;
        if (x > x1) x1 = x;
        if (y < y0) y0 = y;
        if (y > y1) y1 = y;
      }
    }
  }
  const gw = x1 - x0 + 1;
  const gh = y1 - y0 + 1;
  const c = document.createElement("canvas");
  c.width = gw;
  c.height = gh;
  const ctx = c.getContext("2d");
  ctx.font = bc.font;
  ctx.textAlign = "center";
  ctx.textBaseline = "middle";
  const ink = ctx.createLinearGradient(0, 0, 0, gh);
  const stops = ref ? inkStops(ref, 9) : [[0, "#5a2a14"], [0.55, "#3f1808"], [1, "#2a0e04"]];
  stops.forEach(([k, col]) => ink.addColorStop(k, col));
  ctx.fillStyle = ink;
  ctx.fillText("0", w - x0, h - y0);
  return c;
}

function makeTexture(n, arc, len, draw) {
  const k = Math.min(6, 400 / Math.max(arc, len));
  const cellW = Math.round(arc * k);
  const cellH = Math.round(len * k);
  const c = document.createElement("canvas");
  c.width = n * cellW;
  c.height = cellH;
  const ctx = c.getContext("2d");
  for (let i = 0; i < n; i += 1) {
    ctx.save();
    ctx.translate(i * cellW + cellW / 2, cellH / 2);
    ctx.rotate(Math.PI / 2);
    draw(ctx, i, cellH, cellW);
    ctx.restore();
  }
  const tex = new THREE.CanvasTexture(c);
  tex.colorSpace = THREE.SRGBColorSpace;
  tex.anisotropy = renderer.capabilities.getMaxAnisotropy();
  tex.wrapS = THREE.RepeatWrapping;
  return tex;
}

const reels = [];
const GROUPS = [];

function makeReel({ cx, cy, len, r, n, tex, clipTop, clipBottom }) {
  const group = new THREE.Group();
  group.position.set(cx, -cy, 0);
  group.rotation.z = Math.PI / 2;
  const planes = clipTop != null ? [new THREE.Plane(new THREE.Vector3(0, -1, 0), clipTop), new THREE.Plane(new THREE.Vector3(0, 1, 0), -clipBottom)] : [];
  const mat = new THREE.MeshBasicMaterial({ map: tex, transparent: true, clippingPlanes: planes, side: THREE.FrontSide });
  const geo = new THREE.CylinderGeometry(r, r, len, 128, 1, true);
  const mesh = new THREE.Mesh(geo, mat);
  group.add(mesh);
  scene.add(group);
  const reel = { mesh, n, theta: 0, digit: 0, state: "idle", target: 0, t0: 0, from: 0 };
  reel.theta = angleFor(reel, 0);
  reel.target = reel.theta;
  mesh.rotation.y = reel.theta;
  reels.push(reel);
  return reel;
}

function angleFor(reel, digit) {
  return -TAU * ((digit + 0.5) / reel.n);
}

async function build() {
  const [ingr, nums] = await Promise.all([
    Promise.all(INGREDIENTS.map((f) => loadImg(`${WB}/${f}.png`))),
    Promise.all([1, 2, 3, 4, 5, 6, 7, 8, 9].map((d) => loadImg(`${CNT}/Number_${d}.png`))),
  ]);
  nums.unshift(zeroImage(96, 140, nums[7]));

  const b = BARREL;
  const s = b.w / b.img.w;
  addImg(art, `${WB}/Wheel_Bg.png`, "art", { left: `${b.x}px`, top: `${b.y}px`, width: `${b.w}px` });
  const win = { x: b.x + b.win.x0 * s, y: b.y + b.win.y0 * s, w: (b.win.x1 - b.win.x0) * s, h: (b.win.y1 - b.win.y0) * s };
  const r = win.h / 2;
  const len = win.w;
  const n = ingr.length;
  const arc = (TAU * r) / n;
  const tex = makeTexture(n, arc, len, (ctx, i, cw, ch) => {
    const im = ingr[i];
    const flat = im.width / im.height > 1.8;
    const sc = Math.min((cw * (flat ? 0.7 : 0.62)) / im.width, (ch * 0.96) / im.height);
    ctx.drawImage(im, (-im.width * sc) / 2, (-im.height * sc) / 2, im.width * sc, im.height * sc);
  });
  const barrel = makeReel({ cx: win.x + win.w / 2, cy: win.y + win.h / 2, len, r, n, tex, clipTop: -win.y, clipBottom: -(win.y + win.h) });
  GROUPS.push({ label: "BARREL", reels: [barrel] });
  const sh = { w: b.shadow.w * s, h: b.shadow.h * s };
  addImg(over, `${WB}/over_Shadow copy.png`, "over", { left: `${win.x + win.w / 2 - sh.w / 2}px`, top: `${win.y + win.h / 2 - sh.h / 2}px`, width: `${sh.w}px`, height: `${sh.h}px`, opacity: "0.9" });
  addPlus({ left: `${win.x + win.w / 2 - 22}px`, top: `${b.y + b.w - 36}px` }, GROUPS[0]);

  ROWS.forEach((row) => {
    const k = row.w / row.iw;
    addImg(art, row.img, "art", { left: `${row.x}px`, top: `${row.y}px`, width: `${row.w}px` });
    const top = row.y + row.win.y0 * k;
    const h = (row.win.y1 - row.win.y0) * k;
    row.groups.forEach((g) => {
      const gx = row.x + g.x0 * k;
      const gw = (g.x1 - g.x0) * k;
      const colW = gw / g.reels.length;
      const grp = { label: row.label + (g.reels.length > 1 ? "" : "-X"), reels: [] };
      g.reels.forEach((cnt, i) => {
        const cx = gx + colW * (i + 0.5);
        addImg(art, `${CNT}/${g.cell}.png`, `art cell ${g.key}`, { left: `${gx + colW * i}px`, top: `${top}px`, width: `${colW}px`, height: `${h}px` });
        const cellArc = h * g.arc;
        const rr = (cnt * cellArc) / TAU;
        const digitTex = makeTexture(cnt, cellArc, colW, (ctx, d, cw, ch) => {
          const im = nums[d];
          const sc = Math.min((ch * g.digit.h) / im.height, (cw * g.digit.w) / im.width);
          ctx.drawImage(im, (-im.width * sc) / 2, (-im.height * sc) / 2, im.width * sc, im.height * sc);
        });
        grp.reels.push(makeReel({ cx, cy: top + h / 2, len: colW - 2, r: rr, n: cnt, tex: digitTex, clipTop: -top, clipBottom: -(top + h) }));
      });
      GROUPS.push(grp);
      addPlus({ left: `${gx + gw / 2 - 22}px`, top: `${top + h + 10}px` }, grp);
    });
  });

  requestAnimationFrame(tick);
}

const MOVE_MS = 3000;

function easeInOutSine(t) {
  return -(Math.cos(Math.PI * t) - 1) / 2;
}

function moveBy(reel, steps, now) {
  const base = reel.state === "idle" ? reel.theta : reel.target;
  reel.from = reel.theta;
  reel.target = base - (TAU * steps) / reel.n;
  reel.digit = (reel.digit + steps) % reel.n;
  reel.t0 = now;
  reel.state = "move";
}

function increment(group, now) {
  for (let i = group.length - 1; i >= 0; i -= 1) {
    const reel = group[i];
    const wrap = reel.digit === reel.n - 1;
    moveBy(reel, 1, now);
    if (!wrap) break;
  }
}

function startSpin() {
  if (reels.some((r) => r.state !== "idle")) return;
  spinBtn.disabled = true;
  const now = performance.now();
  reels.forEach((reel) => {
    const digit = Math.floor(Math.random() * reel.n);
    let steps = (digit - reel.digit + reel.n) % reel.n;
    if (steps === 0) steps = reel.n;
    moveBy(reel, steps, now);
  });
  resultEl.textContent = "";
}

function readout() {
  return GROUPS.map((g) => g.label + " " + g.reels.map((r) => (g.label === "BARREL" ? INGREDIENTS[r.digit].split("_")[0] : r.digit)).join("")).join("   ");
}

function tick(now) {
  let busy = false;
  reels.forEach((reel) => {
    if (reel.state === "idle") return;
    busy = true;
    const k = Math.min(1, (now - reel.t0) / MOVE_MS);
    reel.theta = reel.from + (reel.target - reel.from) * easeInOutSine(k);
    if (k >= 1) {
      reel.theta = reel.target;
      reel.state = "idle";
    }
    reel.mesh.rotation.y = reel.theta;
  });
  if (!busy) {
    if (spinBtn.disabled) spinBtn.disabled = false;
    resultEl.textContent = readout();
  }
  renderer.render(scene, camera);
  requestAnimationFrame(tick);
}

function fit() {
  const s = Math.min(window.innerWidth / W, window.innerHeight / H);
  stage.style.transform = `translate(${-W / 2 * s}px, ${-H / 2 * s}px) scale(${s})`;
  renderer.setSize(W, H, false);
  canvas.style.width = `${W}px`;
  canvas.style.height = `${H}px`;
  renderer.setPixelRatio(Math.min(3, (window.devicePixelRatio || 1) * s));
}

window.addEventListener("resize", fit);
spinBtn.addEventListener("click", startSpin);
window.burgerSpin = startSpin;
window.burgerPlus = (i) => increment(GROUPS[i].reels, performance.now());
window.burgerRead = readout;
fit();
build().catch((e) => {
  resultEl.textContent = "load error: " + e.message;
});
