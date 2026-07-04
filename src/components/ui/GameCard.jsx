import FavoriteStar from "./FavoriteStar";
import styles from "./GameCard.module.css";

export default function GameCard({ game, onClick }) {
  return (
    <div className={styles.card} onClick={() => onClick?.(game)}>
      <div className={styles.bg} data-name={game.name} style={{ "--c1": game.colors[0], "--c2": game.colors[1] }} />
      <FavoriteStar gameId={game.id} size={16} />
    </div>
  );
}
