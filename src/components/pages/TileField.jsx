import { useRef, useLayoutEffect, useState, useMemo } from "react";
import styles from "./BonusParkPage.module.css";
import { transformFor } from "./perspective";

const ASSET = "/bonus-park";

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
  selected,
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
          className={styles.tileGrid}
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
          return (
            <div
              key={i}
              className={gone ? `${styles.cell} ${styles.cellGone}` : styles.cell}
              onClick={
                !gone && onTileClick
                  ? (e) => onTileClick(i, offset + i, e.currentTarget.getBoundingClientRect())
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
      {coverSrc && coverT && (
        <div
          className={styles.coverLayer}
          style={{ width: `${coverT.gw}px`, height: `${coverT.gh}px`, transform: coverT.transform, transformOrigin: "0 0" }}
        >
          <img className={styles.coverImg} src={coverSrc} alt="" />
        </div>
      )}
      {showGlow && glowT && (
        <div
          className={styles.glowLayer}
          style={{ width: `${glowT.gw}px`, height: `${glowT.gh}px`, transform: glowT.transform, transformOrigin: "0 0" }}
        >
          <img className={styles.fieldGlow} src={`${ASSET}/opt/Light_field.webp`} alt="" />
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
