import { useApp } from "@/context/AppContext";
import { useI18n } from "@/i18n/I18nProvider";
import { Chat, Telegram, Mail } from "@/components/ui/Icons";
import styles from "./SupportPopup.module.css";

export default function SupportPopup() {
  const { supportOpen, setSupportOpen, sendEvent } = useApp();
  const { t } = useI18n();

  const channels = [
    { id: "chat", icon: <Chat />, labelKey: "support.chat" },
    { id: "tg", icon: <Telegram />, labelKey: "support.telegram" },
    { id: "email", icon: <Mail />, labelKey: "support.email" },
  ];

  return (
    <>
      <div className={`${styles.scrim} ${supportOpen ? styles.on : ""}`} onClick={() => setSupportOpen(false)} />
      <div className={`${styles.popup} ${supportOpen ? styles.on : ""}`}>
        <div className={styles.row}>
          {channels.map((ch) => (
            <button key={ch.id} className={styles.btn} onClick={() => sendEvent("SUPPORT_OPEN", { channel: ch.id })}>
              <div className={styles.icon}>{ch.icon}</div>
              {t(ch.labelKey)}
            </button>
          ))}
        </div>
      </div>
    </>
  );
}
