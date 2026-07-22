import { useState, useRef, useEffect } from "react";
import { useApp } from "@/context/AppContext";
import TileField from "./TileField";
import Particles from "./Particles";
import Balloons from "./Balloons";
import Flag from "./Flag";
import FerrisSprite from "./FerrisSprite";
import CarouselSprite from "./CarouselSprite";
import styles from "./BonusParkPage.module.css";

const ASSET = "/bonus-park";

const ENERGY_GOAL = 1042;

const FIELDS = [
  { key: "tl", sector: 1, cols: 10, rows: 10, seed: 0, offset: 0, gapX: 0, gapY: 0 },
  { key: "tr", sector: 2, cols: 10, rows: 10, seed: 1, offset: 100, gapX: 0, gapY: 0 },
  { key: "ml", sector: 3, cols: 10, rows: 12, seed: 2, offset: 200, gapX: 0, gapY: 0 },
  { key: "mr", sector: 4, cols: 10, rows: 12, seed: 3, offset: 320, gapX: 0, gapY: 0 },
  { key: "bl", sector: 5, cols: 10, rows: 10, seed: 4, offset: 440, gapX: -0.5, gapY: -0.5 },
  { key: "br", sector: 6, cols: 10, rows: 10, seed: 5, offset: 540, gapX: -0.5, gapY: -0.5 },
];

const FLASK_KEY = "bonuspark.flask";
const DEFAULT_FLASK = {
  pos: [
    { x: 20.4, y: 31.62 },
    { x: 79.46, y: 31.55 },
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

const COIN = { id: "coin", x: 50, y: 26.1 };

const FLAGS_KEY = "bonuspark.flags";
const DEFAULT_FLAGS = [
  { id: "f1", img: "Flag_1.png", x: 24.49, y: 15.01, w: 4.4 },
  { id: "f2", img: "Flag_2.png", x: 35.05, y: 12.07, w: 4.4 },
  { id: "fc", img: "Flag_centr.png", x: 51.84, y: 11.66, w: 5 },
  { id: "f3", img: "Flag_2.png", x: 67.69, y: 12.63, w: 4.4 },
  { id: "f4", img: "Flag_1.png", x: 79.57, y: 14.16, w: 4.4 },
  { id: "lb", img: "Flag_Blue_R.png", x: 3.87, y: 45.58, w: 4.5 },
  { id: "lp", img: "Flag_Purple_L.png", x: 97.16, y: 46.53, w: 4.5 },
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
        <img className={styles.flaskBg} src={`${ASSET}/bg_flask.png`} alt="" />
        <img
          className={styles.flaskFill}
          src={`${ASSET}/inside_flask.png`}
          alt=""
          style={{ clipPath: `inset(${(1 - fill) * 100}% 0 0 0)` }}
        />
      </div>
      <img className={styles.flaskFront} src={`${ASSET}/front_flask.png`} alt="" />
    </div>
  );
}

const COVER = {
  tl: "Cover_Field.png",
  ml: "Cover_Field.png",
  bl: "Cover_Field.png",
  tr: "Cover_Field_2.png",
  mr: "Cover_Field_2.png",
  br: "Cover_Field_2.png",
};

const DEFAULT_FIELDS = {
  tl: { tl: { x: 19.5, y: 39.04 }, tr: { x: 43.48, y: 39.09 }, br: { x: 42.75, y: 47.03 }, bl: { x: 17.22, y: 47.03 } },
  tr: { tl: { x: 56.63, y: 39.04 }, tr: { x: 80.38, y: 39.04 }, br: { x: 82.78, y: 46.97 }, bl: { x: 57.19, y: 46.95 } },
  ml: { tl: { x: 16.94, y: 48.54 }, tr: { x: 42.53, y: 48.57 }, br: { x: 41.69, y: 58.98 }, bl: { x: 13.87, y: 58.95 } },
  mr: { tl: { x: 57.36, y: 48.49 }, tr: { x: 83.06, y: 48.52 }, br: { x: 85.85, y: 58.92 }, bl: { x: 58.2, y: 58.98 } },
  bl: { tl: { x: 13.5, y: 60.76 }, tr: { x: 41.69, y: 60.92 }, br: { x: 40.77, y: 70.17 }, bl: { x: 10.51, y: 70.21 } },
  br: { tl: { x: 58, y: 60.83 }, tr: { x: 86.21, y: 60.88 }, br: { x: 89.29, y: 70.26 }, bl: { x: 59.03, y: 70.31 } },
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
  { id: "b1", type: "balloon", x: 4.05, y: 65.07 },
  { id: "b2", type: "balloon", x: 78.02, y: 65.54 },
  { id: "f1", type: "flag", x: 68.15, y: 78.23, rot: 6, flip: false },
  { id: "f2", type: "flag", x: 33.71, y: 78.41, rot: 6, flip: true },
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
  tl: { tl: { x: 19.28, y: 37.52 }, tr: { x: 46.77, y: 37.55 }, br: { x: 43.42, y: 47.49 }, bl: { x: 15.38, y: 47.46 } },
  tr: { tl: { x: 55.78, y: 37.56 }, tr: { x: 83.52, y: 37.36 }, br: { x: 83.38, y: 47.34 }, bl: { x: 55.58, y: 47.41 } },
  ml: { tl: { x: 16.72, y: 46.46 }, tr: { x: 46.43, y: 46.43 }, br: { x: 42.36, y: 59.57 }, bl: { x: 12.14, y: 59.49 } },
  mr: { tl: { x: 56.47, y: 46.51 }, tr: { x: 86.41, y: 46.25 }, br: { x: 86.63, y: 59.54 }, bl: { x: 56.75, y: 59.49 } },
  bl: { tl: { x: 13.07, y: 58.84 }, tr: { x: 46.31, y: 58.79 }, br: { x: 41.28, y: 70.83 }, bl: { x: 8.76, y: 70.59 } },
  br: { tl: { x: 56.87, y: 58.93 }, tr: { x: 89.8, y: 58.84 }, br: { x: 89.91, y: 70.73 }, bl: { x: 56.87, y: 70.78 } },
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
  tl: { tl: { x: 17.16, y: 39.01 }, tr: { x: 43.53, y: 39.01 }, br: { x: 43.37, y: 47.08 }, bl: { x: 17.16, y: 47.08 } },
  tr: { tl: { x: 53.74, y: 38.99 }, tr: { x: 80.44, y: 39.01 }, br: { x: 83.45, y: 47 }, bl: { x: 57.19, y: 47 } },
  ml: { tl: { x: 14.43, y: 48.47 }, tr: { x: 42.53, y: 48.47 }, br: { x: 42.42, y: 59.03 }, bl: { x: 13.76, y: 58.98 } },
  mr: { tl: { x: 54.24, y: 48.36 }, tr: { x: 83.01, y: 48.44 }, br: { x: 86.68, y: 58.95 }, bl: { x: 58.14, y: 59 } },
  bl: { tl: { x: 10.69, y: 60.74 }, tr: { x: 41.78, y: 60.83 }, br: { x: 41.59, y: 70.21 }, bl: { x: 10.4, y: 70.31 } },
  br: { tl: { x: 54.62, y: 60.68 }, tr: { x: 86.33, y: 60.84 }, br: { x: 90.11, y: 70.31 }, bl: { x: 58.72, y: 70.31 } },
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

  const flashAt = (target, coin = false) => {
    flashId.current += 1;
    const id = flashId.current;
    setTankFlashes((list) => [...list, { id, x: target.x, y: target.y, coin }]);
    setTimeout(() => setTankFlashes((list) => list.filter((f) => f.id !== id)), 900);
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
        particlesRef.current?.flyEnergy(cx, cy, tx, ty, () => flashAt(tank, false));
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

  const flyEnergyOnExit = (zoneKey, isTop) => {
    const inner = stageRef.current;
    if (!inner || !particlesRef.current) return;
    const rect = inner.getBoundingClientRect();
    const c = fields[zoneKey];
    const fx = rect.left + (((c.tl.x + c.tr.x + c.br.x + c.bl.x) / 4 / 100) * rect.width);
    const fy = rect.top + (((c.tl.y + c.tr.y + c.br.y + c.bl.y) / 4 / 100) * rect.height);

    const send = (target, delay, coin) => {
      const tx = rect.left + (target.x / 100) * rect.width;
      const ty = rect.top + (target.y / 100) * rect.height;
      setTimeout(() => {
        particlesRef.current?.flyEnergy(fx, fy, tx, ty, () => flashAt(target, coin));
      }, delay);
    };

    if (isTop) {
      send(COIN, 0, true);
    } else {
      flask.pos.forEach((_, i) => send(flaskTarget(i), i * 160, false));
      send(COIN, 330, true);
    }
  };

  const exitZoom = () => {
    const zone = zoomZone;
    const isTop = zone === "tl" || zone === "tr";
    const destroyedNow = zoomDestroyed.current;
    setZoomZone(null);
    setLit(null);
    if (zone && destroyedNow > 0) {
      setTimeout(() => flyEnergyOnExit(zone, isTop), 950);
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
          <img className={styles.gridBg} src={`${ASSET}/down_bg.jpg`} alt="Castle fields" />
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
