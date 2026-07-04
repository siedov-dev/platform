import { useState, useEffect, useRef } from "react";
import { useApp } from "@/context/AppContext";
import styles from "./FavoriteStar.module.css";

export default function FavoriteStar({ gameId, size = 16 }) {
  const { favorites, toggleFavorite } = useApp();
  const isFav = favorites.has(gameId);
  const [animating, setAnimating] = useState(false);
  const prevFav = useRef(isFav);

  useEffect(() => {
    if (isFav && !prevFav.current) {
      setAnimating(true);
      const timer = setTimeout(() => setAnimating(false), 600);
      return () => clearTimeout(timer);
    }
    prevFav.current = isFav;
  }, [isFav]);

  const handleClick = (e) => {
    e.stopPropagation();
    toggleFavorite(gameId);
  };

  return (
    <button
      className={`${styles.star} ${isFav ? styles.active : ""} ${animating ? styles.ignite : ""}`}
      style={{ width: size, height: size }}
      onClick={handleClick}
    >
      ★
      {animating && (
        <>
          <span className={`${styles.spark} ${styles.s1}`} />
          <span className={`${styles.spark} ${styles.s2}`} />
          <span className={`${styles.spark} ${styles.s3}`} />
          <span className={`${styles.spark} ${styles.s4}`} />
          <span className={`${styles.spark} ${styles.s5}`} />
          <span className={`${styles.spark} ${styles.s6}`} />
          <span className={styles.ring} />
        </>
      )}
    </button>
  );
}
