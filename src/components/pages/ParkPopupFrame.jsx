import { useLayoutEffect, useRef } from "react";
import styles from "./ParkPopupFrame.module.css";
import { PARK_ASSET } from "./parkAssetBase";

const PBS = PARK_ASSET + "/Piramid_Bg_Select";
const LINE = PARK_ASSET + "/Pyramid_Game/Pyramid_Line.png";
const CROWN = `${PBS}/Crown_PopUp.png`;
const DECOR = `${PBS}/Decor_PopUp.png`;
const CAP = 44;

function GrowSkin({ grow }) {
  const skinRef = useRef(null);
  useLayoutEffect(() => {
    const skin = skinRef.current;
    const panel = skin && skin.parentElement;
    if (!panel || !skin) return undefined;
    const apply = () => {
      const ph = panel.offsetHeight;
      if (!ph) return;
      skin.style.setProperty("--ph", `${ph}px`);
      skin.style.setProperty("--sh-k", String(Math.max(0.05, (ph - grow) / ph)));
    };
    apply();
    let raf = requestAnimationFrame(() => {
      raf = requestAnimationFrame(() => {
        skin.dataset.ready = "1";
      });
    });
    const ro = new ResizeObserver(apply);
    ro.observe(panel);
    return () => {
      cancelAnimationFrame(raf);
      ro.disconnect();
    };
  }, [grow]);
  return (
    <div ref={skinRef} className={styles.skin} aria-hidden="true" style={{ "--cap": `${CAP}px`, "--grow": `${grow}px` }}>
      <div className={styles.skinShadow} />
      <div className={`${styles.skinPiece} ${styles.skinTop}`}>
        <div className={styles.skinFill} />
        <div className={styles.skinFull} />
      </div>
      <div className={`${styles.skinPiece} ${styles.skinMid}`}>
        <div className={styles.skinFill} />
        <div className={styles.skinFull} />
      </div>
      <div className={`${styles.skinPiece} ${styles.skinBot}`}>
        <div className={styles.skinFill} />
        <div className={styles.skinFull} />
      </div>
    </div>
  );
}

export default function ParkPopupFrame({ title, timeLeft, children, grow = 0, compact = false }) {
  const growing = grow > 0;
  const boardCls = [styles.board, growing ? styles.growBoard : "", growing && compact ? styles.growCompact : ""].join(" ");
  return (
    <div className={boardCls} style={growing ? { "--grow": `${grow}px` } : undefined}>
      <div className={`${styles.panel} ${growing ? styles.growPanel : ""}`}>
        {growing && <GrowSkin grow={grow} />}
        <img className={styles.crown} src={CROWN} alt="" draggable={false} />
        <div className={`${styles.inner} ${growing ? styles.growInner : ""}`}>
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
