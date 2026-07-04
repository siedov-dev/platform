import { useI18n } from "@/i18n/I18nProvider";
import styles from "./StatusOverlay.module.css";

export default function StatusOverlay({ loading, error, onRetry }) {
  const { t } = useI18n();
  if (!loading && !error) return null;

  return (
    <div className={styles.overlay}>
      {error ? (
        <div className={styles.box}>
          <div className={styles.message}>{t("status.error")}</div>
          {onRetry ? (
            <button className={styles.retry} onClick={onRetry}>
              {t("status.retry")}
            </button>
          ) : null}
        </div>
      ) : (
        <div className={styles.spinner} role="status" aria-label={t("status.loading")} />
      )}
    </div>
  );
}
