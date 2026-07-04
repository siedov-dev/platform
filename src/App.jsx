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
import styles from "./App.module.css";

const PAGES = {
  home: HomePage,
  games: GamesPage,
  promotions: PromotionsPage,
  bonus: BonusPage,
  bonuspark: BonusParkPage,
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

export default function App() {
  const { loading, error, retry } = useApp();

  return (
    <div className={styles.phone}>
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
