import { useRef, useLayoutEffect, useState, useMemo } from "react";
import styles from "./BonusParkPage.module.css";
import { transformFor } from "./perspective";
import { PARK_ASSET } from "./parkAssetBase";

const ASSET = PARK_ASSET;

const IS_DROID = typeof navigator !== "undefined" && /Android/i.test(navigator.userAgent);

const DIG_BODY = {
  0: [7.5, 18, 20], 1: [9.8, 17, 20], 2: [8.4, 18, 21], 3: [7.8, 18, 21], 4: [7.8, 18.3, 21],
  5: [8.4, 17.3, 20], 6: [8.4, 18, 20], 7: [7.8, 18.3, 21], 8: [7.5, 18, 21], 9: [7.5, 18, 20],
};

const FIELD_ICN = {
  cards: "Icn_Card_S.png",
  wheel: "Icn_Wheel_S.png",
  pyramid: "Icn_Pyramid_S.png",
  figures: "Icn_Pyramid_S.png",
  coinflip: "Icn_Joker_S.png",
  coinhand: "Icn_Joker_S.png",
  matches: "Icn_Joker_S.png",
};

export function tileFor(r, c, seed) {
  let h = Math.imul(r + 1, 374761393) ^ Math.imul(c + 1, 668265263) ^ Math.imul(seed + 1, 2246822519);
  h = Math.imul(h ^ (h >>> 15), 2246822519);
  h = Math.imul(h ^ (h >>> 13), 3266489917);
  h = (h ^ (h >>> 16)) >>> 0;
  return (h % 10) + 1;
}

export default function TileField({
  corners,
  glowCorners,
  coverCorners,
  cols,
  rows,
  seed,
  offset = 0,
  gapX = 0,
  gapY = 0,
  scaleX = 1.15,
  scaleY = 1.15,
  edge = 0,
  destroyed,
  marks,
  selected,
  taken,
  litTile,
  onTileClick,
  showGlow,
  coverSrc,
}) {
  const hostRef = useRef(null);
  const [size, setSize] = useState({ w: 0, h: 0 });

  useLayoutEffect(() => {
    const host = hostRef.current;
    const update = () => {
      const w = host.clientWidth;
      const h = host.clientHeight;
      setSize((s) => (s.w === w && s.h === h ? s : { w, h }));
    };
    const ro = new ResizeObserver(update);
    ro.observe(host);
    update();
    return () => ro.disconnect();
  }, []);

  const tileT = useMemo(
    () => (size.w > 0 ? transformFor(corners, size.w, size.h, cols, rows, 0, 0) : null),
    [corners, size.w, size.h, cols, rows]
  );
  const glowT = useMemo(
    () => (size.w > 0 && glowCorners ? transformFor(glowCorners, size.w, size.h) : null),
    [glowCorners, size.w, size.h]
  );
  const coverT = useMemo(
    () => (size.w > 0 && coverCorners ? transformFor(coverCorners, size.w, size.h) : null),
    [coverCorners, size.w, size.h]
  );

  return (
    <div ref={hostRef} className={styles.tileHost}>
      {tileT && (
        <div
          className={IS_DROID ? `${styles.tileGrid} ${styles.tileGridDroid}` : styles.tileGrid}
          style={{
            width: `${tileT.gw}px`,
            height: `${tileT.gh}px`,
            transform: tileT.transform,
            transformOrigin: "0 0",
            gridTemplateColumns: `repeat(${cols}, 1fr)`,
            gridTemplateRows: `repeat(${rows}, 1fr)`,
            columnGap: `${gapX}px`,
            rowGap: `${gapY}px`,
            "--tsx": scaleX,
            "--tsy": scaleY,
          }}
        >
          {Array.from({ length: rows * cols }, (_, i) => {
          const r = Math.floor(i / cols);
          const c = i % cols;
          const gone = destroyed ? destroyed.has(offset + i) : false;
          const topAlive = r === 0 || !destroyed || !destroyed.has(offset + i - cols);
          return (
            <div
              key={i}
              className={
                gone
                  ? `${styles.cell} ${styles.cellGone} ${topAlive ? styles.holeShade : ""}`
                  : styles.cell
              }
              onClick={
                onTileClick
                  ? (e) => onTileClick(i, offset + i, e.currentTarget.getBoundingClientRect(), gone)
                  : undefined
              }
            >
              <span className={styles.tileClip}>
                <img className={styles.tileImg} src={`${ASSET}/opt/Tile_${tileFor(r, c, seed)}.webp`} alt="" />
              </span>
            </div>
          );
          })}
        </div>
      )}
      {tileT && (
        <div
          className={styles.tileEdgeLayer}
          style={{
            width: `${tileT.gw}px`,
            height: `${tileT.gh}px`,
            transform: tileT.transform,
            transformOrigin: "0 0",
          }}
        />
      )}
      {!selected && onTileClick && tileT && (
        <div
          className={styles.fieldHit}
          style={{
            width: `${tileT.gw}px`,
            height: `${tileT.gh}px`,
            transform: tileT.transform,
            transformOrigin: "0 0",
          }}
          onClick={(e) => onTileClick(0, offset, e.currentTarget.getBoundingClientRect(), true)}
        />
      )}
      {taken && tileT && (
        <div
          className={styles.takenLayer}
          style={{
            width: `${tileT.gw}px`,
            height: `${tileT.gh}px`,
            transform: tileT.transform,
            transformOrigin: "0 0",
          }}
        />
      )}
      {coverSrc && coverT && (
        <div
          className={styles.coverLayer}
          style={{ width: `${coverT.gw}px`, height: `${coverT.gh}px`, transform: coverT.transform, transformOrigin: "0 0" }}
        >
          <img className={styles.coverImg} src={coverSrc} alt="" />
        </div>
      )}
      {showGlow && tileT && (
        <div
          className={styles.glowLayer}
          style={{ width: `${tileT.gw}px`, height: `${tileT.gh}px`, transform: tileT.transform, transformOrigin: "0 0" }}
        >
          <div className={styles.fieldOutline} />
        </div>
      )}
      {tileT && marks && destroyed && (
        <div
          className={styles.markLayer}
          style={{
            width: `${tileT.gw}px`,
            height: `${tileT.gh}px`,
            transform: tileT.transform,
            transformOrigin: "0 0",
            gridTemplateColumns: `repeat(${cols}, 1fr)`,
            gridTemplateRows: `repeat(${rows}, 1fr)`,
            columnGap: `${gapX}px`,
            rowGap: `${gapY}px`,
          }}
        >
          {Array.from({ length: rows * cols }, (_, i) => {
            const mark = destroyed.has(offset + i) ? marks[offset + i] : null;
            if (!mark) return null;
            return (
              <span
                key={i}
                className={styles.markCell}
                style={{ gridColumn: (i % cols) + 1, gridRow: Math.floor(i / cols) + 1 }}
              >
                {mark.kind === "money" ? null : FIELD_ICN[mark.game] ? (
                  <img className={styles.tileMarkIcn} src={`${ASSET}/Icn_Field/${FIELD_ICN[mark.game]}`} alt="" />
                ) : null}
              </span>
            );
          })}
        </div>
      )}
      {marks && destroyed && size.h > 0 && tileT && (
        <div className={styles.digitLayer}>
          {Array.from({ length: rows * cols }, (_, i) => {
            const mark = destroyed.has(offset + i) ? marks[offset + i] : null;
            if (!mark || mark.kind !== "money") return null;
            const mm = tileT.transform.slice(9, -1).split(",").map(Number);
            const proj = (x, y) => {
              const w = mm[3] * x + mm[7] * y + mm[15];
              return {
                x: (mm[0] * x + mm[4] * y + mm[12]) / w,
                y: (mm[1] * x + mm[5] * y + mm[13]) / w,
              };
            };
            const r = Math.floor(i / cols);
            const c = i % cols;
            const tlp = proj((c / cols) * tileT.gw, (r / rows) * tileT.gh);
            const trp = proj(((c + 1) / cols) * tileT.gw, (r / rows) * tileT.gh);
            const brp = proj(((c + 1) / cols) * tileT.gw, ((r + 1) / rows) * tileT.gh);
            const blp = proj((c / cols) * tileT.gw, ((r + 1) / rows) * tileT.gh);
            const px = (tlp.x + trp.x + brp.x + blp.x) / 4;
            const py = (tlp.y + trp.y + brp.y + blp.y) / 4;
            const wTop = Math.abs(trp.x - tlp.x);
            const wBot = Math.abs(brp.x - blp.x);
            const size2 = ((wTop + wBot) / 2) * 0.86;
            return (
              <span
                key={i}
                className={styles.energyCell}
                style={{
                  left: `${(px - size2 / 2).toFixed(2)}px`,
                  top: `${(py - size2 / 2).toFixed(2)}px`,
                  width: `${size2.toFixed(2)}px`,
                  height: `${size2.toFixed(2)}px`,
                }}
              >
                <img src={`${ASSET}/Ui_Exchange_HUD/Icn_Energy_Purple.png`} alt="" draggable={false} />
              </span>
            );
          })}
        </div>
      )}
      {marks && destroyed && size.h > 0 && tileT && (
        <div className={styles.digitLayer}>
          {Array.from({ length: rows * cols }, (_, i) => {
            const mark = destroyed.has(offset + i) ? marks[offset + i] : null;
            if (!mark || mark.kind !== "cash") return null;
            const mm = tileT.transform.slice(9, -1).split(",").map(Number);
            const proj = (x, y) => {
              const w = mm[3] * x + mm[7] * y + mm[15];
              return {
                x: (mm[0] * x + mm[4] * y + mm[12]) / w,
                y: (mm[1] * x + mm[5] * y + mm[13]) / w,
              };
            };
            const r = Math.floor(i / cols);
            const c = i % cols;
            const gx = ((c + 0.5) / cols) * tileT.gw;
            const gy = ((r + 0.5) / rows) * tileT.gh;
            const p = proj(gx, gy);
            const gym = tileT.gh * 0.5;
            const pm = proj(tileT.gw * 0.5, gym);
            const pm2 = proj(tileT.gw * 0.5 + tileT.gw / cols, gym);
            const cwPx = Math.abs(pm2.x - pm.x);
            const k = (cwPx * 0.56) / 24;
            const digs = String(mark.value).split("");
            const GAP_U = 2;
            let cursor = 0;
            const items = digs.map((d, di) => {
              const bb = DIG_BODY[d];
              const item = { d, di, left: cursor - bb[0], z: digs.length - di };
              cursor += bb[1] - bb[0] + GAP_U;
              return item;
            });
            const totalU = cursor - GAP_U;
            return (
              <span
                key={i}
                className={styles.digitCell}
                style={{ left: `${p.x - (totalU * k) / 2}px`, top: `${p.y - 12 * k}px` }}
              >
                <span
                  className={styles.digitRow}
                  style={{ width: `${totalU}px`, transform: `scale(${k.toFixed(4)})` }}
                >
                  <span className={styles.digitShade} style={{ width: `${totalU}px` }} />
                  {items.map((it) => (
                    <img
                      key={it.di}
                      className={styles.digitGlyph}
                      src={`${ASSET}/Preview_Number_S/Number_${it.d}_S.png`}
                      alt=""
                      draggable={false}
                      style={{ left: `${it.left.toFixed(1)}px`, zIndex: it.z }}
                    />
                  ))}
                </span>
              </span>
            );
          })}
        </div>
      )}
      {tileT && litTile != null && !(destroyed && destroyed.has(offset + litTile)) && (
        <div
          className={styles.topGlowLayer}
          style={{
            width: `${tileT.gw}px`,
            height: `${tileT.gh}px`,
            transform: tileT.transform,
            transformOrigin: "0 0",
            gridTemplateColumns: `repeat(${cols}, 1fr)`,
            gridTemplateRows: `repeat(${rows}, 1fr)`,
          }}
        >
          <span
            className={styles.topGlowCell}
            style={{ gridColumn: (litTile % cols) + 1, gridRow: Math.floor(litTile / cols) + 1 }}
          >
            <img className={styles.tileGlow} src={`${ASSET}/opt/Light.webp`} alt="" />
          </span>
        </div>
      )}
    </div>
  );
}
