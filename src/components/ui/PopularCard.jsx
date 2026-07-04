import FavoriteStar from "./FavoriteStar";
import styles from "./PopularCard.module.css";

export default function PopularCard({ game, onClick }) {
  return (
    <div className={styles.card} style={{ "--c1": game.colors[0], "--c2": game.colors[1] }} onClick={() => onClick?.(game)}>
      <FavoriteStar gameId={game.id} size={14} />
      <div className={styles.label}>{game.name}</div>
    </div>
  );
}
