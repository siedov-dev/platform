import { useState, useEffect } from "react";
import { useApp } from "@/context/AppContext";
import { useI18n } from "@/i18n/I18nProvider";
import SearchInput from "@/components/ui/SearchInput";
import GameCard from "@/components/ui/GameCard";
import IconButton from "@/components/ui/IconButton";
import BottomNav from "@/components/layout/BottomNav";
import { Close } from "@/components/ui/Icons";
import styles from "./GamesPage.module.css";

export default function GamesPage() {
  const [providerId, setProviderId] = useState("all");
  const [categoryId, setCategoryId] = useState("popular");
  const [sheetOpen, setSheetOpen] = useState(false);
  const [provSearch, setProvSearch] = useState("");
  const [gameSearch, setGameSearch] = useState("");

  const { providers, categories, games, gamesLoading, loadGames, openGame } = useApp();
  const { t } = useI18n();

  const currentProv = providers.find((p) => p.id === providerId);
  const visCats = categories.filter((c) => !currentProv?.categories || currentProv.categories.includes(c.id));

  useEffect(() => {
    if (visCats.length && !visCats.find((c) => c.id === categoryId)) setCategoryId(visCats[0].id);
  }, [providerId]);

  useEffect(() => {
    const id = setTimeout(() => {
      loadGames({ provider: providerId, category: categoryId, q: gameSearch });
    }, 300);
    return () => clearTimeout(id);
  }, [providerId, categoryId, gameSearch, loadGames]);

  const filtProv = providers.filter((p) => p.name.toLowerCase().includes(provSearch.toLowerCase()));

  return (
    <>
      <div className={styles.pageBg} />
      <div className={styles.header}>
        <SearchInput placeholder={t("games.search")} value={gameSearch} onChange={setGameSearch} />
        <button className={styles.provBtn} onClick={() => setSheetOpen(true)}>
          <span>{t("games.provider")}</span>
          <span><span className={styles.provVal}>{currentProv?.name || "All"}</span><span className={styles.chevron}>›</span></span>
        </button>
      </div>

      <div className={styles.layout}>
        <div className={styles.sidebar}>
          {visCats.map((cat) => (
            <button key={cat.id} className={`${styles.catBtn} ${cat.id === categoryId ? styles.catActive : ""}`} onClick={() => setCategoryId(cat.id)}>
              <span className={styles.catIcon}>{cat.icon}</span>
              <span>{t(cat.nameKey)}</span>
            </button>
          ))}
        </div>
        <div className={styles.gridWrap}>
          {gamesLoading ? (
            <div className={styles.gridState}>
              <div className={styles.gridSpinner} role="status" aria-label={t("status.loading")} />
            </div>
          ) : games.length === 0 ? (
            <div className={styles.gridState}>{t("status.empty")}</div>
          ) : (
            <div className={styles.grid}>
              {games.map((g) => <GameCard key={g.id} game={g} onClick={(game) => openGame(game.id)} />)}
            </div>
          )}
        </div>
      </div>

      <div className={`${styles.sheet} ${sheetOpen ? styles.sheetOn : ""}`}>
        <div className={styles.sheetHead}>
          <div className={styles.sheetTitle}>{t("games.provider")}</div>
          <IconButton onClick={() => setSheetOpen(false)} style={{ filter: "none", color: "#cbb38a" }}><Close /></IconButton>
        </div>
        <div className={styles.sheetSearch}>
          <SearchInput placeholder={t("games.providerSearch")} value={provSearch} onChange={setProvSearch} />
        </div>
        <div className={styles.sheetList}>
          {filtProv.map((p) => (
            <button key={p.id} className={`${styles.provRow} ${p.id === providerId ? styles.provActive : ""}`} onClick={() => { setProviderId(p.id); setSheetOpen(false); }}>
              <div className={styles.provLogo}>{p.id === "all" ? "★" : p.name[0]}</div>
              <div>
                <div className={styles.provName}>{p.name}</div>
                <div className={styles.provCount}>{p.gameCount} games</div>
              </div>
            </button>
          ))}
        </div>
        <BottomNav />
      </div>
    </>
  );
}
