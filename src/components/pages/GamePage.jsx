import { useApp } from "@/context/AppContext";
import { useI18n } from "@/i18n/I18nProvider";
import styles from "./GamePage.module.css";

export default function GamePage() {
  const { activeGame, openGame, closeGame } = useApp();
  const { t } = useI18n();

  if (!activeGame) {
    return (
      <div className={styles.wrap}>
        <div className={styles.empty}>{t("status.empty")}</div>
      </div>
    );
  }

  return (
    <div className={styles.wrap}>
      <div className={styles.bar}>
        <button className={styles.close} onClick={closeGame}>‹ {t("game.lobby")}</button>
        <div className={styles.modes}>
          <button
            className={`${styles.mode} ${activeGame.mode === "real" ? styles.modeActive : ""}`}
            onClick={() => openGame(activeGame.gameId, "real")}
          >
            {t("game.real")}
          </button>
          <button
            className={`${styles.mode} ${activeGame.mode === "demo" ? styles.modeActive : ""}`}
            onClick={() => openGame(activeGame.gameId, "demo")}
          >
            {t("game.demo")}
          </button>
        </div>
      </div>

      <div className={styles.frame}>
        {activeGame.loading ? (
          <div className={styles.frameState}>
            <div className={styles.spinner} />
            <div>{t("game.launching")}</div>
          </div>
        ) : activeGame.error ? (
          <div className={styles.frameState}>
            <div className={styles.err}>{t("status.error")}</div>
            <button className={styles.retry} onClick={() => openGame(activeGame.gameId, activeGame.mode)}>{t("status.retry")}</button>
          </div>
        ) : (
          <div className={styles.stage}>
            <div className={styles.crest}>♛</div>
            <div className={styles.gameName}>{activeGame.name || activeGame.gameId}</div>
            <div className={styles.modeTag}>{activeGame.mode === "demo" ? t("game.demo") : t("game.real")}</div>
            <div className={styles.url}>{activeGame.url}</div>
          </div>
        )}
      </div>
    </div>
  );
}
