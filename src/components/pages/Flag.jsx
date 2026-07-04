import styles from "./Flag.module.css";

const ASSET = "/bonus-park";

export default function Flag({ style, scale = 1, rot = 0, flip = false, onPointerDown, dur = 5.5, delay = 0 }) {
  return (
    <div
      className={styles.flag}
      style={{ transform: `scale(${scale}) rotate(${rot}deg)${flip ? " scaleX(-1)" : ""}`, ...style }}
      onPointerDown={onPointerDown}
    >
      <img
        className={styles.flagImg}
        src={`${ASSET}/Flags.png`}
        alt=""
        style={{ animationDuration: `${dur}s`, animationDelay: `${delay}s` }}
      />
    </div>
  );
}
