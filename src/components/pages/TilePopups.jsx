import { useRef } from "react";
import styles from "./TilePopups.module.css";
import usePopFrom from "./usePopFrom";

const CONFETTI = Array.from({ length: 26 }, (_, i) => ({
  left: (i * 37 + 11) % 100,
  delay: ((i * 53) % 20) / 10,
  dur: 2.6 + ((i * 29) % 14) / 10,
  size: 4 + ((i * 13) % 4),
  color: ["#f2c94c", "#e2574c", "#4ca3f2", "#58d68d", "#c07ef0", "#f29b4c"][i % 6],
}));

function Confetti() {
  return (
    <div className={styles.confetti}>
      {CONFETTI.map((c, i) => (
        <span
          key={i}
          style={{
            left: `${c.left}%`,
            width: c.size,
            height: c.size,
            background: c.color,
            animationDelay: `${c.delay}s`,
            animationDuration: `${c.dur}s`,
          }}
        />
      ))}
    </div>
  );
}

export function LootPopup({ tile, onClaim, onClose }) {
  return (
    <div className={styles.overlay}>
      <div className={styles.popup}>
        <Confetti />
        <div className={styles.head}>
          <span className={styles.title}>Общий сейф</span>
          <button className={styles.close} onClick={onClose}>✕</button>
        </div>
        <div className={styles.icon}>💰</div>
        <div className={styles.text}>
          Вы нашли сейф под плиткой
          <br />
          {tile}!
        </div>
        <button className={styles.mainBtn} onClick={onClaim}>Забрать лут</button>
      </div>
    </div>
  );
}

export function GameIntroPopup({ name, tile, onStart, onClose, from }) {
  const boxRef = useRef(null);
  usePopFrom(boxRef, from);
  return (
    <div className={styles.overlay}>
      <div ref={boxRef} className={`${styles.popup} ${from ? styles.noPop : ""}`}>
        <div className={styles.head}>
          <span className={styles.title}>Мини-игра</span>
          <button className={styles.close} onClick={onClose}>✕</button>
        </div>
        <div className={styles.icon}>🎰</div>
        <div className={styles.text}>
          Под плиткой {tile} — игра
          <br />
          «{name}»!
        </div>
        <button className={styles.mainBtn} onClick={onStart}>Начать игру</button>
      </div>
    </div>
  );
}
