import { useEffect, useRef, useState } from "react";
import { useServerGame } from "./parkSocket";
import { useWallet } from "./parkWallet";
import styles from "./PyramidGame.module.css";
import { PARK_ASSET, PARK_DESK } from "./parkAssetBase";

const HD = PARK_DESK ? "_hd" : "";
const P = PARK_ASSET + "/Piramid_Bg_Select" + HD;
const UI = PARK_ASSET + "/Ui_Exchange_HUD";
const MONEY = `${UI}/Icn_Currency.png`;
const LINE = PARK_ASSET + "/Pyramid_Game" + HD + "/Pyramid_Line.png";
const ARROW = PARK_ASSET + "/Evnt_FW" + HD + "/Arrow_Evnt_Ferris%20wheel.png";
const PCROWN = `${P}/Crown_PopUp.png`;
const PDECOR = `${P}/Decor_PopUp.png`;

const PRIZES = [
  { id: "castle", img: "Castle.png", value: 100 },
  { id: "shot", img: "Shot_1000.png", value: 30 },
  { id: "burger", img: "Burger777_Icon.png", value: 15 },
  { id: "wheel", img: "Wheel.png", value: 12 },
  { id: "carousel", img: "Carousel.png", value: 10 },
];

const POOL = [0, 0, 1, 1, 1, 2, 2, 2, 2, 3, 3, 3, 3, 3, 3, 4, 4, 4, 4, 4];
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
  const sg = useServerGame();
  const wallet = useWallet();
  const startRef = useRef(null);
  const busyRef = useRef(false);

  useEffect(() => {
    if (!sg || startRef.current) return;
    startRef.current = sg
      .start("pyramid")
      .then((r) => r.game_id || null)
      .catch(() => null);
  }, [sg]);

  const serverStep = async (step) => {
    if (!sg) return null;
    const gid = startRef.current ? await startRef.current : null;
    if (!gid) return null;
    try {
      const r = await sg.play(gid, { step });
      if (r && typeof r.cash === "number") wallet?.applyTruth({ cash: r.cash });
      if (r && r.done && r.prize > 0) wallet?.hold("cash", r.prize);
      return r;
    } catch (e) {
      return null;
    }
  };

  useEffect(() => () => timers.current.forEach(clearTimeout), []);

  const finish = (idx, value) => {
    if (takenRef.current || (idx == null && value == null)) return;
    takenRef.current = true;
    setTaken(true);
    const v = typeof value === "number" ? value : PRIZES[idx].value;
    timers.current.push(setTimeout(() => onFinish?.(v), 1100));
  };

  const take = () => {
    if (takenRef.current || rolling || activeRef.current == null || busyRef.current) return;
    if (sg) {
      busyRef.current = true;
      serverStep("take").then((r) => {
        busyRef.current = false;
        finish(activeRef.current, r && typeof r.prize === "number" ? r.prize : undefined);
      });
      return;
    }
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
            if (sg) {
              serverStep("roll").then((r) => {
                const at = r && r.item ? PRIZES.findIndex((pz) => pz.id === r.item) : -1;
                const idx = at >= 0 ? at : rollIndex();
                activeRef.current = idx;
                setActive(idx);
                finish(idx, r && typeof r.prize === "number" ? r.prize : undefined);
              });
              return 0;
            }
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
    if (rolling || takenRef.current || picks <= 0 || busyRef.current) return;
    if (sg) {
      busyRef.current = true;
      setRolling(true);
      serverStep("roll").then((r) => {
        busyRef.current = false;
        const idx = r && r.item ? PRIZES.findIndex((p) => p.id === r.item) : -1;
        const prize = r && r.done && typeof r.prize === "number" ? r.prize : null;
        if (r && typeof r.rolls_left === "number") setPicks(r.rolls_left);
        else setPicks((n) => n - 1);
        runSpin(idx >= 0 ? idx : rollIndex(), !!(r && r.done), prize);
      });
      return;
    }
    const lastPick = picks <= 1;
    setPicks((n) => n - 1);
    setRolling(true);
    runSpin(rollIndex(), lastPick, null);
  };

  const runSpin = (target, lastPick, prize) => {
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
            if (lastPick) timers.current.push(setTimeout(() => finish(idx, prize == null ? undefined : prize), 650));
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
