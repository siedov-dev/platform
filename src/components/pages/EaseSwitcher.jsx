import { useState, useEffect } from "react";
import { EASINGS, DEFAULT_EASE, EASE_KEY, applyEase } from "./easings";

export default function EaseSwitcher() {
  const [ease, setEase] = useState(() => localStorage.getItem(EASE_KEY) || DEFAULT_EASE);

  useEffect(() => {
    applyEase(ease);
  }, [ease]);

  const onChange = (e) => {
    const v = e.target.value;
    setEase(v);
    localStorage.setItem(EASE_KEY, v);
  };

  const wrap = {
    position: "fixed",
    top: "10px",
    left: "10px",
    zIndex: 200,
    display: "flex",
    flexDirection: "column",
    gap: "4px",
    padding: "8px 10px",
    borderRadius: "10px",
    border: "1px solid #7a4a1f",
    background: "rgba(15, 9, 9, 0.9)",
    color: "#e8c66a",
    fontSize: "11px",
    fontWeight: 600,
  };
  const sel = {
    background: "#241407",
    color: "#e8c66a",
    border: "1px solid #7a4a1f",
    borderRadius: "6px",
    padding: "4px 6px",
    fontSize: "12px",
    maxWidth: "170px",
  };

  return (
    <div style={wrap}>
      <span>Кривая анимаций (тест)</span>
      <select value={ease} onChange={onChange} style={sel}>
        {Object.keys(EASINGS).map((k) => (
          <option key={k} value={k}>
            {k}
          </option>
        ))}
      </select>
    </div>
  );
}
