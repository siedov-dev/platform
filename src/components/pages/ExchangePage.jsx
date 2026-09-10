import { useState } from "react";
import { useApp } from "@/context/AppContext";
import ParkHud from "./ParkHud";
import ExchangePopup from "./ExchangePopup";
import styles from "./ExchangePage.module.css";

export default function ExchangePage() {
  const { navigate } = useApp();
  const [wallet, setWallet] = useState({ coins: 0, tickets: 9000, cash: 9000 });

  return (
    <div className={styles.page}>
      <img className={styles.bg} src="/bonus-park/main_vertical.webp" alt="" draggable={false} />
      <ParkHud values={wallet} />
      <ExchangePopup
        wallet={wallet}
        onClose={() => navigate("home")}
        onExchange={(amt, coins) => setWallet((w) => ({ ...w, cash: w.cash - amt, coins: w.coins + coins }))}
      />
    </div>
  );
}
