import { useState, useEffect } from "react";
import { useApp } from "@/context/AppContext";
import TopBar from "@/components/layout/TopBar";
import BottomNav from "@/components/layout/BottomNav";
import Drawer from "@/components/layout/Drawer";
import SupportPopup from "@/components/layout/SupportPopup";
import StatusOverlay from "@/components/ui/StatusOverlay";
import Notice from "@/components/ui/Notice";
import HomePage from "@/components/pages/HomePage";
import GamesPage from "@/components/pages/GamesPage";
import PromotionsPage from "@/components/pages/PromotionsPage";
import BonusPage from "@/components/pages/BonusPage";
import SportPage from "@/components/pages/SportPage";
import DepositPage from "@/components/pages/DepositPage";
import GamePage from "@/components/pages/GamePage";
import BonusParkPage from "@/components/pages/BonusParkPage";
import Lunopark from "@/components/pages/Lunopark";
import FerrisWheel from "@/components/pages/FerrisWheel";
import MiniGamesPage from "@/components/pages/MiniGamesPage";
import styles from "./App.module.css";

const PAGES = {
  home: HomePage,
  games: GamesPage,
  promotions: PromotionsPage,
  bonus: BonusPage,
  bonuspark: BonusParkPage,
  lunopark: Lunopark,
  ferris: FerrisWheel,
  minigames: MiniGamesPage,
  sport: SportPage,
  deposit: DepositPage,
  game: GamePage,
};

function PageRouter() {
  const { route } = useApp();
  const Page = PAGES[route] || HomePage;

  return (
    <div className={`${styles.content} ${route === "bonus" ? styles.bonusMode : ""}`}>
      <Page />
    </div>
  );
}

const BASE_W = 390;
const BASE_H = 844;

function computeScale() {
  const w = window.visualViewport?.width || window.innerWidth;
  const h = window.visualViewport?.height || window.innerHeight;
  return Math.min(w / BASE_W, h / BASE_H);
}

function useFitScale() {
  const [scale, setScale] = useState(computeScale);
  useEffect(() => {
    const calc = () => setScale(computeScale());
    calc();
    window.addEventListener("resize", calc);
    window.addEventListener("orientationchange", calc);
    window.visualViewport?.addEventListener("resize", calc);
    return () => {
      window.removeEventListener("resize", calc);
      window.removeEventListener("orientationchange", calc);
      window.visualViewport?.removeEventListener("resize", calc);
    };
  }, []);
  return scale;
}

export default function App() {
  const { loading, error, retry } = useApp();
  const scale = useFitScale();

  return (
    <div className={styles.phone} style={{ transform: `scale(${scale})` }}>
      <div className={styles.screen}>
        <PageRouter />
        <TopBar />
        <BottomNav />
        <Drawer />
        <SupportPopup />
        <StatusOverlay loading={loading} error={error} onRetry={retry} />
        <Notice />
      </div>
    </div>
  );
}
