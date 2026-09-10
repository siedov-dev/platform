import ParkPopupFrame from "./ParkPopupFrame";
import styles from "./CashWin.module.css";

const MONEY = "/bonus-park/Ui_Exchange_HUD/Icn_Currency.png";

export default function CashWin({ value = 0 }) {
  return (
    <ParkPopupFrame>
      <div className={styles.wrap}>
        <img className={styles.money} src={MONEY} alt="" draggable={false} />
        <span className={styles.val}>{value}</span>
      </div>
    </ParkPopupFrame>
  );
}
