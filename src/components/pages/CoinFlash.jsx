import { useState, useEffect, useRef } from "react";
import styles from "./CoinFlash.module.css";

const ASSET = "/bonus-park/Flash_Coin_Lights";

// vertical lightning striking DOWN through the coin into the pedestal (blue top -> pink bottom hits the base)
const BOLTS = [
  { img: "Flash_4.png", x: -4, y: 20, w: 40, delay: 0 },
  { img: "Flash_5.png", x: 12, y: 24, w: 32, delay: 0.05 },
  { img: "Flash_3.png", x: -24, y: 12, w: 24, delay: 0.1 },
  { img: "Flash_1.png", x: 22, y: 8, w: 16, delay: 0.03 },
];

const SPARKS = [
  { img: "Flash_Light_Pink_1.png", x: -8, y: 66, w: 12, delay: 0.06 },
  { img: "Flash_Light_Pink_2.png", x: 10, y: 70, w: 11, delay: 0.14 },
  { img: "Flash_Light_blue_2.png", x: -18, y: 58, w: 12, delay: 0.1 },
  { img: "Flash_Light_blue_1.png", x: 20, y: 62, w: 10, delay: 0.2 },
];

export default function CoinFlash({
  className,
  style,
  fill = 0,
  live = false,
  drop = false,
  pulseKey = 0,
  fullKey = 0,
  onPointerDown,
  onPointerMove,
  onPointerUp,
}) {
  const lvl = Math.max(0, Math.min(1, fill));
  const [showLine, setShowLine] = useState(false);
  const prevFill = useRef(lvl);
  const prevLive = useRef(live);
  const hideTimer = useRef(null);

  useEffect(() => {
    if (live) {
      clearTimeout(hideTimer.current);
      setShowLine(true);
    } else if (prevLive.current) {
      clearTimeout(hideTimer.current);
      hideTimer.current = setTimeout(() => setShowLine(false), 700);
    } else if (lvl > prevFill.current + 0.0001) {
      setShowLine(true);
      clearTimeout(hideTimer.current);
      hideTimer.current = setTimeout(() => setShowLine(false), 700);
    } else if (lvl < prevFill.current - 0.0001) {
      clearTimeout(hideTimer.current);
      setShowLine(false);
    }
    prevFill.current = lvl;
    prevLive.current = live;
    return () => clearTimeout(hideTimer.current);
  }, [lvl, live]);

  return (
    <div
      className={className ? `${styles.wrap} ${className}` : styles.wrap}
      style={style}
      onPointerDown={onPointerDown}
      onPointerMove={onPointerMove}
      onPointerUp={onPointerUp}
      onPointerCancel={onPointerUp}
    >
      <img className={`${styles.layer} ${styles.aura} ${styles.auraA}`} src={`${ASSET}/Flash_Light_Pink_1.png`} alt="" draggable={false} />
      <img className={`${styles.layer} ${styles.aura} ${styles.auraB}`} src={`${ASSET}/Flash_Light_Pink_2.png`} alt="" draggable={false} />

      <div className={styles.coinBox}>
        {/* empty (silver) base */}
        <img className={styles.coinImg} src={`${ASSET}/Coin_Spielmen_Off.png`} alt="" draggable={false} />
        {/* gold fill grows from the bottom up to the current level */}
        <img
          className={`${styles.coinImg} ${styles.coinFill} ${live ? (drop ? styles.coinDrop : styles.coinLive) : ""}`}
          src={`${ASSET}/Coin_Spielmen_On.png`}
          alt=""
          draggable={false}
          style={{ clipPath: `inset(${(1 - lvl) * 100}% 0 0 0)` }}
        />
        {/* light band appears only during a fill event, then fades out */}
        <img
          className={`${styles.fillLine} ${live ? (drop ? styles.fillLineDrop : styles.fillLineLive) : ""}`}
          src={`${ASSET}/Coin_Lights.png`}
          alt=""
          draggable={false}
          style={{ top: `${(1 - lvl) * 100}%`, opacity: showLine ? 1 : 0 }}
        />
      </div>

      {fullKey > 0 && (
        <div className={styles.fullBurst} key={`full${fullKey}`}>
          <img className={`${styles.layer} ${styles.fullGlow}`} src={`${ASSET}/Flash_Light_blue_bg.png`} alt="" draggable={false} />
          <img className={`${styles.layer} ${styles.fullFlare}`} src={`${ASSET}/Flash_over_light.png`} alt="" draggable={false} />
        </div>
      )}

      {/* effects only on orb impact, restart each time pulseKey changes */}
      {pulseKey > 0 && (
        <div className={styles.burst} key={pulseKey}>
          <img className={`${styles.layer} ${styles.glowBurst}`} src={`${ASSET}/Flash_Light_blue_bg.png`} alt="" draggable={false} />
          {BOLTS.map((b, i) => (
            <img
              key={`b${i}`}
              className={`${styles.layer} ${styles.bolt}`}
              src={`${ASSET}/${b.img}`}
              alt=""
              draggable={false}
              style={{ width: `${b.w}%`, marginLeft: `${b.x}%`, marginTop: `${b.y}%`, animationDelay: `${b.delay}s` }}
            />
          ))}
          {SPARKS.map((s, i) => (
            <img
              key={`s${i}`}
              className={`${styles.layer} ${styles.spark}`}
              src={`${ASSET}/${s.img}`}
              alt=""
              draggable={false}
              style={{ width: `${s.w}%`, marginLeft: `${s.x}%`, marginTop: `${s.y}%`, animationDelay: `${s.delay}s` }}
            />
          ))}
          <img className={`${styles.layer} ${styles.flare}`} src={`${ASSET}/Flash_over_light.png`} alt="" draggable={false} />
        </div>
      )}
    </div>
  );
}
