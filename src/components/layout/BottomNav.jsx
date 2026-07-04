import { useApp } from "@/context/AppContext";
import { useI18n } from "@/i18n/I18nProvider";
import styles from "./BottomNav.module.css";

export default function BottomNav() {
  const { route, navigate } = useApp();
  const { t } = useI18n();

  const tabs = [
    { id: "games", label: t("nav.allGames") },
    { id: "promotions", label: t("nav.provider"), sub: t("nav.actions") },
    { id: "bonuspark", label: t("nav.bonus"), isCenter: true },
    { id: "sport", label: t("nav.sport") },
    { id: "deposit", label: t("nav.deposit") },
  ];

  return (
    <nav className={styles.nav}>
      {tabs.map((tab) => (
        <button
          key={tab.id}
          className={`${styles.pill} ${tab.isCenter ? styles.center : ""} ${tab.id === route ? styles.active : ""}`}
          onClick={() => navigate(tab.id === route ? "home" : tab.id)}
        >
          <span className={styles.label}>{tab.label}</span>
          {tab.sub && <small className={styles.sub}>{tab.sub}</small>}
        </button>
      ))}
    </nav>
  );
}
