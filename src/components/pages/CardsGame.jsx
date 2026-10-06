import { useEffect, useLayoutEffect, useRef, useState } from "react";
import { createPortal } from "react-dom";
import ParkPopupFrame from "./ParkPopupFrame";
import { useServerGame, useParkServer } from "./parkSocket";
import { useWallet } from "./parkWallet";
import styles from "./CardsGame.module.css";
import { PARK_ASSET } from "./parkAssetBase";

const DESK =
  typeof window !== "undefined" && typeof window.matchMedia === "function"
    ? window.matchMedia("(hover: hover) and (pointer: fine)").matches
    : false;
const A = DESK ? PARK_ASSET + "/Cards_Game_hd" : PARK_ASSET + "/Cards_Game";
const A_GRID = A;
const BACK = "Card_all_0000_Layer-287-copy-14.webp";
const MONEY = PARK_ASSET + "/Ui_Exchange_HUD/Icn_Currency.png";

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
const FLIP_STAGGER_MS = 14;
const FLIP_MS = 640;
const BADGE_FADE_MS = 380;
const FLIP_TOTAL_MS = FLIP_MS + 54 * FLIP_STAGGER_MS;

const KNOWN_CARDS = new Set(buildDeck().map((c) => c.img));

const POPUP_ARM_MS = 600;

const serverCardImg = (name) => {
  if (typeof name !== "string") return null;
  const m = /^(\d+)_(Clubs|Diamonds|Hearts|Spades)$/.exec(name);
  const img = m ? `${m[2]}_${m[1]}.webp` : `${name}.webp`;
  return KNOWN_CARDS.has(img) ? img : null;
};

export default function CardsGame({ onFinish }) {
  const [deck] = useState(buildDeck);
  const [opened, setOpened] = useState(() => new Set());
  const [big, setBig] = useState(null);
  const boardRef = useRef(null);
  const flyerRef = useRef(null);
  const timers = useRef([]);
  const sg = useServerGame();
  const park = useParkServer();
  const wallet = useWallet();
  const startRef = useRef(null);
  const [remote, setRemote] = useState({});

  const remoteCard = (name, prize) => ({ img: serverCardImg(name) || deck.find((c) => c.pts === prize)?.img || BACK, pts: typeof prize === "number" ? prize : 0 });

  const addRemote = (list) => {
    const items = (Array.isArray(list) ? list : [list]).filter((o) => o && Number.isInteger(Number(o.choice)));
    if (!items.length) return;
    setRemote((cur) => {
      const next = { ...cur };
      items.forEach((o) => {
        next[Number(o.choice)] = remoteCard(o.card, o.prize);
      });
      return next;
    });
    setOpened((p) => {
      const n = new Set(p);
      items.forEach((o) => n.add(Number(o.choice)));
      return n;
    });
  };

  useEffect(() => {
    if (!sg || startRef.current) return;
    startRef.current = sg
      .start("cards")
      .then((r) => {
        if (Array.isArray(r.opened)) {
          console.info("[park] карты: уже открыто", r.opened.length);
          addRemote(r.opened);
        }
        return r.game_id || null;
      })
      .catch(() => null);
  }, [sg]);

  useEffect(() => {
    if (!sg) return undefined;
    const offOpen = sg.client.on("card_opened", (m) => {
      if (!m) return;
      if (m.user_id != null && park && String(m.user_id) === String(park.userId)) return;
      console.info("[park] карты: открыл другой игрок", JSON.stringify(m));
      addRemote(m);
    });
    const offReset = sg.client.on("cards_reset", () => {
      if (pickedRef.current) {
        console.info("[park] карты: колода перемешана, жду раскрытие последней карты");
        resetPendingRef.current = true;
        return;
      }
      console.info("[park] карты: колода перемешана");
      runFlipBack(null);
    });
    return () => {
      offOpen();
      offReset();
    };
  }, [sg]);

  useEffect(() => () => timers.current.forEach(clearTimeout), []);

  const armedRef = useRef(false);
  useEffect(() => {
    const t = setTimeout(() => {
      armedRef.current = true;
    }, POPUP_ARM_MS);
    return () => clearTimeout(t);
  }, []);

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

  const [flipBack, setFlipBack] = useState(false);
  const resetPendingRef = useRef(false);

  const [badgesOff, setBadgesOff] = useState(false);
  const [flipFaces, setFlipFaces] = useState({});
  const remoteRef = useRef(remote);
  remoteRef.current = remote;

  const flipBusyRef = useRef(false);

  const runFlipBack = (pts) => {
    if (flipBusyRef.current) return;
    flipBusyRef.current = true;
    resetPendingRef.current = false;
    setBadgesOff(true);
    later(() => {
      setFlipFaces({ ...remoteRef.current });
      setFlipBack(true);
      setRemote({});
      setOpened(new Set());
    }, BADGE_FADE_MS);
    later(() => {
      setFlipBack(false);
      setBadgesOff(false);
      setFlipFaces({});
      flipBusyRef.current = false;
    }, BADGE_FADE_MS + FLIP_TOTAL_MS + 60);
    if (pts != null) later(() => onFinish?.(pts), BADGE_FADE_MS + FLIP_TOTAL_MS + 450);
  };

  const finish = () => {
    setBig((cur) => {
      if (!cur) return cur;
      const pts = cur.card ? cur.card.pts : 0;
      if (sg && cur.card) setRemote((rm) => ({ ...rm, [cur.idx]: cur.card }));
      setOpened((p) => {
        const n = new Set(p);
        n.add(cur.idx);
        return n;
      });
      if (resetPendingRef.current) later(() => runFlipBack(pts), 500);
      else later(() => onFinish?.(pts), 250);
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
    if (!armedRef.current || big || opened.has(idx) || pickedRef.current) return;
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

    const show = () => {
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

    if (!sg) {
      show();
      return;
    }
    (async () => {
      const gid = startRef.current ? await startRef.current : null;
      if (gid) {
        try {
          const r = await sg.play(gid, { choice: idx });
          if (r && typeof r.cash === "number") wallet?.applyTruth({ cash: r.cash });
          if (r && typeof r.prize === "number") {
            const img = serverCardImg(r.card) || deck.find((c) => c.pts === r.prize)?.img || payload.card.img;
            payload.card = { img, pts: r.prize };
            if (r.prize > 0) wallet?.hold("cash", r.prize);
          }
        } catch (e) {
          if (e && e.code === "card_opened") {
            console.warn("[park] карты: позиция уже открыта", idx);
            pickedRef.current = false;
            if (resetPendingRef.current) runFlipBack(null);
            return;
          }
          payload.card = deck[idx];
        }
      }
      show();
    })();
  };

  const renderCard = (idx) => {
    const card = remote[idx] || deck[idx];
    const isOpen = opened.has(idx);
    const flying = big && big.idx === idx;
    return (
      <button
        key={idx}
        type="button"
        className={`${styles.cardBtn} ${flying ? styles.cardAway : ""} ${flipBack ? styles.cardFlip : ""}`}
        onClick={(e) => flip(idx, e)}
      >
        <span
          className={styles.cardInner}
          style={{
            transform: `rotateY(${isOpen ? 180 : 0}deg)`,
            transitionDelay: flipBack ? `${idx * FLIP_STAGGER_MS}ms` : "0ms",
            animationDelay: flipBack ? `${idx * FLIP_STAGGER_MS}ms` : "0ms",
          }}
        >
          <img className={`${styles.cardImg} ${styles.cardFront}`} src={`${A_GRID}/${BACK}`} alt="" draggable={false} />
          {(isOpen || flipFaces[idx]) && (
            <img className={`${styles.cardImg} ${styles.cardRear}`} src={`${A_GRID}/${(flipFaces[idx] || card).img}`} alt="" draggable={false} />
          )}
        </span>
        {isOpen && card.pts != null && <span className={`${styles.badge} ${badgesOff ? styles.badgeOff : ""}`}>{card.pts}</span>}
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
        <div className={styles.bigWrap}>
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
