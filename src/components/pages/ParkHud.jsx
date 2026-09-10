import { useEffect, useLayoutEffect, useRef, useState } from "react";
import { useWallet } from "./parkWallet";
import styles from "./ParkHud.module.css";

const A = "/bonus-park/Ui_Icon_Bg_Up";

const CURRENCIES = [
  { key: "coins", icon: "Icn_Coin_Front.png", plus: false },
  { key: "tickets", icon: "Icn_Tiket.png", plus: false },
  { key: "cash", icon: "/bonus-park/Ui_Exchange_HUD/Icn_Currency.png", abs: true, plus: true },
];

const fmt = (n) => (n || 0).toLocaleString("ru-RU").replace(/ /g, " ");

function useCountUp(target) {
  const [shown, setShown] = useState(target);
  const from = useRef(target);
  const raf = useRef(0);

  useEffect(() => {
    const start = from.current;
    const diff = target - start;
    if (!diff) {
      setShown(target);
      return undefined;
    }
    const t0 = performance.now();
    const dur = Math.max(700, Math.min(1200, 780 + Math.abs(diff) * 0.7));
    const step = (t) => {
      const k = Math.min(1, (t - t0) / dur);
      const e = 1 - Math.pow(1 - k, 3);
      const v = Math.round(start + diff * e);
      setShown(v);
      from.current = v;
      if (k < 1) raf.current = requestAnimationFrame(step);
      else from.current = target;
    };
    raf.current = requestAnimationFrame(step);
    return () => cancelAnimationFrame(raf.current);
  }, [target]);

  return shown;
}

function ChipVal({ value, plus }) {
  const shown = useCountUp(value);
  const label = fmt(shown);
  const [pop, setPop] = useState(0);
  const [fit, setFit] = useState(1);
  const prev = useRef(value);
  const boxRef = useRef(null);
  const numRef = useRef(null);

  useEffect(() => {
    if (prev.current !== value) {
      prev.current = value;
      setPop((n) => n + 1);
    }
  }, [value]);

  useLayoutEffect(() => {
    const box = boxRef.current;
    const num = numRef.current;
    if (!box || !num) return undefined;
    const apply = () => {
      const cs = getComputedStyle(box);
      const avail = box.clientWidth - (parseFloat(cs.paddingLeft) || 0) - (parseFloat(cs.paddingRight) || 0);
      const need = num.scrollWidth;
      setFit(need > 0 && avail > 0 ? Math.min(1, avail / need) : 1);
    };
    apply();
    const ro = new ResizeObserver(apply);
    ro.observe(box);
    return () => ro.disconnect();
  }, [label.length]);

  return (
    <span
      ref={boxRef}
      key={pop}
      className={`${styles.chipVal} ${plus ? styles.chipValPlus : ""} ${pop ? styles.chipBump : ""}`}
    >
      <span
        ref={numRef}
        className={styles.chipNum}
        style={{ transform: fit < 1 ? `scale(${fit.toFixed(3)})` : undefined }}
      >
        {label}
      </span>
    </span>
  );
}

export default function ParkHud({ values = {}, tickets = true, coinProgress = 0.69, onProfile, onInfo, onPlus }) {
  const wallet = useWallet();
  const shown = wallet ? { coins: wallet.coins, tickets: wallet.tickets, cash: wallet.cash } : values;
  const list = tickets ? CURRENCIES : CURRENCIES.filter((c) => c.key !== "tickets");
  const dash = (281.49 * (1 - Math.max(0, Math.min(1, coinProgress)))).toFixed(2);

  return (
    <div className={styles.hud}>
      <button type="button" className={styles.avatar} onClick={onProfile}>
        <img className={styles.avatarRing} src={`${A}/Bg_Avatar_profile.png`} alt="" draggable={false} />
        <img className={styles.avatarMan} src={`${A}/Avarat_Grey.png`} alt="" draggable={false} />
        <img className={styles.avatarBadge} src={`${A}/Icn_Coin_Front.png`} alt="" draggable={false} />
      </button>

      {list.map((c) => (
        <div key={c.key} className={`${styles.chip} ${c.plus ? styles.chipWide : ""}`}>
          <span className={styles.chipBg}>
            <img className={styles.chipGloss} src={`${A}/Currency_Bg_Light.png`} alt="" draggable={false} />
          </span>
          {c.plus && (
            <button type="button" className={styles.plus} onClick={() => onPlus?.(c.key)}>
              <img className={styles.plusBg} src={`${A}/Btn_Plus.png`} alt="" draggable={false} />
              <img className={styles.plusIcn} src={`${A}/Icn_Plus.png`} alt="" draggable={false} />
            </button>
          )}
          {c.key === "coins" ? (
            <span className={styles.seal}>
              <svg className={styles.sealRing} viewBox="0 0 100 100" aria-hidden="true">
                <g
                  transform="rotate(-90 50 50)"
                  fill="none"
                  strokeWidth="6"
                  strokeLinecap="round"
                  strokeDasharray="281.49"
                  strokeDashoffset={dash}
                >
                  <circle className={styles.sealHalo} cx="50" cy="50" r="44.8" />
                  <circle className={styles.sealLine} cx="50" cy="50" r="44.8" />
                </g>
              </svg>
              <img
                ref={(el) => wallet?.setTarget(c.key, el)}
                className={styles.sealFace}
                src={`${A}/${c.icon}`}
                alt=""
                draggable={false}
              />
              <span className={styles.sealBorder} />
            </span>
          ) : (
            <img
              ref={(el) => wallet?.setTarget(c.key, el)}
              className={styles.chipIcon}
              src={c.abs ? c.icon : `${A}/${c.icon}`}
              alt=""
              draggable={false}
            />
          )}
          <ChipVal value={shown[c.key] || 0} plus={c.plus} />
        </div>
      ))}

      <button type="button" className={styles.info} onClick={onInfo}>
        <img src={`${A}/Btn_Info.png`} alt="" draggable={false} />
      </button>
    </div>
  );
}
