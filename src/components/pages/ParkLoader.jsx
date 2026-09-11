import { useEffect, useRef, useState } from "react";
import styles from "./ParkLoader.module.css";

const A = "/bonus-park";
const UI = `${A}/Ui_Icon_Bg_Up`;

const SETS = {
  overview: [`${A}/main_vertical.webp`],
  park: [
    `${A}/Up_Bg_3.webp`,
    `${A}/bush_wheel.png`,
    `${UI}/Btn_SPIN.png`,
    `${UI}/Monitor_Line.png`,
    `${UI}/Icn_IceCreame.png`,
    `${A}/Hat_Spin/Hat_1.webp`,
    `${UI}/Icn_Cocteil.png`,
    `${UI}/Icn_Key.png`,
    `${UI}/Icn_Mask.png`,
    `${UI}/Icn_Crown.png`,
    `${A}/Coin_Spin/Coin_1.webp`,
    `${A}/castle_slot.webp`,
    `${A}/crystal/crystal_Blue_off.png`,
    `${A}/crystal/crystal_Yellow_off.png`,
    `${A}/crystal/crystal_Red_off.png`,
    `${A}/crystal/crystal_Purple_off.png`,
    `${A}/Train/Train_Head.png`,
    `${A}/Train/Train_Body.png`,
    `${A}/Win_Num_T/Win_Number_+.png`,
    ...[1, 2, 3, 5, 10, 15, 25, 50, 100, 200, 500].map((v) => `${A}/Win_Num_T/Win_Number_${v}.png`),
    `${A}/Icn_Fx/shine_blue_a.webp`,
  ],
  grid: [
    `${A}/Down_Bg_3.webp`,
    `${A}/opt/Light_field.webp`,
    `${A}/opt/Light.webp`,
    `${A}/Tiles/field_cut.webp`,
    `${A}/Ui_Exchange_HUD/Icn_Energy_Purple.png`,
    `${A}/Icn_Field/Icn_Card_S.png`,
    `${A}/Icn_Field/Icn_Joker_S.png`,
    `${A}/Icn_Field/Icn_Pyramid_S.png`,
    `${A}/Icn_Field/Icn_Wheel_S.png`,
    ...Array.from({ length: 10 }, (_, i) => `${A}/opt/Tile_${i + 1}.webp`),
  ],
};

const isSafari = () => {
  if (typeof navigator === "undefined") return false;
  const ua = navigator.userAgent;
  return /^((?!chrome|android|crios|fxios).)*safari/i.test(ua) || /iPad|iPhone|iPod/.test(ua);
};

const videoSets = () => {
  const safari = isSafari();
  const ext = safari ? "mov" : "webm";
  const carousel = safari ? "carousel_250_new.mov" : "0000-0250.webm";
  return {
    park: [`${A}/Wheel_video/wheel.${ext}`, `${A}/Carousel_video/${carousel}`],
  };
};

const VCACHE = new Map();

const MIN_MS = 900;
const VIDEO_CAP_MS = 22000;

let vhost = null;
const videoHost = () => {
  if (typeof document === "undefined") return null;
  if (vhost && vhost.isConnected) return vhost;
  vhost = document.createElement("div");
  vhost.setAttribute("aria-hidden", "true");
  vhost.style.cssText =
    "position:fixed;left:0;top:0;width:1px;height:1px;opacity:0.01;pointer-events:none;z-index:-1;overflow:hidden";
  document.body.appendChild(vhost);
  return vhost;
};

export default function ParkLoader({ view, onDone }) {
  const [pct, setPct] = useState(0);
  const keep = useRef([]);

  useEffect(() => {
    const list = SETS[view] || [];
    const vlist = videoSets()[view] || [];
    const total = list.length + vlist.length * 3 || 1;
    let loaded = 0;
    let cancelled = false;
    const t0 = performance.now();

    const bump = () => {
      loaded += 1;
      if (!cancelled) setPct(Math.round((loaded / total) * 100));
    };

    const jobs = list.map((src) => {
      const img = new Image();
      img.src = src;
      keep.current.push(img);
      const done = img.decode ? img.decode().catch(() => {}) : Promise.resolve();
      return done.then(bump, bump);
    });

    const vjobs = vlist.map(
      (src) =>
        new Promise((res) => {
          const done = VCACHE.get(src);
          if (done && done.readyState >= 3) {
            bump();
            bump();
            bump();
            res();
            return;
          }
          const v = done || document.createElement("video");
          if (!done) {
            v.muted = true;
            v.defaultMuted = true;
            v.playsInline = true;
            v.setAttribute("muted", "");
            v.setAttribute("playsinline", "");
            v.setAttribute("webkit-playsinline", "");
            v.preload = "auto";
            v.src = src;
            v.style.cssText = "width:1px;height:1px;opacity:0.01";
            videoHost()?.appendChild(v);
            VCACHE.set(src, v);
          }
          let steps = 0;
          const step = () => {
            steps += 1;
            bump();
            if (steps >= 3) res();
          };
          const check = () => {
            if (v.readyState >= 3 && steps < 3) {
              while (steps < 3) step();
            }
          };
          v.addEventListener("loadedmetadata", () => steps < 1 && step());
          v.addEventListener("loadeddata", () => steps < 2 && step());
          v.addEventListener("canplaythrough", check);
          v.addEventListener("error", () => {
            while (steps < 3) step();
          });
          if (!done) v.load();
          const iv = setInterval(() => {
            check();
            if (steps >= 3) clearInterval(iv);
          }, 200);
          setTimeout(() => {
            clearInterval(iv);
            while (steps < 3) step();
          }, VIDEO_CAP_MS);
        })
    );

    const cap = vlist.length ? VIDEO_CAP_MS + 500 : 4000;
    Promise.race([Promise.all([...jobs, ...vjobs]), new Promise((r) => setTimeout(r, cap))]).then(() => {
      if (cancelled) return;
      setPct(100);
      const wait = Math.max(0, MIN_MS - (performance.now() - t0));
      setTimeout(() => {
        if (!cancelled) onDone?.();
      }, wait);
    });

    return () => {
      cancelled = true;
    };
  }, [view]);

  return (
    <div className={styles.wrap}>
      <div className={styles.pct}>{pct}%</div>
      <div className={styles.bar}>
        <span className={styles.fill} style={{ width: `calc((100% - 6px) * ${pct / 100})` }}>
          <span className={styles.gloss} />
        </span>
      </div>
    </div>
  );
}
