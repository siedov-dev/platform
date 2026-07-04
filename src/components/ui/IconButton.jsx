import styles from "./IconButton.module.css";

export default function IconButton({ onClick, children, className = "", label, style }) {
  return (
    <button className={`${styles.btn} ${className}`} onClick={onClick} aria-label={label} style={style}>
      {children}
    </button>
  );
}
