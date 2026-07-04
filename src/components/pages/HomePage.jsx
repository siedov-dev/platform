import { useApp } from "@/context/AppContext";
import { useI18n } from "@/i18n/I18nProvider";
import PopularCard from "@/components/ui/PopularCard";
import mainBg from "@/assets/main-bg.jpg";
import styles from "./HomePage.module.css";

export default function HomePage() {
  const { popularGames, navigate } = useApp();
  const { t } = useI18n();

  return (
    <div className={styles.page}>
      <div className={styles.bg} style={{ backgroundImage: `url(${mainBg})` }} />
      <div className={styles.strip}>
        <div className={styles.stripTitle}><span>{t("home.popular")}</span></div>
        <div className={styles.rail}>
          {popularGames.map((g) => (
            <PopularCard key={g.id} game={g} onClick={() => navigate("games")} />
          ))}
        </div>
      </div>
    </div>
  );
}
