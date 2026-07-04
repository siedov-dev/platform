import { useApp } from "@/context/AppContext";
import { useI18n } from "@/i18n/I18nProvider";
import IconButton from "@/components/ui/IconButton";
import { Close } from "@/components/ui/Icons";
import { MENU_ITEMS } from "@/constants";
import styles from "./Drawer.module.css";

export default function Drawer() {
  const { drawerOpen, setDrawerOpen } = useApp();
  const { t, lang, setLang, availableLanguages } = useI18n();

  const cycleLang = () => {
    const idx = availableLanguages.indexOf(lang);
    setLang(availableLanguages[(idx + 1) % availableLanguages.length]);
  };

  return (
    <>
      <div className={`${styles.scrim} ${drawerOpen ? styles.on : ""}`} onClick={() => setDrawerOpen(false)} />
      <div className={`${styles.drawer} ${drawerOpen ? styles.on : ""}`}>
        <div className={styles.header}>
          <div className={styles.title}>{t("menu.title")}</div>
          <IconButton onClick={() => setDrawerOpen(false)} style={{ width: 32, height: 32, borderRadius: 10 }}>
            <Close />
          </IconButton>
        </div>
        <div className={styles.avatars}>
          {["Z", "A", "M", "S"].map((l) => <div key={l} className={styles.avatar}>{l}</div>)}
          <div className={`${styles.avatar} ${styles.add}`}>+</div>
        </div>
        <div className={styles.menu}>
          {MENU_ITEMS.map((item) => (
            <div key={item.id} className={styles.menuItem}>
              <span className={styles.menuIcon}>{item.icon}</span>
              <span>{t(item.labelKey)}</span>
            </div>
          ))}
        </div>
        <div className={styles.footer}>
          <div className={styles.footerTitle}>{t("menu.followUs")}</div>
          <div className={styles.socials}>
            {["Tg", "In", "VK", "Yt", "Fb"].map((s) => <div key={s} className={styles.social}>{s}</div>)}
          </div>
          <div className={styles.lang} onClick={cycleLang}>
            🌐 <span>{t("menu.language")}</span> <span style={{ marginLeft: "auto" }}>›</span>
          </div>
        </div>
      </div>
    </>
  );
}
