import { useState, useEffect } from "react";
import { gamesApi, gameField } from "@/api/gamesClient";
import styles from "./MiniGames.module.css";

function rand(n) {
  return Math.floor(Math.random() * n);
}

const delay = (ms) => new Promise((r) => setTimeout(r, ms));

async function play5050() {
  const startRes = await gamesApi.cardsStart();
  const gid = gameField(startRes, "game_id");
  const playRes = await gamesApi.cardsPlay(gid);
  return gameField(playRes, "result") === "win";
}

function CoinFlip() {
  const [choice, setChoice] = useState(null);
  const [landed, setLanded] = useState(null);
  const [win, setWin] = useState(null);
  const [flipping, setFlipping] = useState(false);
  const [error, setError] = useState(null);

  const play = async (side) => {
    if (flipping) return;
    setChoice(side);
    setLanded(null);
    setWin(null);
    setError(null);
    setFlipping(true);
    try {
      const [isWin] = await Promise.all([play5050(), delay(1300)]);
      setWin(isWin);
      setLanded(isWin ? side : side === "heads" ? "tails" : "heads");
    } catch (e) {
      setError(e.message);
    } finally {
      setFlipping(false);
    }
  };

  return (
    <div className={styles.game}>
      <div className={`${styles.coin} ${flipping ? styles.coinFlip : ""}`}>
        <span className={styles.coinFace}>{landed === "tails" ? "T" : "H"}</span>
      </div>
      {error ? (
        <div className={styles.err}>{error}</div>
      ) : win !== null && !flipping ? (
        <div className={styles.resultText}>
          {landed.toUpperCase()} — {win ? "WIN" : "LOSE"}
        </div>
      ) : (
        <div className={styles.hint}>{flipping ? "..." : "Heads or tails?"}</div>
      )}
      <div className={styles.controls}>
        <button className={styles.btn} disabled={flipping} onClick={() => play("heads")}>Heads</button>
        <button className={styles.btn} disabled={flipping} onClick={() => play("tails")}>Tails</button>
      </div>
    </div>
  );
}

function CoinHand() {
  const [picked, setPicked] = useState(null);
  const [coinHand, setCoinHand] = useState(null);
  const [win, setWin] = useState(null);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState(null);

  const play = async (hand) => {
    if (busy || picked) return;
    setPicked(hand);
    setWin(null);
    setError(null);
    setBusy(true);
    try {
      const [isWin] = await Promise.all([play5050(), delay(600)]);
      setWin(isWin);
      setCoinHand(isWin ? hand : hand === "left" ? "right" : "left");
    } catch (e) {
      setError(e.message);
    } finally {
      setBusy(false);
    }
  };
  const reset = () => {
    setPicked(null);
    setCoinHand(null);
    setWin(null);
    setError(null);
  };

  return (
    <div className={styles.game}>
      <div className={styles.hands}>
        {["left", "right"].map((h) => (
          <button key={h} className={styles.hand} disabled={busy || !!picked} onClick={() => play(h)}>
            {coinHand === h ? "🪙" : "✊"}
          </button>
        ))}
      </div>
      {error ? (
        <div className={styles.err}>{error}</div>
      ) : win !== null ? (
        <>
          <div className={styles.resultText}>{win ? "WIN" : "LOSE"}</div>
          <div className={styles.controls}>
            <button className={styles.btn} onClick={reset}>Again</button>
          </div>
        </>
      ) : (
        <div className={styles.hint}>{busy ? "..." : "Which hand hides the coin?"}</div>
      )}
    </div>
  );
}

function Matches() {
  const [picked, setPicked] = useState(null);
  const [longOne, setLongOne] = useState(null);
  const [win, setWin] = useState(null);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState(null);

  const play = async (i) => {
    if (busy || picked !== null) return;
    setPicked(i);
    setWin(null);
    setError(null);
    setBusy(true);
    try {
      const [isWin] = await Promise.all([play5050(), delay(500)]);
      setWin(isWin);
      setLongOne(isWin ? i : i === 0 ? 1 : 0);
    } catch (e) {
      setError(e.message);
    } finally {
      setBusy(false);
    }
  };
  const reset = () => {
    setPicked(null);
    setLongOne(null);
    setWin(null);
    setError(null);
  };

  return (
    <div className={styles.game}>
      <div className={styles.matches}>
        {[0, 1].map((i) => (
          <button key={i} className={styles.matchBtn} disabled={busy || picked !== null} onClick={() => play(i)}>
            <span
              className={`${styles.match} ${
                longOne !== null ? (longOne === i ? styles.matchLong : styles.matchShort) : styles.matchHidden
              }`}
            />
          </button>
        ))}
      </div>
      {error ? (
        <div className={styles.err}>{error}</div>
      ) : win !== null ? (
        <>
          <div className={styles.resultText}>{win ? "LONG — WIN" : "SHORT — LOSE"}</div>
          <div className={styles.controls}>
            <button className={styles.btn} onClick={reset}>Again</button>
          </div>
        </>
      ) : (
        <div className={styles.hint}>{busy ? "..." : "Pick the long match"}</div>
      )}
    </div>
  );
}

const SUITS = [
  ["♠", "black"],
  ["♥", "red"],
  ["♦", "red"],
  ["♣", "black"],
];
const RANKS = ["A", "2", "3", "4", "5", "6", "7", "8", "9", "10", "J", "Q", "K"];

function buildDeck() {
  const deck = [];
  for (const [suit, color] of SUITS) {
    for (const rank of RANKS) deck.push({ label: rank + suit, color });
  }
  deck.push({ label: "JKR", color: "black", joker: true });
  deck.push({ label: "JKR", color: "red", joker: true });
  deck.push({ label: "JKR", color: "gold", joker: true, golden: true });
  return deck;
}

function Cards() {
  const [deck, setDeck] = useState(buildDeck);
  const [current, setCurrent] = useState(null);
  const [drawn, setDrawn] = useState([]);

  const draw = () => {
    if (!deck.length) return;
    const i = rand(deck.length);
    const card = deck[i];
    setDeck(deck.filter((_, j) => j !== i));
    setCurrent(card);
    setDrawn([card, ...drawn].slice(0, 24));
  };
  const reset = () => {
    setDeck(buildDeck());
    setCurrent(null);
    setDrawn([]);
  };

  const cardColor = (c) => (c.golden ? "gold" : c.color);

  return (
    <div className={styles.game}>
      <div className={styles.mock}>mock — no backend yet</div>
      <div className={`${styles.card} ${current ? styles[`card_${cardColor(current)}`] : styles.cardBack}`}>
        <span className={styles.cardLabel}>{current ? current.label : "?"}</span>
      </div>
      <div className={styles.info}>Deck: {deck.length} / 55</div>
      <div className={styles.controls}>
        <button className={styles.btn} disabled={!deck.length} onClick={draw}>Draw</button>
        <button className={styles.btn} onClick={reset}>Reset</button>
      </div>
      <div className={styles.drawnRow}>
        {drawn.map((c, i) => (
          <span key={i} className={`${styles.miniCard} ${styles[`text_${cardColor(c)}`]}`}>{c.label}</span>
        ))}
      </div>
    </div>
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

function Wheel() {
  const [gameId, setGameId] = useState(null);
  const [angle, setAngle] = useState(0);
  const [spinning, setSpinning] = useState(false);
  const [result, setResult] = useState(null);
  const [spinsLeft, setSpinsLeft] = useState(null);
  const [error, setError] = useState(null);

  const start = async () => {
    setError(null);
    setResult(null);
    try {
      const res = await gamesApi.wheelStart();
      setGameId(gameField(res, "game_id"));
      setSpinsLeft(gameField(res, "total_spins"));
    } catch (e) {
      setError(e.message);
    }
  };

  useEffect(() => {
    start();
  }, []);

  const spin = async () => {
    if (spinning || !gameId || spinsLeft <= 0) return;
    setSpinning(true);
    setResult(null);
    setError(null);
    try {
      const res = await gamesApi.wheelSpin(gameId);
      const sector = gameField(res, "sector");
      setSpinsLeft(gameField(res, "spins_left"));
      const idx = (((sector.id - 1) % 8) + 8) % 8;
      const base = Math.ceil(angle / 360) * 360;
      const target = base + 5 * 360 + (360 - (idx * 45 + 22.5));
      setAngle(target);
      await delay(3200);
      setResult(sector);
    } catch (e) {
      setError(e.message);
    } finally {
      setSpinning(false);
    }
  };

  const gradient = WHEEL_COLORS.map((c, i) => `${c} ${i * 45}deg ${(i + 1) * 45}deg`).join(",");

  return (
    <div className={styles.game}>
      <div className={styles.wheelWrap}>
        <div className={styles.wheelPointer} />
        <div
          className={styles.wheel}
          style={{
            background: `conic-gradient(${gradient})`,
            transform: `rotate(${angle}deg)`,
            transition: spinning ? "transform 3s cubic-bezier(.17,.67,.2,1)" : "none",
          }}
        >
          {WHEEL_COLORS.map((_, i) => (
            <span
              key={i}
              className={styles.wheelNum}
              style={{ transform: `rotate(${i * 45 + 22.5}deg) translateY(-64px)` }}
            >
              {i + 1}
            </span>
          ))}
        </div>
      </div>
      {error ? (
        <div className={styles.err}>{error}</div>
      ) : result ? (
        <div className={styles.resultText}>x{result.multiplier}</div>
      ) : (
        <div className={styles.hint}>{spinning ? "..." : "Spin the wheel"}</div>
      )}
      <div className={styles.info}>Spins left: {spinsLeft ?? "-"}</div>
      <div className={styles.controls}>
        <button className={styles.btn} disabled={spinning || spinsLeft <= 0} onClick={spin}>Spin</button>
      </div>
    </div>
  );
}

export const MINI_GAMES = [
  { id: "coinflip", title: "Coin Flip (50/50)", Comp: CoinFlip },
  { id: "coinhand", title: "Which Hand (50/50)", Comp: CoinHand },
  { id: "matches", title: "Matches (50/50)", Comp: Matches },
  { id: "cards", title: "Cards", Comp: Cards },
  { id: "figures", title: "Figures", Comp: Figures },
  { id: "wheel", title: "Wheel", Comp: Wheel },
];
