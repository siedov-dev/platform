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

const POPUP_ARM_MS = 600;
const REROLL_ARM_MS = 650;

const PRIZES = [
  { id: "castle", img: "Castle.png", value: 100 },
  { id: "shot", img: "Shot_1000.png", value: 30 },
  { id: "burger", img: "Burger777_Icon.png", value: 15 },
  { id: "wheel", img: "Wheel.png", value: 12 },
  { id: "carousel", img: "Carousel.png", value: 10 },
];

const POOL = [0, 0, 1, 1, 1, 2, 2, 2, 2, 3, 3, 3, 3, 3, 3, 4, 4, 4, 4, 4];
const PICKS = 3;
const TIMER_SEC = 10;
const SPIN_BASE = 21;

const rollIndex = () => POOL[Math.floor(Math.random() * POOL.length)];
const FATAL = new Set(["game_timeout", "game_not_active", "game_not_found"]);

export default function PyramidGame({ onFinish }) {
  const boardRef = useRef(null);
  const [active, setActive] = useState(null);
  const [picks, setPicks] = useState(PICKS);
  const [rolling, setRolling] = useState(false);
  const [taken, setTaken] = useState(false);
  const [locked, setLocked] = useState(true);
  const [takenValue, setTakenValue] = useState(null);
  const [timeLeft, setTimeLeft] = useState(TIMER_SEC);
  const [timerOn, setTimerOn] = useState(true);
  const limitRef = useRef(TIMER_SEC);
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
      .then((r) => {
        if (r && typeof r.timeout === "number" && r.timeout > 0 && r.timeout < TIMER_SEC) {
          limitRef.current = r.timeout;
          setTimeLeft(r.timeout);
        }
        return r.game_id || null;
      })
      .catch(() => null);
  }, [sg]);

  const serverStep = async (step) => {
    if (!sg) return null;
    const gid = startRef.current ? await startRef.current : null;
    if (!gid) return { fail: "no_game" };
    try {
      const r = await sg.play(gid, { step });
      if (r && typeof r.cash === "number") wallet?.applyTruth({ cash: r.cash });
      if (r && r.done && r.prize > 0) wallet?.hold("cash", r.prize);
      return r;
    } catch (e) {
      return { fail: (e && e.code) || "error" };
    }
  };

  const closeNoPrize = (why) => {
    if (takenRef.current) return;
    takenRef.current = true;
    setTimerOn(false);
    console.warn("[park] пирамида: сервер не дал результат, закрываю без приза:", why);
    timers.current.push(setTimeout(() => onFinish?.(null), 250));
  };

  const serverFail = (r, what) => {
    if (!r || !r.fail) return false;
    if (r.fail === "no_game" || FATAL.has(r.fail)) {
      closeNoPrize(r.fail);
      return true;
    }
    console.warn(`[park] пирамида: сервер не ответил на ${what}, можно нажать снова:`, r.fail);
    return true;
  };

  useEffect(() => () => timers.current.forEach(clearTimeout), []);

  const armedRef = useRef(false);
  useEffect(() => {
    const t = setTimeout(() => {
      armedRef.current = true;
      setLocked(false);
    }, POPUP_ARM_MS);
    return () => clearTimeout(t);
  }, []);

  const finish = (idx, value) => {
    if (takenRef.current || (idx == null && value == null)) return;
    takenRef.current = true;
    setTaken(true);
    const v = typeof value === "number" ? value : PRIZES[idx].value;
    setTakenValue(v);
    if (typeof value === "number" && idx != null && value !== PRIZES[idx].value) console.warn("[park] пирамида: сервер прислал prize", value, "а на ячейке", PRIZES[idx].value);
    timers.current.push(setTimeout(() => onFinish?.(v), 1100));
  };

  const take = () => {
    if (!armedRef.current || takenRef.current || rolling || activeRef.current == null || busyRef.current) return;
    if (sg) {
      busyRef.current = true;
      serverStep("take").then(async (r) => {
        if (serverFail(r, "take")) {
          busyRef.current = false;
          return;
        }
        let res = r;
        if (res && res.status === "ok" && res.done !== true) {
          console.warn("[park] пирамида: сервер не завершил игру по take, жду его авто-завершение", JSON.stringify(res));
          const gid = startRef.current ? await startRef.current : null;
          const auto = gid ? await sg.client.waitAuto(gid, 12000) : null;
          if (auto) {
            res = auto;
            if (typeof auto.cash === "number") wallet?.applyTruth({ cash: auto.cash });
            if (auto.prize > 0) wallet?.hold("cash", auto.prize);
          }
        }
        busyRef.current = false;
        finish(activeRef.current, res && typeof res.prize === "number" ? res.prize : undefined);
      });
      return;
    }
    finish(activeRef.current);
  };

  const takeRef = useRef(take);
  takeRef.current = take;

  useEffect(() => {
    if (!timerOn) return undefined;
    const iv = setInterval(() => {
      setTimeLeft((sec) => {
        if (sec <= 1) {
          clearInterval(iv);
          if (takenRef.current) return 0;
          setPicks(0);
          if (activeRef.current == null) {
            if (sg) {
              serverStep("roll").then((r) => {
                if (r && r.fail) {
                  closeNoPrize(r.fail);
                  return;
                }
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
  }, [timerOn]);

  const spin = () => {
    if (!armedRef.current || rolling || takenRef.current || picks <= 0 || busyRef.current) return;
    setTimerOn(false);
    if (sg) {
      busyRef.current = true;
      setRolling(true);
      serverStep("roll").then((r) => {
        busyRef.current = false;
        if (serverFail(r, "крутку")) {
          setRolling(false);
          if (!takenRef.current) setTimerOn(true);
          return;
        }
        const idx = r && r.item ? PRIZES.findIndex((p) => p.id === r.item) : -1;
        const prize = r && r.done && typeof r.prize === "number" ? r.prize : null;
        const left = r && typeof r.rolls_left === "number" ? r.rolls_left : picks - 1;
        setPicks(left);
        const done = !!(r && r.done);
        runSpin(idx >= 0 ? idx : rollIndex(), done, prize, !done && left <= 0);
      });
      return;
    }
    const lastPick = picks <= 1;
    setPicks((n) => n - 1);
    setRolling(true);
    runSpin(rollIndex(), lastPick, null, false);
  };

  const runSpin = (target, lastPick, prize, autoTake = false) => {
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
            else if (autoTake) timers.current.push(setTimeout(() => takeRef.current(), 650));
            else {
              armedRef.current = false;
              setLocked(true);
              timers.current.push(
                setTimeout(() => {
                  armedRef.current = true;
                  setLocked(false);
                  setTimeLeft(limitRef.current);
                  setTimerOn(true);
                }, REROLL_ARM_MS)
              );
            }
          }
        }, acc)
      );
    }
  };

  const canTake = !rolling && !taken && active != null && !locked;

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
              disabled={!canTake}
            >
              Take
            </button>
            <button
              type="button"
              className={`${styles.choise} ${picks <= 0 || rolling || taken || locked ? styles.choiseOff : ""}`}
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
          <span className={styles.takenNum}>+{takenValue != null ? takenValue : active == null ? 0 : PRIZES[active].value}</span>
        </div>
      )}
    </div>
  );
}
