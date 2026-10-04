import styles from "./Balloons.module.css";
import { PARK_ASSET } from "./parkAssetBase";

const ASSET = PARK_ASSET;

const ITEMS = [
  { src: "Baloon_Yellow.png", base: 1, len: 82, sway: 3.5, dur: 4.6, delay: 0.9 },
  { src: "Baloon_orange.png", base: 5, len: 70, sway: 3, dur: 4.8, delay: 0.2 },
  { src: "Baloon_Blue.png", base: 15, len: 62, sway: 3, dur: 5.0, delay: 0.4 },
  { src: "Baloon_blue2.png", base: -13, len: 56, sway: 3.5, dur: 4.2, delay: 1.1 },
  { src: "Baloon_Red.png", base: 15, len: 49, sway: 3.5, dur: 4.4, delay: 0.7 },
  { src: "Baloon_Green.png", base: 3, len: 38, sway: 3, dur: 5.2, delay: 0 },
];

export default function Balloons({ className, style, scale = 1, onPointerDown, onPointerMove, onPointerUp, onPointerCancel }) {
  return (
    <div
      className={`${styles.bunch} ${className || ""}`}
      style={{ transform: `scale(${scale})`, ...style }}
      onPointerDown={onPointerDown}
      onPointerMove={onPointerMove}
      onPointerUp={onPointerUp}
      onPointerCancel={onPointerCancel}
    >
      {ITEMS.map((b, i) => (
        <div key={i} className={styles.fan} style={{ transform: `rotate(${b.base}deg)` }}>
          <div
            className={styles.sway}
            style={{ animationDuration: `${b.dur}s`, animationDelay: `${b.delay}s`, "--sway": `${b.sway}deg` }}
          >
            <span className={styles.string} style={{ height: `${b.len}px` }} />
            <img className={styles.ball} src={`${ASSET}/${b.src}`} alt="" style={{ bottom: `${b.len - 4}px` }} />
          </div>
        </div>
      ))}
      <span className={styles.knot} />
    </div>
  );
}
