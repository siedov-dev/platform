import ParkPopupFrame from "./ParkPopupFrame";
import styles from "./SafePopup.module.css";

const MONEY = "/bonus-park/Ui_Exchange_HUD/Icn_Currency.png";

export default function SafePopup({ value = 0, onClaim }) {
  return (
    <ParkPopupFrame title="Safe">
      <div className={styles.wrap}>
        <img className={styles.money} src={MONEY} alt="" draggable={false} />
        <span className={styles.val}>{value}</span>
      </div>
      <button type="button" className={styles.take} onClick={onClaim}>
        Take
      </button>
    </ParkPopupFrame>
  );
}
