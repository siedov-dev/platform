import styles from "./CenterGlow.module.css";

const ASSET = "/bonus-park/Flash_Coin_Lights";

const LMAX = 100;
const ANGLES = [
  -82, -75, -68, -61, -55, -49, -43, -37, -31, -25, -19, -13, -7, -2, 2, 7, 13, 19, 25, 31, 37, 43, 49, 55, 61, 68, 75,
  82,
];
const VAR = [1, 0.66, 0.88, 0.54, 1.06, 0.72, 0.94, 0.6, 1.02, 0.78, 0.64, 0.97, 0.85, 0.58, 1.04, 0.7];
const WID = [2.1, 1.1, 1.7, 0.9, 2.4, 1.3, 1.9, 1, 2.2, 1.4, 1.1, 1.8];
const DUR = [2.8, 1.9, 3.6, 1.6, 4.2, 2.3, 3.1, 2, 3.4, 1.7, 4.6, 2.5];

const RAYS = ANGLES.map((a, i) => {
  const c = Math.cos((a * Math.PI) / 180);
  return {
    a,
    len: LMAX * (0.3 + 0.7 * Math.pow(c, 1.35)) * VAR[i % VAR.length],
    w: WID[i % WID.length],
    d: DUR[i % DUR.length],
    o: 0.62 + 0.38 * Math.pow(c, 1.1),
  };
});

const MOTES = [
  { x: 12, y: 82, s: 2.2, d: 3.4, rise: 30 },
  { x: 26, y: 54, s: 1.7, d: 4.2, rise: 42 },
  { x: 38, y: 22, s: 2, d: 3.8, rise: 34 },
  { x: 50, y: 66, s: 1.5, d: 5, rise: 46 },
  { x: 62, y: 34, s: 2.3, d: 4.6, rise: 38 },
  { x: 74, y: 78, s: 1.8, d: 3.2, rise: 32 },
  { x: 86, y: 46, s: 1.6, d: 5.4, rise: 44 },
  { x: 19, y: 30, s: 2, d: 4, rise: 36 },
  { x: 32, y: 90, s: 1.4, d: 4.8, rise: 40 },
  { x: 45, y: 44, s: 1.9, d: 3.6, rise: 34 },
  { x: 57, y: 12, s: 1.5, d: 5.2, rise: 48 },
  { x: 68, y: 58, s: 1.7, d: 4.4, rise: 36 },
  { x: 80, y: 20, s: 2.1, d: 3.9, rise: 42 },
  { x: 92, y: 68, s: 1.5, d: 4.7, rise: 38 },
  { x: 6, y: 58, s: 1.8, d: 5.1, rise: 44 },
];

export default function CenterGlow({ on = true, flash = 0, tilt = 0 }) {
  return (
    <div
      className={`${styles.layer} ${on ? styles.on : ""}`}
      style={tilt ? { transform: `rotate(${tilt}deg)` } : undefined}
    >
      {on && flash > 0 && <span key={flash} className={styles.ignite} />}

      <div className={styles.mound} />

      <div className={styles.fan}>
        {RAYS.map((r, i) => (
          <span
            key={`r${r.a}`}
            className={styles.rayWrap}
            style={{
              width: `${r.w}%`,
              height: `${r.len.toFixed(1)}%`,
              opacity: r.o.toFixed(2),
              transform: `translateX(-50%) rotate(${r.a}deg)`,
            }}
          >
            <span
              className={styles.ray}
              style={{ animationDuration: `${r.d}s`, animationDelay: `-${(i * 0.47).toFixed(2)}s` }}
            />
          </span>
        ))}
      </div>

      <div className={styles.core} />
      <div className={styles.bloom} />

      {MOTES.map((m, i) => (
        <span
          key={`${m.x}-${m.y}`}
          className={styles.mote}
          style={{
            left: `${m.x}%`,
            top: `${m.y}%`,
            width: `${m.s}px`,
            height: `${m.s}px`,
            animationDuration: `${m.d}s`,
            animationDelay: `-${(i * 0.43).toFixed(2)}s`,
            "--rise": `${-m.rise}px`,
          }}
        />
      ))}

      <div className={styles.strip}>
        <img className={styles.stripImg} src={`${ASSET}/Coin_Lights.png`} alt="" draggable={false} />
        <span className={styles.arcRim} />
        <span className={styles.arcLine} />
        <span className={styles.arcCore} />
        <span className={styles.arcSheen} />
      </div>
    </div>
  );
}
