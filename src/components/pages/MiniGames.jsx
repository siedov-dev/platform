import { useState, useEffect, useLayoutEffect, useRef } from "react";
import { gamesApi, gameField } from "@/api/gamesClient";
import FerrisEvent, { CABIN_SLOT, CABIN_TICKETS } from "./FerrisEvent";
import CardsGame from "./CardsGame";
import PyramidGame from "./PyramidGame";
import ParkPopupFrame from "./ParkPopupFrame";
import { jokerVideo, releaseJoker } from "./jokerVideos";
import { useServerGame } from "./parkSocket";
import { useWallet } from "./parkWallet";
import styles from "./MiniGames.module.css";
import { PARK_ASSET } from "./parkAssetBase";

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

function useServerRound(game, active) {
  const sg = useServerGame();
  const wallet = useWallet();
  const startRef = useRef(null);
  useEffect(() => {
    if (!sg || !active || startRef.current) return;
    startRef.current = sg
      .start(game)
      .then((r) => r.game_id || null)
      .catch(() => null);
  }, [sg, active, game]);
  const play = async (payload) => {
    if (!sg) return null;
    const gid = startRef.current ? await startRef.current : null;
    if (!gid) return null;
    try {
      const r = await sg.play(gid, payload);
      if (r && typeof r.cash === "number") wallet?.applyTruth({ cash: r.cash });
      if (r && typeof r.tickets === "number") wallet?.applyTruth({ tickets: r.tickets });
      return r;
    } catch (e) {
      return null;
    }
  };
  return { on: !!sg, play, wallet };
}

async function resolve5050(round, choice, forced) {
  if ((!round || !round.on) && typeof forced === "boolean") return forced;
  if (!round || !round.on) return play5050();
  const r = await round.play({ choice });
  if (!r || typeof r.win !== "boolean") return play5050();
  if (r.win && r.prize > 0) round.wallet?.hold("cash", r.prize);
  return r.win;
}

const CHOICE_SEC = 10;
const PICK_SEC = 10;
const MATCH_OPEN_H = 136;
const WIN_PAY = 50;
const JOKER_DIR = "/bonus-park/joker";
const JOKER_CLIPS = { coin: "start" };
const JOKER_MOV = true;
const MATCH_DIR = "/bonus-park/joker";
const IS_SAFARI = () => {
  const ua = typeof navigator === "undefined" ? "" : navigator.userAgent;
  return /^((?!chrome|android|crios|fxios).)*safari/i.test(ua) || /iPad|iPhone|iPod/.test(ua);
};
const MATCH_EXT = () => (IS_SAFARI() ? "mov" : "webm");
const MATCH_CLIPS = {
  start: "start",
  loop: "loop",
  leftUpYes: "left_up_yes",
  leftUpNo: "left_up_no",
  leftYes: "left_yes",
  leftNo: "left_no",
  rightYes: "right_yes",
  rightNo: "right_no",
};
export const MATCH_FILES = () => Object.values(MATCH_CLIPS).map((f) => `${MATCH_DIR}/${f}.${MATCH_EXT()}`);
const MATCH_BRANCH = {
  left: { win: ["leftUpYes", "leftYes"], lose: ["leftUpNo", "leftNo"] },
  right: { win: ["rightYes"], lose: ["rightNo"] },
};
const MATCH_FINAL = new Set(["leftYes", "leftNo", "rightYes", "rightNo"]);
const COIN_OPEN_H = 72;
const COIN_REVEAL_MS = 2900;
const COIN_DIR = "/bonus-park/joker_coin";
const COIN_EXT = () => (IS_SAFARI() ? "mov" : "webm");
const COIN_CLIPS = { start: "start", idle: "idle", idle2: "idle", loopA: "loop", loopB: "loop", left: "coin_left", right: "coin_right" };
const COIN_SUFFIX = { loopB: "?p=2", idle2: "?p=2" };
const COIN_LOOP_ALT = { idle: "idle2" };
const COIN_RANGES = { loopA: { from: 0, to: 0.5 }, loopB: { from: 0.5, to: 1 } };
const COIN_LOOPS = new Set(["idle"]);
const coinUrl = (key) => `${COIN_DIR}/${COIN_CLIPS[key]}.${COIN_EXT()}${COIN_SUFFIX[key] || ""}`;
export const COIN_FILES = () => (COIN_EXT() ? [...new Set(Object.keys(COIN_CLIPS).map(coinUrl))] : []);
const COIN_FINAL = new Set(["left", "right"]);
const coinVideo = () => COIN_EXT() !== "";
const FLIP_OPEN_H = 72;
const FLIP_REVEAL_MS = 850;
const FLIP_DIR = "/bonus-park/joker_heads_tails";
const FLIP_EXT = () => (IS_SAFARI() ? "mov" : "webm");
const FLIP_CLIPS = {
  start: "start",
  idle: "idle",
  idle2: "idle2",
  twirl: "twirl",
  tease: "tease",
  coinDown: "coin_down",
  toss: "toss",
  spin: "coin_spin",
  catch: "catch",
  heads: "heads",
  tails: "tails",
};
const FLIP_RANGES = { coinDown: { from: 0, to: 0.5 }, toss: { from: 0.52, to: 1 }, heads: { from: 0.2, to: 1 }, tails: { from: 0.2, to: 1 } };
const FLIP_LOOPS = new Set(["idle", "idle2", "twirl", "tease"]);
const FLIP_LOOP_ALT = { idle: "twirl", twirl: "idle" };
const FLIP_RATE = { spin: 2 };
const FLIP_SIDE_IMG = { heads: PARK_ASSET + "/Icn_Coin_Front_1.png", tails: PARK_ASSET + "/Icn_Coin_Back_1.png" };
const FLIP_SIDE_RING = { heads: { cy: "45.1%", d: "82.4%" }, tails: { cy: "46.9%", d: "82.8%" } };
const flipUrl = (key) => `${FLIP_DIR}/${FLIP_CLIPS[key]}.${FLIP_EXT()}`;
export const FLIP_FILES = () => (FLIP_EXT() ? Object.keys(FLIP_CLIPS).map(flipUrl) : []);
const FLIP_FINAL = new Set(["heads", "tails"]);
const flipVideo = () => FLIP_EXT() !== "";

const jokerSrc = (clip) => {
  if (!IS_SAFARI()) return `${JOKER_DIR}/${clip}.webm`;
  return JOKER_MOV ? `${JOKER_DIR}/${clip}.mov` : "";
};

function JokerClip({ clip }) {
  const src = jokerSrc(clip);
  if (!src) return null;
  return (
    <video
      className={styles.jokerClip}
      src={src}
      autoPlay
      loop
      muted
      playsInline
      preload="auto"
      disablePictureInPicture
    />
  );
}

const DOUBLE_TAP_MS = 350;

function useDoubleTap(ref, onDouble) {
  const cb = useRef(onDouble);
  cb.current = onDouble;
  useEffect(() => {
    let last = 0;
    const onTap = (e) => {
      if (e.pointerType === "mouse" && e.button !== 0) return;
      const now = performance.now();
      if (now - last < DOUBLE_TAP_MS) {
        last = 0;
        cb.current?.();
        return;
      }
      last = now;
    };
    document.addEventListener("pointerdown", onTap, true);
    return () => document.removeEventListener("pointerdown", onTap, true);
  }, []);
}

function JokerStage({ seq, loopKey, onClip, onEnd }) {
  const boxRef = useRef(null);
  const vids = useRef({});
  const st = useRef({ cur: 0, seq, started: false });
  const cb = useRef({ onClip, onEnd });
  cb.current = { onClip, onEnd };
  st.current.seq = seq;

  const isLast = (idx) => idx === st.current.seq.length - 1;

  const frames = (n) =>
    new Promise((res) => {
      const step = (k) => (k <= 0 ? res() : requestAnimationFrame(() => step(k - 1)));
      step(n);
    });

  const show = (key) => {
    Object.entries(vids.current).forEach(([k, el]) => {
      if (!el) return;
      if (k === key) {
        el.style.display = "block";
        el.style.zIndex = "2";
        el.style.opacity = "1";
      } else {
        el.style.zIndex = "1";
      }
    });
  };

  const hideOthers = async (key) => {
    const others = Object.entries(vids.current).filter(([k, el]) => el && k !== key);
    await frames(2);
    if (st.current.seq[st.current.cur] !== key) return;
    others.forEach(([, el]) => {
      el.style.opacity = "0";
    });
    await frames(2);
    if (st.current.seq[st.current.cur] !== key) return;
    others.forEach(([, el]) => {
      el.pause();
      el.style.display = "none";
    });
  };

  const ready = (v) =>
    v.readyState >= 3
      ? Promise.resolve()
      : new Promise((res) => {
          const done = () => {
            v.removeEventListener("canplay", done);
            res();
          };
          v.addEventListener("canplay", done);
          if (v.preload !== "auto") {
            v.preload = "auto";
            v.load();
          }
        });

  const seeked = (v) =>
    new Promise((res) => {
      let done = false;
      const fin = () => {
        if (done) return;
        done = true;
        v.removeEventListener("seeked", fin);
        res();
      };
      v.addEventListener("seeked", fin);
      setTimeout(fin, 250);
    });

  const firstFrame = (v) =>
    new Promise((res) => {
      let done = false;
      const fin = () => {
        if (done) return;
        done = true;
        res();
      };
      if (typeof v.requestVideoFrameCallback === "function") v.requestVideoFrameCallback(fin);
      else requestAnimationFrame(() => requestAnimationFrame(fin));
      setTimeout(fin, 300);
    });

  const advance = async () => {
    const s = st.current;
    if (s.adv) return;
    const next = s.cur + 1;
    const key = s.seq[next];
    if (!key) {
      cb.current.onEnd?.(s.seq[s.cur]);
      return;
    }
    const v = vids.current[key];
    if (!v) return;
    s.adv = true;
    v.style.opacity = "0.001";
    v.style.zIndex = "1";
    v.style.display = "block";
    await ready(v);
    v.loop = isLast(next) && key === loopKey;
    if (v.currentTime > 0.01) {
      v.currentTime = 0;
      await seeked(v);
    }
    try {
      await v.play();
    } catch {
      s.adv = false;
      return;
    }
    await firstFrame(v);
    await frames(1);
    s.cur = next;
    s.adv = false;
    show(key);
    hideOthers(key);
    cb.current.onClip?.(key);
  };

  useLayoutEffect(() => {
    const box = boxRef.current;
    if (!box) return undefined;
    const ext = MATCH_EXT();
    const onEnded = (e) => {
      const key = Object.keys(vids.current).find((k) => vids.current[k] === e.target);
      if (key && st.current.seq[st.current.cur] === key) advance();
    };
    Object.entries(MATCH_CLIPS).forEach(([key, file]) => {
      const v = jokerVideo(`${MATCH_DIR}/${file}.${ext}`);
      v.className = styles.jokerVid;
      const first = key === st.current.seq[0];
      v.style.opacity = first ? "1" : "0";
      v.style.display = first ? "block" : "none";
      v.loop = false;
      v.addEventListener("ended", onEnded);
      vids.current[key] = v;
      box.appendChild(v);
    });
    const s = st.current;
    const first = vids.current[s.seq[s.cur]];
    if (first) {
      s.started = true;
      first.loop = isLast(s.cur) && s.seq[s.cur] === loopKey;
      first.currentTime = 0;
      first.play().catch(() => {});
    }
    return () => {
      st.current.started = false;
      Object.values(vids.current).forEach((v) => {
        if (!v) return;
        v.removeEventListener("ended", onEnded);
        v.pause();
        v.currentTime = 0;
        v.loop = false;
        v.style.display = "";
        v.style.opacity = "";
        v.style.zIndex = "";
        releaseJoker(v);
      });
      vids.current = {};
    };
  }, []);

  useEffect(() => {
    const s = st.current;
    const key = s.seq[s.cur];
    const v = vids.current[key];
    if (v) v.loop = isLast(s.cur) && key === loopKey;
    if (key === loopKey && !isLast(s.cur)) advance();
  }, [seq, loopKey]);

  useDoubleTap(boxRef, () => {
    const s = st.current;
    const key = s.seq[s.cur];
    const v = vids.current[key];
    if (s.adv || !v || v.loop || isLast(s.cur)) return;
    console.info("[park] джокер: скип клипа", key);
    v.pause();
    advance();
  });

  return <div ref={boxRef} className={styles.jokerStage} />;
}

function CoinStage({ seq, onClip, onEnd }) {
  const boxRef = useRef(null);
  const vids = useRef({});
  const st = useRef({ cur: 0, seq, started: false });
  const cb = useRef({ onClip, onEnd });
  cb.current = { onClip, onEnd };
  st.current.seq = seq;

  const isLast = (idx) => idx === st.current.seq.length - 1;

  const frames = (n) =>
    new Promise((res) => {
      const step = (k) => (k <= 0 ? res() : requestAnimationFrame(() => step(k - 1)));
      step(n);
    });

  const show = (key) => {
    Object.entries(vids.current).forEach(([k, el]) => {
      if (!el) return;
      if (k === key) {
        el.style.display = "block";
        el.style.zIndex = "2";
        el.style.opacity = "1";
      } else {
        el.style.zIndex = "1";
      }
    });
  };

  const hideOthers = async (key) => {
    const others = Object.entries(vids.current).filter(([k, el]) => el && k !== key);
    await frames(2);
    if (st.current.seq[st.current.cur] !== key) return;
    others.forEach(([, el]) => {
      el.style.opacity = "0";
    });
    await frames(2);
    if (st.current.seq[st.current.cur] !== key) return;
    others.forEach(([, el]) => {
      el.pause();
      el.style.display = "none";
    });
  };

  const ready = (v) =>
    v.readyState >= 3
      ? Promise.resolve()
      : new Promise((res) => {
          const done = () => {
            v.removeEventListener("canplay", done);
            res();
          };
          v.addEventListener("canplay", done);
          if (v.preload !== "auto") {
            v.preload = "auto";
            v.load();
          }
        });

  const seeked = (v) =>
    new Promise((res) => {
      let done = false;
      const fin = () => {
        if (done) return;
        done = true;
        v.removeEventListener("seeked", fin);
        res();
      };
      v.addEventListener("seeked", fin);
      setTimeout(fin, 600);
    });

  const firstFrame = (v) =>
    new Promise((res) => {
      let done = false;
      const fin = () => {
        if (done) return;
        done = true;
        res();
      };
      if (typeof v.requestVideoFrameCallback === "function") v.requestVideoFrameCallback(fin);
      else requestAnimationFrame(() => requestAnimationFrame(fin));
      setTimeout(fin, 300);
    });

  const onFrame = (v, fn) => {
    if (typeof v.requestVideoFrameCallback === "function") v.requestVideoFrameCallback(fn);
    else requestAnimationFrame(fn);
  };

  const watchEnd = (v, key, endT) => {
    const tick = () => {
      if (st.current.seq[st.current.cur] !== key || vids.current[key] !== v) return;
      if (v.ended || v.currentTime >= endT - 0.03) {
        v.pause();
        advance();
        return;
      }
      onFrame(v, tick);
    };
    onFrame(v, tick);
  };

  const loopWatch = (v, key) => {
    const alt = COIN_LOOP_ALT[key];
    const nv = vids.current[alt];
    if (!nv) return;
    let primed = false;
    let priming = false;
    const prime = async () => {
      priming = true;
      nv.style.opacity = "0.001";
      nv.style.zIndex = "1";
      nv.style.display = "block";
      if (nv.currentTime > 0.01) {
        nv.currentTime = 0;
        await seeked(nv);
      }
      try {
        await nv.play();
      } catch {
        priming = false;
        return;
      }
      await firstFrame(nv);
      nv.pause();
      primed = true;
      priming = false;
    };
    const tick = async () => {
      if (st.current.seq[st.current.cur] !== key || vids.current[key] !== v) return;
      const left = v.duration - v.currentTime;
      if (!primed && !priming && left < 1) prime();
      if (!primed || (left > 0.06 && !v.ended)) {
        requestAnimationFrame(tick);
        return;
      }
      try {
        await nv.play();
      } catch {
        return;
      }
      await firstFrame(nv);
      if (st.current.seq[st.current.cur] !== key || vids.current[key] !== v) {
        nv.pause();
        return;
      }
      vids.current[key] = nv;
      vids.current[alt] = v;
      show(key);
      await frames(2);
      v.style.opacity = "0";
      await frames(2);
      v.pause();
      v.style.display = "none";
      v.currentTime = 0;
      loopWatch(nv, key);
    };
    requestAnimationFrame(tick);
  };

  const advance = async () => {
    const s = st.current;
    if (s.adv) return;
    const next = s.cur + 1;
    const key = s.seq[next];
    if (!key) {
      cb.current.onEnd?.(s.seq[s.cur]);
      return;
    }
    const v = vids.current[key];
    if (!v) return;
    s.adv = true;
    v.style.opacity = "0.001";
    v.style.zIndex = "1";
    v.style.display = "block";
    await ready(v);
    v.loop = false;
    const r = COIN_RANGES[key];
    const from = r ? r.from * v.duration : 0;
    if (Math.abs(v.currentTime - from) > 0.01) {
      v.currentTime = from;
      await seeked(v);
    }
    try {
      await v.play();
    } catch {
      s.adv = false;
      return;
    }
    await firstFrame(v);
    await frames(1);
    s.cur = next;
    s.adv = false;
    show(key);
    hideOthers(key);
    if (r && r.to < 1) watchEnd(v, key, r.to * v.duration);
    if (COIN_LOOP_ALT[key]) loopWatch(v, key);
    cb.current.onClip?.(key);
  };

  useLayoutEffect(() => {
    const box = boxRef.current;
    if (!box) return undefined;
    const onEnded = (e) => {
      const key = Object.keys(vids.current).find((k) => vids.current[k] === e.target);
      if (key && st.current.seq[st.current.cur] === key) advance();
    };
    Object.keys(COIN_CLIPS).forEach((key) => {
      const v = jokerVideo(coinUrl(key));
      v.className = styles.jokerVid;
      const first = key === st.current.seq[0];
      v.style.opacity = first ? "1" : "0";
      v.style.display = first ? "block" : "none";
      v.loop = false;
      v.addEventListener("ended", onEnded);
      vids.current[key] = v;
      box.appendChild(v);
    });
    const s = st.current;
    const first = vids.current[s.seq[s.cur]];
    if (first) {
      s.started = true;
      first.loop = false;
      first.currentTime = 0;
      first.play().catch(() => {});
    }
    return () => {
      st.current.started = false;
      Object.values(vids.current).forEach((v) => {
        if (!v) return;
        v.removeEventListener("ended", onEnded);
        v.pause();
        v.currentTime = 0;
        v.loop = false;
        v.style.display = "";
        v.style.opacity = "";
        v.style.zIndex = "";
        releaseJoker(v);
      });
      vids.current = {};
    };
  }, []);

  useEffect(() => {
    const s = st.current;
    const v = vids.current[s.seq[s.cur]];
    if (v && (v.paused || COIN_LOOPS.has(s.seq[s.cur])) && !s.adv && !isLast(s.cur)) advance();
  }, [seq]);

  useDoubleTap(boxRef, () => {
    const s = st.current;
    const key = s.seq[s.cur];
    const v = vids.current[key];
    if (s.adv || !v || COIN_LOOP_ALT[key] || isLast(s.cur)) return;
    console.info("[park] монета: скип клипа", key);
    v.pause();
    advance();
  });

  return <div ref={boxRef} className={styles.jokerStage} />;
}

function FlipStage({ seq, onClip, onEnd }) {
  const boxRef = useRef(null);
  const vids = useRef({});
  const st = useRef({ cur: 0, seq, started: false, loopKey: null });
  const cb = useRef({ onClip, onEnd });
  cb.current = { onClip, onEnd };
  st.current.seq = seq;

  const isLast = (idx) => idx === st.current.seq.length - 1;
  const curKey = () => st.current.loopKey || st.current.seq[st.current.cur];

  const frames = (n) =>
    new Promise((res) => {
      const step = (k) => (k <= 0 ? res() : requestAnimationFrame(() => step(k - 1)));
      step(n);
    });

  const show = (key) => {
    Object.entries(vids.current).forEach(([k, el]) => {
      if (!el) return;
      if (k === key) {
        el.style.display = "block";
        el.style.zIndex = "2";
        el.style.opacity = "1";
      } else {
        el.style.zIndex = "1";
      }
    });
  };

  const hideOthers = async (key) => {
    const others = Object.entries(vids.current).filter(([k, el]) => el && k !== key);
    await frames(2);
    if (curKey() !== key) return;
    others.forEach(([, el]) => {
      el.style.opacity = "0";
    });
    await frames(2);
    if (curKey() !== key) return;
    others.forEach(([, el]) => {
      el.pause();
      el.style.display = "none";
    });
  };

  const ready = (v) =>
    v.readyState >= 3
      ? Promise.resolve()
      : new Promise((res) => {
          const done = () => {
            v.removeEventListener("canplay", done);
            res();
          };
          v.addEventListener("canplay", done);
          if (v.preload !== "auto") {
            v.preload = "auto";
            v.load();
          }
        });

  const seeked = (v) =>
    new Promise((res) => {
      let done = false;
      const fin = () => {
        if (done) return;
        done = true;
        v.removeEventListener("seeked", fin);
        res();
      };
      v.addEventListener("seeked", fin);
      setTimeout(fin, 600);
    });

  const firstFrame = (v) =>
    new Promise((res) => {
      let done = false;
      const fin = () => {
        if (done) return;
        done = true;
        res();
      };
      if (typeof v.requestVideoFrameCallback === "function") v.requestVideoFrameCallback(fin);
      else requestAnimationFrame(() => requestAnimationFrame(fin));
      setTimeout(fin, 300);
    });

  const onFrame = (v, fn) => {
    if (typeof v.requestVideoFrameCallback === "function") v.requestVideoFrameCallback(fn);
    else requestAnimationFrame(fn);
  };

  const watchEnd = (v, key, endT) => {
    const tick = () => {
      if (curKey() !== key || vids.current[key] !== v) return;
      if (v.ended || v.currentTime >= endT - 0.03) {
        v.pause();
        advance();
        return;
      }
      onFrame(v, tick);
    };
    onFrame(v, tick);
  };

  const loopWatch = (v, key) => {
    const alt = FLIP_LOOP_ALT[key];
    const nv = vids.current[alt];
    if (!nv) return;
    let primed = false;
    let priming = false;
    const prime = async () => {
      priming = true;
      nv.style.opacity = "0.001";
      nv.style.zIndex = "1";
      nv.style.display = "block";
      if (nv.currentTime > 0.01) {
        nv.currentTime = 0;
        await seeked(nv);
      }
      try {
        await nv.play();
      } catch {
        priming = false;
        return;
      }
      await firstFrame(nv);
      nv.pause();
      primed = true;
      priming = false;
    };
    const tick = async () => {
      if (curKey() !== key || vids.current[key] !== v) return;
      const left = v.duration - v.currentTime;
      if (!primed && !priming && left < 1) prime();
      if (!primed || (left > 0.06 && !v.ended)) {
        requestAnimationFrame(tick);
        return;
      }
      try {
        await nv.play();
      } catch {
        return;
      }
      await firstFrame(nv);
      if (curKey() !== key || vids.current[key] !== v) {
        nv.pause();
        return;
      }
      st.current.loopKey = alt;
      show(alt);
      await frames(2);
      v.style.opacity = "0";
      await frames(2);
      v.pause();
      v.style.display = "none";
      v.currentTime = 0;
      loopWatch(nv, alt);
    };
    requestAnimationFrame(tick);
  };

  const advance = async () => {
    const s = st.current;
    if (s.adv) return;
    const next = s.cur + 1;
    const key = s.seq[next];
    if (!key) {
      cb.current.onEnd?.(curKey());
      return;
    }
    const v = vids.current[key];
    if (!v) return;
    s.adv = true;
    v.style.opacity = "0.001";
    v.style.zIndex = "1";
    v.style.display = "block";
    await ready(v);
    v.loop = false;
    v.playbackRate = FLIP_RATE[key] || 1;
    const r = FLIP_RANGES[key];
    const from = r ? r.from * v.duration : 0;
    if (Math.abs(v.currentTime - from) > 0.01) {
      v.currentTime = from;
      await seeked(v);
    }
    try {
      await v.play();
    } catch {
      s.adv = false;
      return;
    }
    await firstFrame(v);
    await frames(1);
    s.cur = next;
    s.loopKey = null;
    s.adv = false;
    show(key);
    hideOthers(key);
    if (r && r.to < 1) watchEnd(v, key, r.to * v.duration);
    if (FLIP_LOOP_ALT[key]) loopWatch(v, key);
    cb.current.onClip?.(key);
  };

  useLayoutEffect(() => {
    const box = boxRef.current;
    if (!box) return undefined;
    const onEnded = (e) => {
      const key = Object.keys(vids.current).find((k) => vids.current[k] === e.target);
      if (key && curKey() === key) advance();
    };
    Object.keys(FLIP_CLIPS).forEach((key) => {
      const v = jokerVideo(flipUrl(key));
      v.className = styles.jokerVid;
      const first = key === st.current.seq[0];
      v.style.opacity = first ? "1" : "0";
      v.style.display = first ? "block" : "none";
      v.loop = false;
      v.addEventListener("ended", onEnded);
      vids.current[key] = v;
      box.appendChild(v);
    });
    const s = st.current;
    const first = vids.current[s.seq[s.cur]];
    if (first) {
      s.started = true;
      first.loop = false;
      first.currentTime = 0;
      first.play().catch(() => {});
    }
    return () => {
      st.current.started = false;
      Object.values(vids.current).forEach((v) => {
        if (!v) return;
        v.removeEventListener("ended", onEnded);
        v.pause();
        v.currentTime = 0;
        v.loop = false;
        v.style.display = "";
        v.style.opacity = "";
        v.style.zIndex = "";
        releaseJoker(v);
      });
      vids.current = {};
    };
  }, []);

  useEffect(() => {
    const s = st.current;
    const v = vids.current[curKey()];
    if (v && (v.paused || FLIP_LOOPS.has(curKey())) && !s.adv && !isLast(s.cur)) advance();
  }, [seq]);

  useDoubleTap(boxRef, () => {
    const s = st.current;
    const key = curKey();
    const v = vids.current[key];
    if (s.adv || !v || FLIP_LOOP_ALT[key] || isLast(s.cur)) return;
    console.info("[park] орёл-решка: скип клипа", key);
    v.pause();
    advance();
  });

  return <div ref={boxRef} className={styles.jokerStage} />;
}

const MONEY_ICN = PARK_ASSET + "/Ui_Exchange_HUD/Icn_Currency.png";
const LINE_ICN = PARK_ASSET + "/Pyramid_Game/Pyramid_Line.png";

function WinPay({ show, value = WIN_PAY, low = false, cls = "" }) {
  if (!show) return null;
  return (
    <div className={`${styles.payFx} ${low ? styles.payFxLow : ""} ${cls}`}>
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
    let s = seconds;
    const iv = setInterval(() => {
      s -= 1;
      if (s <= 0) {
        clearInterval(iv);
        setLeft(0);
        endRef.current?.();
        return;
      }
      setLeft(s);
    }, 1000);
    return () => clearInterval(iv);
  }, [active, seconds]);
  return left;
}

function CoinFlip({ onFinish, forced }) {
  const [video] = useState(flipVideo);
  const [phase, setPhase] = useState(video ? "intro" : "pick");
  const [seq, setSeq] = useState(["start", "idle"]);
  const [picked, setPicked] = useState(null);
  const [autoPick, setAutoPick] = useState(false);
  const [landed, setLanded] = useState(null);
  const [win, setWin] = useState(null);
  const [payOn, setPayOn] = useState(false);
  const [flipping, setFlipping] = useState(false);
  const doneRef = useRef(false);
  const timers = useRef([]);
  const round = useServerRound("coinflip", phase === "pick");

  useEffect(() => {
    Object.values(FLIP_SIDE_IMG).forEach((src) => {
      const img = new Image();
      img.src = src;
    });
  }, []);

  useEffect(() => () => timers.current.forEach(clearTimeout), []);
  const later = (fn, ms) => timers.current.push(setTimeout(fn, ms));

  const play = async (side) => {
    if (doneRef.current) return;
    doneRef.current = true;
    setPicked(side);
    setPhase("chosen");
    if (video) setSeq((s) => [...s, "coinDown", "toss", "spin", "catch"]);
    else setFlipping(true);
    const [isWin] = await Promise.all([resolve5050(round, side === "heads" ? 0 : 1, forced), delay(video ? 450 : 1300)]);
    const shown = isWin ? side : side === "heads" ? "tails" : "heads";
    setWin(isWin);
    setLanded(shown);
    if (video) {
      setSeq((s) => [...s, shown]);
      return;
    }
    setFlipping(false);
    later(() => {
      setPhase("final");
      setPayOn(true);
      later(() => onFinish?.(isWin ? 1 : 0), 1500);
    }, 300);
  };

  const left = useCountdown(phase === "pick", PICK_SEC, () => {
    setAutoPick(true);
    play(Math.random() < 0.5 ? "heads" : "tails");
  });

  const onClip = (key) => {
    if (key === "idle") setPhase((p) => (p === "intro" ? "pick" : p));
    if (FLIP_FINAL.has(key)) {
      setPhase("final");
      later(() => setPayOn(true), FLIP_REVEAL_MS);
    }
  };
  const onEnd = (key) => {
    if (FLIP_FINAL.has(key)) later(() => onFinish?.(win ? 1 : 0), 300);
  };

  const btnsCls = [
    styles.flipBtns,
    phase === "pick" ? styles.flipBtnsShow : "",
    phase === "chosen" || phase === "final" ? styles.flipBtnsHide : "",
    autoPick ? styles.flipBtnsAuto : "",
  ].join(" ");

  return (
    <ParkPopupFrame title="Coin Flip" timeLeft={left} grow={video ? FLIP_OPEN_H : 0} compact={phase !== "pick"}>
      <div className={styles.game}>
        {video && <FlipStage seq={seq} onClip={onClip} onEnd={onEnd} />}
        {video && <img className={styles.flipOrn} src={LINE_ICN} alt="" draggable={false} />}
        <WinPay show={win === true && payOn} low={video} />
        {video ? (
          <div className={styles.flipWrap}>
            <div className={btnsCls}>
              {["heads", "tails"].map((side) => (
                <button
                  key={side}
                  className={`${styles.flipBtn} ${styles.flipCoinBtn} ${picked === side ? styles.flipPicked : ""}`}
                  disabled={phase !== "pick"}
                  onClick={() => play(side)}
                  style={{ "--ring-cy": FLIP_SIDE_RING[side].cy, "--ring-d": FLIP_SIDE_RING[side].d }}
                >
                  <img className={styles.flipCoinImg} src={FLIP_SIDE_IMG[side]} alt={side} draggable={false} />
                </button>
              ))}
            </div>
          </div>
        ) : (
          <>
            <div className={`${styles.coin} ${flipping ? styles.coinFlip : ""}`}>
              <span className={styles.coinFace}>{landed === "tails" ? "T" : "H"}</span>
            </div>
            {phase === "final" ? (
              <div className={styles.resultText}>
                {landed.toUpperCase()} — {win ? "WIN" : "LOSE"}
              </div>
            ) : (
              <div className={styles.hint}>{flipping ? "..." : "Heads or tails?"}</div>
            )}
            <div className={styles.controls}>
              <button className={styles.btn} disabled={phase !== "pick"} onClick={() => play("heads")}>Heads</button>
              <button className={styles.btn} disabled={phase !== "pick"} onClick={() => play("tails")}>Tails</button>
            </div>
          </>
        )}
      </div>
    </ParkPopupFrame>
  );
}

function CoinHand({ onFinish, forced }) {
  const [video] = useState(coinVideo);
  const [phase, setPhase] = useState(video ? "intro" : "pick");
  const [seq, setSeq] = useState(["start", "idle"]);
  const [picked, setPicked] = useState(null);
  const [autoPick, setAutoPick] = useState(false);
  const [coinHand, setCoinHand] = useState(null);
  const [win, setWin] = useState(null);
  const [payOn, setPayOn] = useState(false);
  const doneRef = useRef(false);
  const timers = useRef([]);
  const round = useServerRound("coinhand", phase === "pick");

  useEffect(() => () => timers.current.forEach(clearTimeout), []);
  const later = (fn, ms) => timers.current.push(setTimeout(fn, ms));

  const play = async (i) => {
    if (doneRef.current) return;
    doneRef.current = true;
    setPicked(i);
    setPhase("chosen");
    if (video) setSeq((s) => [...s, i === 0 ? "loopA" : "loopB"]);
    const [isWin] = await Promise.all([resolve5050(round, i, forced), delay(450)]);
    setWin(isWin);
    const hand = isWin ? i : 1 - i;
    setCoinHand(hand);
    if (video) {
      setSeq((s) => [...s, hand === 0 ? "left" : "right"]);
      return;
    }
    later(() => {
      setPhase("final");
      setPayOn(true);
      later(() => onFinish?.(isWin ? 1 : 0), 1500);
    }, 700);
  };

  const left = useCountdown(phase === "pick", PICK_SEC, () => {
    setAutoPick(true);
    play(Math.random() < 0.5 ? 0 : 1);
  });

  const onClip = (key) => {
    if (key === "idle") setPhase((p) => (p === "intro" ? "pick" : p));
    if (COIN_FINAL.has(key)) {
      setPhase("final");
      later(() => setPayOn(true), COIN_REVEAL_MS);
    }
  };
  const onEnd = (key) => {
    if (COIN_FINAL.has(key)) later(() => onFinish?.(win ? 1 : 0), 300);
  };

  const handsCls = [
    styles.coinHands,
    phase === "pick" ? styles.coinHandsShow : "",
    phase === "chosen" || phase === "final" ? styles.coinHandsHide : "",
    autoPick ? styles.coinHandsAuto : "",
  ].join(" ");

  return (
    <ParkPopupFrame title="Which Hand" timeLeft={left} grow={video ? COIN_OPEN_H : 0} compact={phase !== "pick"}>
      <div className={styles.game}>
        {video && <CoinStage seq={seq} onClip={onClip} onEnd={onEnd} />}
        {video && <img className={styles.coinOrn} src={LINE_ICN} alt="" draggable={false} />}
        <WinPay show={win === true && payOn} low={video} />
        {video ? (
          <div className={styles.coinWrap}>
            <div className={handsCls}>
              {[0, 1].map((i) => (
                <button
                  key={i}
                  className={`${styles.coinBtn} ${picked === i ? styles.coinPicked : ""}`}
                  disabled={phase !== "pick"}
                  onClick={() => play(i)}
                >
                  {i ? "Right" : "Left"}
                </button>
              ))}
            </div>
          </div>
        ) : (
          <>
            <div className={styles.hands}>
              {[0, 1].map((i) => (
                <button key={i} className={styles.hand} disabled={phase !== "pick"} onClick={() => play(i)}>
                  {coinHand === i && phase === "final" ? "🪙" : "✊"}
                </button>
              ))}
            </div>
            {phase === "final" ? (
              <div className={styles.resultText}>{win ? "WIN" : "LOSE"}</div>
            ) : (
              <div className={styles.hint}>{phase === "chosen" ? "..." : "Which hand hides the coin?"}</div>
            )}
          </>
        )}
      </div>
    </ParkPopupFrame>
  );
}

function Matches({ onFinish, forced }) {
  const [video] = useState(() => jokerSrc("x") !== "");
  const [phase, setPhase] = useState(video ? "intro" : "pick");
  const [seq, setSeq] = useState(["start", "loop"]);
  const [picked, setPicked] = useState(null);
  const [win, setWin] = useState(null);
  const [payOn, setPayOn] = useState(false);
  const doneRef = useRef(false);
  const timers = useRef([]);
  const round = useServerRound("matches", phase === "pick");

  useEffect(() => () => timers.current.forEach(clearTimeout), []);
  const later = (fn, ms) => timers.current.push(setTimeout(fn, ms));

  const play = async (i) => {
    if (doneRef.current) return;
    doneRef.current = true;
    setPicked(i);
    setPhase("chosen");
    const [isWin] = await Promise.all([resolve5050(round, i, forced), delay(450)]);
    setWin(isWin);
    if (video) {
      setSeq((s) => [...s, ...MATCH_BRANCH[i === 0 ? "left" : "right"][isWin ? "win" : "lose"]]);
      return;
    }
    later(() => {
      setPhase("final");
      setPayOn(true);
      later(() => onFinish?.(isWin ? 1 : 0), 1500);
    }, 700);
  };

  const left = useCountdown(phase === "pick", PICK_SEC, () => {
    play(Math.random() < 0.5 ? 0 : 1);
  });

  const onClip = (key) => {
    if (key === "loop") setPhase((p) => (p === "intro" ? "pick" : p));
    if (MATCH_FINAL.has(key)) {
      setPhase("final");
      later(() => setPayOn(true), key === "rightYes" ? 2600 : 2300);
    }
  };
  const onEnd = (key) => {
    if (MATCH_FINAL.has(key)) later(() => onFinish?.(win ? 1 : 0), 300);
  };

  const matchesCls = [
    styles.matches,
    phase === "pick" ? styles.matchesShow : "",
    phase === "chosen" || phase === "final" ? styles.matchesHide : "",
  ].join(" ");

  return (
    <ParkPopupFrame title="Matches" timeLeft={left} grow={MATCH_OPEN_H} compact={phase !== "pick"}>
      <div className={styles.game}>
        {video && <JokerStage seq={seq} loopKey="loop" onClip={onClip} onEnd={onEnd} />}
        {video && <img className={styles.jokerOrn} src={LINE_ICN} alt="" draggable={false} />}
        <WinPay show={win === true && payOn} low />
        <div className={`${styles.matchesWrap} ${phase === "pick" ? styles.matchesWrapOpen : ""}`}>
          <div className={matchesCls}>
            {[0, 1].map((i) => (
              <button
                key={i}
                className={`${styles.matchBtn} ${picked === i ? styles.matchPicked : ""}`}
                style={{ "--tilt": i ? "7deg" : "-7deg" }}
                disabled={phase !== "pick"}
                onClick={() => play(i)}
              >
                <span className={styles.matchBody}>
                  <span className={styles.match} />
                </span>
              </button>
            ))}
          </div>
          <div className={styles.matchesHint}>Pick the long match</div>
        </div>
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

const cabinForPrize = (got) => {
  const id = Number(got.id);
  const prize = got.tickets;
  if (typeof prize !== "number" || CABIN_TICKETS[id] === prize) return got;
  const ids = Object.keys(CABIN_TICKETS).map(Number).filter((k) => CABIN_TICKETS[k] === prize);
  if (!ids.length) return got;
  return { ...got, id: ids[(Number.isFinite(id) ? id : 0) % ids.length] };
};

function Wheel({ onFinish, forced }) {
  const [angle, setAngle] = useState(0);
  const [spinning, setSpinning] = useState(false);
  const [result, setResult] = useState(null);
  const timers = useRef([]);
  const round = useServerRound("wheel", true);
  const roundRef = useRef(round);
  roundRef.current = round;
  const askRef = useRef(null);

  useEffect(() => () => timers.current.forEach(clearTimeout), []);

  useEffect(() => {
    let cancelled = false;
    const ask = askRef.current || (askRef.current = (async () => {
      const rd = roundRef.current;
      if (rd && rd.on) {
        const r = await rd.play({});
        if (r && r.sector >= 1 && r.sector <= 8) {
          if (r.prize > 0) rd.wallet?.hold("tickets", r.prize);
          return { id: r.sector, tickets: r.prize };
        }
        return null;
      }
      try {
        const res = await withTimeout(gamesApi.wheelStart());
        const gid = gameField(res, "game_id");
        const spun = await withTimeout(gamesApi.wheelSpin(gid));
        return gameField(spun, "sector");
      } catch {
        return null;
      }
    })());
    const t = setTimeout(async () => {
      if (cancelled) return;
      setSpinning(true);
      const got =
        (await ask) ||
        (typeof forced === "number"
          ? cabinForPrize({ id: 1 + Math.floor(Math.random() * 8), tickets: forced })
          : { id: 1 + Math.floor(Math.random() * 8) });
      if (cancelled) return;
      const sector = cabinForPrize(got);
      const slot = CABIN_SLOT.indexOf(Number(sector.id));
      const idx = slot >= 0 ? slot : (((sector.id - 1) % 8) + 8) % 8;
      await new Promise((r) => requestAnimationFrame(() => requestAnimationFrame(r)));
      if (cancelled) return;
      setAngle(5 * 360 + ((((180 - idx * 45) % 360) + 360) % 360));
      timers.current.push(
        setTimeout(() => {
          setSpinning(false);
          setResult(sector);
          timers.current.push(setTimeout(() => onFinish?.(sector.tickets ?? CABIN_TICKETS[Number(sector.id)] ?? 0), 1700));
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
      <div className={styles.hint}>{result ? `+${result.tickets ?? CABIN_TICKETS[Number(result.id)] ?? 0}` : spinning ? "..." : ""}</div>
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
