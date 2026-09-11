import { useState, useEffect, useRef } from "react";
import { gamesApi, gameField } from "@/api/gamesClient";
import FerrisEvent, { CABIN_SLOT } from "./FerrisEvent";
import CardsGame from "./CardsGame";
import PyramidGame from "./PyramidGame";
import ParkPopupFrame from "./ParkPopupFrame";
import styles from "./MiniGames.module.css";

const delay = (ms) => new Promise((r) => setTimeout(r, ms));

const API_WAIT = 800;
const withTimeout = (p, ms = API_WAIT) =>
  Promise.race([p, new Promise((_, rej) => setTimeout(() => rej(new Error("timeout")), ms))]);

async function play5050() {
  try {
    const startRes = await withTimeout(gamesApi.cardsStart());
    const gid = gameField(startRes, "game_id");
    const playRes = await withTimeout(gamesApi.cardsPlay(gid));
    return gameField(playRes, "result") === "win";
  } catch {
    return Math.random() < 0.5;
  }
}

const CHOICE_SEC = 10;
const WIN_PAY = 50;
const JOKER_DIR = "/bonus-park/joker";
const JOKER_CLIPS = { coin: "102719_RGBA_00001.webm", match: "115402_RGBA_00001.webm" };

function JokerClip({ clip }) {
  return (
    <video
      className={styles.jokerClip}
      src={`${JOKER_DIR}/${clip}`}
      autoPlay
      loop
      muted
      playsInline
      preload="auto"
      disablePictureInPicture
    />
  );
}
const MONEY_ICN = "/bonus-park/Ui_Exchange_HUD/Icn_Currency.png";

function WinPay({ show, value = WIN_PAY }) {
  if (!show) return null;
  return (
    <div className={styles.payFx}>
      <img className={styles.payMoney} src={MONEY_ICN} alt="" draggable={false} />
      <span className={styles.payNum}>+{value}</span>
    </div>
  );
}

function useCountdown(active, seconds, onEnd) {
  const [left, setLeft] = useState(seconds);
  const endRef = useRef(onEnd);
  endRef.current = onEnd;
  useEffect(() => {
    if (!active) return undefined;
    setLeft(seconds);
    const iv = setInterval(() => {
      setLeft((s) => {
        if (s <= 1) {
          clearInterval(iv);
          endRef.current?.();
          return 0;
        }
        return s - 1;
      });
    }, 1000);
    return () => clearInterval(iv);
  }, [active, seconds]);
  return left;
}

function CoinFlip({ onFinish }) {
  const [landed, setLanded] = useState(null);
  const [win, setWin] = useState(null);
  const [flipping, setFlipping] = useState(false);
  const doneRef = useRef(false);
  const timers = useRef([]);

  useEffect(() => () => timers.current.forEach(clearTimeout), []);

  const play = async (side) => {
    if (doneRef.current) return;
    doneRef.current = true;
    setLanded(null);
    setWin(null);
    setFlipping(true);
    const [isWin] = await Promise.all([play5050(), delay(1300)]);
    setWin(isWin);
    setLanded(isWin ? side : side === "heads" ? "tails" : "heads");
    setFlipping(false);
    timers.current.push(setTimeout(() => onFinish?.(isWin ? 1 : 0), 1800));
  };

  const left = useCountdown(!doneRef.current && win === null, CHOICE_SEC, () => {
    play(Math.random() < 0.5 ? "heads" : "tails");
  });

  return (
    <ParkPopupFrame title="Coin Flip" timeLeft={left}>
      <div className={styles.game}>
        <JokerClip clip={JOKER_CLIPS.coin} />
        <WinPay show={win === true && !flipping} />
        <div className={`${styles.coin} ${flipping ? styles.coinFlip : ""}`}>
          <span className={styles.coinFace}>{landed === "tails" ? "T" : "H"}</span>
        </div>
        {win !== null && !flipping ? (
          <div className={styles.resultText}>
            {landed.toUpperCase()} — {win ? "WIN" : "LOSE"}
          </div>
        ) : (
          <div className={styles.hint}>{flipping ? "..." : "Heads or tails?"}</div>
        )}
        <div className={styles.controls}>
          <button className={styles.btn} disabled={flipping || win !== null} onClick={() => play("heads")}>Heads</button>
          <button className={styles.btn} disabled={flipping || win !== null} onClick={() => play("tails")}>Tails</button>
        </div>
      </div>
    </ParkPopupFrame>
  );
}

function CoinHand({ onFinish }) {
  const [coinHand, setCoinHand] = useState(null);
  const [win, setWin] = useState(null);
  const [busy, setBusy] = useState(false);
  const doneRef = useRef(false);
  const timers = useRef([]);

  useEffect(() => () => timers.current.forEach(clearTimeout), []);

  const play = async (hand) => {
    if (doneRef.current) return;
    doneRef.current = true;
    setBusy(true);
    const [isWin] = await Promise.all([play5050(), delay(600)]);
    setWin(isWin);
    setCoinHand(isWin ? hand : hand === "left" ? "right" : "left");
    setBusy(false);
    timers.current.push(setTimeout(() => onFinish?.(isWin ? 1 : 0), 1800));
  };

  const left = useCountdown(!doneRef.current && win === null, CHOICE_SEC, () => {
    play(Math.random() < 0.5 ? "left" : "right");
  });

  return (
    <ParkPopupFrame title="Which Hand" timeLeft={left}>
      <div className={styles.game}>
        <WinPay show={win === true} />
        <div className={styles.hands}>
          {["left", "right"].map((h) => (
            <button key={h} className={styles.hand} disabled={busy || win !== null} onClick={() => play(h)}>
              {coinHand === h ? "🪙" : "✊"}
            </button>
          ))}
        </div>
        {win !== null ? (
          <div className={styles.resultText}>{win ? "WIN" : "LOSE"}</div>
        ) : (
          <div className={styles.hint}>{busy ? "..." : "Which hand hides the coin?"}</div>
        )}
      </div>
    </ParkPopupFrame>
  );
}

function Matches({ onFinish }) {
  const [longOne, setLongOne] = useState(null);
  const [win, setWin] = useState(null);
  const [busy, setBusy] = useState(false);
  const doneRef = useRef(false);
  const timers = useRef([]);

  useEffect(() => () => timers.current.forEach(clearTimeout), []);

  const play = async (i) => {
    if (doneRef.current) return;
    doneRef.current = true;
    setBusy(true);
    const [isWin] = await Promise.all([play5050(), delay(500)]);
    setWin(isWin);
    setLongOne(isWin ? i : i === 0 ? 1 : 0);
    setBusy(false);
    timers.current.push(setTimeout(() => onFinish?.(isWin ? 1 : 0), 1800));
  };

  const left = useCountdown(!doneRef.current && win === null, CHOICE_SEC, () => {
    play(Math.random() < 0.5 ? 0 : 1);
  });

  return (
    <ParkPopupFrame title="Matches" timeLeft={left}>
      <div className={styles.game}>
        <JokerClip clip={JOKER_CLIPS.match} />
        <WinPay show={win === true} />
        <div className={styles.matches}>
          {[0, 1].map((i) => (
            <button key={i} className={styles.matchBtn} disabled={busy || win !== null} onClick={() => play(i)}>
              <span
                className={`${styles.match} ${
                  longOne !== null ? (longOne === i ? styles.matchLong : styles.matchShort) : styles.matchHidden
                }`}
              />
            </button>
          ))}
        </div>
        {win !== null ? (
          <div className={styles.resultText}>{win ? "LONG — WIN" : "SHORT — LOSE"}</div>
        ) : (
          <div className={styles.hint}>{busy ? "..." : "Pick the long match"}</div>
        )}
      </div>
    </ParkPopupFrame>
  );
}

function Figures() {
  const [gameId, setGameId] = useState(null);
  const [figure, setFigure] = useState(null);
  const [rerolls, setRerolls] = useState(null);
  const [taken, setTaken] = useState(false);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState(null);

  const start = async () => {
    setBusy(true);
    setError(null);
    setTaken(false);
    try {
      const res = await gamesApi.pyramidStart();
      setGameId(gameField(res, "game_id"));
      setFigure(gameField(res, "figure_data"));
      setRerolls(gameField(res, "rerolls_left"));
    } catch (e) {
      setError(e.message);
    } finally {
      setBusy(false);
    }
  };

  useEffect(() => {
    start();
  }, []);

  const reroll = async () => {
    if (busy || !gameId || rerolls <= 0) return;
    setBusy(true);
    setError(null);
    try {
      const [res] = await Promise.all([gamesApi.pyramidAction(gameId, "reroll"), delay(500)]);
      setFigure(gameField(res, "figure_data"));
      setRerolls(gameField(res, "rerolls_left"));
    } catch (e) {
      setError(e.message);
    } finally {
      setBusy(false);
    }
  };

  const keep = async () => {
    if (busy || !gameId) return;
    setBusy(true);
    setError(null);
    try {
      await gamesApi.pyramidAction(gameId, "claim");
      setTaken(true);
    } catch (e) {
      setError(e.message);
    } finally {
      setBusy(false);
    }
  };

  return (
    <div className={styles.game}>
      <div className={`${styles.figure} ${busy ? styles.figureBusy : ""}`}>
        {figure ? (
          <>
            <span className={styles.figureName}>{figure.name}</span>
            <span className={styles.figureId}>x{figure.multiplier}</span>
          </>
        ) : (
          <span className={styles.figureName}>...</span>
        )}
      </div>
      <div className={styles.info}>Rerolls left: {rerolls ?? "-"}</div>
      {error && <div className={styles.err}>{error}</div>}
      {taken ? (
        <>
          <div className={styles.resultText}>Taken!</div>
          <div className={styles.controls}>
            <button className={styles.btn} disabled={busy} onClick={start}>Again</button>
          </div>
        </>
      ) : (
        <div className={styles.controls}>
          <button className={styles.btnGold} disabled={busy || !gameId} onClick={keep}>Keep</button>
          <button className={styles.btn} disabled={busy || !gameId || rerolls <= 0} onClick={reroll}>Reroll</button>
        </div>
      )}
    </div>
  );
}

const WHEEL_COLORS = [
  "#e8c66a",
  "#c0392b",
  "#2980b9",
  "#27ae60",
  "#d4a017",
  "#8e44ad",
  "#d35400",
  "#16a085",
];

function Wheel({ onFinish }) {
  const [angle, setAngle] = useState(0);
  const [spinning, setSpinning] = useState(false);
  const [result, setResult] = useState(null);
  const timers = useRef([]);

  useEffect(() => () => timers.current.forEach(clearTimeout), []);

  useEffect(() => {
    let cancelled = false;
    const ask = (async () => {
      try {
        const res = await withTimeout(gamesApi.wheelStart());
        const gid = gameField(res, "game_id");
        const spun = await withTimeout(gamesApi.wheelSpin(gid));
        return gameField(spun, "sector");
      } catch {
        return null;
      }
    })();
    const t = setTimeout(async () => {
      if (cancelled) return;
      setSpinning(true);
      const sector = (await ask) || { id: 1 + Math.floor(Math.random() * 8) };
      if (cancelled) return;
      const slot = CABIN_SLOT.indexOf(Number(sector.id));
      const idx = slot >= 0 ? slot : (((sector.id - 1) % 8) + 8) % 8;
      await new Promise((r) => requestAnimationFrame(() => requestAnimationFrame(r)));
      if (cancelled) return;
      setAngle(5 * 360 + ((((180 - idx * 45) % 360) + 360) % 360));
      timers.current.push(
        setTimeout(() => {
          setSpinning(false);
          setResult(sector);
          timers.current.push(setTimeout(() => onFinish?.(sector.id), 1700));
        }, 3200)
      );
    }, 1000);
    return () => {
      cancelled = true;
      clearTimeout(t);
    };
  }, []);

  return (
    <ParkPopupFrame title="Wheel">
      <div className={styles.wheelBox}>
        <FerrisEvent angle={angle} spinning={spinning} prize={result ? result.id : null} />
      </div>
      <div className={styles.hint}>{result ? `+${result.id}` : spinning ? "..." : ""}</div>
    </ParkPopupFrame>
  );
}

export const MINI_GAMES = [
  { id: "coinflip", title: "Coin Flip (50/50)", Comp: CoinFlip, bare: true },
  { id: "coinhand", title: "Which Hand (50/50)", Comp: CoinHand, bare: true },
  { id: "matches", title: "Matches (50/50)", Comp: Matches, bare: true },
  { id: "cards", title: "Cards", Comp: CardsGame, bare: true },
  { id: "pyramid", title: "Pyramid", Comp: PyramidGame, bare: true },
  { id: "wheel", title: "Wheel", Comp: Wheel, bare: true },
];
