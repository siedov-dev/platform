import { useState } from "react";
import { useApp } from "@/context/AppContext";
import { gamesApi } from "@/api/gamesClient";
import styles from "./Lunopark.module.css";

const USER_ID = "u1";
const CLUSTER_ID = "global_cluster";
const SYMBOLS = ["★", "200", "100", "50", "15", "10", "5", "3", "2", "1", "✖"];

const delay = (ms) => new Promise((r) => setTimeout(r, ms));

function outcomeSymbol(outcome) {
  if (outcome === "BONUS") return "★";
  if (outcome === "LOSE") return "✖";
  if (outcome && outcome.startsWith("WIN_")) return outcome.slice(4);
  return "✖";
}

export default function Lunopark() {
  const { navigate } = useApp();
  const [busy, setBusy] = useState(false);
  const [sym, setSym] = useState("★");
  const [flash, setFlash] = useState(false);
  const [outcome, setOutcome] = useState(null);
  const [lastWin, setLastWin] = useState(0);
  const [flags, setFlags] = useState(0);
  const [wagons, setWagons] = useState(0);
  const [balance, setBalance] = useState(0);
  const [carousel, setCarousel] = useState(null);
  const [wheel, setWheel] = useState(null);
  const [error, setError] = useState(null);

  const spin = async () => {
    if (busy) return;
    setBusy(true);
    setError(null);
    setOutcome(null);
    setLastWin(0);
    setFlash(true);
    const flashTimer = setInterval(() => setSym(SYMBOLS[Math.floor(Math.random() * SYMBOLS.length)]), 80);

    let res;
    try {
      res = await gamesApi.lunoparkPlay(USER_ID, CLUSTER_ID);
    } catch (e) {
      clearInterval(flashTimer);
      setFlash(false);
      setError(e.message);
      setBusy(false);
      return;
    }
    await delay(1100);
    clearInterval(flashTimer);
    setFlash(false);

    const events = res.events || [];
    const base = events.find((e) => e.type === "base_slot");
    if (base) {
      setSym(outcomeSymbol(base.outcome));
      setOutcome(base.outcome);
      if (base.coins > 0) setLastWin(base.coins);
    }

    const flagEv = events.find((e) => e.type === "flag_collected" || e.type === "flag_trigger");
    if (flagEv) {
      if (flagEv.type === "flag_trigger") {
        setFlags(4);
        await delay(750);
      } else {
        setFlags(flagEv.current_flags);
        await delay(450);
      }
    }

    const carEv = events.find((e) => e.type === "carousel");
    if (carEv) {
      setWagons(carEv.total_wagons_now);
      setCarousel({ coins: carEv.coins_won, wagons: carEv.wagons_won });
      await delay(2400);
      setCarousel(null);
    }

    const wheelEv = events.find((e) => e.type === "ferris_wheel");
    if (wheelEv) {
      setWheel({ coins: wheelEv.coins_won });
      await delay(3000);
      setWheel(null);
    }

    const state = res.state || {};
    setFlags(state.cluster_flags ?? 0);
    setWagons(state.user_wagons ?? 0);
    setBalance((b) => b + (res.total_coins_won || 0));
    setBusy(false);
  };

  const outClass = outcome === "BONUS" ? styles.bonus : outcome === "LOSE" ? styles.lose : outcome ? styles.win : "";

  return (
    <div className={styles.page}>
      <button className={styles.exit} onClick={() => navigate("games")}>✕</button>
      <h1 className={styles.title}>LUNAPARK</h1>
      <div className={styles.balance}>🪙 {balance}</div>

      <div className={styles.counters}>
        <div className={styles.counter}>
          <span className={styles.counterLabel}>Flags · room</span>
          <div className={styles.row}>
            {[0, 1, 2, 3].map((i) => (
              <span key={i} className={`${styles.flag} ${i < flags ? styles.flagOn : ""}`}>🚩</span>
            ))}
          </div>
        </div>
        <div className={styles.counter}>
          <span className={styles.counterLabel}>Wagons</span>
          <div className={styles.row}>
            {[0, 1, 2, 3, 4, 5].map((i) => (
              <span key={i} className={`${styles.wagon} ${i < wagons ? styles.wagonOn : ""}`}>🚃</span>
            ))}
          </div>
        </div>
      </div>

      <div className={styles.pillar}>
        <div className={styles.pillarTop} />
        <div className={styles.window}>
          <span className={`${styles.sym} ${flash ? styles.flashing : ""} ${outClass}`}>{sym}</span>
        </div>
        <div className={styles.pillarBase} />
      </div>

      <div className={styles.resultRow}>
        {error ? (
          <span className={styles.err}>{error}</span>
        ) : outcome && !busy ? (
          <span className={styles.resultText}>
            {outcome === "BONUS" ? "BONUS!" : outcome === "LOSE" ? "No win" : `Win +${lastWin}`}
          </span>
        ) : (
          <span className={styles.hint}>Spin to hit BONUS</span>
        )}
      </div>

      <button className={styles.spinBtn} disabled={busy} onClick={spin}>
        {busy ? "…" : "SPIN"}
      </button>

      {carousel && (
        <div className={styles.overlay}>
          <div className={styles.spinner}>🎠</div>
          <div className={styles.overlayTitle}>Carousel</div>
          <div className={styles.overlayResult}>
            +{carousel.coins} 🪙{carousel.wagons > 0 ? ` · +${carousel.wagons} 🚃` : ""}
          </div>
        </div>
      )}

      {wheel && (
        <div className={styles.overlay}>
          <div className={styles.ferris}>🎡</div>
          <div className={styles.overlayTitle}>Ferris Wheel</div>
          <div className={styles.overlayResult}>+{wheel.coins} 🪙</div>
        </div>
      )}
    </div>
  );
}
