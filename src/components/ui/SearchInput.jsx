import { Search } from "./Icons";
import styles from "./SearchInput.module.css";

export default function SearchInput({ placeholder, value, onChange }) {
  return (
    <div className={styles.bar}>
      <Search />
      <input
        type="text"
        placeholder={placeholder}
        value={value || ""}
        onChange={(e) => onChange?.(e.target.value)}
      />
    </div>
  );
}
