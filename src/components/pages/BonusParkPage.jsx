import { useState, useRef, useEffect } from "react";
import { useApp } from "@/context/AppContext";
import TileField from "./TileField";
import Particles from "./Particles";
import Balloons from "./Balloons";
import Flag from "./Flag";
import FerrisSprite from "./FerrisSprite";
import CarouselSprite from "./CarouselSprite";
import HumanSprite from "./HumanSprite";
import Human9Walker from "./Human9Walker";
import CoinFlash from "./CoinFlash";
import { LootPopup, GameIntroPopup } from "./TilePopups";
import { MINI_GAMES } from "./MiniGames";
import styles from "./BonusParkPage.module.css";

const ASSET = "/bonus-park";

const ENERGY_GOAL = 1042;

const OUTCOME_SEQ = ["energy", "money", "game", "energy", "game", "money", "game", "energy", "game"];

const GAPS_KEY = "bonuspark.seam5";
const SEAM_BASE = { w: -5.2, d: -5.6, inset: 0.4 };
const DEFAULT_SEAM = {
  tl: { w: -5.2, d: -5.6, inset: 0.2 },
  tr: { w: -5.2, d: -5.6, inset: 1.1 },
  ml: { w: -5.2, d: -5.6, inset: 0.4 },
  mr: { w: -5.2, d: -5.6, inset: 0 },
  bl: { w: -5.2, d: -5.6, inset: 0.6 },
  br: { w: -5.2, d: -5.6, inset: 0.3 },
};

function loadGaps() {
  try {
    const raw = localStorage.getItem(GAPS_KEY);
    if (raw) {
      const p = JSON.parse(raw);
      const out = {};
      for (const k of Object.keys(DEFAULT_SEAM)) out[k] = { ...SEAM_BASE, ...(p[k] || {}) };
      return out;
    }
  } catch {
    return JSON.parse(JSON.stringify(DEFAULT_SEAM));
  }
  return JSON.parse(JSON.stringify(DEFAULT_SEAM));
}

const FIELDS = [
  { key: "tl", sector: 1, cols: 10, rows: 10, seed: 0, offset: 0, gapX: 0, gapY: 0 },
  { key: "tr", sector: 2, cols: 10, rows: 10, seed: 1, offset: 100, gapX: 0, gapY: 0 },
  { key: "ml", sector: 3, cols: 10, rows: 12, seed: 2, offset: 200, gapX: -0.5, gapY: -0.5 },
  { key: "mr", sector: 4, cols: 10, rows: 12, seed: 3, offset: 320, gapX: -0.5, gapY: -0.5 },
  { key: "bl", sector: 5, cols: 10, rows: 10, seed: 4, offset: 440, gapX: -0.5, gapY: -0.5 },
  { key: "br", sector: 6, cols: 10, rows: 10, seed: 5, offset: 540, gapX: -0.5, gapY: -0.5 },
];

const FLASK_KEY = "bonuspark.flask";
const DEFAULT_FLASK = {
  pos: [
    { x: 20.23, y: 31.11 },
    { x: 81.15, y: 30.9 },
  ],
  w: 7,
};

function loadFlask() {
  try {
    const raw = localStorage.getItem(FLASK_KEY);
    if (raw) return { ...DEFAULT_FLASK, ...JSON.parse(raw) };
  } catch {
    return DEFAULT_FLASK;
  }
  return DEFAULT_FLASK;
}

const COINCFG = { x: 50.4, y: 26, w: 13 };

const HUMAN = { x: 46.6, y: 38.9, w: 6.4, from: 0, to: 559, fps: 15 };

const WALKPATH = [
  { x: -3.4, y: 72.7 },
  { x: 5.6, y: 75.7 },
  { x: 25.5, y: 71.9 },
  { x: 45.9, y: 71.6 },
  { x: 56.4, y: 64.9 },
  { x: 60.1, y: 62.5 },
  { x: 70.4, y: 58.6 },
  { x: 68.5, y: 55.7 },
  { x: 72, y: 53.8 },
  { x: 57.8, y: 49.9 },
  { x: 50.7, y: 51 },
  { x: 44.3, y: 49.8 },
  { x: 31.5, y: 53.7 },
  { x: 35.3, y: 56 },
  { x: 31.2, y: 58.3 },
  { x: 44, y: 63.3 },
  { x: 30.3, y: 64.1 },
  { x: 11.1, y: 67.9 },
  { x: -1.3, y: 65.6 },
];

const GRAPH9_KEY = "bonuspark.walkgraph10";

const DEFAULT_GRAPH9 = {
  nodes: [
    { x: -2.67, y: 62.17 }, { x: 12.21, y: 65.35 }, { x: 6.49, y: 71.16 }, { x: 7.86, y: 73.06 },
    { x: 13.82, y: 71.69 }, { x: 21.6, y: 70 }, { x: 28.93, y: 68.62 }, { x: 33.05, y: 68.2 },
    { x: 47.94, y: 68.52 }, { x: 55.04, y: 66.4 }, { x: 56.87, y: 61.65 }, { x: 59.62, y: 59.85 },
    { x: 63.97, y: 58.26 }, { x: 69.47, y: 55.94 }, { x: 66.26, y: 53.29 }, { x: 70.38, y: 51.28 },
    { x: 66.03, y: 49.49 }, { x: 59.85, y: 48.01 }, { x: 53.89, y: 48.43 }, { x: 45.88, y: 47.79 },
    { x: 33.05, y: 51.28 }, { x: 36.26, y: 52.76 }, { x: 35.8, y: 54.14 }, { x: 32.82, y: 55.83 },
    { x: 38.55, y: 58.26 }, { x: 44.27, y: 60.8 }, { x: 49.31, y: 64.29 }, { x: 57.1, y: 68.62 },
    { x: 65.11, y: 68.2 }, { x: 77.25, y: 68.94 }, { x: 88.93, y: 72.22 }, { x: 94.66, y: 70.1 },
    { x: 91.22, y: 65.35 }, { x: 103.13, y: 61.65 },
  ],
  edges: [
    [0, 1], [1, 2], [2, 3], [3, 4], [4, 5], [5, 6], [6, 7], [7, 8], [8, 9], [9, 10],
    [10, 11], [11, 12], [12, 13], [13, 14], [14, 15], [15, 16], [16, 17], [17, 18], [18, 19], [19, 20],
    [20, 21], [21, 22], [22, 23], [23, 24], [24, 25], [25, 26], [26, 27], [27, 28], [28, 29], [29, 30],
    [30, 31], [31, 32], [32, 33],
  ],
};

const STOP9 = [];

function loadGraph9() {
  try {
    const raw = localStorage.getItem(GRAPH9_KEY);
    if (raw) return JSON.parse(raw);
  } catch {
    return DEFAULT_GRAPH9;
  }
  return DEFAULT_GRAPH9;
}

const FLAGS_KEY = "bonuspark.flags";
const DEFAULT_FLAGS = [
  { id: "f1", img: "Flag_1.png", x: 24.79, y: 14.11, w: 4.4 },
  { id: "f2", img: "Flag_2.png", x: 35.43, y: 11.21, w: 4.4 },
  { id: "fc", img: "Flag_centr.png", x: 52.75, y: 10.26, w: 5 },
  { id: "f3", img: "Flag_2.png", x: 69.14, y: 11.7, w: 4.4 },
  { id: "f4", img: "Flag_1.png", x: 81.48, y: 13.05, w: 4.4 },
  { id: "lb", img: "Flag_Blue_R.png", x: 2.59, y: 44.87, w: 4.5 },
  { id: "lp", img: "Flag_Purple_L.png", x: 99.25, y: 45.4, w: 4.5 },
];

function loadFlags() {
  try {
    const raw = localStorage.getItem(FLAGS_KEY);
    if (raw) return JSON.parse(raw);
  } catch {
    return DEFAULT_FLAGS;
  }
  return DEFAULT_FLAGS;
}

function WindFlag({ img, x, y, w, delay, dur, onPointerDown }) {
  const banner = img.includes("Blue") || img.includes("Purple");
  return (
    <img
      className={banner ? styles.windBanner : styles.windFlag}
      src={`${ASSET}/${img}`}
      alt=""
      draggable={false}
      onPointerDown={onPointerDown}
      style={{
        left: `${x}%`,
        top: `${y}%`,
        width: `${w}%`,
        animationDelay: banner ? "0s" : delay,
        animationDuration: banner ? undefined : dur,
        pointerEvents: onPointerDown ? "auto" : "none",
        cursor: onPointerDown ? "move" : undefined,
      }}
    />
  );
}

function Flask({ x, y, w, fill, onPointerDown }) {
  return (
    <div
      className={styles.flask}
      onPointerDown={onPointerDown}
      style={{
        left: `${x}%`,
        top: `${y}%`,
        width: `${w}%`,
        pointerEvents: onPointerDown ? "auto" : undefined,
        cursor: onPointerDown ? "move" : undefined,
      }}
    >
      <div className={styles.flaskGlass}>
        <img className={styles.flaskBg} src={`${ASSET}/opt/bg_flask.webp`} alt="" />
        <img
          className={styles.flaskFill}
          src={`${ASSET}/opt/inside_flask.webp`}
          alt=""
          style={{ clipPath: `inset(${(1 - fill) * 100}% 0 0 0)` }}
        />
      </div>
      <img className={styles.flaskFront} src={`${ASSET}/opt/front_flask.webp`} alt="" />
    </div>
  );
}

const COVER = {
  tl: "opt/Cover_Field.webp",
  ml: "opt/Cover_Field.webp",
  bl: "opt/Cover_Field.webp",
  tr: "opt/Cover_Field_2.webp",
  mr: "opt/Cover_Field_2.webp",
  br: "opt/Cover_Field_2.webp",
};

const DEFAULT_FIELDS = {
  tl: { tl: { x: 19.43, y: 38.5 }, tr: { x: 43.84, y: 38.57 }, br: { x: 43.13, y: 46.81 }, bl: { x: 16.98, y: 46.75 } },
  tr: { tl: { x: 57.43, y: 38.54 }, tr: { x: 81.94, y: 38.53 }, br: { x: 84.22, y: 46.7 }, bl: { x: 58.02, y: 46.65 } },
  ml: { tl: { x: 16.59, y: 48.33 }, tr: { x: 43.07, y: 48.33 }, br: { x: 42.15, y: 59.09 }, bl: { x: 13.69, y: 59.08 } },
  mr: { tl: { x: 58.3, y: 48.36 }, tr: { x: 84.72, y: 48.35 }, br: { x: 87.71, y: 59.06 }, bl: { x: 59.16, y: 59.1 } },
  bl: { tl: { x: 12.98, y: 60.99 }, tr: { x: 42.1, y: 61.01 }, br: { x: 41.32, y: 70.7 }, bl: { x: 10.02, y: 70.64 } },
  br: { tl: { x: 59.3, y: 61.06 }, tr: { x: 88.23, y: 61.09 }, br: { x: 91.11, y: 70.74 }, bl: { x: 60.09, y: 70.72 } },
};

function shrinkQuad(q, pct) {
  if (!pct) return q;
  const k = 1 - pct / 100;
  const cx = (q.tl.x + q.tr.x + q.br.x + q.bl.x) / 4;
  const cy = (q.tl.y + q.tr.y + q.br.y + q.bl.y) / 4;
  const f = (p) => ({ x: cx + (p.x - cx) * k, y: cy + (p.y - cy) * k });
  return { tl: f(q.tl), tr: f(q.tr), br: f(q.br), bl: f(q.bl) };
}

const FIELDS_KEY = "bonuspark.fields";

function loadFields() {
  try {
    const raw = localStorage.getItem(FIELDS_KEY);
    if (raw) return { ...DEFAULT_FIELDS, ...JSON.parse(raw) };
  } catch {
    return DEFAULT_FIELDS;
  }
  return DEFAULT_FIELDS;
}

const DECOR_KEY = "bonuspark.decor";
const DEFAULT_DECOR = [
  { id: "b1", type: "balloon", x: -7.82, y: 63.51 },
  { id: "b2", type: "balloon", x: 85.28, y: 63.74 },
  { id: "f1", type: "flag", x: 71.59, y: 76.59, rot: 6, flip: false },
  { id: "f2", type: "flag", x: 30.31, y: 76.5, rot: 6, flip: true },
];

function loadDecor() {
  try {
    const raw = localStorage.getItem(DECOR_KEY);
    if (raw) return JSON.parse(raw);
  } catch {
    return DEFAULT_DECOR;
  }
  return DEFAULT_DECOR;
}

const ZONE_KEY = "bonuspark.zones";
const DEFAULT_ZONES = {
  tl: { tl: { x: 18.97, y: 36.78 }, tr: { x: 47.41, y: 36.85 }, br: { x: 43.73, y: 47.29 }, bl: { x: 15.15, y: 47.22 } },
  tr: { tl: { x: 56.49, y: 36.85 }, tr: { x: 84.99, y: 36.85 }, br: { x: 84.99, y: 47.2 }, bl: { x: 56.38, y: 47.11 } },
  ml: { tl: { x: 16.36, y: 46.2 }, tr: { x: 47.15, y: 46.11 }, br: { x: 42.85, y: 59.74 }, bl: { x: 11.68, y: 59.64 } },
  mr: { tl: { x: 57.29, y: 46.15 }, tr: { x: 88, y: 46.22 }, br: { x: 88.41, y: 59.69 }, bl: { x: 57.23, y: 59.7 } },
  bl: { tl: { x: 12.53, y: 59.06 }, tr: { x: 46.33, y: 59.12 }, br: { x: 41.61, y: 71.23 }, bl: { x: 7.89, y: 71.2 } },
  br: { tl: { x: 58.35, y: 59.11 }, tr: { x: 92.01, y: 59.1 }, br: { x: 91.81, y: 71.26 }, bl: { x: 58.17, y: 71.15 } },
};

function loadZones() {
  try {
    const raw = localStorage.getItem(ZONE_KEY);
    if (raw) return { ...DEFAULT_ZONES, ...JSON.parse(raw) };
  } catch {
    return DEFAULT_ZONES;
  }
  return DEFAULT_ZONES;
}

const TILECFG_KEY = "bonuspark.tilecfg";
const DEFAULT_TILECFG = { scaleX: 1.15, scaleY: 1.4 };

function loadTileCfg() {
  try {
    const raw = localStorage.getItem(TILECFG_KEY);
    if (raw) return { ...DEFAULT_TILECFG, ...JSON.parse(raw) };
  } catch {
    return DEFAULT_TILECFG;
  }
  return DEFAULT_TILECFG;
}


const COVERS_KEY = "bonuspark.covers";
const DEFAULT_COVERS = {
  tl: { tl: { x: 17.07, y: 38.46 }, tr: { x: 43.84, y: 38.53 }, br: { x: 43.76, y: 46.81 }, bl: { x: 16.94, y: 46.76 } },
  tr: { tl: { x: 54.45, y: 38.44 }, tr: { x: 81.9, y: 38.51 }, br: { x: 84.99, y: 46.7 }, bl: { x: 57.97, y: 46.65 } },
  ml: { tl: { x: 14.06, y: 48.31 }, tr: { x: 43.14, y: 48.25 }, br: { x: 42.8, y: 59.14 }, bl: { x: 13.69, y: 59.07 } },
  mr: { tl: { x: 55.12, y: 48.24 }, tr: { x: 84.59, y: 48.32 }, br: { x: 88.43, y: 59.09 }, bl: { x: 59.11, y: 59.07 } },
  bl: { tl: { x: 10.19, y: 60.93 }, tr: { x: 42.09, y: 61 }, br: { x: 42.02, y: 70.7 }, bl: { x: 9.86, y: 70.68 } },
  br: { tl: { x: 55.82, y: 60.94 }, tr: { x: 88.22, y: 61.08 }, br: { x: 91.99, y: 70.75 }, bl: { x: 60.03, y: 70.73 } },
};

function loadCovers() {
  try {
    const raw = localStorage.getItem(COVERS_KEY);
    if (raw) return { ...DEFAULT_COVERS, ...JSON.parse(raw) };
  } catch {
    return { ...DEFAULT_COVERS };
  }
  return { ...DEFAULT_COVERS };
}

function Overview({ onPick }) {
  return (
    <div className={styles.overview}>
      <img className={styles.overviewImg} src={`${ASSET}/main_vertical.png`} alt="Bonus location" />
      <button className={`${styles.half} ${styles.top}`} onClick={() => onPick("park")}>
        <span className={styles.halfLabel}>Park</span>
      </button>
      <button className={`${styles.half} ${styles.bottom}`} onClick={() => onPick("grid")}>
        <span className={styles.halfLabel}>Fields</span>
      </button>
    </div>
  );
}

function GridGame() {
  const stageRef = useRef(null);
  const [fields, setFields] = useState(loadFields);
  const [decor, setDecor] = useState(loadDecor);
  const [selectedZone, setSelectedZone] = useState(null);
  const [zoomZone, setZoomZone] = useState(null);
  const [lit, setLit] = useState(null);
  const [destroyed, setDestroyed] = useState(() => new Set());
  const [zones, setZones] = useState(loadZones);
  const [cfg] = useState(loadTileCfg);
  const [covers, setCovers] = useState(loadCovers);
  const [tankFlashes, setTankFlashes] = useState([]);
  const [hookKey, setHookKey] = useState(0);
  const [hookOn, setHookOn] = useState(false);
  const hookTimer = useRef(null);
  const flashId = useRef(0);
  const zoomDestroyed = useRef(0);
  const particlesRef = useRef(null);
  const [flask, setFlask] = useState(loadFlask);
  const [flags, setFlags] = useState(loadFlags);
  const [coinFill, setCoinFill] = useState(0);
  const [coinPulse, setCoinPulse] = useState(0);
  const [tilePopup, setTilePopup] = useState(null);
  const outcomeIdx = useRef(0);
  const gameIdx = useRef(0);
  const [gaps, setGaps] = useState(loadGaps);

  const flaskTarget = (i) => ({ x: flask.pos[i].x, y: flask.pos[i].y + flask.w * 0.11 });

  const triggerHook = () => {
    setHookKey((k) => k + 1);
    setHookOn(true);
    if (hookTimer.current) clearTimeout(hookTimer.current);
    hookTimer.current = setTimeout(() => setHookOn(false), 900);
  };

  const flashAt = (target) => {
    flashId.current += 1;
    const id = flashId.current;
    setTankFlashes((list) => [...list, { id, x: target.x, y: target.y, coin: false }]);
    setTimeout(() => setTankFlashes((list) => list.filter((f) => f.id !== id)), 900);
  };

  const flashCoin = (amount) => {
    setCoinPulse((p) => p + 1);
    setCoinFill((f) => Math.min(1, f + amount));
  };

  const handleTileClick = (zoneKey, index, cellId, rect) => {
    if (zoomZone !== zoneKey) {
      if (selectedZone !== zoneKey) {
        setSelectedZone(zoneKey);
        return;
      }
      zoomDestroyed.current = 0;
      setZoomZone(zoneKey);
      setLit(null);
      return;
    }
    if (lit !== index) {
      setLit(index);
      return;
    }
    const outcome = OUTCOME_SEQ[outcomeIdx.current % OUTCOME_SEQ.length];
    outcomeIdx.current += 1;
    if (rect) {
      const cx = rect.left + rect.width / 2;
      const cy = rect.top + rect.height / 2;
      particlesRef.current?.burst(cx, cy, rect.width);

      if (outcome === "energy") {
        const tank = zoneKey === "tl" ? flaskTarget(0) : zoneKey === "tr" ? flaskTarget(1) : null;
        if (tank && stageRef.current) {
          const inner = stageRef.current.getBoundingClientRect();
          const tx = inner.left + (tank.x / 100) * inner.width;
          const ty = inner.top + (tank.y / 100) * inner.height;
          particlesRef.current?.flyEnergy(cx, cy, tx, ty, () => flashAt(tank));
        }
      }
    }
    if (outcome === "energy") {
      zoomDestroyed.current += 1;
      if (zoneKey !== "tl" && zoneKey !== "tr") {
        triggerHook();
      }
    } else if (outcome === "money") {
      setTilePopup({ type: "money", tile: cellId });
    } else {
      const game = MINI_GAMES[gameIdx.current % MINI_GAMES.length];
      gameIdx.current += 1;
      setTilePopup({ type: "game", game, tile: cellId, stage: "intro" });
    }
    setDestroyed((prev) => {
      const next = new Set(prev);
      next.add(cellId);
      return next;
    });
    setLit(null);
  };

  const flyEnergyOnExit = (zoneKey, isTop, destroyedNow) => {
    const inner = stageRef.current;
    if (!inner || !particlesRef.current) return;
    const rect = inner.getBoundingClientRect();
    const c = fields[zoneKey];
    const fx = rect.left + (((c.tl.x + c.tr.x + c.br.x + c.bl.x) / 4 / 100) * rect.width);
    const fy = rect.top + (((c.tl.y + c.tr.y + c.br.y + c.bl.y) / 4 / 100) * rect.height);

    const send = (target, delay, onArrive) => {
      const tx = rect.left + (target.x / 100) * rect.width;
      const ty = rect.top + (target.y / 100) * rect.height;
      setTimeout(() => {
        particlesRef.current?.flyEnergy(fx, fy, tx, ty, onArrive);
      }, delay);
    };

    const coinArrive = () => flashCoin(destroyedNow * 0.01);
    if (isTop) {
      send(COINCFG, 0, coinArrive);
    } else {
      flask.pos.forEach((_, i) => send(flaskTarget(i), i * 160, () => flashAt(flaskTarget(i))));
      send(COINCFG, 330, coinArrive);
    }
  };

  const exitZoom = () => {
    const zone = zoomZone;
    const isTop = zone === "tl" || zone === "tr";
    const destroyedNow = zoomDestroyed.current;
    setZoomZone(null);
    setLit(null);
    if (zone && destroyedNow > 0) {
      setTimeout(() => flyEnergyOnExit(zone, isTop, destroyedNow), 950);
    }
  };

  const zoomStyle = (() => {
    if (!zoomZone) return undefined;
    const c = fields[zoomZone];
    const xs = [c.tl.x, c.tr.x, c.br.x, c.bl.x];
    const ys = [c.tl.y, c.tr.y, c.br.y, c.bl.y];
    const minX = Math.min(...xs);
    const maxX = Math.max(...xs);
    const minY = Math.min(...ys);
    const maxY = Math.max(...ys);
    const cx = (minX + maxX) / 2;
    const cy = (minY + maxY) / 2;
    const z = Math.min(72 / (maxX - minX), 72 / (maxY - minY), 3.4);
    return { transform: `translate(${z * (50 - cx)}%, ${z * (50 - cy)}%) scale(${z})` };
  })();

  return (
    <div className={styles.grid}>
      {zoomZone && (
        <button className={styles.zoomBack} onClick={exitZoom}>← Fields</button>
      )}
      <Particles ref={particlesRef} />
      {hookOn && <div key={hookKey} className={styles.screenHook} />}
      {tilePopup?.type === "money" && (
        <LootPopup
          tile={tilePopup.tile}
          onClaim={() => {
            flashCoin(0.01);
            setTilePopup(null);
          }}
          onClose={() => setTilePopup(null)}
        />
      )}
      {tilePopup?.type === "game" && tilePopup.stage === "intro" && (
        <GameIntroPopup
          name={tilePopup.game.title}
          tile={tilePopup.tile}
          onStart={() => setTilePopup({ ...tilePopup, stage: "play" })}
          onClose={() => setTilePopup(null)}
        />
      )}
      {tilePopup?.type === "game" && tilePopup.stage === "play" && (
        <GamePopup title={tilePopup.game.title} onClose={() => setTilePopup(null)}>
          <tilePopup.game.Comp />
        </GamePopup>
      )}
      <div className={styles.gridStage} style={zoomStyle}>
        <div className={`${styles.gridInner} ${styles.rasterPinOn}`} ref={stageRef}>
          <img className={styles.gridBg} src={`${ASSET}/Bg_Over/Down_Bg.png`} alt="Castle fields" draggable={false} />
          {flags.map((f, i) => (
            <WindFlag
              key={f.id}
              img={f.img}
              x={f.x}
              y={f.y}
              w={f.w}
              delay={`${-(i * 0.73).toFixed(2)}s`}
              dur={`${(3.4 + (i % 4) * 0.55).toFixed(2)}s`}
            />
          ))}
          {flask.pos.map((p, i) => (
            <Flask key={i} x={p.x} y={p.y} w={flask.w} fill={Math.min(1, destroyed.size / ENERGY_GOAL)} />
          ))}
          <CoinFlash
            fill={coinFill}
            pulseKey={coinPulse}
            style={{ left: `${COINCFG.x}%`, top: `${COINCFG.y}%`, width: `${COINCFG.w}%` }}
          />
          {!zoomZone && (
            <HumanSprite
              className={styles.uparkWheel}
              animate
              from={HUMAN.from}
              to={HUMAN.to}
              fps={HUMAN.fps}
              style={{
                left: `${HUMAN.x}%`,
                top: `${HUMAN.y}%`,
                width: `${HUMAN.w}%`,
                transform: "translate(-50%, -100%)",
              }}
            />
          )}
          {tankFlashes.map((f) => (
            <span
              key={f.id}
              className={f.coin ? styles.coinFlash : styles.tankFlash}
              style={{ left: `${f.x}%`, top: `${f.y}%` }}
            />
          ))}
          {FIELDS.map((f) => {
            const active = zoomZone === f.key;
            const highlighted = !zoomZone && selectedZone === f.key;
            const interactive = !zoomZone || active;
            return (
              <TileField
                key={f.key}
                corners={shrinkQuad(fields[f.key], gaps[f.key].inset)}
                glowCorners={zones[f.key]}
                coverCorners={covers[f.key]}
                cols={f.cols}
                rows={f.rows}
                seed={f.seed}
                offset={f.offset}
                gapX={0}
                gapY={0}
                scaleX={1 - gaps[f.key].w * 0.02}
                scaleY={1 - gaps[f.key].d * 0.06}
                destroyed={destroyed}
                selected={active}
                showGlow={highlighted}
                litTile={active ? lit : null}
                coverSrc={`${ASSET}/${COVER[f.key]}`}
                onTileClick={
                  interactive ? (i, cellId, rect) => handleTileClick(f.key, i, cellId, rect) : undefined
                }
              />
            );
          })}
          {decor.map((d) =>
            d.type === "balloon" ? (
              <Balloons
                key={d.id}
                scale={0.42}
                style={{ left: `${d.x}%`, top: `${d.y}%`, pointerEvents: "none" }}
              />
            ) : (
              <Flag
                key={d.id}
                scale={0.6}
                rot={d.rot}
                flip={d.flip}
                style={{ left: `${d.x}%`, top: `${d.y}%`, pointerEvents: "none" }}
              />
            )
          )}
        </div>
      </div>
    </div>
  );
}

export function GamePopup({ title, onClose, children }) {
  return (
    <div className={styles.popupOverlay} onClick={onClose}>
      <div className={styles.popup} onClick={(e) => e.stopPropagation()}>
        <div className={styles.popupHead}>
          <h3>{title}</h3>
          <button className={styles.popupClose} onClick={onClose}>✕</button>
        </div>
        <div className={styles.popupBody}>{children}</div>
      </div>
    </div>
  );
}

const UPARK_KEY = "bonuspark.upark3";
const DEFAULT_UPARK = {
  wheel: { x: 6.6, y: 38.5, w: 30.5 },
  bush: { x: -2, y: 51.5, w: 24 },
  carousel: { x: 71, y: 43.9, w: 34 },
  crP: { x: 48.3, y: 37.2, w: 7.5 },
  crR: { x: 48.05, y: 42.71, w: 8 },
  crY: { x: 47.8, y: 48.45, w: 8.5 },
  crB: { x: 47.8, y: 54.7, w: 8.5 },
  coin: { x: 46.4, y: 79.5, w: 10 },
  flask1: { x: 25.8, y: 86.7, w: 5.5 },
  flask2: { x: 77.5, y: 86.6, w: 5.5 },
  star1: { x: 59.95, y: 55.69, w: 7.5 },
  star2: { x: 64.81, y: 53.75, w: 7.5 },
  star3: { x: 62.45, y: 54.75, w: 7.5 },
  star4: { x: 36.45, y: 55.98, w: 7.5 },
  star5: { x: 33.25, y: 54.84, w: 7.5 },
  star6: { x: 29.81, y: 53.37, w: 7.5 },
  uf1: { x: 29.46, y: 71.78, w: 3.4 },
  uf2: { x: 38.42, y: 69.37, w: 3.4 },
  ufc: { x: 53.12, y: 68.7, w: 4 },
  uf3: { x: 67.12, y: 69.61, w: 2.9 },
  uf4: { x: 77.84, y: 70.78, w: 3.4 },
};

const UPARK_FLAGS = {
  uf1: "Flag_1.png",
  uf2: "Flag_2.png",
  ufc: "Flag_centr.png",
  uf3: "Flag_2.png",
  uf4: "Flag_1.png",
};

const TILEPHOTO_KEY = "bonuspark.tilesphoto3";
const DEFAULT_TILEPHOTO = {
  a: {
    tl: { x: 55.2, y: 92.8 },
    tr: { x: 78.3, y: 92.8 },
    br: { x: 80.9, y: 99.8 },
    bl: { x: 57.9, y: 99.8 },
  },
  b: {
    tl: { x: 22.8, y: 92.8 },
    tr: { x: 45.8, y: 92.8 },
    br: { x: 45.6, y: 99.9 },
    bl: { x: 22.9, y: 99.9 },
  },
};

function loadTilePhoto() {
  try {
    const raw = localStorage.getItem(TILEPHOTO_KEY);
    if (raw) {
      const p = JSON.parse(raw);
      if (p.a && p.b) return p;
    }
  } catch {
    return { ...DEFAULT_TILEPHOTO };
  }
  return { ...DEFAULT_TILEPHOTO };
}

function homographyMatrix(w, h, p) {
  const adj = (m) => [
    m[4] * m[8] - m[5] * m[7], m[2] * m[7] - m[1] * m[8], m[1] * m[5] - m[2] * m[4],
    m[5] * m[6] - m[3] * m[8], m[0] * m[8] - m[2] * m[6], m[2] * m[3] - m[0] * m[5],
    m[3] * m[7] - m[4] * m[6], m[1] * m[6] - m[0] * m[7], m[0] * m[4] - m[1] * m[3],
  ];
  const mult = (a, b) => {
    const c = [];
    for (let i = 0; i < 3; i += 1) {
      for (let j = 0; j < 3; j += 1) {
        let s = 0;
        for (let k = 0; k < 3; k += 1) s += a[3 * i + k] * b[3 * k + j];
        c[3 * i + j] = s;
      }
    }
    return c;
  };
  const multV = (m, v) => [
    m[0] * v[0] + m[1] * v[1] + m[2] * v[2],
    m[3] * v[0] + m[4] * v[1] + m[5] * v[2],
    m[6] * v[0] + m[7] * v[1] + m[8] * v[2],
  ];
  const basis = (x1, y1, x2, y2, x3, y3, x4, y4) => {
    const m = [x1, x2, x3, y1, y2, y3, 1, 1, 1];
    const v = multV(adj(m), [x4, y4, 1]);
    return mult(m, [v[0], 0, 0, 0, v[1], 0, 0, 0, v[2]]);
  };
  const s = basis(0, 0, w, 0, 0, h, w, h);
  const d = basis(p[0].x, p[0].y, p[1].x, p[1].y, p[3].x, p[3].y, p[2].x, p[2].y);
  const t = mult(d, adj(s));
  const n = t.map((v) => v / t[8]);
  return `matrix3d(${n[0]},${n[3]},0,${n[6]},${n[1]},${n[4]},0,${n[7]},0,0,1,0,${n[2]},${n[5]},0,1)`;
}

function TilesPhoto({ quad, onPointerDown }) {
  const ref = useRef(null);
  const [dim, setDim] = useState({ w: 0, h: 0 });

  useEffect(() => {
    const host = ref.current.parentElement;
    const measure = () => setDim({ w: host.clientWidth, h: host.clientHeight });
    measure();
    const ro = new ResizeObserver(measure);
    ro.observe(host);
    return () => ro.disconnect();
  }, []);

  const pts = [quad.tl, quad.tr, quad.br, quad.bl].map((p) => ({
    x: (p.x / 100) * dim.w,
    y: (p.y / 100) * dim.h,
  }));
  const tf = dim.w > 0 ? homographyMatrix(dim.w, dim.h, pts) : "none";

  return (
    <div
      ref={ref}
      onPointerDown={onPointerDown}
      style={{
        position: "absolute",
        left: 0,
        top: 0,
        width: "100%",
        height: "100%",
        transformOrigin: "0 0",
        transform: tf,
        pointerEvents: "none",
        zIndex: 2,
      }}
    >
      <img
        src={`${ASSET}/Tiles/field_cut.webp`}
        alt=""
        draggable={false}
        style={{
          width: "100%",
          height: "100%",
          display: "block",
          pointerEvents: onPointerDown ? "auto" : "none",
          cursor: onPointerDown ? "move" : undefined,
        }}
      />
    </div>
  );
}

const UPARK_Z = { star1: 8, star2: 7, star3: 6, star4: 8, star5: 7, star6: 6 };

const CRYSTAL_CFG = {
  crP: {
    off: "crystal/crystal_Purple_off.png",
    on: "crystal/crystal_Purple_on.png",
    ray: "crystal/rays crystal_Purple.png",
    onBox: { left: -44.79, top: -43.75, w: 197.92, h: 143.75 },
  },
  crR: {
    off: "crystal/crystal_Red_off.png",
    on: "crystal/crystal_Red_on.png",
    ray: "crystal/rays crystal_Red.png",
    onBox: { left: -52.08, top: -44.06, w: 204.17, h: 144.06 },
  },
  crY: {
    off: "crystal/crystal_Yellow_off.png",
    on: "crystal/crystal_Yellow_on.png",
    ray: "crystal/rays_crystal_Yellow.png",
    onBox: { left: -72.04, top: -52.45, w: 250.54, h: 152.45 },
  },
  crB: {
    off: "crystal/crystal_Blue_off.png",
    on: "crystal/crystal_Blue_on.png",
    ray: "crystal/rays crystal_Blue.png",
    onBox: { left: -55.91, top: -47.18, w: 202.15, h: 148.59 },
  },
};

const STAR_KEYS = ["star1", "star2", "star3", "star4", "star5", "star6"];
const STAR_MIRROR = new Set(["star4", "star5", "star6"]);

function CrystalSpot({ x, y, w, cfg, on }) {
  return (
    <div style={{ position: "absolute", left: `${x}%`, top: `${y}%`, width: `${w}%`, zIndex: 2, pointerEvents: "none" }}>
      <img className={styles.groundShadow} src={`${ASSET}/${cfg.off}`} alt="" draggable={false} />
      <img src={`${ASSET}/${cfg.off}`} alt="" draggable={false} style={{ width: "100%", display: "block" }} />
      <div className={styles.crystalOnWrap} style={{ opacity: on ? 1 : 0 }}>
        <img className={styles.crystalRays} src={`${ASSET}/${cfg.ray}`} alt="" draggable={false} />
        <img
          src={`${ASSET}/${cfg.on}`}
          alt=""
          draggable={false}
          style={{
            position: "absolute",
            left: `${cfg.onBox.left}%`,
            top: `${cfg.onBox.top}%`,
            width: `${cfg.onBox.w}%`,
            height: `${cfg.onBox.h}%`,
            maxWidth: "none",
          }}
        />
      </div>
    </div>
  );
}

function StarSpot({ x, y, w, on, mirror, z }) {
  const flip = mirror ? "scaleX(-1)" : undefined;
  return (
    <div style={{ position: "absolute", left: `${x}%`, top: `${y}%`, width: `${w}%`, zIndex: z, pointerEvents: "none" }}>
      <img className={styles.groundShadow} src={`${ASSET}/Bg_Over/Star_Off.png`} alt="" draggable={false} />
      <img
        src={`${ASSET}/Bg_Over/Star_Off.png`}
        alt=""
        draggable={false}
        style={{ width: "100%", display: "block", transform: flip }}
      />
      <img
        src={`${ASSET}/Bg_Over/Star_On.png`}
        alt=""
        draggable={false}
        className={styles.starOnImg}
        style={{ opacity: on ? 1 : 0, transform: flip }}
      />
    </div>
  );
}

function loadUpark() {
  try {
    const raw = localStorage.getItem(UPARK_KEY);
    if (raw) return { ...DEFAULT_UPARK, ...JSON.parse(raw) };
  } catch {
    return DEFAULT_UPARK;
  }
  return DEFAULT_UPARK;
}

function UpperPark() {
  const [graph] = useState(loadGraph9);
  const [upark] = useState(loadUpark);
  const [tilePhoto] = useState(loadTilePhoto);
  const [lit] = useState({});

  return (
    <div className={styles.upark}>
      <div className={styles.uparkInner}>
        <img className={styles.uparkBg} src={`${ASSET}/Bg_Over/Up_Bg.png`} alt="Park" />
        <FerrisSprite
          className={styles.uparkWheel}
          style={{ left: `${upark.wheel.x}%`, top: `${upark.wheel.y}%`, width: `${upark.wheel.w}%` }}
        />
        <CarouselSprite
          className={styles.uparkWheel}
          style={{ left: `${upark.carousel.x}%`, top: `${upark.carousel.y}%`, width: `${upark.carousel.w}%` }}
        />
        <img
          className={styles.uparkBush}
          src={`${ASSET}/bush_wheel.png`}
          alt=""
          draggable={false}
          style={{ left: `${upark.bush.x}%`, top: `${upark.bush.y}%`, width: `${upark.bush.w}%` }}
        />
        <TilesPhoto quad={tilePhoto.a} />
        <TilesPhoto quad={tilePhoto.b} />
        <img
          className={styles.uparkWheel}
          src={`${ASSET}/Flash_Coin_Lights/Coin_Spielmen_Off.png`}
          alt=""
          draggable={false}
          style={{ left: `${upark.coin.x}%`, top: `${upark.coin.y}%`, width: `${upark.coin.w}%` }}
        />
        {Object.keys(CRYSTAL_CFG).map((k) => (
          <CrystalSpot key={k} x={upark[k].x} y={upark[k].y} w={upark[k].w} cfg={CRYSTAL_CFG[k]} on={!!lit[k]} />
        ))}
        {STAR_KEYS.map((k) => (
          <StarSpot
            key={k}
            x={upark[k].x}
            y={upark[k].y}
            w={upark[k].w}
            on={!!lit[k]}
            mirror={STAR_MIRROR.has(k)}
            z={UPARK_Z[k]}
          />
        ))}
        <Flask x={upark.flask1.x} y={upark.flask1.y} w={upark.flask1.w} fill={0} />
        <Flask x={upark.flask2.x} y={upark.flask2.y} w={upark.flask2.w} fill={0} />
        {Object.keys(UPARK_FLAGS).map((k, i) => (
          <WindFlag
            key={k}
            img={UPARK_FLAGS[k]}
            x={upark[k].x}
            y={upark[k].y}
            w={upark[k].w}
            delay={`${-(i * 0.73).toFixed(2)}s`}
            dur={`${(3.4 + (i % 4) * 0.55).toFixed(2)}s`}
          />
        ))}
        {graph.nodes.length >= 2 && graph.edges.length >= 1 && (
          <Human9Walker nodes={graph.nodes} edges={graph.edges} stops={STOP9} w={6} speed={0.2} />
        )}
      </div>
    </div>
  );
}

export default function BonusParkPage() {
  const { navigate } = useApp();
  const [view, setView] = useState("overview");
  const [anim, setAnim] = useState("out");
  const timers = useRef([]);

  const go = (v) => {
    setAnim("in");
    const t1 = setTimeout(() => {
      setView(v);
      setAnim("out");
      const t2 = setTimeout(() => setAnim(null), 500);
      timers.current.push(t2);
    }, 400);
    timers.current.push(t1);
  };

  useEffect(() => {
    const t = setTimeout(() => setAnim(null), 500);
    timers.current.push(t);
    const list = timers.current;
    return () => list.forEach(clearTimeout);
  }, []);

  const pick = (v) => go(v);

  return (
    <div className={styles.page}>
      {view === "overview" && <Overview onPick={pick} />}
      {view === "grid" && <GridGame onBack={() => go("overview")} />}
      {view === "park" && <UpperPark />}

      <button className={styles.exit} onClick={() => (view === "overview" ? navigate("home") : go("overview"))}>✕</button>

      {anim && <div className={`${styles.cover} ${anim === "in" ? styles.coverIn : styles.coverOut}`} />}
    </div>
  );
}
