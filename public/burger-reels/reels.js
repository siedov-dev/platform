import * as THREE from "three";
import { RoomEnvironment } from "three/addons/environments/RoomEnvironment.js";

const W = 390;
const H = 640;
const TAU = Math.PI * 2;

const LAYOUT = {
  barrel: { x: 195, y: 120, len: 190, r: 52 },
  rows: [
    { label: "BURGER COST", y: 268, groups: [{ x: 48, w: 58, h: 60, reels: [8] }, { x: 118, w: 156, h: 60, reels: [10, 10, 10] }] },
    { label: "REPEAT INGREDIENTS", y: 418, groups: [{ x: 40, w: 58, h: 60, reels: [8] }, { x: 110, w: 184, h: 60, reels: [10, 10, 10, 10] }] },
  ],
};

const stage = document.getElementById("stage");
const ui = document.getElementById("ui");
const canvas = document.getElementById("gl");
const spinBtn = document.getElementById("spin");
const resultEl = document.getElementById("result");

const renderer = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true });
renderer.setPixelRatio(Math.min(2.5, window.devicePixelRatio || 1));
renderer.localClippingEnabled = true;
renderer.outputColorSpace = THREE.SRGBColorSpace;
renderer.toneMapping = THREE.ACESFilmicToneMapping;
renderer.toneMappingExposure = 0.92;

const scene = new THREE.Scene();
const pmrem = new THREE.PMREMGenerator(renderer);
scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;

const camera = new THREE.OrthographicCamera(0, W, 0, -H, -2000, 2000);
camera.position.set(0, 0, 600);
camera.lookAt(0, 0, 0);

scene.add(new THREE.AmbientLight(0xfff1d6, 0.55));
const key = new THREE.DirectionalLight(0xfff4de, 1.4);
key.position.set(-120, 300, 500);
scene.add(key);
const rim = new THREE.DirectionalLight(0xffd27a, 0.5);
rim.position.set(200, -200, 300);
scene.add(rim);

const gold = new THREE.MeshStandardMaterial({ color: 0xe0b24a, metalness: 1, roughness: 0.28 });
const goldDark = new THREE.MeshStandardMaterial({ color: 0x8a5a14, metalness: 1, roughness: 0.4 });
const teal = new THREE.MeshPhysicalMaterial({ color: 0x13b39a, metalness: 0.15, roughness: 0.12, clearcoat: 1, clearcoatRoughness: 0.08, emissive: 0x064e43, emissiveIntensity: 0.5, transmission: 0.1, thickness: 2 });
const ruby = new THREE.MeshPhysicalMaterial({ color: 0xd3142c, metalness: 0.15, roughness: 0.12, clearcoat: 1, emissive: 0x4a0410, emissiveIntensity: 0.5 });

function digitTexture(n, arc, len) {
  const k = Math.min(6, 360 / Math.max(arc, len));
  const cellW = Math.round(arc * k);
  const cellH = Math.round(len * k);
  const c = document.createElement("canvas");
  c.width = n * cellW;
  c.height = cellH;
  const ctx = c.getContext("2d");
  const base = ctx.createLinearGradient(0, 0, 0, cellH);
  base.addColorStop(0, "#efe2c2");
  base.addColorStop(0.5, "#fff8e4");
  base.addColorStop(1, "#e9d8b4");
  ctx.fillStyle = base;
  ctx.fillRect(0, 0, c.width, c.height);
  for (let i = 0; i < n; i += 1) {
    const x0 = i * cellW;
    ctx.strokeStyle = "rgba(120, 85, 30, 0.22)";
    ctx.lineWidth = Math.max(2, cellW * 0.02);
    ctx.beginPath();
    ctx.moveTo(x0 + 0.5, 0);
    ctx.lineTo(x0 + 0.5, cellH);
    ctx.stroke();
    ctx.save();
    ctx.translate(x0 + cellW / 2, cellH / 2);
    ctx.rotate(Math.PI / 2);
    const size = Math.min(cellW * 0.9, cellH * 1.25);
    ctx.font = `900 ${size}px "Arial Black", Arial, sans-serif`;
    ctx.textAlign = "center";
    ctx.textBaseline = "middle";
    ctx.lineJoin = "round";
    ctx.lineWidth = size * 0.1;
    ctx.strokeStyle = "rgba(255,255,255,0.75)";
    ctx.strokeText(String(i), 0, size * 0.04 + size * 0.03);
    ctx.lineWidth = size * 0.06;
    ctx.strokeStyle = "#1a1612";
    ctx.strokeText(String(i), 0, size * 0.04);
    const ink = ctx.createLinearGradient(0, -size / 2, 0, size / 2);
    ink.addColorStop(0, "#5a5048");
    ink.addColorStop(0.45, "#1e1a16");
    ink.addColorStop(1, "#000");
    ctx.fillStyle = ink;
    ctx.fillText(String(i), 0, size * 0.04);
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

function makeReel({ cx, cy, len, r, n, clipTop, clipBottom }) {
  const group = new THREE.Group();
  group.position.set(cx, -cy, 0);
  group.rotation.z = Math.PI / 2;
  const planes = clipTop != null ? [new THREE.Plane(new THREE.Vector3(0, -1, 0), clipTop), new THREE.Plane(new THREE.Vector3(0, 1, 0), -clipBottom)] : [];
  const mat = new THREE.MeshStandardMaterial({ map: digitTexture(n, (TAU * r) / n, len), roughness: 0.42, metalness: 0.05, clippingPlanes: planes });
  const geo = new THREE.CylinderGeometry(r, r, len, 96, 1, true);
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

function addEl(cls, style, text) {
  const el = document.createElement("div");
  el.className = cls;
  Object.assign(el.style, style);
  if (text) el.textContent = text;
  ui.appendChild(el);
  return el;
}

const b = LAYOUT.barrel;
const barrel = makeReel({ cx: b.x, cy: b.y, len: b.len, r: b.r, n: 10 });
GROUPS.push({ label: "BARREL", reels: [barrel] });
{
  const m = barrel.mesh;
  const capGeo = new THREE.CylinderGeometry(b.r * 1.08, b.r * 1.08, 8, 64);
  const bandGeo = new THREE.TorusGeometry(b.r * 1.02, 3.2, 16, 96);
  [-1, 1].forEach((s) => {
    const cap = new THREE.Mesh(capGeo, gold);
    cap.position.y = s * (b.len / 2 + 4);
    m.add(cap);
    const band = new THREE.Mesh(bandGeo, goldDark);
    band.rotation.x = Math.PI / 2;
    band.position.y = s * (b.len / 2 - 6);
    m.add(band);
    const hub = new THREE.Mesh(new THREE.CylinderGeometry(b.r * 0.55, b.r * 0.75, 18, 48), gold);
    hub.position.y = s * (b.len / 2 + 17);
    m.add(hub);
    const crown = new THREE.Group();
    crown.position.y = s * (b.len / 2 + 30);
    const gemCount = 8;
    for (let i = 0; i < gemCount; i += 1) {
      const a = (i / gemCount) * TAU;
      const gem = new THREE.Mesh(new THREE.OctahedronGeometry(8, 0), i % 4 === 0 ? ruby : teal);
      gem.position.set(Math.sin(a) * b.r * 0.6, 0, Math.cos(a) * b.r * 0.6);
      gem.rotation.set(0, a, 0);
      gem.scale.set(1, 0.6, 1.5);
      crown.add(gem);
      const prong = new THREE.Mesh(new THREE.TorusGeometry(9, 1.5, 8, 24), gold);
      prong.position.copy(gem.position);
      prong.rotation.set(0, a, 0);
      crown.add(prong);
    }
    const big = new THREE.Mesh(new THREE.OctahedronGeometry(17, 0), teal);
    big.scale.set(1.1, 0.75, 1.1);
    big.position.y = s * 12;
    crown.add(big);
    const bigRing = new THREE.Mesh(new THREE.TorusGeometry(17, 2.6, 10, 40), gold);
    bigRing.rotation.x = Math.PI / 2;
    bigRing.position.y = s * 6;
    crown.add(bigRing);
    m.add(crown);
  });
}

addEl("bar", { left: `${b.x - b.len / 2 + 6}px`, top: `${b.y - b.r - 10}px`, width: `${b.len - 12}px`, height: "8px" });
addEl("bar", { left: `${b.x - b.len / 2 + 6}px`, top: `${b.y + b.r + 2}px`, width: `${b.len - 12}px`, height: "8px" });
addPlus({ left: `${b.x - 20}px`, top: `${b.y + b.r + 22}px` }, GROUPS[0]);

LAYOUT.rows.forEach((row) => {
  addEl("plate", { left: "95px", top: `${row.y - 52}px`, width: "200px", height: "24px" }, row.label);
  row.groups.forEach((g) => {
    const top = row.y - g.h / 2;
    addEl("frame", { left: `${g.x}px`, top: `${top}px`, width: `${g.w}px`, height: `${g.h}px` });
    const colW = g.w / g.reels.length;
    const grp = { label: row.label.split(" ")[0] + (g.reels.length > 1 ? "" : "-X"), reels: [] };
    g.reels.forEach((n, i) => {
      const cx = g.x + colW * (i + 0.5);
      const cellArc = g.h / 1.12;
      const r = (n * cellArc) / TAU;
      grp.reels.push(makeReel({ cx, cy: row.y, len: colW - 6, r, n, clipTop: -top, clipBottom: -(top + g.h) }));
      if (i > 0) addEl("sep", { left: `${g.x + colW * i - 1.5}px`, top: `${top + 3}px`, height: `${g.h - 6}px` });
    });
    GROUPS.push(grp);
    addPlus({ left: `${g.x + g.w / 2 - 20}px`, top: `${top + g.h + 12}px` }, grp);
    const gl = addEl("gem", { left: `${g.x - 5}px`, top: `${top + g.h / 2 - 5}px` });
    gl.style.setProperty("--g", "#1dd3b0");
    gl.style.setProperty("--gd", "#0b5d4d");
    const gr = addEl("gem", { left: `${g.x + g.w - 6}px`, top: `${top + g.h / 2 - 5}px` });
    gr.style.setProperty("--g", "#ff3b5c");
    gr.style.setProperty("--gd", "#6e0a1c");
  });
});

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
  return GROUPS.map((g) => g.label + " " + g.reels.map((r) => r.digit).join("")).join("   ");
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
requestAnimationFrame(tick);
