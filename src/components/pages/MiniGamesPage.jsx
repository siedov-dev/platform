import { useState } from "react";
import { useApp } from "@/context/AppContext";
import { MINI_GAMES } from "./MiniGames";
import { GamePopup } from "./BonusParkPage";
import styles from "./BonusParkPage.module.css";

export default function MiniGamesPage() {
  const { navigate } = useApp();
  const [popup, setPopup] = useState(null);
  const active = MINI_GAMES.find((g) => g.id === popup);

  return (
    <div className={styles.page}>
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
            <button key={g.id} className={styles.parkCard} onClick={() => setPopup(g.id)}>
              <span className={styles.parkCardTitle}>{g.title}</span>
              <span className={styles.parkCardPlay}>Play</span>
            </button>
          ))}
        </div>
      </div>

      <button className={styles.exit} style={{ top: 12 }} onClick={() => navigate("games")}>✕</button>

      {active && (
        <GamePopup title={active.title} bare={active.bare} onClose={() => setPopup(null)}>
          <active.Comp />
        </GamePopup>
      )}
    </div>
  );
}
