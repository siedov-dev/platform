import styles from "./ParkPopupFrame.module.css";

const PBS = "/bonus-park/Piramid_Bg_Select";
const LINE = "/bonus-park/Pyramid_Game/Pyramid_Line.png";
const CROWN = `${PBS}/Crown_PopUp.png`;
const DECOR = `${PBS}/Decor_PopUp.png`;

export default function ParkPopupFrame({ title, timeLeft, children }) {
  return (
    <div className={styles.board}>
      <div className={styles.panel}>
        <img className={styles.crown} src={CROWN} alt="" draggable={false} />
        <div className={styles.inner}>
          {title ? (
            <>
              <div className={styles.title}>{title}</div>
              <div className={styles.lineWrap}>
                <img className={styles.line} src={LINE} alt="" draggable={false} />
              </div>
            </>
          ) : null}

          <div className={styles.body}>{children}</div>

          {timeLeft != null && (
            <div className={styles.timer}>
              <span className={styles.clock} />
              <span className={styles.timerVal}>{timeLeft} s</span>
            </div>
          )}
        </div>
        <img className={styles.decor} src={DECOR} alt="" draggable={false} />
      </div>
    </div>
  );
}
