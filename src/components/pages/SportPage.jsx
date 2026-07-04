import { useState, useEffect } from "react";
import { useApp } from "@/context/AppContext";
import { useI18n } from "@/i18n/I18nProvider";
import { SPORTS_TABS } from "@/constants";
import styles from "./SportPage.module.css";

export default function SportPage() {
  const [activeSport, setActiveSport] = useState("soccer");
  const [stake, setStake] = useState("");
  const { matches, matchesLoading, loadMatches, betSlip, addSelection, removeSelection, clearSlip, placeBet } = useApp();
  const { t } = useI18n();

  useEffect(() => {
    loadMatches(activeSport);
  }, [activeSport, loadMatches]);

  const isSelected = (matchId, type) => betSlip.some((s) => s.matchId === matchId && s.type === type);

  const onPick = (m, type, odds) => {
    if (isSelected(m.id, type)) {
      removeSelection(m.id);
    } else {
      addSelection({ matchId: m.id, type, odds, label: `${m.teamA} - ${m.teamB}` });
    }
  };

  const onPlace = async () => {
    const value = Number(stake);
    if (!value || value <= 0) return;
    const res = await placeBet(value);
    if (res) setStake("");
  };

  return (
    <>
      <div className={styles.bg} />
      <div className={styles.scroll}>
        <div className={styles.tabs}>
          {SPORTS_TABS.map((tab) => (
            <button key={tab.id} className={`${styles.tab} ${tab.id === activeSport ? styles.tabActive : ""}`} onClick={() => setActiveSport(tab.id)}>
              <span>{tab.icon}</span><span>{tab.name}</span>
            </button>
          ))}
        </div>
        <div className={styles.sectionTitle}>{t("sport.topMatches")}</div>

        {matchesLoading ? (
          <div className={styles.state}>{t("status.loading")}</div>
        ) : matches.length === 0 ? (
          <div className={styles.state}>{t("status.empty")}</div>
        ) : (
          matches.map((m) => (
            <div key={m.id} className={styles.match}>
              <div className={styles.league}>{m.league}</div>
              <div className={styles.teams}>
                <div className={styles.team}><div className={styles.crest} /><div className={styles.teamName}>{m.teamA}</div></div>
                <div className={styles.vs}>VS</div>
                <div className={styles.team}><div className={styles.crest} /><div className={styles.teamName}>{m.teamB}</div></div>
              </div>
              <div className={styles.odds}>
                {[{ l: "1", v: m.odds.home }, { l: "X", v: m.odds.draw }, { l: "2", v: m.odds.away }]
                  .filter((o) => o.v > 0)
                  .map((o) => (
                    <button
                      key={o.l}
                      className={`${styles.odd} ${isSelected(m.id, o.l) ? styles.oddActive : ""}`}
                      onClick={() => onPick(m, o.l, o.v)}
                    >
                      <span>{o.l}</span><b>{o.v.toFixed(2)}</b>
                    </button>
                  ))}
              </div>
            </div>
          ))
        )}
      </div>

      {betSlip.length > 0 && (
        <div className={styles.slip}>
          <div className={styles.slipHead}>
            <span>{t("sport.betSlip")} ({betSlip.length})</span>
            <button className={styles.slipClear} onClick={clearSlip}>{t("sport.clear")}</button>
          </div>
          <div className={styles.slipRows}>
            {betSlip.map((s) => (
              <div key={s.matchId} className={styles.slipRow}>
                <span className={styles.slipLabel}>{s.label}</span>
                <span className={styles.slipPick}>{s.type} · {s.odds.toFixed(2)}</span>
                <button className={styles.slipDel} onClick={() => removeSelection(s.matchId)}>×</button>
              </div>
            ))}
          </div>
          <div className={styles.slipFooter}>
            <input
              className={styles.stakeInput}
              type="number"
              inputMode="decimal"
              placeholder={t("sport.stake")}
              value={stake}
              onChange={(e) => setStake(e.target.value)}
            />
            <button className={styles.placeBtn} onClick={onPlace}>{t("sport.place")}</button>
          </div>
        </div>
      )}
    </>
  );
}
