import { useRef } from "react";
import styles from "./ParkBox.module.css";

const r2 = (v) => Math.round(v * 100) / 100;

export default function ParkBox({ box, edit, onChange, onSelect, hot, z = 6, label, interactive = false, children }) {
  const ref = useRef(null);

  const startMove = (e) => {
    if (!edit) return;
    e.preventDefault();
    e.stopPropagation();
    onSelect?.();
    const p = ref.current.parentElement.getBoundingClientRect();
    let lx = e.clientX;
    let ly = e.clientY;
    const move = (ev) => {
      const dx = ((ev.clientX - lx) / p.width) * 100;
      const dy = ((ev.clientY - ly) / p.height) * 100;
      lx = ev.clientX;
      ly = ev.clientY;
      onChange((b) => ({ ...b, x: r2(b.x + dx), y: r2(b.y + dy) }));
    };
    const up = () => {
      window.removeEventListener("pointermove", move);
      window.removeEventListener("pointerup", up);
    };
    window.addEventListener("pointermove", move);
    window.addEventListener("pointerup", up);
  };

  return (
    <div
      ref={ref}
      className={`${styles.box} ${edit ? styles.edit : ""} ${hot ? styles.hot : ""}`}
      onPointerDown={startMove}
      style={{
        left: `${box.x}%`,
        top: `${box.y}%`,
        width: `${box.w}%`,
        height: box.h === undefined ? undefined : `${box.h}%`,
        zIndex: z,
        pointerEvents: edit || interactive ? "auto" : "none",
      }}
    >
      {children}
      {edit && <span className={styles.tag}>{label}</span>}
    </div>
  );
}
