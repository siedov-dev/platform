import { useEffect, useRef, useState } from "react";
import styles from "./PyramidGame.module.css";

const P = "/bonus-park/Piramid_Bg_Select";
const UI = "/bonus-park/Ui_Exchange_HUD";
const MONEY = `${UI}/Icn_Currency.png`;
const LINE = "/bonus-park/Pyramid_Game/Pyramid_Line.png";
const ARROW = "/bonus-park/Evnt_FW/Arrow_Evnt_Ferris%20wheel.png";
const PCROWN = `${P}/Crown_PopUp.png`;
const PDECOR = `${P}/Decor_PopUp.png`;

const PRIZES = [
  { id: "castle", img: "Castle.png", value: 100 },
  { id: "shot", img: "Shot_1000.png", value: 30 },
  { id: "burger", img: "Burger777_Icon.png", value: 15 },
  { id: "wheel", img: "Wheel.png", value: 12 },
  { id: "carousel", img: "Carousel.png", value: 10 },
];

const POOL = [0, 0, 1, 1, 1, 2, 2, 2, 2, 3, 3, 3, 3, 3, 4, 4, 4, 4, 4, 4];
const PICKS = 3;
const TIMER_SEC = 60;
const SPIN_BASE = 21;

const rollIndex = () => POOL[Math.floor(Math.random() * POOL.length)];

export default function PyramidGame({ onFinish }) {
  const boardRef = useRef(null);
  const [active, setActive] = useState(null);
  const [picks, setPicks] = useState(PICKS);
  const [rolling, setRolling] = useState(false);
  const [taken, setTaken] = useState(false);
  const [timeLeft, setTimeLeft] = useState(TIMER_SEC);
  const timers = useRef([]);
  const takenRef = useRef(false);
  const activeRef = useRef(null);

  useEffect(() => () => timers.current.forEach(clearTimeout), []);

  const finish = (idx) => {
    if (takenRef.current || idx == null) return;
    takenRef.current = true;
    setTaken(true);
    timers.current.push(setTimeout(() => onFinish?.(PRIZES[idx].value), 1100));
  };

  const take = () => {
    if (takenRef.current || rolling || activeRef.current == null) return;
    finish(activeRef.current);
  };

  const takeRef = useRef(take);
  takeRef.current = take;

  useEffect(() => {
    const iv = setInterval(() => {
      setTimeLeft((sec) => {
        if (sec <= 1) {
          clearInterval(iv);
          if (takenRef.current) return 0;
          setPicks(0);
          if (activeRef.current == null) {
            const idx = rollIndex();
            activeRef.current = idx;
            setActive(idx);
            finish(idx);
          } else {
            takeRef.current();
          }
          return 0;
        }
        return sec - 1;
      });
    }, 1000);
    return () => clearInterval(iv);
  }, []);

  const spin = () => {
    if (rolling || takenRef.current || picks <= 0) return;
    const lastPick = picks <= 1;
    setPicks((n) => n - 1);
    setRolling(true);
    const target = rollIndex();
    const start = activeRef.current == null ? 0 : (activeRef.current + 1) % PRIZES.length;
    const extra = (((target - (start + SPIN_BASE - 1)) % PRIZES.length) + PRIZES.length) % PRIZES.length;
    const total = SPIN_BASE + extra;
    let acc = 0;
    for (let i = 0; i < total; i++) {
      const idx = (start + i) % PRIZES.length;
      const last = i === total - 1;
      acc += 46 + 150 * Math.pow(i / total, 2.6);
      timers.current.push(
        setTimeout(() => {
          activeRef.current = idx;
          setActive(idx);
          if (last) {
            setRolling(false);
            if (lastPick) timers.current.push(setTimeout(() => finish(idx), 650));
          }
        }, acc)
      );
    }
  };

  const canTake = !rolling && !taken && active != null;

  return (
    <div ref={boardRef} className={styles.board}>
      <div className={styles.panel}>
        <img className={styles.crown} src={PCROWN} alt="" draggable={false} />
        <div className={styles.inner}>
          <div className={styles.title}>Name Event</div>

          <div className={styles.rows}>
            <img className={styles.titleLine} src={LINE} alt="" draggable={false} />
            {PRIZES.map((p, i) => {
              const on = active === i;
              return (
                <div key={p.id} className={`${styles.row} ${on ? styles.rowOn : ""}`}>
                  <span className={styles.band} />
                  <img className={styles.arrowL} src={ARROW} alt="" draggable={false} />
                  <img className={styles.arrowR} src={ARROW} alt="" draggable={false} />
                  <span className={styles.icnWrap}>
                    <img className={styles.flash} src={`${P}/Flash_Light_blue_2.png`} alt="" draggable={false} />
                    <img className={styles.icn} src={`${P}/${p.img}`} alt="" draggable={false} />
                  </span>
                  <span className={styles.val}>
                    <img className={styles.money} src={MONEY} alt="" draggable={false} />
                    <span className={styles.num}>{p.value}</span>
                  </span>
                  <img className={styles.line} src={LINE} alt="" draggable={false} />
                </div>
              );
            })}
          </div>

          <div className={styles.timer}>
            <span className={styles.clock} />
            <span className={styles.timerVal}>{timeLeft} s</span>
          </div>

          <div className={styles.btns}>
            <button
              type="button"
              className={`${styles.take} ${canTake ? styles.takeOn : ""}`}
              onClick={take}
            >
              Take
            </button>
            <button
              type="button"
              className={`${styles.choise} ${picks <= 0 || rolling || taken ? styles.choiseOff : ""}`}
              onClick={spin}
            >
              <span className={styles.choiseTxt}>Choise {picks}/{PICKS}</span>
            </button>
          </div>
        </div>
        <img className={styles.decor} src={PDECOR} alt="" draggable={false} />
      </div>

      {taken && (
        <div className={styles.takenFx}>
          <img className={styles.takenMoney} src={MONEY} alt="" draggable={false} />
          <span className={styles.takenNum}>+{active == null ? 0 : PRIZES[active].value}</span>
        </div>
      )}
    </div>
  );
}
