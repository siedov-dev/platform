import { useEffect, useLayoutEffect, useRef, useState } from "react";
import styles from "./MonitorScreen.module.css";
import { PARK_ASSET } from "./parkAssetBase";

const UI = PARK_ASSET + "/Ui_Icon_Bg_Up";
const FX = PARK_ASSET + "/Icn_Fx";
const SHINE_DELAY = 260;
const SHINE_HOLD = 4700;

const TONE_CLS = { gold: "shineGold", navy: "shineNavy" };

const SHINE_MAP = {
  hat: { tone: "blue", fade: 700, triple: true, hold: 2640 },
  castle: { tone: "blue", fade: 700, triple: true },
  coin: { tone: "navy", fade: 700, hold: 1090 },
};

const RAY_LEN = [64, 19, 32, 13, 48, 24, 36, 15, 56, 26, 34, 17, 42, 21, 28, 14];
const RAY_W = [3, 1.2, 1.8, 1, 2.4, 1.4, 2, 1, 2.8, 1.5, 1.9, 1.1, 2.2, 1.3, 1.6, 1];
const RAY_D = [2.1, 1.4, 2.6, 1.7, 2.3, 1.3, 2.8, 1.9, 2.4, 1.5, 2.7, 1.6, 2.2, 1.8, 2.5, 1.35];

const SHINE_RAYS = Array.from({ length: 72 }, (_, i) => ({
  a: i * 5,
  len: RAY_LEN[i % RAY_LEN.length] * (i % 9 === 0 ? 1.18 : 1),
  w: RAY_W[i % RAY_W.length],
  d: RAY_D[i % RAY_D.length],
  dl: (i * 0.29) % 3,
}));

const SHINE_SPARKS = [
  { x: 26, y: 62, s: 3, rise: 44, d: 1.9, dl: 0 },
  { x: 72, y: 58, s: 2.4, rise: 52, d: 2.3, dl: 0.4 },
  { x: 82, y: 70, s: 3.2, rise: 38, d: 1.7, dl: 0.85 },
  { x: 18, y: 72, s: 2.6, rise: 46, d: 2.1, dl: 0.22 },
  { x: 50, y: 78, s: 2.8, rise: 56, d: 2.5, dl: 1.1 },
  { x: 63, y: 66, s: 3, rise: 40, d: 1.6, dl: 0.6 },
  { x: 35, y: 74, s: 2.2, rise: 50, d: 2.2, dl: 1.35 },
  { x: 88, y: 56, s: 2.4, rise: 42, d: 1.8, dl: 0.95 },
  { x: 11, y: 60, s: 2.8, rise: 48, d: 2.4, dl: 1.55 },
  { x: 44, y: 68, s: 2.4, rise: 54, d: 2, dl: 0.72 },
];

const CRYSTAL = PARK_ASSET + "/Crystal_Fx";

const CRYSTAL_FRAMES = [];
for (let i = 1; i <= 25; i += 2) CRYSTAL_FRAMES.push(`${CRYSTAL}/ezgif-frame-${String(i).padStart(3, "0")}.png`);

export const REEL_ICONS = [
  { key: "ice", img: "Icn_IceCreame.png", h: 151, bh: 163, dy: 20.5 , wm: 1.1, winDy: 7, sdy: -7.9, wdy: -6.62 },
  { key: "hat", img: "Hat_1.png", dir: PARK_ASSET + "/Hat_Spin", fxImg: "Icn_Hat.png", h: 111.84, bh: 123.7, wh: 110.03, wm: 1.26, noSway: true, sdy: -0.88, lockPos: true, wfix: -1.52 },
  { key: "cocktail", img: "Icn_Cocteil.png", h: 132, bh: 146, dy: 12.4 , wm: 1.1, winDy: 5, sdy: -4.83, wdy: -9.97 },
  { key: "key", img: "Icn_Key.png", h: 108, bh: 117 , wm: 1.27, noSway: true, sdy: 5.04, wdy: 6, wh: 102.6, rs: 0.95, lockPos: true, wfix: -4.18 },
  { key: "mask", img: "Icn_Mask.png", h: 104, bh: 104 , wm: 1.38, sdy: 2.22, wdy: -9.16, up: -1 },
  { key: "castle", img: "castle_slot.webp", dir: PARK_ASSET, h: 103, bh: 103, noFx: true, wm: 0.99, sdy: 1.48, noSway: true, wdy: -11.23, up: 0, rs: 0.95, popMs: 920 },
  { key: "crown", img: "Icn_Crown.png", h: 110, bh: 110, wm: 1.22, cdy: [2.96, 2.96, 1.49], inside: true, wdy: -11, up: -1.5 },
  { key: "coin", img: "Coin_1.png", dir: PARK_ASSET + "/Coin_Spin", h: 83.19, bh: 83.19, wh: 82.2, grow: 1.3, noFx: true, wm: 1.22, noSway: true, sdy: 9.87, wdy: 2.12, up: -2, lockPos: true, wfix: -9.19, iosSdy: -2.96, iosWfix: 1.82 },
  { key: "crystal", img: "ezgif-frame_Base_1.png", dir: CRYSTAL, h: 121, bh: 121, noFx: true, noSway: true, sdy: 8, cdy: [2.96, 0, 2.96], wm: 1.5, wh: 117, lockPos: true, wfix: -11.27, glowFx: "Fx_Light_5.png", glowK: 1.66 },
];

const NI = REEL_ICONS.length;
const PITCH = 1.55;
const idxOf = (k) => Math.max(0, REEL_ICONS.findIndex((i) => i.key === k));

const FLAT = false;

const COLS_ARC = [
  { dy: 8.6, rot: -5.06 },
  { dy: 1.75, rot: -1.57 },
  { dy: 2.78, rot: 1.99 },
];

const COL_BAND = [
  { top: 7.5, bot: 87.4 },
  { top: 0.12, bot: 80.65 },
  { top: 0.9, bot: 82.4 },
];

const LIFT = 2.2;

const COLS = FLAT ? COLS_ARC.map(({ dy }) => ({ dy: dy - LIFT, rot: 0 })) : COLS_ARC;

const ICON_TILT = [-4, -1, 3];
const ICON_DY_PX = [-7.92, -6.92, -6.92];

const PAD_K = (1 - 1 / PITCH) / 2;
const restPct = (idx) => ((idx + PAD_K) / (NI * 2)) * 100;

const PX_TO_BOX = 1.285;
const PX_TO_SLOT = PX_TO_BOX / PITCH;

const IS_IOS =
  typeof navigator !== "undefined" &&
  (/iP(hone|ad|od)/.test(navigator.userAgent) ||
    (navigator.platform === "MacIntel" && (navigator.maxTouchPoints || 0) > 1));

const PLAT_LIFT = {
  ice: 0,
  cocktail: 0,
  hat: -1.5,
  crown: [0.5, -1.5, 0.5],
  mask: 0.5,
  castle: 1.5,
  crystal: 0.94,
};

const IOS_LIFT = { castle: 1.5 };

const liftOf = (ic, c = 1) => {
  if (IS_IOS) return IOS_LIFT[ic.key] ?? 0;
  const v = PLAT_LIFT[ic.key] ?? -3.5;
  return Array.isArray(v) ? v[c] ?? v[1] : v;
};

const STARS = [
  { x: 16, y: 12, s: 15, dx: -3, dy: -5, r: 26, d: 0.74, dl: 0 },
  { x: 62, y: 8, s: 11, dx: 4, dy: -6, r: -20, d: 0.62, dl: 0.11 },
  { x: 84, y: 22, s: 17, dx: 2, dy: -4, r: 32, d: 0.86, dl: 0.23 },
  { x: 34, y: 27, s: 12, dx: -5, dy: -7, r: -28, d: 0.58, dl: 0.34 },
  { x: 8, y: 41, s: 13, dx: 3, dy: -5, r: 18, d: 0.79, dl: 0.06 },
  { x: 71, y: 38, s: 19, dx: -4, dy: -6, r: -34, d: 0.68, dl: 0.19 },
  { x: 45, y: 48, s: 11, dx: 5, dy: -4, r: 24, d: 0.9, dl: 0.29 },
  { x: 90, y: 55, s: 14, dx: -3, dy: -7, r: -22, d: 0.55, dl: 0.42 },
  { x: 22, y: 62, s: 18, dx: 4, dy: -5, r: 30, d: 0.72, dl: 0.15 },
  { x: 57, y: 69, s: 12, dx: -5, dy: -6, r: -26, d: 0.83, dl: 0.27 },
  { x: 80, y: 78, s: 15, dx: 3, dy: -4, r: 20, d: 0.6, dl: 0.38 },
  { x: 12, y: 84, s: 13, dx: -4, dy: -6, r: -32, d: 0.76, dl: 0.09 },
  { x: 47, y: 90, s: 16, dx: 5, dy: -5, r: 28, d: 0.65, dl: 0.21 },
  { x: 68, y: 95, s: 11, dx: -3, dy: -6, r: -18, d: 0.88, dl: 0.33 },
];

const IS_MOBILE = IS_IOS || (typeof navigator !== "undefined" && /Android/i.test(navigator.userAgent));

const STAR_KF = [
  [0, 0, 0, 0.08, 0],
  [0.14, 1, 0.2, 1.3, 1],
  [0.34, 0.4, 0.45, 0.62, 1.8],
  [0.56, 1, 0.68, 1.08, 2.4],
  [0.78, 0.5, 0.86, 0.7, 3],
  [1, 0, 1, 0.08, 3.6],
];
const STAR_PAD = 12;
const STAR_SPRITES = new Map();
let starImgEl = null;

const starImg = () => {
  if (!starImgEl) {
    starImgEl = new Image();
    starImgEl.src = `${FX}/Star_Fx.png`;
  }
  return starImgEl;
};

const starSprite = (size, k) => {
  const key = `${size}@${k}`;
  const hit = STAR_SPRITES.get(key);
  if (hit) return hit;
  const img = starImg();
  const w = size * k;
  const h = (size * img.naturalHeight) / img.naturalWidth * k;
  const pad = STAR_PAD * k;
  const cv = document.createElement("canvas");
  cv.width = Math.ceil(w + pad * 2);
  cv.height = Math.ceil(h + pad * 2);
  const ctx = cv.getContext("2d");
  if (!ctx) return cv;
  ctx.shadowColor = "rgba(255, 150, 240, 0.55)";
  ctx.shadowBlur = 9 * k;
  ctx.drawImage(img, pad, pad, w, h);
  ctx.shadowColor = "rgba(255, 214, 255, 0.9)";
  ctx.shadowBlur = 4 * k;
  ctx.drawImage(img, pad, pad, w, h);
  ctx.shadowColor = "transparent";
  ctx.shadowBlur = 0;
  ctx.drawImage(img, pad, pad, w, h);
  STAR_SPRITES.set(key, cv);
  return cv;
};

const starPose = (p) => {
  let a = STAR_KF[0];
  let b = STAR_KF[STAR_KF.length - 1];
  for (let i = 1; i < STAR_KF.length; i += 1) {
    if (p <= STAR_KF[i][0]) {
      a = STAR_KF[i - 1];
      b = STAR_KF[i];
      break;
    }
  }
  const span = b[0] - a[0] || 1;
  const t = Math.min(1, Math.max(0, (p - a[0]) / span));
  return [a[1] + (b[1] - a[1]) * t, a[2] + (b[2] - a[2]) * t, a[3] + (b[3] - a[3]) * t, a[4] + (b[4] - a[4]) * t];
};

function StarsCanvas({ run, shift }) {
  const ref = useRef(null);

  useEffect(() => {
    const cv = ref.current;
    if (!cv || !run) return undefined;
    const host = cv.parentElement;
    if (!host) return undefined;
    const k = Math.min(9, (window.devicePixelRatio || 1) * 3);
    const w = host.clientWidth || 1;
    const h = host.clientHeight || 1;
    cv.width = Math.max(1, Math.round(w * k));
    cv.height = Math.max(1, Math.round(h * k));
    const ctx = cv.getContext("2d");
    if (!ctx) return undefined;
    const img = starImg();
    const t0 = performance.now();
    let raf = 0;
    const frame = (now) => {
      raf = requestAnimationFrame(frame);
      if (!img.complete || !img.naturalWidth) return;
      ctx.clearRect(0, 0, cv.width, cv.height);
      const el = (now - t0) / 1000;
      for (let i = 0; i < STARS.length; i += 1) {
        const s = STARS[i];
        const e = el - (s.dl + shift);
        if (e < 0) continue;
        const p = (e % s.d) / s.d;
        const [op, kk, sc, kr] = starPose(p);
        if (op <= 0.001) continue;
        const sp = starSprite(s.s, k);
        const cx = ((s.x / 100) * w + s.dx * kk) * k;
        const cy = ((s.y / 100) * h + s.dy * kk) * k;
        ctx.save();
        ctx.globalAlpha = op;
        ctx.translate(cx, cy);
        ctx.rotate((s.r * kr * Math.PI) / 180);
        ctx.scale(sc, sc);
        ctx.drawImage(sp, -sp.width / 2, -sp.height / 2);
        ctx.restore();
      }
    };
    raf = requestAnimationFrame(frame);
    return () => {
      cancelAnimationFrame(raf);
      ctx.clearRect(0, 0, cv.width, cv.height);
    };
  }, [run, shift]);

  return <canvas ref={ref} className={styles.starsCanvas} />;
}

const byKey = (k) => REEL_ICONS.find((i) => i.key === k) || REEL_ICONS[0];


const SCR_D =
  "M -0.60 16.68 L 1.47 15.93 L 5.20 14.58 L 8.93 13.23 L 12.67 11.87 L 16.40 10.52 L 20.13 9.61 L 23.87 8.71 L 27.60 7.81 L 31.33 6.91 L 35.07 6.00 L 38.80 5.10 L 42.53 4.65 L 46.27 3.74 L 50 3.29 L 53.73 3.29 L 57.47 2.84 L 61.20 2.84 L 64.93 2.84 L 68.67 2.84 L 72.40 3.29 L 76.13 3.74 L 79.87 3.74 L 83.60 4.20 L 87.33 5.10 L 91.07 5.55 L 94.80 6.45 L 98.53 7.36 L 100.60 7.86 L 100.60 84.17 L 98.53 83.67 L 94.80 82.76 L 91.07 81.86 L 87.33 81.20 L 83.60 80.51 L 79.87 80.05 L 76.13 79.60 L 72.40 78.70 L 68.67 78.70 L 64.93 78.25 L 61.20 78.25 L 57.47 78.25 L 53.73 78.25 L 50 78.70 L 46.27 78.70 L 42.53 79.60 L 38.80 80.05 L 35.07 80.96 L 31.33 81.41 L 27.60 82.31 L 23.87 83.22 L 20.13 84.12 L 16.40 85.47 L 12.67 86.38 L 8.93 87.73 L 5.20 88.63 L 1.47 89.99 L -0.60 90.74 Z";

const SNAP_KEYS = new Set(["coin", "key", "crystal"]);
const NO_LEAVE = [false, false, false];
const HOLD_KEYS = new Set(["key", "coin", "crystal"]);
const ALL_LEAVE = [true, true, true];

const CR_BASE_G = { top: 0.1167, bot: 0.8833 };
const CR_FRAME_G = { top: 0.0633, bot: 0.9467 };

const TWIST_TURN_MS = 560;
const TWIST_TURNS = 1;

function WinImg({ src, className, style, s0, dy0, dyw, inside, leaving, active, runKey, popMs = 800 }) {
  const ref = useRef(null);
  const pdy = (style && style["--pdy"]) || "0%";

  useLayoutEffect(() => {
    const el = ref.current;
    if (!el || !active || leaving || typeof el.animate !== "function") return undefined;
    const from = { transform: `translateY(${dy0}) translateY(${pdy}) scale(${s0})` };
    const to = { transform: `translateY(${dyw}) scale(1.16)` };
    const anim = el.animate([from, to], {
      duration: popMs,
      easing: "cubic-bezier(0.34, 1.42, 0.64, 1)",
      fill: "both",
    });
    return () => anim.cancel();
  }, [leaving, active, runKey, s0, dy0, dyw, inside, pdy, popMs]);

  return <img ref={ref} className={className} src={src} alt="" draggable={false} style={style} />;
}

const HAT_DIR = PARK_ASSET + "/Hat_Spin";
const HAT_COUNT = 40;
const HAT_MS = 2400;
const HAT_POP_DELAY = 230;
const HAT_POP_MS = 700;
const HAT_START = 700;
const HAT_BACK_MS = 500;
const HAT_GROW = 1.334;
const HAT_LOOPS = 2;
const HAT_TOTAL = HAT_START + HAT_MS + HAT_BACK_MS;
const FRAME_FADE_MS = 620;

const HAT_SHEET = { cols: 5, rows: 4, count: 20, fw: 366, fh: 375, url: "/bonus-park/Hat_Spin/hat_sheet.png" };
const HAT_SHEET_B = { ...HAT_SHEET, url: "/bonus-park/Hat_Spin/hat_sheet2.png" };
const hatSheetOf = (i) => (i < HAT_SHEET.count ? HAT_SHEET : HAT_SHEET_B);
const COIN_SHEET_URL = "/bonus-park/Coin_Spin/coin_sheet.png";
const COIN_SHEET = { cols: 6, rows: 5, count: 30, fw: 191, fh: 190, url: COIN_SHEET_URL, cls: "coinSheet" };

const SHEET_IMGS = {};
const sheetImg = (url) => {
  if (SHEET_IMGS[url]) return SHEET_IMGS[url];
  const im = new Image();
  im.src = url;
  im.decode?.().catch(() => {});
  SHEET_IMGS[url] = im;
  return im;
};

const useCanvasFrames = () => IS_IOS;

const drawSheetFrame = (canvas, sheet, i) => {
  if (!canvas) return false;
  const im = sheetImg(sheet.url);
  if (!im.complete || !im.naturalWidth) {
    im.addEventListener("load", () => drawSheetFrame(canvas, sheet, i), { once: true });
    return false;
  }
  const ctx = canvas.getContext("2d");
  if (!ctx) return false;
  const k = Math.min(sheet.count - 1, Math.max(0, i));
  ctx.clearRect(0, 0, sheet.fw, sheet.fh);
  ctx.drawImage(im, (k % sheet.cols) * sheet.fw, Math.floor(k / sheet.cols) * sheet.fh, sheet.fw, sheet.fh, 0, 0, sheet.fw, sheet.fh);
  return true;
};

const HAT_FRAMES = [];
for (let i = 1; i <= HAT_COUNT; i += 1) HAT_FRAMES.push(`${HAT_DIR}/Hat_${i}.png`);

function HatIcon({ className, style, leaving, active, runKey, hold, sync }) {
  const rootRef = useRef(null);
  const imgRef = useRef(null);
  const rafRef = useRef(0);
  const timerRef = useRef(0);
  const anchorRef = useRef({ key: null, t0: 0 });
  const leavingRef = useRef(leaving);
  leavingRef.current = leaving;
  const [phase, setPhase] = useState("grow");

  useLayoutEffect(() => {
    const el = rootRef.current;
    if (!el || !active || leaving || phase !== "grow") return undefined;
    if (typeof el.animate !== "function") return undefined;
    const s0 = style && style["--s0"] ? style["--s0"] : 1;
    const dy0 = style && style["--dy0"] ? style["--dy0"] : "0%";
    const dyw = style && style["--dyw"] ? style["--dyw"] : "0%";
    const anim = el.animate(
      [
        { transform: `translateY(${dy0}) scale(${s0})` },
        { transform: `translateY(${dyw}) scale(${HAT_GROW})` },
      ],
      {
        duration: sync ? 800 : HAT_POP_MS,
        delay: sync ? 0 : HAT_POP_DELAY,
        easing: sync ? "cubic-bezier(0.34, 1.42, 0.64, 1)" : "cubic-bezier(0.4, 0, 0.25, 1)",
        fill: "both",
      }
    );
    return () => anim.cancel();
  }, [active, leaving, runKey, phase, sync]);

  useEffect(() => {
    let cancelled = false;
    const show = (idx) => {
      const el = imgRef.current;
      if (!el) return;
      if (IS_IOS) {
        const i = Math.min(HAT_COUNT - 1, Math.max(0, idx));
        drawSheetFrame(el, hatSheetOf(i), i % HAT_SHEET.count);
        return;
      }
      el.src = HAT_FRAMES[idx];
    };
    if (IS_IOS) {
      sheetImg(HAT_SHEET.url);
      sheetImg(HAT_SHEET_B.url);
    }
    show(0);
    setPhase("grow");
    if (!active) {
      return () => {
        cancelled = true;
      };
    }
    const runFrames = () => {
      const t0 = performance.now();
      let shown = -1;
      const tick = (now) => {
        if (cancelled) return;
        const t = Math.min(1, (now - t0) / HAT_MS);
        const steps = HAT_COUNT * HAT_LOOPS;
        const idx = Math.min(steps - 1, Math.floor(t * steps)) % HAT_COUNT;
        if (idx !== shown) {
          shown = idx;
          show(idx);
        }
        if (t < 1) {
          rafRef.current = requestAnimationFrame(tick);
          return;
        }
        if (hold) {
          runFrames();
          return;
        }
        if (leavingRef.current) return;
        show(0);
        setPhase("return");
        timerRef.current = setTimeout(() => {
          if (!cancelled) setPhase("parked");
        }, HAT_BACK_MS);
      };
      rafRef.current = requestAnimationFrame(tick);
    };
    if (anchorRef.current.key !== runKey) anchorRef.current = { key: runKey, t0: performance.now() };
    const wait = Math.max(0, anchorRef.current.t0 + HAT_START - performance.now());
    timerRef.current = setTimeout(() => {
      if (cancelled) return;
      runFrames();
    }, wait);
    return () => {
      cancelled = true;
      cancelAnimationFrame(rafRef.current);
      clearTimeout(timerRef.current);
    };
  }, [active, runKey, hold]);

  useEffect(() => {
    if (!leaving || !hold) return undefined;
    cancelAnimationFrame(rafRef.current);
    clearTimeout(timerRef.current);
    const el = imgRef.current;
    if (el) {
      if (IS_IOS) drawSheetFrame(el, HAT_SHEET, 0);
      else el.src = HAT_FRAMES[0];
    }
    setPhase("return");
    const t = setTimeout(() => setPhase("parked"), HAT_BACK_MS);
    return () => clearTimeout(t);
  }, [leaving, hold]);

  const poseCls =
    phase === "return"
      ? styles.hatReturn
      : phase === "parked"
        ? styles.hatParked
        : leaving
          ? styles.hatLeave
          : "";

  return (
    <span
      ref={rootRef}
      className={`${className} ${poseCls} ${styles.hatStack}`}
      style={{ ...style, "--hsg": HAT_GROW }}
    >
      {IS_IOS ? (
        <canvas ref={imgRef} width={HAT_SHEET.fw} height={HAT_SHEET.fh} className={styles.hatFrame} />
      ) : (
        <img ref={imgRef} className={styles.hatFrame} src={HAT_FRAMES[0]} alt="" draggable={false} />
      )}
    </span>
  );
}

function TwistIcon({ cfg, className, style, leaving, active, runKey, glow, glowH, hold }) {
  const imgsRef = useRef([]);
  const rafRef = useRef(0);
  const timerRef = useRef(0);
  const crossRef = useRef(0);
  const rootRef = useRef(null);
  const holdRef = useRef(hold);
  holdRef.current = hold;
  const anchorRef = useRef({ key: null, t0: 0 });
  const backRef = useRef(null);
  const finalRef = useRef(null);
  const spinMsRef = useRef(0);
  const prevHoldRef = useRef(hold);
  const releaseRef = useRef(0);
  const [phase, setPhase] = useState("spin");
  const phaseRef = useRef(phase);
  phaseRef.current = phase;


  useEffect(() => {
    if (!cfg.fitTo || !active) return undefined;
    let raf1 = 0;
    let raf2 = 0;
    const apply = () => {
      const els = imgsRef.current;
      const frame = els[0];
      const ref = cfg.fitTo();
      if (!frame || !ref) return;
      const fr = frame.getBoundingClientRect();
      if (!fr.height || !ref.height) return;
      const gB = cfg.fitG ? cfg.fitG.base : CR_BASE_G;
      const gF = cfg.fitG ? cfg.fitG.frame : CR_FRAME_G;
      const refVis = ref.height * (gB.bot - gB.top);
      const refBot = ref.top + ref.height * gB.bot;
      const cur = parseFloat(frame.style.getPropertyValue("--fsc")) || 1;
      const curDy = parseFloat(frame.style.getPropertyValue("--fy")) || 0;
      const frVis = fr.height * (gF.bot - gF.top);
      const frBot = fr.top + fr.height * gF.bot;
      const scale = cur * (refVis / frVis) * (cfg.fitK || 1);
      const dy = curDy + (refBot - frBot);
      for (let i = 0; i < els.length; i += 1) {
        if (!els[i] || i === finalIdx) continue;
        els[i].style.setProperty("--fsc", scale.toFixed(4));
        els[i].style.setProperty("--fy", `${dy.toFixed(2)}px`);
      }
    };
    raf1 = requestAnimationFrame(() => {
      raf2 = requestAnimationFrame(() => {
        apply();
        requestAnimationFrame(apply);
      });
    });
    return () => {
      cancelAnimationFrame(raf1);
      cancelAnimationFrame(raf2);
    };
  }, [active, runKey]);

  useLayoutEffect(() => {
    const el = rootRef.current;
    if (!el || !cfg.pop || !active || leaving || phase !== "spin") return undefined;
    if (typeof el.animate !== "function") return undefined;
    const s0 = style && style["--s0"] ? style["--s0"] : 1;
    const dy0 = style && style["--dy0"] ? style["--dy0"] : "0%";
    const dyw = style && style["--dyw"] ? style["--dyw"] : "0%";
    const grown = parseFloat(style && style["--sg"]) || 1.16;
    const up = cfg.growUp ? ((grown - parseFloat(s0)) / 2) * 100 * (cfg.growUpK ?? 1) : 0;
    const anim = el.animate(
      [
        { transform: `translateY(${dy0}) scale(${s0})` },
        { transform: `translateY(calc(${dyw} - ${up.toFixed(2)}%)) scale(${grown})` },
      ],
      {
        duration: cfg.popMs || 520,
        delay: cfg.popDelay || 0,
        easing: "cubic-bezier(0.4, 0, 0.25, 1)",
        fill: "both",
      }
    );
    return () => anim.cancel();
  }, [active, leaving, runKey, phase]);

  const sheet = IS_IOS && cfg.sheet ? cfg.sheet : null;
  const frames = [];
  if (cfg.list) {
    frames.push(...cfg.list);
  } else {
    for (let f = 1; f <= cfg.count; f += 1) frames.push(`${cfg.dir}/${cfg.base}_${f}.${cfg.ext || "png"}`);
  }
  if (cfg.final) frames.push(cfg.final);
  const finalIdx = cfg.final ? frames.length - 1 : -1;
  const count = cfg.list ? cfg.list.length : cfg.count;

  useEffect(() => {
    let cancelled = false;
    const els = imgsRef.current;
    const show = (idx) => {
      if (sheet) {
        const el = els[0];
        if (!el) return;
        const i = Math.min(sheet.count - 1, Math.max(0, idx));
        if (useCanvasFrames()) {
          drawSheetFrame(el, sheet, i);
          return;
        }
        el.style.backgroundPosition = `${((i % sheet.cols) * 100) / (sheet.cols - 1)}% ${(Math.floor(i / sheet.cols) * 100) / (sheet.rows - 1)}%`;
        return;
      }
      for (let i = 0; i < els.length; i += 1) {
        if (!els[i]) continue;
        els[i].style.visibility = i === idx ? "visible" : "hidden";
        els[i].style.transition = "";
        els[i].style.opacity = "";
        els[i].style.zIndex = "";
      }
    };

    const cross = (fromIdx, toIdx, ms) => {
      const a = els[fromIdx];
      const b = els[toIdx];
      if (!a || !b) {
        show(toIdx);
        return;
      }
      for (let i = 0; i < els.length; i += 1) {
        if (els[i] && i !== fromIdx && i !== toIdx) els[i].style.visibility = "hidden";
      }
      a.style.transition = "none";
      b.style.transition = "none";
      a.style.visibility = "visible";
      b.style.visibility = "visible";
      a.style.opacity = "1";
      b.style.opacity = "0";
      a.style.zIndex = "1";
      b.style.zIndex = "2";
      const go = () => {
        requestAnimationFrame(() => {
          if (cancelled) return;
          b.style.transition = `opacity ${ms}ms ease-in-out`;
          b.style.opacity = "1";
          if (cfg.crossFade) {
            a.style.transition = `opacity ${ms}ms ease-in-out`;
            a.style.opacity = "0";
          }
        });
      };
      const wait = fromIdx === finalIdx ? cfg.crossDelay || 0 : 0;
      if (wait) {
        clearTimeout(crossRef.current);
        crossRef.current = setTimeout(() => {
          if (!cancelled) go();
        }, wait);
      } else {
        go();
      }
    };

    const runSeq = (seq, dur, onEnd) => {
      const t0 = performance.now();
      let shown = -1;
      const tick = (now) => {
        if (cancelled) return;
        const t = Math.min(1, (now - t0) / dur);
        const idx = Math.min(seq.length - 1, Math.floor(t * seq.length));
        if (idx !== shown) {
          shown = idx;
          show(seq[idx]);
        }
        if (t < 1) rafRef.current = requestAnimationFrame(tick);
        else onEnd?.();
      };
      rafRef.current = requestAnimationFrame(tick);
    };

    const turns = cfg.turns || TWIST_TURNS;
    const from0 = cfg.spinFrom || 0;
    const fwd = [];
    for (let l = 0; l < turns; l += 1) {
      for (let f = from0; f < count; f += 1) fwd.push(f);
      if (cfg.endFirst) for (let f = count - 2; f > 0; f -= 1) fwd.push(f);
    }
    const back = [];
    for (let l = 0; l < turns; l += 1) for (let f = count - 1; f >= 0; f -= 1) back.push(f);
    const spinMs = (cfg.turnMs || TWIST_TURN_MS) * turns * (cfg.endFirst ? 1.7 : 1);

    if (cfg.cross && active && finalIdx >= 0) {
      cross(finalIdx, 0, cfg.crossMs || 420);
    } else {
      show(0);
    }
    if (!active) {
      setPhase("spin");
      return () => {
        cancelled = true;
        cancelAnimationFrame(rafRef.current);
        clearTimeout(timerRef.current);
      };
    }
    const runFwd = () => {
      if (cancelled) return;
      runSeq(fwd, spinMs, onFwdEnd);
    };

    function onFwdEnd() {
      if (cancelled) return;
      if (cfg.endFirst) show(0);
      if (holdRef.current) return;
      if (!cfg.settle) return;
      timerRef.current = setTimeout(() => {
        if (cancelled) return;
        setPhase("return");
        if (cfg.noBack) {
          timerRef.current = setTimeout(() => {
            if (cancelled) return;
            setPhase("parked");
            if (finalIdx >= 0) {
              if (cfg.cross) cross(0, finalIdx, cfg.crossMs || 420);
              else show(finalIdx);
            }
          }, 520);
          return;
        }
        runSeq(back, spinMs, () => {
          if (cancelled) return;
          setPhase("parked");
          if (finalIdx >= 0) show(finalIdx);
        });
      }, cfg.settleGap ?? 360);
    }

    if (anchorRef.current.key !== runKey) anchorRef.current = { key: runKey, t0: performance.now() };
    const since = performance.now() - anchorRef.current.t0;
    if (cfg.still) {
      timerRef.current = setTimeout(onFwdEnd, Math.max(0, (cfg.delay || 0) + 900 - since));
    } else if (cfg.delay) {
      timerRef.current = setTimeout(runFwd, Math.max(0, cfg.delay - since));
    } else {
      runFwd();
    }

    spinMsRef.current = spinMs;

    backRef.current = () => {
      if (cancelled) return;
      runSeq(back, spinMs, () => {
        if (!cancelled && finalIdx >= 0) show(finalIdx);
      });
    };

    finalRef.current = () => {
      if (cancelled || finalIdx < 0) return;
      if (cfg.cross) cross(0, finalIdx, cfg.crossMs || 420);
      else show(finalIdx);
    };

    return () => {
      cancelled = true;
      backRef.current = null;
      finalRef.current = null;
      cancelAnimationFrame(rafRef.current);
      clearTimeout(timerRef.current);
      clearTimeout(crossRef.current);
      clearTimeout(releaseRef.current);
    };
  }, [active, runKey]);

  useEffect(() => {
    if (!hold || !leaving || !active) return undefined;
    if (!cfg.noBack) backRef.current?.();
    return undefined;
  }, [hold, leaving, active, cfg.noBack]);

  useEffect(() => {
    const was = prevHoldRef.current;
    prevHoldRef.current = hold;
    if (!was || hold || !active || leaving || phaseRef.current !== "spin") return undefined;
    setPhase("return");
    if (!cfg.noBack) backRef.current?.();
    clearTimeout(releaseRef.current);
    releaseRef.current = setTimeout(() => {
      setPhase("parked");
      if (cfg.noBack) finalRef.current?.();
    }, cfg.noBack ? 520 : Math.max(500, spinMsRef.current));
    return undefined;
  }, [hold, active, leaving, cfg.noBack]);

  useEffect(() => () => clearTimeout(releaseRef.current), []);

  const poseCls =
    phase === "return"
      ? styles.twistReturn
      : phase === "parked"
        ? (leaving ? styles.twistParkedFade : styles.twistParked)
        : leaving
          ? styles.twistLeave
          : "";

  const glowOn = active && !leaving && phase === "spin";

  return (
    <span ref={rootRef} className={`${className} ${poseCls} ${styles.twistStack}`} style={style}>
      {glow && (
        <img
          className={`${styles.winGlowFx} ${glowOn ? styles.winGlowFxOn : ""}`}
          src={glow}
          alt=""
          draggable={false}
          style={{ height: `${glowH}%` }}
        />
      )}
      {sheet && useCanvasFrames() ? (
        <canvas
          ref={(el) => {
            imgsRef.current[0] = el;
            if (el && !el.dataset.drawn) {
              el.dataset.drawn = "1";
              drawSheetFrame(el, sheet, 0);
            }
          }}
          width={sheet.fw}
          height={sheet.fh}
          className={styles.twistFrame}
        />
      ) : sheet ? (
        <span
          ref={(el) => {
            imgsRef.current[0] = el;
          }}
          className={`${styles.twistFrame} ${styles[sheet.cls]}`}
        />
      ) : null}
      {(sheet ? [] : frames).map((src, i) => (
        <img
          key={src}
          ref={(el) => {
            imgsRef.current[i] = el;
          }}
          className={styles.twistFrame}
          src={src}
          alt=""
          draggable={false}
          style={{
            visibility: i === 0 ? "visible" : "hidden",
            ...(cfg.align && i !== finalIdx ? { "--fx": cfg.align.fx, "--fsc": cfg.align.fs } : null),
            ...(cfg.finalFs && i === finalIdx ? { "--fsc": cfg.finalFs } : null),
            ...(cfg.frameDy && i !== finalIdx ? { "--fy": cfg.frameDy } : null),
          }}
        />
      ))}
    </span>
  );
}

export default function MonitorScreen({
  spinKey = 0,
  stopKey = 0,
  result = ["ice", "hat", "ice"],
  onLanded,
  onShineChange,
  win = [false, false, false],
  iconScale = 0.767,
}) {
  const stopRef = useRef(false);

  useEffect(() => {
    if (stopKey) stopRef.current = true;
  }, [stopKey]);
  const stripRefs = useRef([]);
  const cellRefs = useRef([]);
  const offRef = useRef([0, 0, 0]);
  const doneRef = useRef(onLanded);
  doneRef.current = onLanded;
  const resultRef = useRef(result);
  resultRef.current = result;
  const [running, setRunning] = useState([false, false, false]);
  const [starsRun, setStarsRun] = useState([false, false, false]);
  const [settled, setSettled] = useState([true, true, true]);
  const [shineOn, setShineOn] = useState([false, false, false]);
  const [hatEnd, setHatEnd] = useState(false);

  const shineCbRef = useRef(onShineChange);
  shineCbRef.current = onShineChange;

  useEffect(() => {
    shineCbRef.current?.(shineOn);
  }, [shineOn]);

  const preloadRef = useRef([]);

  useEffect(() => {
    const list = [];
    for (let f = 1; f <= 9; f += 1) list.push(`${PARK_ASSET}/Key_twist/Key_${f}.png`);
    for (let i = 0; i < CRYSTAL_FRAMES.length; i += 1) list.push(CRYSTAL_FRAMES[i]);
    if (!IS_IOS) {
      for (let f = 1; f <= 30; f += 1) list.push(`${PARK_ASSET}/Coin_Spin/Coin_${f}.png`);
      for (let f = 1; f <= HAT_COUNT; f += 1) list.push(`${HAT_DIR}/Hat_${f}.png`);
    }
    const keep = list.map((src) => {
      const img = new Image();
      img.src = src;
      img.decode?.().catch(() => {});
      return img;
    });
    preloadRef.current = keep;
    return () => {
      preloadRef.current = [];
    };
  }, []);

  const [shownWin, setShownWin] = useState([false, false, false]);
  const [shownRes, setShownRes] = useState(result);
  const [showId, setShowId] = useState(0);
  const [leaveCols, setLeaveCols] = useState(NO_LEAVE);
  const leaving = leaveCols[0] || leaveCols[1] || leaveCols[2];
  const [winOver, setWinOver] = useState(true);
  const hatHoldRef = useRef(false);
  const winKey = win.map((v) => (v ? "1" : "0")).join("");
  const shownWinRef = useRef(shownWin);
  shownWinRef.current = shownWin;
  const shownResRef = useRef(shownRes);
  shownResRef.current = shownRes;
  const holdSpinRef = useRef(-1);

  useEffect(() => {
    setShownWin([false, false, false]);
    setLeaveCols(NO_LEAVE);
  }, [spinKey]);

  useEffect(() => {
    setShownRes((prev) => (shownWin.some(Boolean) || leaving ? prev : result));
  }, [result, shownWin, leaving]);

  useEffect(() => {
    const want = winKey.split("").map((v) => v === "1");
    if (want.some(Boolean)) {
      setShownWin(want);
      setShownRes(resultRef.current);
      setShowId((x) => x + 1);
      setLeaveCols(NO_LEAVE);
      setWinOver(false);
      holdSpinRef.current = spinKey;
      return undefined;
    }
    setWinOver(true);
    const keep =
      holdSpinRef.current === spinKey
        ? shownWinRef.current.map((v, c) => v && SNAP_KEYS.has(shownResRef.current[c]))
        : [false, false, false];
    if (keep.some(Boolean)) {
      const drop = shownWinRef.current.map((v, c) => !!v && !keep[c]);
      if (drop.some(Boolean)) {
        setLeaveCols(drop);
        const tk = setTimeout(() => {
          setShownWin(keep);
          setLeaveCols(NO_LEAVE);
        }, 620);
        return () => clearTimeout(tk);
      }
      setShownWin(keep);
      setLeaveCols(NO_LEAVE);
      return undefined;
    }
    setLeaveCols(ALL_LEAVE);
    const t = setTimeout(() => {
      setShownWin([false, false, false]);
      setLeaveCols(NO_LEAVE);
    }, 620);
    return () => clearTimeout(t);
  }, [winKey, spinKey]);

  const hatInWin = shownRes.some((k, c) => k === "hat" && shownWin[c]);
  hatHoldRef.current = hatInWin;
  const allSame = result[0] === result[1] && result[1] === result[2];
  const wildLine =
    shownRes.includes("hat") &&
    !shownRes.every((k) => k === "hat") &&
    !shownRes.some((k) => k === "coin" || k === "crystal") &&
    new Set(shownRes.filter((k) => k !== "hat")).size === 1;
  const allSettled = settled[0] && settled[1] && settled[2];
  const hatKey = `${showId}:${shownRes.join(",")}:${shownWin.map((v) => (v ? 1 : 0)).join("")}`;

  useEffect(() => {
    const triple = shownResRef.current.every((k) => k === "hat");
    if (!triple || !shownWinRef.current.every(Boolean)) {
      setHatEnd(false);
      return undefined;
    }
    setHatEnd(false);
    const t = setTimeout(() => setHatEnd(true), HAT_TOTAL - FRAME_FADE_MS);
    return () => clearTimeout(t);
  }, [hatKey]);

  const frameOn = allSettled && shownWin.some(Boolean) && !hatEnd && !winOver;
  const shineKey = !allSettled
    ? "000"
    : settled
        .map((s, i) => {
          const cfg = SHINE_MAP[result[i]];
          if (!s || !cfg) return "0";
          if (cfg.triple && !allSame) return "0";
          return "1";
        })
        .join("");

  useEffect(() => {
    const want = shineKey.split("").map((v) => v === "1");
    if (!want.some(Boolean)) {
      setShineOn([false, false, false]);
      return undefined;
    }
    const hatHere = result.some((k) => k === "hat");
    const timers = [setTimeout(() => setShineOn(want), SHINE_DELAY)];
    want.forEach((w, i) => {
      const cfg = SHINE_MAP[result[i]];
      if (!w || !cfg || !cfg.hold) return;
      if (HOLD_KEYS.has(result[i])) return;
      timers.push(
        setTimeout(() => {
          setShineOn((prev) => {
            const n = prev.slice();
            n[i] = false;
            return n;
          });
        }, SHINE_DELAY + cfg.hold)
      );
    });
    if (!hatHere) timers.push(setTimeout(() => setShineOn([false, false, false]), SHINE_HOLD));
    return () => timers.forEach(clearTimeout);
  }, [shineKey]);

  useEffect(() => {
    if (!winOver) return;
    setShineOn([false, false, false]);
  }, [winOver]);

  const fadeTimers = useRef([]);

  useEffect(() => () => fadeTimers.current.forEach(clearTimeout), []);

  useEffect(() => {
    const strips = stripRefs.current;
    const host = strips[0] && strips[0].parentElement;
    if (!host) return undefined;
    const place = () => {
      const cellH = host.offsetHeight || 1;
      const h = cellH * PITCH;
      const pad = (h - cellH) / 2;
      strips.forEach((el, i) => {
        if (!el) return;
        const idx = idxOf(resultRef.current[i]);
        offRef.current[i] = idx * h + pad;
        el.style.transform = `translateY(${-restPct(idx).toFixed(4)}%)`;
      });
    };
    place();
    if (typeof ResizeObserver === "undefined") return undefined;
    const ro = new ResizeObserver(() => place());
    ro.observe(host);
    return () => ro.disconnect();
  }, []);

  useEffect(() => {
    if (!spinKey) return undefined;
    const strips = stripRefs.current;
    if (!strips[0]) return undefined;
    const cellH = strips[0].parentElement.offsetHeight || 1;
    const h = cellH * PITCH;
    const pad = (h - cellH) / 2;
    const cycle = h * NI;

    const reels = strips.map((el, i) => ({
      el,
      off: offRef.current[i] || 0,
      speed: h * (15.5 + i * 1.4),
      kick: 180 + i * 40,
      spinUp: 340,
      stopAt: 1050 + i * 460,
      land: null,
      target: idxOf(resultRef.current[i]),
    }));

    stopRef.current = false;
    fadeTimers.current.forEach(clearTimeout);
    fadeTimers.current = [];
    setShineOn([false, false, false]);
    setRunning([true, true, true]);
    setStarsRun([true, true, true]);
    setSettled([false, false, false]);

    let raf = 0;
    const t0 = performance.now();
    let prev = t0;

    const step = (now) => {
      if (finished) return;
      const el = now - t0;
      const dt = Math.min(0.05, (now - prev) / 1000);
      prev = now;
      let alive = false;

      reels.forEach((r, i) => {
        if (!r.land && (el >= r.stopAt || (stopRef.current && el > 260))) {
          const delta = (((r.target * h + pad - r.off) % cycle) + cycle) % cycle;
          r.land = { from: r.off, to: r.off + cycle + delta, start: now, dur: 780 };
          setRunning((s) => {
            if (!s[i]) return s;
            const n = s.slice();
            n[i] = false;
            return n;
          });
          fadeTimers.current.push(
            setTimeout(() => {
              setStarsRun((s) => {
                const n = s.slice();
                n[i] = false;
                return n;
              });
            }, 520)
          );
        }

        if (r.land) {
          const p = Math.min(1, (now - r.land.start) / r.land.dur);
          const e = 1 - Math.pow(1 - p, 3.2);
          const wob = Math.sin(p * Math.PI * 3) * Math.pow(1 - p, 2.2) * 0.16 * h;
          r.off = r.land.from + (r.land.to - r.land.from) * e + wob;
          if (p < 1) alive = true;
          else if (!r.settledDone) {
            r.settledDone = true;
            setSettled((s) => {
              const n = s.slice();
              n[i] = true;
              return n;
            });
          }
        } else if (el < r.kick) {
          r.off -= h * 1.15 * dt;
          alive = true;
        } else {
          const ramp = Math.min(1, (el - r.kick) / r.spinUp);
          r.off += r.speed * ramp * ramp * dt;
          alive = true;
        }

        const y = ((r.off % cycle) + cycle) % cycle;
        offRef.current[i] = y;
        r.el.style.transform = `translateY(${-y}px)`;
      });

      if (!alive) {
        finish();
        return;
      }
      raf = requestAnimationFrame(step);
    };

    let finished = false;
    const finish = () => {
      if (finished) return;
      finished = true;
      cancelAnimationFrame(raf);
      clearTimeout(guard);
      reels.forEach((r, i) => {
        const y = ((r.target * h + pad) % cycle + cycle) % cycle;
        offRef.current[i] = y;
        r.el.style.transform = `translateY(${-y}px)`;
      });
      setStarsRun([false, false, false]);
      setRunning([false, false, false]);
      setSettled([true, true, true]);
      doneRef.current?.();
    };

    const guard = setTimeout(finish, reels[reels.length - 1].stopAt + 780 + 900);

    raf = requestAnimationFrame(step);
    return () => {
      cancelAnimationFrame(raf);
      clearTimeout(guard);
    };
  }, [spinKey]);

  const reelIconRect = (c, re = /ezgif-frame_Base_1/) => {
    const cell = cellRefs.current[c];
    if (!cell) return null;
    const cr = cell.getBoundingClientRect();
    const cy = cr.top + cr.height / 2;
    let best = null;
    let bestD = Infinity;
    cell.querySelectorAll("img").forEach((im) => {
      if (!re.test(im.src)) return;
      if (im.className && /iconBlur/.test(im.className)) return;
      const r = im.getBoundingClientRect();
      if (r.height < 4) return;
      const d = Math.abs(r.top + r.height / 2 - cy);
      if (d < bestD) {
        bestD = d;
        best = r;
      }
    });
    return best;
  };

  const winCell = (k, c) => {
    const ic = byKey(k);
    const active = shownWin[c];
    const leaving = leaveCols[c];
    const wm = ic.wm || 1;
    const hh = ((ic.wh || ic.h) * wm * iconScale).toFixed(1);
    const wdyEff = ic.lockPos
      ? ICON_DY_PX[c] +
        (ic.sdy || 0) +
        (ic.cdy ? ic.cdy[c] || 0 : 0) +
        (IS_IOS ? (ic.iosSdy || 0) + (ic.iosWfix || 0) : 0) +
        liftOf(ic, c) -
        (ic.up || 0) +
        (ic.wfix || 0)
      : (ic.wdy || 0) + (IS_IOS ? 0 : ic.wdyPc || 0);
    const parkComp = ic.lockPos
      ? "0%"
      : ((ICON_DY_PX[c] + (ic.sdy || 0) + liftOf(ic, c) - (ic.up || 0) - wdyEff + (ic.pfix || 0)) * PX_TO_BOX).toFixed(2) + "%";
    const rest = {
      "--pdy": parkComp,
      "--s0": (1 / wm).toFixed(4),
      "--dy0": `${ic.dy ? ((ic.dy * 100) / (ic.h * wm)).toFixed(2) : 0}%`,
      "--dyw": `${ic.winDy || 0}%`,
      "--sg": ic.grow ?? 1.16,
    };
    return (
      <div
        key={`w${c}-${k}`}
        className={`${styles.winCell} ${shownWin[c] ? "" : styles.winCellOff}`}
        style={{ transform: `translateY(${(COLS[c].dy + wdyEff * PX_TO_BOX).toFixed(2)}%) rotate(${COLS[c].rot}deg)` }}
      >
        <span className={`${styles.winSway} ${active && !ic.noSway ? styles.winSwayOn : ""} ${ic.noSway ? styles.winNoSway : ""}`}>
          {ic.key === "coin" || ic.key === "key" || ic.key === "crystal" ? (
            <TwistIcon
              cfg={
                ic.key === "coin"
                  ? {
                      dir: PARK_ASSET + "/Coin_Spin",
                      base: "Coin",
                      count: 30,
                      sheet: COIN_SHEET,
                      turnMs: 696,
                      pop: true,
                      popMs: 520,
                      popDelay: 230,
                      delay: 560,
                      settle: true,
                      noBack: true,
                      settleGap: 0,
                    }
                  : ic.key === "crystal"
                    ? {
                        list: CRYSTAL_FRAMES,
                        turns: 1,
                        turnMs: 620,
                        pop: true,
                        popMs: 520,
                        popDelay: 230,
                        cross: true,
                        crossMs: 330,
                        crossDelay: 230,
                        crossFade: true,
                        delay: 560,
                        settleGap: 0,
                        settle: true,
                        noBack: true,
                        fitTo: () => reelIconRect(c),
                        final: `${CRYSTAL}/ezgif-frame_Base_1.png`,
                      }
                    : {
                        dir: PARK_ASSET + "/Key_twist",
                        base: "Key",
                        count: 9,
                        settle: true,
                        pop: true,
                        popMs: 800,
                        final: `${UI}/Icn_Key.png`,
                        align: { fx: "-6.92%", fs: 0.966 },
                      }
              }
              leaving={leaving}
              active={active}
              runKey={showId}
              hold={HOLD_KEYS.has(ic.key) && !winOver}
              glow={ic.glowFx ? `${ic.dir}/${ic.glowFx}` : undefined}
              glowH={(100 * (ic.glowK || 2.1)).toFixed(0)}
              className={styles.winIcon}
              style={{ height: `${hh}%`, ...rest }}
            />
          ) : ic.key === "hat" ? (
            <HatIcon
              className={styles.winIcon}
              style={{ height: `${hh}%`, ...rest }}
              leaving={leaving}
              active={active}
              runKey={showId}
              hold={wildLine}
              sync={wildLine}
            />
          ) : (
            <WinImg
              className={`${styles.winIcon} ${ic.outline ? styles.winRim : ""} ${leaving ? styles.winLeave : ""}`}
              src={`${ic.dir || UI}/${ic.img}`}
              style={{ height: `${hh}%`, ...rest }}
              s0={rest["--s0"]}
              dy0={rest["--dy0"]}
              dyw={rest["--dyw"]}
              inside={!!ic.inside}
              leaving={leaving}
              active={active}
              runKey={showId}
              popMs={ic.popMs || 800}
            />
          )}
        </span>
      </div>
    );
  };

  return (
    <div className={styles.screen}>
      {COLS.map((col, c) => (
        <div
          key={c}
          ref={(el) => {
            cellRefs.current[c] = el;
          }}
          className={`${styles.cell} ${running[c] ? styles.moving : ""} ${starsRun[c] ? styles.starsRun : ""} ${
            shownWin[c] ? styles.won : ""
          } ${shownWin[c] && leaveCols[c] ? styles.wonBack : ""} ${
            shownWin[c] && leaveCols[c] && SNAP_KEYS.has(shownRes[c]) ? styles.wonSnap : ""
          } ${shownRes[c] === "crystal" && !leaveCols[c] ? styles.wonSoft : ""}`}
          style={{
            transform: `translateY(${col.dy}%) rotate(${col.rot}deg)`,
            clipPath: `inset(${(COL_BAND[c].top - col.dy).toFixed(2)}% 0% ${(100 - COL_BAND[c].bot + col.dy).toFixed(2)}% 0%)`,
            WebkitClipPath: `inset(${(COL_BAND[c].top - col.dy).toFixed(2)}% 0% ${(100 - COL_BAND[c].bot + col.dy).toFixed(2)}% 0%)`,
            "--irot": `${ICON_TILT[c] - col.rot}deg`,
          }}
        >
          <span
            style={{ transition: `opacity ${(SHINE_MAP[result[c]]?.fade || 700) / 1000}s ease` }}
            className={`${styles.shineWrap} ${shineOn[c] ? styles.shineOn : ""} ${
              styles[TONE_CLS[SHINE_MAP[result[c]]?.tone]] || ""
            }`}
          >
            <img
              className={styles.shineBaked}
              src={`${PARK_ASSET}/Icn_Fx/shine_${SHINE_MAP[result[c]]?.tone || "blue"}_a.webp`}
              alt=""
              draggable={false}
            />
            {SHINE_SPARKS.map((s) => (
              <span
                key={`sp${s.x}-${s.y}`}
                className={styles.shineSpark}
                style={{
                  left: `${s.x}%`,
                  top: `${s.y}%`,
                  width: `${s.s}px`,
                  height: `${s.s}px`,
                  animationDuration: `${s.d}s`,
                  animationDelay: `-${s.dl}s`,
                  "--rise": `${-s.rise}px`,
                }}
              />
            ))}
          </span>

          <div
            className={styles.strip}
            style={{ height: `${NI * 200 * PITCH}%` }}
            ref={(el) => {
              stripRefs.current[c] = el;
            }}
          >
            {[...REEL_ICONS, ...REEL_ICONS].map((ic, i) => (
              <div key={`${c}-${i}-${ic.key}`} className={styles.slot} style={{ height: `${100 / (NI * 2)}%` }}>
                <img
                  className={styles.icon}
                  src={`${ic.dir || UI}/${ic.img}`}
                  alt=""
                  draggable={false}
                  style={{
                    height: `${((ic.h * (ic.rs || 1) * iconScale) / PITCH).toFixed(1)}%`,
                    top: `${(50 + (ic.dy || 0) / PITCH + (ICON_DY_PX[c] + (ic.sdy || 0) + (ic.cdy ? ic.cdy[c] || 0 : 0) + (IS_IOS ? ic.iosSdy || 0 : 0) + liftOf(ic, c) - (ic.up || 0)) * PX_TO_SLOT).toFixed(2)}%`,
                  }}
                />
                <img
                  className={`${styles.iconBlur} ${ic.noFx ? styles.softBlur : ""}`}
                  src={ic.noFx ? `${ic.dir || UI}/${ic.img}` : `${FX}/${ic.fxImg || ic.img}`}
                  alt=""
                  draggable={false}
                  style={{
                    height: `${((ic.bh * (ic.rs || 1) * iconScale) / PITCH).toFixed(1)}%`,
                    top: `${(50 + (ic.dy || 0) / PITCH + (ICON_DY_PX[c] + (ic.bdy ?? ic.sdy ?? 0) + (ic.cdy ? ic.cdy[c] || 0 : 0) + (IS_IOS ? ic.iosSdy || 0 : 0) + liftOf(ic, c) - (ic.up || 0)) * PX_TO_SLOT).toFixed(2)}%`,
                  }}
                />
              </div>
            ))}
          </div>


          <div className={styles.stars}>
            {IS_MOBILE ? (
              <StarsCanvas run={starsRun[c]} shift={c * 0.11} />
            ) : null}
            {(IS_MOBILE ? [] : STARS).map((s) => (
              <img
                key={`${c}-${s.x}-${s.y}`}
                className={styles.star}
                src={`${FX}/Star_Fx.png`}
                alt=""
                draggable={false}
                style={{
                  left: `${s.x}%`,
                  top: `${s.y}%`,
                  width: `${s.s}px`,
                  animationDuration: `${s.d}s`,
                  animationDelay: `${(s.dl + c * 0.11).toFixed(2)}s`,
                  "--dx": `${s.dx}px`,
                  "--dy": `${s.dy}px`,
                  "--r": `${s.r}deg`,
                }}
              />
            ))}
          </div>
        </div>
      ))}


      <svg
        className={`${styles.scrFrame} ${frameOn ? styles.scrFrameOn : ""}`}
        viewBox="0 0 100 100"
        preserveAspectRatio="none"
        aria-hidden="true"
      >
        <path className={styles.scrFrameLine} d={SCR_D} />
      </svg>


      <div className={`${styles.winLayer} ${styles.winLayerClip}`}>
        {shownRes.map((k, c) => (byKey(k).inside ? winCell(k, c) : <div key={`wa${c}`} />))}
      </div>
      <div className={styles.winLayer}>
        {shownRes.map((k, c) => (k === "crystal" ? winCell(k, c) : <div key={`wc${c}`} />))}
      </div>
      <div className={styles.winLayer}>
        {shownRes.map((k, c) => (!byKey(k).inside && k !== "crystal" ? winCell(k, c) : <div key={`wb${c}`} />))}
      </div>

      <span className={styles.preHold} aria-hidden="true">
        {REEL_ICONS.map((ic) => (
          <img key={ic.key} src={`${ic.dir || UI}/${ic.img}`} alt="" draggable={false} />
        ))}
      </span>
    </div>
  );
}
