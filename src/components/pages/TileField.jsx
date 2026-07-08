import { useRef, useLayoutEffect, useState } from "react";
import styles from "./BonusParkPage.module.css";
import { transformFor } from "./perspective";

const ASSET = "/bonus-park";

export function tileFor(r, c, seed) {
  return ((r * 131 + c * 197 + r * c * 59 + seed * 103) % 10) + 1;
}

export default function TileField({
  corners,
  glowCorners,
  cols,
  rows,
  seed,
  offset = 0,
  gapX = 0,
  gapY = 0,
  tileScale = 1.1,
  destroyed,
  selected,
  litTile,
  onTileClick,
  showGlow,
}) {
  const hostRef = useRef(null);
  const [size, setSize] = useState({ w: 0, h: 0 });

  useLayoutEffect(() => {
    const host = hostRef.current;
    const update = () => setSize({ w: host.clientWidth, h: host.clientHeight });
    const ro = new ResizeObserver(update);
    ro.observe(host);
    update();
    return () => ro.disconnect();
  }, []);

  const tileT = size.w > 0 ? transformFor(corners, size.w, size.h, cols, rows, gapX, gapY) : null;
  const glowT = size.w > 0 && glowCorners ? transformFor(glowCorners, size.w, size.h) : null;

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
            gridTemplateColumns: `repeat(${cols}, ${tileT.cellW}px)`,
            gridTemplateRows: `repeat(${rows}, ${tileT.cellH}px)`,
            columnGap: `${gapX}px`,
            rowGap: `${gapY}px`,
            "--ts": tileScale,
          }}
        >
          {Array.from({ length: rows * cols }, (_, i) => {
          const r = Math.floor(i / cols);
          const c = i % cols;
          const gone = destroyed ? destroyed.has(offset + i) : false;
          const lit = !gone && litTile === i;
          return (
            <div
              key={i}
              className={gone ? `${styles.cell} ${styles.cellGone}` : styles.cell}
              onClick={!gone && onTileClick ? () => onTileClick(i, offset + i) : undefined}
            >
              <span className={styles.tileClip}>
                <img className={styles.tileImg} src={`${ASSET}/Tile_${tileFor(r, c, seed)}.png`} alt="" />
              </span>
              {lit && <img className={styles.tileGlow} src={`${ASSET}/Light.png`} alt="" />}
            </div>
          );
          })}
        </div>
      )}
      {showGlow && glowT && (
        <div
          className={styles.glowLayer}
          style={{ width: `${glowT.gw}px`, height: `${glowT.gh}px`, transform: glowT.transform, transformOrigin: "0 0" }}
        >
          <img
            className={styles.fieldGlow}
            src={`${ASSET}/Light_field.png`}
            alt=""
            style={{ animationDelay: `${(seed || 0) * 0.5}s` }}
          />
        </div>
      )}
    </div>
  );
}
