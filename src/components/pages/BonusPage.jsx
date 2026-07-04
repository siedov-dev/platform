import { useEffect } from "react";
import { useApp } from "@/context/AppContext";
import { useI18n } from "@/i18n/I18nProvider";
import IconButton from "@/components/ui/IconButton";
import { CloseRing } from "@/components/ui/Icons";
import bonusBg from "@/assets/bonus-park.jpg";
import pirateCoin from "@/assets/pirate-coin.png";
import vipProfile from "@/assets/vip-profile.png";
import styles from "./BonusPage.module.css";

export default function BonusPage() {
  const { user, balance, navigate, userBonuses, bonusesLoading, loadUserBonuses, activateBonus, cancelBonus } = useApp();
  const { t } = useI18n();

  useEffect(() => {
    loadUserBonuses();
  }, [loadUserBonuses]);

  return (
    <>
      <div className={styles.bg} style={{ backgroundImage: `url(${bonusBg})` }} />

      <div className={styles.pirate}>
        <img src={pirateCoin} alt="" />
      </div>

      <div className={styles.vipCard}>
        <div className={styles.vipBody}>
          <div className={styles.vipAvatar}>
            <img src={vipProfile} alt="" />
            <span className={styles.crown}>♛</span>
            <span className={styles.level}>{user?.vipLevel || "KING"}</span>
          </div>
          <div className={styles.hi}>{t("bonus.welcome")}</div>
          <div className={styles.name}>{user?.username || "Player"}</div>
          <div className={styles.id}>id-{user?.id || "000000"}</div>
        </div>
        <button className={styles.exchange}>{t("bonus.exchange")}</button>
      </div>

      <div className={styles.coins}>
        <div className={`${styles.pill} ${styles.gold}`}><div className={styles.circle}>$</div>{balance.coins}</div>
        <div className={`${styles.pill} ${styles.silver}`}><div className={styles.circle}>$</div>{balance.bonus}</div>
      </div>

      <div className={styles.topbar}>
        <span className={styles.spacer} />
        <IconButton className={styles.closeRing} onClick={() => navigate("home")} label="close"><CloseRing /></IconButton>
      </div>

      <div className={styles.list}>
        <div className={styles.listTitle}>{t("bonus.myBonuses")}</div>
        {bonusesLoading ? (
          <div className={styles.listState}>{t("status.loading")}</div>
        ) : userBonuses.length === 0 ? (
          <div className={styles.listState}>{t("status.empty")}</div>
        ) : (
          userBonuses.map((b) => (
            <div key={b.id} className={styles.bonusCard}>
              <div className={styles.bonusHead}>
                <span className={styles.bonusName}>{b.name}</span>
                <span className={styles.bonusStatus}>{b.statusName}</span>
              </div>
              <div className={styles.bonusDesc}>{b.description}</div>
              <div className={styles.bonusMeta}>
                <span className={styles.bonusBalance}>{b.balanceString} {b.balanceName}</span>
                {b.progressPercents > 0 && (
                  <div className={styles.progress}><span style={{ width: `${b.progressPercents}%` }} /></div>
                )}
              </div>
              <div className={styles.bonusActions}>
                {b.isPending && (
                  <button className={styles.activate} onClick={() => activateBonus(b.id)}>{t("bonus.activate")}</button>
                )}
                {b.isCancelable && (
                  <button className={styles.cancel} onClick={() => cancelBonus(b.id)}>{t("bonus.cancel")}</button>
                )}
              </div>
            </div>
          ))
        )}
      </div>
    </>
  );
}
