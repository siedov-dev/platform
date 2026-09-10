import { useEffect, useLayoutEffect, useRef, useState } from "react";
import { createPortal } from "react-dom";
import ParkPopupFrame from "./ParkPopupFrame";
import styles from "./CardsGame.module.css";

const A = "/bonus-park/Cards_Game";
const BACK = "Card_all_0000_Layer-287-copy-14.webp";
const MONEY = "/bonus-park/Ui_Exchange_HUD/Icn_Currency.png";

const SUITS = ["Clubs", "Diamonds", "Hearts", "Spades"];


function CardFix({ hostRef, children }) {
  const [box, setBox] = useState(null);

  useLayoutEffect(() => {
    const el = hostRef.current;
    if (!el) return undefined;
    const apply = () => {
      const r = el.getBoundingClientRect();
      if (!r.width) return;
      setBox({
        x: r.left,
        y: r.top,
        w: el.offsetWidth,
        h: el.offsetHeight,
        k: el.offsetWidth ? r.width / el.offsetWidth : 1,
      });
    };
    apply();
    const ro = new ResizeObserver(apply);
    ro.observe(el);
    window.addEventListener("resize", apply);
    window.addEventListener("scroll", apply, true);
    return () => {
      ro.disconnect();
      window.removeEventListener("resize", apply);
      window.removeEventListener("scroll", apply, true);
    };
  }, [hostRef]);

  if (!box) return null;
  return createPortal(
    <div
      className={styles.cardFix}
      style={{
        left: `${box.x}px`,
        top: `${box.y}px`,
        width: `${box.w}px`,
        height: `${box.h}px`,
        transform: `scale(${box.k.toFixed(3)})`,
      }}
    >
      {children}
    </div>,
    document.body
  );
}

function TopBeam({ hostRef, fade, prize }) {
  const [box, setBox] = useState(null);

  useLayoutEffect(() => {
    const el = hostRef.current;
    if (!el) return undefined;
    const apply = () => {
      const r = el.getBoundingClientRect();
      if (!r.width) return;
      setBox({
        cx: r.left + r.width / 2,
        w: r.width * 0.66,
        top: r.top - r.height * 0.34,
        h: r.height * 1.6,
        py: r.top + r.height * 0.085,
        k: el.offsetWidth ? r.width / el.offsetWidth : 1,
      });
    };
    apply();
    const ro = new ResizeObserver(apply);
    ro.observe(el);
    window.addEventListener("resize", apply);
    window.addEventListener("scroll", apply, true);
    return () => {
      ro.disconnect();
      window.removeEventListener("resize", apply);
      window.removeEventListener("scroll", apply, true);
    };
  }, [hostRef]);

  if (!box) return null;
  return createPortal(
    <>
      <img
        className={`${styles.beam} ${fade}`}
        src={`${A}/Shine_Card-Win.webp`}
        alt=""
        draggable={false}
        style={{ left: `${box.cx}px`, top: `${box.top}px`, width: `${box.w}px`, height: `${box.h}px` }}
      />
      {prize != null && (
        <div
          className={styles.prizeFix}
          style={{ left: `${box.cx}px`, top: `${box.py}px`, transform: `scale(${box.k.toFixed(3)})` }}
        >
          <div className={`${styles.prize} ${fade}`}>
            <img className={styles.prizeMoney} src={MONEY} alt="" draggable={false} />
            <span className={styles.prizeNum}>{prize}</span>
          </div>
        </div>
      )}
    </>,
    document.body
  );
}

function buildDeck() {
  const deck = [];
  for (const s of SUITS) {
    for (let n = 2; n <= 10; n += 1) deck.push({ img: `${s}_${n}.webp`, pts: n });
    deck.push({ img: `Jack_${s}.webp`, pts: 10 });
    deck.push({ img: `Queen_${s}.webp`, pts: 10 });
    deck.push({ img: `King_${s}.webp`, pts: 10 });
    deck.push({ img: `Ace_${s}.webp`, pts: 11 });
  }
  deck.push({ img: "Joker_Dark.webp", pts: 50 });
  deck.push({ img: "Joker_Red.webp", pts: 50 });
  deck.push({ img: "Ace_Gold.webp", pts: 100 });
  for (let i = deck.length - 1; i > 0; i -= 1) {
    const j = Math.floor(Math.random() * (i + 1));
    [deck[i], deck[j]] = [deck[j], deck[i]];
  }
  return deck;
}

const PICK_SEC = 15;

export default function CardsGame({ onFinish }) {
  const [deck] = useState(buildDeck);
  const [opened, setOpened] = useState(() => new Set());
  const [big, setBig] = useState(null);
  const boardRef = useRef(null);
  const flyerRef = useRef(null);
  const timers = useRef([]);

  useEffect(() => () => timers.current.forEach(clearTimeout), []);

  useEffect(() => {
    [`${A}/Shine_Card-Win.webp`, `${A}/Light_Fx_Card.webp`, MONEY].forEach((src) => {
      const img = new Image();
      img.src = src;
    });
  }, []);

  const later = (fn, ms) => timers.current.push(setTimeout(fn, ms));

  const clearTimers = () => {
    timers.current.forEach(clearTimeout);
    timers.current = [];
  };

  useEffect(() => {
    if (!big || big.phase !== "start") return undefined;
    if (flyerRef.current) flyerRef.current.getBoundingClientRect();
    const raf = requestAnimationFrame(() => {
      setBig((cur) => (cur && cur.phase === "start" ? { ...cur, phase: "center" } : cur));
    });
    return () => cancelAnimationFrame(raf);
  }, [big]);

  const finish = () => {
    setBig((cur) => {
      if (!cur) return cur;
      const pts = cur.card ? cur.card.pts : 0;
      setOpened((p) => {
        const n = new Set(p);
        n.add(cur.idx);
        return n;
      });
      later(() => onFinish?.(pts), 250);
      return null;
    });
  };

  const close = () => {
    setBig((cur) => {
      if (!cur || cur.phase === "back") return cur;
      clearTimers();
      later(finish, 1100);
      return { ...cur, phase: "back", landed: false };
    });
  };

  const onFlyEnd = (e) => {
    if (e.target !== e.currentTarget || e.propertyName !== "transform") return;
    setBig((cur) => {
      if (!cur) return cur;
      if (cur.phase === "back") {
        finish();
        return cur;
      }
      if (cur.phase === "center" && !cur.landed) {
        later(close, 1750);
        return { ...cur, landed: true };
      }
      return cur;
    });
  };

  const [left, setLeft] = useState(PICK_SEC);
  const pickedRef = useRef(false);

  useEffect(() => {
    const iv = setInterval(() => {
      setLeft((sec) => {
        if (pickedRef.current) return sec;
        if (sec <= 1) {
          clearInterval(iv);
          if (!pickedRef.current) {
            const host = boardRef.current;
            const btns = host ? [...host.querySelectorAll("[class*=cardBtn]")] : [];
            const pick = btns[Math.floor(Math.random() * btns.length)];
            if (pick) pick.click();
          }
          return 0;
        }
        return sec - 1;
      });
    }, 1000);
    return () => clearInterval(iv);
  }, []);

  const flip = (idx, e) => {
    if (big || opened.has(idx) || pickedRef.current) return;
    pickedRef.current = true;
    const el = boardRef.current;
    const b = el.getBoundingClientRect();
    const f = b.width / (el.offsetWidth || 1) || 1;
    const r = e.currentTarget.getBoundingClientRect();
    const from = {
      left: (r.left - b.left) / f,
      top: (r.top - b.top) / f,
      w: r.width / f,
      h: r.height / f,
    };
    const s = (el.offsetWidth * 0.46) / from.w;
    const host = el.closest("[class*='popupOverlay']") || el.parentElement || el;
    const hb = host.getBoundingClientRect();
    const dx = (hb.left + hb.width / 2 - b.left) / f - (from.left + from.w / 2);
    const dy = (hb.top + hb.height / 2 - b.top) / f - (from.top + from.h / 2);
    const payload = { idx, card: deck[idx], from, to: { dx, dy, s }, phase: "start", landed: false };

    const face = new Image();
    face.src = `${A}/${payload.card.img}`;
    const start = () => setBig((cur) => (cur ? cur : payload));
    if (face.decode) {
      Promise.race([face.decode(), new Promise((res) => setTimeout(res, 300))])
        .catch(() => {})
        .then(start);
    } else {
      start();
    }
  };

  const renderCard = (idx) => {
    const card = deck[idx];
    const isOpen = opened.has(idx);
    const flying = big && big.idx === idx;
    return (
      <button
        key={idx}
        type="button"
        className={`${styles.cardBtn} ${flying ? styles.cardAway : ""}`}
        onClick={(e) => flip(idx, e)}
      >
        <img
          className={styles.cardImg}
          src={`${A}/${isOpen ? card.img : BACK}`}
          alt=""
          draggable={false}
        />
        {isOpen && card.pts != null && <span className={styles.badge}>{card.pts}</span>}
      </button>
    );
  };

  const landed = big && big.landed && big.phase === "center";
  const fxCls = big && big.phase === "back" ? styles.fxOut : "";

  return (
    <ParkPopupFrame title="Cards" timeLeft={left}>
    <div ref={boardRef} className={styles.board}>
      <div className={styles.grid}>{Array.from({ length: 49 }, (_, i) => renderCard(i))}</div>
      <div className={styles.lastRow}>{Array.from({ length: 6 }, (_, i) => renderCard(49 + i))}</div>

      {big && (
        <div className={styles.bigWrap} onClick={close}>
          {(landed || big.phase === "back") && (
            <>
              <TopBeam hostRef={boardRef} fade={fxCls} prize={big.card.pts} />
            </>
          )}
          <CardFix hostRef={boardRef}>
          <div
            ref={flyerRef}
            className={styles.flyer}
            onTransitionEnd={onFlyEnd}
            style={{
              left: `${big.from.left}px`,
              top: `${big.from.top}px`,
              width: `${big.from.w}px`,
              height: `${big.from.h}px`,
              transform:
                big.phase === "center"
                  ? `translate(${big.to.dx}px, ${big.to.dy}px) scale(${big.to.s})`
                  : "translate(0px, 0px) scale(1)",
            }}
          >
            <img
              className={`${styles.flyGlow} ${landed ? styles.flyGlowOn : ""}`}
              src={`${A}/Light_Fx_Card.webp`}
              alt=""
              draggable={false}
            />
            <div className={`${styles.flipInner} ${big.phase !== "start" ? styles.flipped : ""}`}>
              <img className={styles.faceBack} src={`${A}/${BACK}`} alt="" draggable={false} />
              <img className={styles.faceFront} src={`${A}/${big.card.img}`} alt="" draggable={false} />
            </div>
          </div>
          </CardFix>
        </div>
      )}
    </div>
    </ParkPopupFrame>
  );
}
