import { useState, useRef, useEffect } from "react";
import { useApp } from "@/context/AppContext";
import TileField from "./TileField";
import Particles from "./Particles";
import Balloons from "./Balloons";
import Flag from "./Flag";
import FerrisSprite from "./FerrisSprite";
import CarouselSprite from "./CarouselSprite";
import HumanSprite from "./HumanSprite";
import CoinFlash from "./CoinFlash";
import styles from "./BonusParkPage.module.css";

const ASSET = "/bonus-park";

const ENERGY_GOAL = 1042;

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
    { x: 20.23, y: 31.32 },
    { x: 80.92, y: 31.22 },
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

const FLAGS_KEY = "bonuspark.flags";
const DEFAULT_FLAGS = [
  { id: "f1", img: "Flag_1.png", x: 24.79, y: 14.11, w: 4.4 },
  { id: "f2", img: "Flag_2.png", x: 35.43, y: 11.21, w: 4.4 },
  { id: "fc", img: "Flag_centr.png", x: 52.67, y: 10.54, w: 5 },
  { id: "f3", img: "Flag_2.png", x: 69.14, y: 11.7, w: 4.4 },
  { id: "f4", img: "Flag_1.png", x: 81.48, y: 13.05, w: 4.4 },
  { id: "lb", img: "Flag_Blue_R.png", x: 2.68, y: 45.57, w: 4.5 },
  { id: "lp", img: "Flag_Purple_L.png", x: 98.79, y: 46.3, w: 4.5 },
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
      style={{
        left: `${x}%`,
        top: `${y}%`,
        width: `${w}%`,
        animationDelay: banner ? "0s" : delay,
        animationDuration: banner ? undefined : dur,
        cursor: onPointerDown ? "grab" : "default",
      }}
      onPointerDown={onPointerDown}
    />
  );
}

function Flask({ x, y, w, fill }) {
  return (
    <div className={styles.flask} style={{ left: `${x}%`, top: `${y}%`, width: `${w}%` }}>
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
  tl: { tl: { x: 19.43, y: 38.79 }, tr: { x: 43.73, y: 38.87 }, br: { x: 43.13, y: 47.1 }, bl: { x: 16.98, y: 47.04 } },
  tr: { tl: { x: 57.43, y: 38.87 }, tr: { x: 81.94, y: 38.86 }, br: { x: 84.22, y: 47.03 }, bl: { x: 58.02, y: 46.98 } },
  ml: { tl: { x: 16.59, y: 48.55 }, tr: { x: 43.07, y: 48.55 }, br: { x: 42.15, y: 59.31 }, bl: { x: 13.69, y: 59.3 } },
  mr: { tl: { x: 58.3, y: 48.58 }, tr: { x: 84.71, y: 48.6 }, br: { x: 87.64, y: 59.31 }, bl: { x: 59.16, y: 59.32 } },
  bl: { tl: { x: 12.98, y: 61.21 }, tr: { x: 42, y: 61.29 }, br: { x: 41.27, y: 70.84 }, bl: { x: 10.02, y: 70.86 } },
  br: { tl: { x: 59.07, y: 61.28 }, tr: { x: 88, y: 61.31 }, br: { x: 90.88, y: 70.96 }, bl: { x: 59.86, y: 70.94 } },
};

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
  { id: "b1", type: "balloon", x: -5.98, y: 63.4 },
  { id: "b2", type: "balloon", x: 84.16, y: 63.41 },
  { id: "f1", type: "flag", x: 72.28, y: 76.26, rot: 6, flip: false },
  { id: "f2", type: "flag", x: 29.39, y: 76.5, rot: 6, flip: true },
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
  tl: { tl: { x: 18.97, y: 37.07 }, tr: { x: 47.41, y: 37.14 }, br: { x: 43.73, y: 47.58 }, bl: { x: 15.15, y: 47.51 } },
  tr: { tl: { x: 56.49, y: 37.18 }, tr: { x: 84.99, y: 37.18 }, br: { x: 84.99, y: 47.53 }, bl: { x: 56.38, y: 47.44 } },
  ml: { tl: { x: 16.36, y: 46.42 }, tr: { x: 47.15, y: 46.33 }, br: { x: 42.85, y: 59.96 }, bl: { x: 11.68, y: 59.86 } },
  mr: { tl: { x: 57.29, y: 46.37 }, tr: { x: 88, y: 46.44 }, br: { x: 88.41, y: 59.91 }, bl: { x: 57.23, y: 59.92 } },
  bl: { tl: { x: 12.53, y: 59.28 }, tr: { x: 46.33, y: 59.34 }, br: { x: 41.61, y: 71.45 }, bl: { x: 7.89, y: 71.42 } },
  br: { tl: { x: 58.12, y: 59.33 }, tr: { x: 91.78, y: 59.32 }, br: { x: 91.58, y: 71.48 }, bl: { x: 57.94, y: 71.37 } },
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
  tl: { tl: { x: 17.07, y: 38.75 }, tr: { x: 43.76, y: 38.82 }, br: { x: 43.76, y: 47.1 }, bl: { x: 16.94, y: 47.05 } },
  tr: { tl: { x: 54.45, y: 38.77 }, tr: { x: 81.9, y: 38.84 }, br: { x: 84.99, y: 47.03 }, bl: { x: 57.97, y: 46.98 } },
  ml: { tl: { x: 14.06, y: 48.53 }, tr: { x: 43.14, y: 48.47 }, br: { x: 42.8, y: 59.36 }, bl: { x: 13.69, y: 59.29 } },
  mr: { tl: { x: 55.12, y: 48.46 }, tr: { x: 84.67, y: 48.61 }, br: { x: 88.43, y: 59.34 }, bl: { x: 59.11, y: 59.29 } },
  bl: { tl: { x: 10.19, y: 61.15 }, tr: { x: 42.09, y: 61.22 }, br: { x: 42.02, y: 70.92 }, bl: { x: 9.86, y: 70.9 } },
  br: { tl: { x: 55.59, y: 61.16 }, tr: { x: 87.99, y: 61.3 }, br: { x: 91.76, y: 70.97 }, bl: { x: 59.8, y: 70.95 } },
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

  const startFlagDrag = (id) => (e) => {
    e.preventDefault();
    e.stopPropagation();
    const rect0 = stageRef.current.getBoundingClientRect();
    const item = flags.find((f) => f.id === id);
    const offX = e.clientX - (rect0.left + (item.x / 100) * rect0.width);
    const offY = e.clientY - (rect0.top + (item.y / 100) * rect0.height);
    const move = (ev) => {
      const rect = stageRef.current.getBoundingClientRect();
      const x = Math.round(((ev.clientX - offX - rect.left) / rect.width) * 100 * 100) / 100;
      const y = Math.round(((ev.clientY - offY - rect.top) / rect.height) * 100 * 100) / 100;
      setFlags((prev) => {
        const next = prev.map((f) => (f.id === id ? { ...f, x, y } : f));
        localStorage.setItem(FLAGS_KEY, JSON.stringify(next));
        return next;
      });
    };
    const up = () => {
      window.removeEventListener("pointermove", move);
      window.removeEventListener("pointerup", up);
    };
    window.addEventListener("pointermove", move);
    window.addEventListener("pointerup", up);
  };

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
    zoomDestroyed.current += 1;
    if (rect) {
      const cx = rect.left + rect.width / 2;
      const cy = rect.top + rect.height / 2;
      particlesRef.current?.burst(cx, cy, rect.width);

      const tank = zoneKey === "tl" ? flaskTarget(0) : zoneKey === "tr" ? flaskTarget(1) : null;
      if (tank && stageRef.current) {
        const inner = stageRef.current.getBoundingClientRect();
        const tx = inner.left + (tank.x / 100) * inner.width;
        const ty = inner.top + (tank.y / 100) * inner.height;
        particlesRef.current?.flyEnergy(cx, cy, tx, ty, () => flashAt(tank));
      }
    }
    if (zoneKey !== "tl" && zoneKey !== "tr") {
      triggerHook();
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

  const startDecorDrag = (id) => (e) => {
    e.preventDefault();
    const rect0 = stageRef.current.getBoundingClientRect();
    const item = decor.find((d) => d.id === id);
    const offX = e.clientX - (rect0.left + (item.x / 100) * rect0.width);
    const offY = e.clientY - (rect0.top + (item.y / 100) * rect0.height);
    const move = (ev) => {
      const rect = stageRef.current.getBoundingClientRect();
      const x = ((ev.clientX - offX - rect.left) / rect.width) * 100;
      const y = ((ev.clientY - offY - rect.top) / rect.height) * 100;
      setDecor((prev) => {
        const next = prev.map((d) => (d.id === id ? { ...d, x, y } : d));
        localStorage.setItem(DECOR_KEY, JSON.stringify(next));
        return next;
      });
    };
    const up = () => {
      window.removeEventListener("pointermove", move);
      window.removeEventListener("pointerup", up);
    };
    window.addEventListener("pointermove", move);
    window.addEventListener("pointerup", up);
  };

  return (
    <div className={styles.grid}>
      {zoomZone && (
        <button className={styles.zoomBack} onClick={exitZoom}>← Fields</button>
      )}
      <Particles ref={particlesRef} />
      {hookOn && <div key={hookKey} className={styles.screenHook} />}
      <div className={styles.gridStage}>
        <div className={styles.gridInner} ref={stageRef} style={zoomStyle}>
          <img className={styles.gridBg} src={`${ASSET}/opt/down_bg.webp`} alt="Castle fields" draggable={false} />
          {flags.map((f, i) => (
            <WindFlag
              key={f.id}
              img={f.img}
              x={f.x}
              y={f.y}
              w={f.w}
              delay={`${-(i * 0.73).toFixed(2)}s`}
              dur={`${(3.4 + (i % 4) * 0.55).toFixed(2)}s`}
              onPointerDown={startFlagDrag(f.id)}
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
                corners={fields[f.key]}
                glowCorners={zones[f.key]}
                coverCorners={covers[f.key]}
                cols={f.cols}
                rows={f.rows}
                seed={f.seed}
                offset={f.offset}
                gapX={f.gapX}
                gapY={f.gapY}
                scaleX={cfg.scaleX}
                scaleY={cfg.scaleY}
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
                style={{ left: `${d.x}%`, top: `${d.y}%`, pointerEvents: "auto", cursor: "grab" }}
                onPointerDown={startDecorDrag(d.id)}
              />
            ) : (
              <Flag
                key={d.id}
                scale={0.6}
                rot={d.rot}
                flip={d.flip}
                style={{ left: `${d.x}%`, top: `${d.y}%`, pointerEvents: "auto", cursor: "grab" }}
                onPointerDown={startDecorDrag(d.id)}
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

const UPARK = {
  wheel: { x: 2.9, y: 40, w: 32.5 },
  bush: { x: -6, y: 53.9, w: 26 },
  carousel: { x: 69.2, y: 45.7, w: 38 },
};

function UpperPark() {
  return (
    <div className={styles.upark}>
      <div className={styles.uparkInner}>
        <img className={styles.uparkBg} src={`${ASSET}/upper_bg.jpg`} alt="Park" />
        <FerrisSprite
          className={styles.uparkWheel}
          style={{ left: `${UPARK.wheel.x}%`, top: `${UPARK.wheel.y}%`, width: `${UPARK.wheel.w}%` }}
        />
        <CarouselSprite
          className={styles.uparkWheel}
          style={{ left: `${UPARK.carousel.x}%`, top: `${UPARK.carousel.y}%`, width: `${UPARK.carousel.w}%` }}
        />
        <img
          className={styles.uparkBush}
          src={`${ASSET}/bush_wheel.png`}
          alt=""
          draggable={false}
          style={{ left: `${UPARK.bush.x}%`, top: `${UPARK.bush.y}%`, width: `${UPARK.bush.w}%` }}
        />
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
