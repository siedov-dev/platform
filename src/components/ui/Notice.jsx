import { useEffect } from "react";
import { useApp } from "@/context/AppContext";
import styles from "./Notice.module.css";

export default function Notice() {
  const { notice, setNotice } = useApp();

  useEffect(() => {
    if (!notice) return;
    const id = setTimeout(() => setNotice(null), 3000);
    return () => clearTimeout(id);
  }, [notice, setNotice]);

  if (!notice) return null;

  return (
    <div className={`${styles.toast} ${notice.kind === "error" ? styles.error : styles.success}`} onClick={() => setNotice(null)}>
      {notice.text}
    </div>
  );
}
