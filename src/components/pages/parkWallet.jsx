import { createContext, useCallback, useContext, useEffect, useRef, useState } from "react";
import { createPortal } from "react-dom";
import styles from "./parkWallet.module.css";

const KEY = "bonuspark.wallet1";
const START = { coins: 100, tickets: 999, cash: 999 };
const FLY_MS = 700;
const STEP_MS = 52;
const CURVE_PTS = 16;

const ICON = {
  coins: "/bonus-park/Ui_Icon_Bg_Up/Icn_Coin_Front.png",
  tickets: "/bonus-park/Ui_Icon_Bg_Up/Icn_Tiket.png",
  cash: "/bonus-park/Ui_Exchange_HUD/Icn_Currency.png",
};

const load = () => {
  try {
    const raw = JSON.parse(localStorage.getItem(KEY) || "null");
    if (raw && typeof raw === "object") return { ...START, ...raw };
  } catch (e) {
    return { ...START };
  }
  return { ...START };
};

const Ctx = createContext(null);

export const useWallet = () => useContext(Ctx);

export const centerOf = (sel) => {
  const el = typeof sel === "string" ? document.querySelector(sel) : sel;
  if (!el) return null;
  const r = el.getBoundingClientRect();
  if (!r.width && !r.height) return null;
  return { x: r.left + r.width / 2, y: r.top + r.height / 2 };
};

function flyFrames(from, to, spread, spin) {
  const cx = (from.x + to.x) / 2 + spread.x;
  const cy = Math.min(from.y, to.y) - 60 - Math.abs(to.x - from.x) * 0.12 + spread.y;
  const frames = [];
  for (let i = 0; i <= CURVE_PTS; i += 1) {
    const t = i / CURVE_PTS;
    const e = t * t * (3 - 2 * t);
    const u = 1 - e;
    const x = u * u * from.x + 2 * u * e * cx + e * e * to.x;
    const y = u * u * from.y + 2 * u * e * cy + e * e * to.y;
    const grow = t < 0.2 ? 0.34 + (t / 0.2) * 0.86 : 1.2 - 0.86 * Math.pow((t - 0.2) / 0.8, 1.7);
    const op = t < 0.1 ? t / 0.1 : t > 0.88 ? Math.max(0, (1 - t) / 0.12) : 1;
    frames.push({
      transform: `translate(${x.toFixed(1)}px, ${y.toFixed(1)}px) translate(-50%, -50%) scale(${grow.toFixed(3)}) rotate(${(spin * e).toFixed(1)}deg)`,
      opacity: op.toFixed(2),
      offset: t,
    });
  }
  return frames;
}

function Flyer({ item }) {
  const ref = useRef(null);

  useEffect(() => {
    const el = ref.current;
    if (!el || typeof el.animate !== "function") return undefined;
    const { from, to, delay, spread, spin, dur } = item;
    const anim = el.animate(flyFrames(from, to, spread, spin), {
      duration: dur,
      delay,
      easing: "linear",
      fill: "both",
    });
    return () => anim.cancel();
  }, [item]);

  return (
    <span ref={ref} className={styles.flyer}>
      <span className={styles.flyGlow} />
      <img className={styles.flyIcon} src={ICON[item.kind]} alt="" draggable={false} />
    </span>
  );
}

function HitPulse({ item }) {
  return <span className={styles.hit} style={{ left: `${item.to.x}px`, top: `${item.to.y}px` }} />;
}

export function WalletProvider({ children }) {
  const [val, setVal] = useState(load);
  const valRef = useRef(val);
  const targets = useRef({});
  const seq = useRef(0);
  const timers = useRef([]);
  const [flights, setFlights] = useState([]);
  const [pulses, setPulses] = useState([]);

  valRef.current = val;

  useEffect(() => {
    try {
      localStorage.setItem(KEY, JSON.stringify(val));
    } catch (e) {
      return;
    }
  }, [val]);

  useEffect(() => () => timers.current.forEach(clearTimeout), []);

  const setTarget = useCallback((kind, el) => {
    targets.current[kind] = el;
  }, []);

  const add = useCallback((kind, n) => {
    setVal((v) => ({ ...v, [kind]: Math.max(0, (v[kind] || 0) + n) }));
  }, []);

  const spend = useCallback((kind, n) => {
    if ((valRef.current[kind] || 0) < n) return false;
    const next = { ...valRef.current, [kind]: valRef.current[kind] - n };
    valRef.current = next;
    setVal(next);
    return true;
  }, []);

  const award = useCallback(
    (kind, n, from) => {
      const amount = Math.round(n || 0);
      if (amount <= 0) return;
      const to = centerOf(targets.current[kind]);
      if (!to || !from) {
        add(kind, amount);
        return;
      }
      const count = amount < 3 ? amount : Math.max(2, Math.min(7, Math.round(Math.log2(amount + 1))));
      const id = seq.current + 1;
      seq.current = id;
      const items = Array.from({ length: count }, (_, i) => {
        const k = count > 1 ? i / (count - 1) - 0.5 : 0;
        return {
          id: `f${id}n${i}`,
          group: `f${id}n`,
          kind,
          from,
          to,
          delay: Math.round(i * STEP_MS),
          dur: FLY_MS + i * 26,
          spin: (i % 2 ? 1 : -1) * (18 + i * 9),
          spread: { x: k * 92, y: Math.abs(k) * 34 - 14 },
        };
      });
      setFlights((f) => [...f, ...items]);
      add(kind, amount);
      items.forEach((it, i) => {
        timers.current.push(
          setTimeout(() => {
            const pid = `p${id}n${i}`;
            setPulses((ps) => [...ps, { id: pid, to }]);
            timers.current.push(setTimeout(() => setPulses((ps) => ps.filter((x) => x.id !== pid)), 420));
          }, it.delay + it.dur - 90)
        );
      });
      timers.current.push(
        setTimeout(() => {
          setFlights((f) => f.filter((x) => x.group !== `f${id}n`));
        }, (count - 1) * STEP_MS + FLY_MS + (count - 1) * 26 + 40)
      );
    },
    [add]
  );

  const api = { ...val, spend, award, add, setTarget };

  return (
    <Ctx.Provider value={api}>
      {children}
      {(flights.length > 0 || pulses.length > 0) &&
        createPortal(
          <div className={styles.flyLayer}>
            {pulses.map((it) => (
              <HitPulse key={it.id} item={it} />
            ))}
            {flights.map((it) => (
              <Flyer key={it.id} item={it} />
            ))}
          </div>,
          document.body
        )}
    </Ctx.Provider>
  );
}
