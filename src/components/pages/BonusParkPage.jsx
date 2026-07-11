import { useState, useRef } from "react";
import { useApp } from "@/context/AppContext";
import TileField from "./TileField";
import { MINI_GAMES } from "./MiniGames";
import Balloons from "./Balloons";
import Flag from "./Flag";
import styles from "./BonusParkPage.module.css";

const ASSET = "/bonus-park";

const FIELDS = [
  { key: "tl", sector: 1, cols: 10, rows: 10, seed: 0, offset: 0 },
  { key: "tr", sector: 2, cols: 10, rows: 10, seed: 1, offset: 100 },
  { key: "ml", sector: 3, cols: 10, rows: 12, seed: 2, offset: 200 },
  { key: "mr", sector: 4, cols: 10, rows: 12, seed: 3, offset: 320 },
  { key: "bl", sector: 5, cols: 10, rows: 10, seed: 4, offset: 440 },
  { key: "br", sector: 6, cols: 10, rows: 10, seed: 5, offset: 540 },
];

const COVER = {
  tl: "Cover_Field.png",
  ml: "Cover_Field.png",
  bl: "Cover_Field.png",
  tr: "Cover_Field_2.png",
  mr: "Cover_Field_2.png",
  br: "Cover_Field_2.png",
};

const DEFAULT_FIELDS = {
  tl: { tl: { x: 25.39, y: 33.36 }, tr: { x: 44.9, y: 33.4 }, br: { x: 44.42, y: 43.04 }, bl: { x: 23.23, y: 42.97 } },
  tr: { tl: { x: 56.17, y: 33.45 }, tr: { x: 76.12, y: 33.36 }, br: { x: 78.07, y: 42.95 }, bl: { x: 56.88, y: 42.95 } },
  ml: { tl: { x: 22.91, y: 44.9 }, tr: { x: 44.23, y: 44.85 }, br: { x: 43.61, y: 57.6 }, bl: { x: 20.39, y: 57.6 } },
  mr: { tl: { x: 56.99, y: 44.91 }, tr: { x: 78.55, y: 44.91 }, br: { x: 81.07, y: 57.56 }, bl: { x: 57.83, y: 57.56 } },
  bl: { tl: { x: 20.02, y: 59.93 }, tr: { x: 43.49, y: 59.95 }, br: { x: 42.86, y: 71.24 }, bl: { x: 17.54, y: 71.27 } },
  br: { tl: { x: 57.66, y: 60.02 }, tr: { x: 81.16, y: 59.93 }, br: { x: 83.6, y: 71.4 }, bl: { x: 58.34, y: 71.35 } },
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
  tl: { tl: { x: 25.14, y: 31.51 }, tr: { x: 47.83, y: 31.52 }, br: { x: 44.63, y: 43.45 }, bl: { x: 22.05, y: 43.3 } },
  tr: { tl: { x: 55.5, y: 31.51 }, tr: { x: 78.56, y: 31.46 }, br: { x: 78.65, y: 43.29 }, bl: { x: 55.58, y: 43.43 } },
  ml: { tl: { x: 22.86, y: 42.51 }, tr: { x: 47.51, y: 42.43 }, br: { x: 43.78, y: 58.07 }, bl: { x: 18.96, y: 58.12 } },
  mr: { tl: { x: 56.27, y: 42.43 }, tr: { x: 81.06, y: 42.46 }, br: { x: 81.49, y: 58.08 }, bl: { x: 56.48, y: 57.94 } },
  bl: { tl: { x: 19.68, y: 57.8 }, tr: { x: 47.45, y: 57.58 }, br: { x: 43.11, y: 71.81 }, bl: { x: 16.19, y: 71.7 } },
  br: { tl: { x: 56.99, y: 57.84 }, tr: { x: 84.1, y: 57.85 }, br: { x: 84.02, y: 71.83 }, bl: { x: 56.99, y: 71.78 } },
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
  tl: { tl: { x: 23.22, y: 33.25 }, tr: { x: 45.02, y: 33.31 }, br: { x: 44.92, y: 43.01 }, bl: { x: 23.05, y: 42.95 } },
  tr: { tl: { x: 53.86, y: 33.23 }, tr: { x: 76.32, y: 33.38 }, br: { x: 78.7, y: 42.99 }, bl: { x: 56.67, y: 42.91 } },
  ml: { tl: { x: 20.81, y: 44.89 }, tr: { x: 44.32, y: 44.73 }, br: { x: 44.32, y: 57.6 }, bl: { x: 20.16, y: 57.61 } },
  mr: { tl: { x: 54.35, y: 44.66 }, tr: { x: 78.6, y: 44.89 }, br: { x: 81.75, y: 57.58 }, bl: { x: 57.46, y: 57.56 } },
  bl: { tl: { x: 17.88, y: 59.92 }, tr: { x: 43.52, y: 59.83 }, br: { x: 43.68, y: 71.35 }, bl: { x: 17.49, y: 71.3 } },
  br: { tl: { x: 54.63, y: 59.75 }, tr: { x: 81.33, y: 59.93 }, br: { x: 84.11, y: 71.3 }, bl: { x: 58.08, y: 71.35 } },
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
  const [fields] = useState(loadFields);
  const [decor, setDecor] = useState(loadDecor);
  const [selectedZone, setSelectedZone] = useState(null);
  const [zoomZone, setZoomZone] = useState(null);
  const [lit, setLit] = useState(null);
  const [destroyed, setDestroyed] = useState(() => new Set());
  const [zones] = useState(loadZones);
  const [cfg] = useState(loadTileCfg);
  const [covers] = useState(loadCovers);

  const handleTileClick = (zoneKey, index, cellId) => {
    if (zoomZone !== zoneKey) {
      if (selectedZone !== zoneKey) {
        setSelectedZone(zoneKey);
        return;
      }
      setZoomZone(zoneKey);
      setLit(null);
      return;
    }
    if (lit !== index) {
      setLit(index);
      return;
    }
    setDestroyed((prev) => {
      const next = new Set(prev);
      next.add(cellId);
      return next;
    });
    setLit(null);
  };

  const exitZoom = () => {
    setZoomZone(null);
    setLit(null);
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
      const x = Math.max(0, Math.min(100, ((ev.clientX - offX - rect.left) / rect.width) * 100));
      const y = Math.max(0, Math.min(100, ((ev.clientY - offY - rect.top) / rect.height) * 100));
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
      <div className={styles.gridStage}>
        <div className={styles.gridInner} ref={stageRef} style={zoomStyle}>
          <img className={styles.gridBg} src={`${ASSET}/garden_long.png`} alt="Castle fields" />
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
                scaleX={cfg.scaleX}
                scaleY={cfg.scaleY}
                destroyed={destroyed}
                selected={active}
                showGlow={highlighted}
                litTile={active ? lit : null}
                coverSrc={!zoomZone ? `${ASSET}/${COVER[f.key]}` : null}
                onTileClick={interactive ? (i, cellId) => handleTileClick(f.key, i, cellId) : undefined}
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

function GamePopup({ title, onClose, children }) {
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

function Park({ onOpen }) {
  const { navigate } = useApp();
  return (
    <div className={styles.park}>
      <div className={styles.parkHead}>
        <span className={styles.parkTitle}>Win Park</span>
      </div>
      <div className={styles.parkGames}>
        <button className={styles.parkCard} onClick={() => navigate("lunopark")}>
          <span className={styles.parkCardTitle}>Lunapark</span>
          <span className={styles.parkCardPlay}>Play</span>
        </button>
        {MINI_GAMES.map((g) => (
          <button key={g.id} className={styles.parkCard} onClick={() => onOpen(g.id)}>
            <span className={styles.parkCardTitle}>{g.title}</span>
            <span className={styles.parkCardPlay}>Play</span>
          </button>
        ))}
      </div>
    </div>
  );
}

export default function BonusParkPage() {
  const { navigate } = useApp();
  const [view, setView] = useState("overview");
  const [popup, setPopup] = useState(null);

  const active = MINI_GAMES.find((g) => g.id === popup);

  return (
    <div className={styles.page}>
      {view === "overview" && <Overview onPick={setView} />}
      {view === "grid" && <GridGame onBack={() => setView("overview")} />}
      {view === "park" && <Park onOpen={setPopup} />}

      <button className={styles.exit} onClick={() => (view === "overview" ? navigate("home") : setView("overview"))}>✕</button>

      {active && (
        <GamePopup title={active.title} onClose={() => setPopup(null)}>
          <active.Comp />
        </GamePopup>
      )}
    </div>
  );
}
