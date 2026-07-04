import { useApp } from "@/context/AppContext";
import { useI18n } from "@/i18n/I18nProvider";
import styles from "./PromotionsPage.module.css";

export default function PromotionsPage() {
  const { promotions, activatePromo } = useApp();
  const { t } = useI18n();

  return (
    <>
      <div className={styles.bg} />
      <div className={styles.scroll}>
        {promotions.map((p) => (
          <div key={p.id} className={styles.card}>
            <div className={styles.deco} />
            <span className={styles.badge}>{p.badge}</span>
            <h3 className={styles.title}>{p.title}</h3>
            <p className={styles.desc}>{p.description}</p>
            <button className={styles.cta} onClick={() => activatePromo(p.id)}>{t(p.ctaKey)}</button>
          </div>
        ))}
      </div>
    </>
  );
}
