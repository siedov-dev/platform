import { useApp } from "@/context/AppContext";
import { useI18n } from "@/i18n/I18nProvider";
import IconButton from "@/components/ui/IconButton";
import { Burger, Headset, CloseRing } from "@/components/ui/Icons";
import styles from "./TopBar.module.css";

const TITLES = {
  games: "games.title",
  promotions: "promo.title",
  bonus: "bonus.title",
  sport: "sport.title",
  deposit: "deposit.title",
};

export default function TopBar() {
  const { route, navigate, setDrawerOpen, setSupportOpen } = useApp();
  const { t } = useI18n();
  const isHome = route === "home";

  if (route === "bonus") return null;

  return (
    <div className={styles.bar}>
      {isHome ? (
        <IconButton onClick={() => setDrawerOpen(true)} label="menu"><Burger /></IconButton>
      ) : (
        <span className={styles.spacer} />
      )}
      {!isHome && <div className={styles.title}>{t(TITLES[route])}</div>}
      {isHome ? (
        <IconButton onClick={() => setSupportOpen((s) => !s)} label="support"><Headset /></IconButton>
      ) : (
        <IconButton className={styles.closeRing} onClick={() => navigate("home")} label="close"><CloseRing /></IconButton>
      )}
    </div>
  );
}
