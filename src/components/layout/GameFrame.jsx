import styles from "./GameFrame.module.css";

const CORNERS = ["tl", "tr", "bl", "br"];

export default function GameFrame() {
  return (
    <div className={styles.frame} aria-hidden="true">
      <span className={`${styles.bar} ${styles.top}`}>
        <span className={styles.weave} />
      </span>
      <span className={`${styles.bar} ${styles.bottom}`}>
        <span className={styles.weave} />
      </span>
      <span className={`${styles.bar} ${styles.left}`}>
        <span className={`${styles.weave} ${styles.weaveV}`} />
      </span>
      <span className={`${styles.bar} ${styles.right}`}>
        <span className={`${styles.weave} ${styles.weaveV}`} />
      </span>
      {CORNERS.map((c) => (
        <span key={c} className={`${styles.corner} ${styles[c]}`} />
      ))}
      <span className={styles.inner} />
    </div>
  );
}
