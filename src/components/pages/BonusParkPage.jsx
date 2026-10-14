import { useState, useRef, useEffect, useLayoutEffect, useMemo, useCallback } from "react";
import { useApp } from "@/context/AppContext";
import TileField from "./TileField";
import Particles from "./Particles";
import Balloons from "./Balloons";
import Flag from "./Flag";
import FerrisSprite from "./FerrisSprite";
import CarouselSprite from "./CarouselSprite";
import CenterGlow from "./CenterGlow";
import ParkBox from "./ParkBox";
import MonitorScreen from "./MonitorScreen";
import ParkHud from "./ParkHud";
import PlayerRow from "./PlayerRow";
import ParkLoader from "./ParkLoader";
import TrainRide from "./TrainRide";
import ParkPopupFrame from "./ParkPopupFrame";

import CoinFlash from "./CoinFlash";
import usePopFrom, { usePopTo } from "./usePopFrom";
import { MINI_GAMES } from "./MiniGames";
import CashWin from "./CashWin";
import { WalletProvider, useWallet, centerOf } from "./parkWallet";
import { ParkSocket, ParkServerContext, useParkServer, parkConfig, parkMode, parkServerEnabled, parkAuth, saveParkAuth, clearParkAuth, parkSelfId, setParkSelfId } from "./parkSocket";
import styles from "./BonusParkPage.module.css";
import { PARK_ASSET, PARK_DESK } from "./parkAssetBase";

const ASSET = PARK_ASSET;

const ENERGY_GOAL = 100;
const SAFE_GOAL = 4;

const AUTO_HOLD_MS = 1355;
const AUTO_STOP_MS = 460;

const OUTCOME_SEQ = ["energy", "money", "game", "cash", "energy", "game", "money", "game", "cash", "energy", "game"];
const CASH_NOMS = [100, 50, 25, 15, 13, 10, 8, 7, 5];
const DOC_POOL = { energy: 442, joker: 6, cash: 83, cards: 55, pyramid: 17, wheel: 8, money: 29 };
const DOC_TOTAL = 640;
const CASH_WEIGHTS = [[100, 1], [50, 1], [25, 2], [15, 3], [13, 4], [10, 20], [8, 27], [7, 11], [5, 14]];
const WHEEL_DOC_POOL = [1, 2, 2, 2, 3, 3, 3, 5];
const JOKER_GAMES = ["coinhand", "matches", "coinflip"];
const SAFE_START = 5;
const PAID_TRY_CASH = 5;

const shuffleArr = (a) => {
  for (let i = a.length - 1; i > 0; i -= 1) {
    const j = Math.floor(Math.random() * (i + 1));
    [a[i], a[j]] = [a[j], a[i]];
  }
  return a;
};

const buildLocalPool = (size) => {
  const keys = Object.keys(DOC_POOL);
  const raw = keys.map((k) => (DOC_POOL[k] * size) / DOC_TOTAL);
  const base = raw.map((v) => Math.floor(v));
  let left = size - base.reduce((s, n) => s + n, 0);
  raw
    .map((v, i) => [i, v - Math.floor(v)])
    .sort((a, b) => b[1] - a[1])
    .forEach(([i]) => {
      if (left > 0) {
        base[i] += 1;
        left -= 1;
      }
    });
  const items = [];
  keys.forEach((k, i) => {
    for (let n = 0; n < base[i]; n += 1) items.push(k);
  });
  return shuffleArr(items);
};

const weightedCash = () => {
  const total = CASH_WEIGHTS.reduce((s, [, w]) => s + w, 0);
  let r = Math.random() * total;
  for (const [v, w] of CASH_WEIGHTS) {
    r -= w;
    if (r <= 0) return v;
  }
  return CASH_WEIGHTS[CASH_WEIGHTS.length - 1][0];
};
const TEST_FIRST_GAMES = ["pyramid", "cards", "coinflip", "coinflip", "coinhand", "coinhand", "matches", "matches"];
const TAKEN_FIELDS = new Set();
const JOKERS = new Set(["coinflip", "coinhand", "matches"]);
const JOKER_PAY = 50;
const TILE_COST = 1;
const COIN_FULL_PAY = 1;
const COIN_UNITS = 100;
const PURPLE_TOTAL = 29;
const BANK_HOLD_MS = 1100;
const BANK_COIN_MS = 1000;
const BANK_MIN_MS = 460;
const BANK_FLASH_MS = 720;
const BANK_DROP_MS = 280;
const SPIN_COST = 1;
const BET_OUT_MS = 260;
const BET_LOCKED = new Set([100]);
const CASH_SHOW_MS = 2200;
const POPUP_ARM_MS = 600;
const POP_OUT_MS = 420;

const GAPS_KEY = "bonuspark.seam6";
const SEAM_BASE = { w: -5.2, d: -5.6, inset: 0.4 };
const DEFAULT_SEAM = {
  tl: { w: -5.2, d: -5.6, inset: 0.2 },
  tr: { w: -5.2, d: -5.6, inset: 1.1 },
  ml: { w: -5.2, d: -5.6, inset: 0.4 },
  mr: { w: -5.2, d: -5.6, inset: 0 },
  bl: { w: -5.2, d: -5.6, inset: 0.6 },
  br: { w: -5.2, d: -5.6, inset: 0.3 },
};

function loadGaps() {
  try {
    const raw = localStorage.getItem(GAPS_KEY);
    if (raw) {
      const p = JSON.parse(raw);
      const out = {};
      for (const k of Object.keys(DEFAULT_SEAM)) out[k] = { ...SEAM_BASE, ...(p[k] || {}) };
      return out;
    }
  } catch {
    return JSON.parse(JSON.stringify(DEFAULT_SEAM));
  }
  return JSON.parse(JSON.stringify(DEFAULT_SEAM));
}

const FIELDS = [
  { key: "tl", sector: 1, cols: 10, rows: 10, seed: 0, offset: 0, gapX: 0, gapY: 0 },
  { key: "tr", sector: 2, cols: 10, rows: 10, seed: 1, offset: 100, gapX: 0, gapY: 0 },
  { key: "ml", sector: 3, cols: 10, rows: 12, seed: 2, offset: 200, gapX: -0.5, gapY: -0.5 },
  { key: "mr", sector: 4, cols: 10, rows: 12, seed: 3, offset: 320, gapX: -0.5, gapY: -0.5 },
  { key: "bl", sector: 5, cols: 10, rows: 10, seed: 4, offset: 440, gapX: -0.5, gapY: -0.5 },
  { key: "br", sector: 6, cols: 10, rows: 10, seed: 5, offset: 540, gapX: -0.5, gapY: -0.5 },
];

const SECTOR_OF = Object.fromEntries(FIELDS.map((f) => [f.key, f.sector]));
const KEY_OF_SECTOR = Object.fromEntries(FIELDS.map((f) => [String(f.sector), f.key]));

const urlSector = () => KEY_OF_SECTOR[new URLSearchParams(window.location.search).get("sector")] || null;

const setUrlSector = (key) => {
  const u = new URL(window.location.href);
  if (key) u.searchParams.set("sector", String(SECTOR_OF[key]));
  else u.searchParams.delete("sector");
  if (u.toString() !== window.location.href) window.history.replaceState(null, "", u.toString());
};


const FLASK_KEY = "bonuspark.flask3";
const DEFAULT_FLASK = {
  pos: [
    { x: 28.43, y: 29.82 },
    { x: 72.68, y: 29.53 },
  ],
  w: 5.225,
};

function loadFlask() {
  try {
    const raw = localStorage.getItem(FLASK_KEY);
    if (raw) return { ...DEFAULT_FLASK, ...JSON.parse(raw) };
  } catch {
    return DEFAULT_FLASK;
  }
  return DEFAULT_FLASK;
}

const COIN_KEY = "bonuspark.coin3";
const DEFAULT_COIN = { x: 50.3, y: 23.39, w: 10.695 };

function loadCoin() {
  try {
    const raw = localStorage.getItem(COIN_KEY);
    if (raw) return { ...DEFAULT_COIN, ...JSON.parse(raw) };
  } catch {
    return DEFAULT_COIN;
  }
  return DEFAULT_COIN;
}

const HUMAN = { x: 46.6, y: 38.9, w: 6.4, from: 0, to: 559, fps: 15 };

const WALKPATH = [
  { x: -3.4, y: 72.7 },
  { x: 5.6, y: 75.7 },
  { x: 25.5, y: 71.9 },
  { x: 45.9, y: 71.6 },
  { x: 56.4, y: 64.9 },
  { x: 60.1, y: 62.5 },
  { x: 70.4, y: 58.6 },
  { x: 68.5, y: 55.7 },
  { x: 72, y: 53.8 },
  { x: 57.8, y: 49.9 },
  { x: 50.7, y: 51 },
  { x: 44.3, y: 49.8 },
  { x: 31.5, y: 53.7 },
  { x: 35.3, y: 56 },
  { x: 31.2, y: 58.3 },
  { x: 44, y: 63.3 },
  { x: 30.3, y: 64.1 },
  { x: 11.1, y: 67.9 },
  { x: -1.3, y: 65.6 },
];

const GRAPH9_KEY = "bonuspark.walkgraph10";

const DEFAULT_GRAPH9 = {
  nodes: [
    { x: -2.67, y: 62.17 }, { x: 12.21, y: 65.35 }, { x: 6.49, y: 71.16 }, { x: 7.86, y: 73.06 },
    { x: 13.82, y: 71.69 }, { x: 21.6, y: 70 }, { x: 28.93, y: 68.62 }, { x: 33.05, y: 68.2 },
    { x: 47.94, y: 68.52 }, { x: 55.04, y: 66.4 }, { x: 56.87, y: 61.65 }, { x: 59.62, y: 59.85 },
    { x: 63.97, y: 58.26 }, { x: 69.47, y: 55.94 }, { x: 66.26, y: 53.29 }, { x: 70.38, y: 51.28 },
    { x: 66.03, y: 49.49 }, { x: 59.85, y: 48.01 }, { x: 53.89, y: 48.43 }, { x: 45.88, y: 47.79 },
    { x: 33.05, y: 51.28 }, { x: 36.26, y: 52.76 }, { x: 35.8, y: 54.14 }, { x: 32.82, y: 55.83 },
    { x: 38.55, y: 58.26 }, { x: 44.27, y: 60.8 }, { x: 49.31, y: 64.29 }, { x: 57.1, y: 68.62 },
    { x: 65.11, y: 68.2 }, { x: 77.25, y: 68.94 }, { x: 88.93, y: 72.22 }, { x: 94.66, y: 70.1 },
    { x: 91.22, y: 65.35 }, { x: 103.13, y: 61.65 },
  ],
  edges: [
    [0, 1], [1, 2], [2, 3], [3, 4], [4, 5], [5, 6], [6, 7], [7, 8], [8, 9], [9, 10],
    [10, 11], [11, 12], [12, 13], [13, 14], [14, 15], [15, 16], [16, 17], [17, 18], [18, 19], [19, 20],
    [20, 21], [21, 22], [22, 23], [23, 24], [24, 25], [25, 26], [26, 27], [27, 28], [28, 29], [29, 30],
    [30, 31], [31, 32], [32, 33],
  ],
};

const STOP9 = [];

const UI_KEY = "bonuspark.ui5";
const PATH_KEY = "bonuspark.sparkpath7";

const DEFAULT_UI = {
  spin: { x: 37.65, y: 80.3, w: 27 },
  monitor: { x: 14.13, y: 25.77, w: 69.31, h: 8.09 },
  glow: { x: 33.9, y: 23.68, w: 31.5, h: 9.1 },
};

const WIN_CHANCE = 0.5;

const SYMS = ["ice", "hat", "cocktail", "key", "mask", "castle", "crown", "coin", "crystal"];
const WILD = "hat";
const PLAIN = SYMS.filter((s) => s !== "coin" && s !== "crystal");
const NOHAT = PLAIN.filter((s) => s !== WILD);

const SYM_RU = {
  ice: "мороженое",
  hat: "шляпа",
  cocktail: "коктейль",
  key: "ключ",
  mask: "маска",
  castle: "замок",
  crown: "корона",
  coin: "монета",
  crystal: "кристалл",
};

const ru = (r) => r.map((k) => SYM_RU[k] || k).join(" / ");

const rnd = (list) => list[Math.floor(Math.random() * list.length)];
const shuffle = (a) => {
  const n = a.slice();
  for (let i = n.length - 1; i > 0; i -= 1) {
    const j = Math.floor(Math.random() * (i + 1));
    [n[i], n[j]] = [n[j], n[i]];
  }
  return n;
};
const two = (pool) => {
  const a = rnd(pool);
  const b = rnd(pool.filter((s) => s !== a));
  return [a, b];
};

const wildMix = (sym) => {
  const v = Math.random();
  if (v < 0.5) return [sym, sym, sym];
  if (v < 0.8) return shuffle([sym, sym, WILD]);
  return shuffle([sym, WILD, WILD]);
};

const COMBOS = [
  { pay: 1, build: () => shuffle(["coin", ...two(NOHAT)]) },
  {
    pay: 2,
    build: () =>
      Math.random() < 0.5
        ? shuffle(["coin", "coin", rnd(NOHAT)])
        : shuffle(["coin", WILD, rnd(NOHAT)]),
  },
  { pay: 3, build: () => (Math.random() < 0.8 ? wildMix("ice") : shuffle(["coin", WILD, WILD])) },
  { pay: 5, build: () => wildMix("cocktail") },
  { pay: 10, build: () => wildMix("key") },
  { pay: 15, build: () => wildMix("mask") },
  { pay: 50, build: () => wildMix("castle") },
  { pay: 100, build: () => wildMix("crown") },
  { pay: 200, build: () => [WILD, WILD, WILD] },
  { pay: 0, screen: true, build: () => shuffle(["crystal", rnd(NOHAT), rnd(NOHAT)]) },
];

const DEMO_SEQ = [
  ["coin", "coin", "hat"],
  ["coin", "hat", "hat"],
  ["key", "hat", "hat"],
  ["key", "key", "hat"],
  ["key", "key", "key"],
  ["castle", "castle", "castle"],
  ["castle", "castle", "hat"],
  ["mask", "mask", "mask"],
  ["crown", "crown", "crown"],
  ["ice", "ice", "ice"],
  ["cocktail", "cocktail", "cocktail"],
  ["key", "key", "key"],
  ["crown", "hat", "ice"],
  ["crown", "hat", "crown"],
  ["mask", "mask", "hat"],
  8, 8, 0, 0, 9, 9, 9, 9, "lose", 8, 0, "lose", 1, 2, "lose", 3, 4, "lose", 5, 6, "lose", 7, "lose"];

const CAR_PRIZE_SEQ = [25, 50, 100];
const CAR_STOP_AT = { 25: [1.68, 5.04, 8.36], 50: [0, 6.68], 100: [3.32] };

const pickStop = (v) => (Array.isArray(v) ? v[Math.floor(Math.random() * v.length)] : v);
const CAR_CARS = { 25: 2, 50: 1, 100: 0 };

const WHEEL_PRIZE_SEQ = [100, 200, 500];
const WHEEL_STOP_AT = { 100: 5.36, 200: 6.08, 500: 0 };

const RIDE_LAND_MS = 2600;

const CRYSTAL_GLOW_MS = 180;

const CRYSTAL_ORDER = ["crB", "crY", "crR", "crP"];
const TRAIN_FULL = 6;

const PAY_TABLE = { ice: 3, cocktail: 5, key: 10, mask: 15, castle: 50, crown: 100, hat: 200 };
const WIN_NUMS = [1, 2, 3, 5, 10, 15, 25, 50, 100, 200, 500];
const WIN_SHOW_MS = 3600;
const HAT_SHOW_MS = WIN_SHOW_MS;
const WIN_NUM_IN = 200;
const WIN_NUM_OUT = 500;
const WIN_LOCK_GAP = 130;
const WIN_OUT_MS = 620;
const ICON_SHOW_MS = { key: WIN_SHOW_MS, coin: WIN_SHOW_MS, crystal: WIN_SHOW_MS, hat: HAT_SHOW_MS };

const GAME_PRELOAD = [
  ...(PARK_DESK
    ? [
        ...["Castle", "Shot_1000", "Burger777_Icon", "Carousel", "Wheel", "Flash_Light_blue_2", "Crown_PopUp", "Decor_PopUp"].map(
          (n) => `${ASSET}/Piramid_Bg_Select_hd/${n}.png`
        ),
        `${ASSET}/Pyramid_Game_hd/Pyramid_Line.png`,
        `${ASSET}/Evnt_FW_hd/Arrow_Evnt_Ferris%20wheel.png`,
      ]
    : []),
  `${ASSET}/Piramid_Bg_Select/Castle.png`,
  `${ASSET}/Piramid_Bg_Select/Shot_1000.png`,
  `${ASSET}/Piramid_Bg_Select/Burger777_Icon.png`,
  `${ASSET}/Piramid_Bg_Select/Carousel.png`,
  `${ASSET}/Piramid_Bg_Select/Wheel.png`,
  `${ASSET}/Piramid_Bg_Select/Flash_Light_blue_2.png`,
  `${ASSET}/Piramid_Bg_Select/Crown_PopUp.png`,
  `${ASSET}/Piramid_Bg_Select/Decor_PopUp.png`,
  `${ASSET}/Pyramid_Game/Pyramid_Line.png`,
  `${ASSET}/Ui_Exchange_HUD/Icn_Currency.png`,
  `${ASSET}/Evnt_FW/Arrow_Evnt_Ferris%20wheel.png`,
  `${ASSET}/Evnt_FW/Evnt_FW_Base.png`,
  `${ASSET}/Evnt_FW/Evnt_FW_wheel.png`,
  `${ASSET}/Evnt_FW/Evnt_FW_Centr.png`,
  `${ASSET}/Evnt_FW/Evnt_FW_Leg.png`,
  `${ASSET}/Evnt_FW/Evnt_FW_Cabin_Blue.png`,
  `${ASSET}/Evnt_FW/Evnt_FW_Cabin_Purple.png`,
  `${ASSET}/Evnt_FW/Evnt_FW_Cabin_Red.png`,
  `${ASSET}/Evnt_FW/Evnt_FW_Cabin__Green.png`,
  `${ASSET}/Cards_Game/Card_all_0000_Layer-287-copy-14.webp`,
  `${ASSET}/Cards_Game${PARK_DESK ? "_hd" : ""}/Card_all_0000_Layer-287-copy-14.webp`,
  `${ASSET}/Cards_Game${PARK_DESK ? "_hd" : ""}/Shine_Card-Win.webp`,
  `${ASSET}/Cards_Game${PARK_DESK ? "_hd" : ""}/Light_Fx_Card.webp`,
];

const GAME_FONTS = ['16px "PyrUncial"', '700 16px "PyrSans"', '900 16px "PyrSans"'];

const SPECIAL_SYMS = new Set(["coin", "crystal"]);

const lineOf = (r) => {
  if (r.some((x) => SPECIAL_SYMS.has(x))) return null;
  const rest = r.filter((x) => x !== WILD);
  if (rest.length === 0) return WILD;
  return new Set(rest).size === 1 ? rest[0] : null;
};

const payFor = (r) => {
  const hats = r.filter((s) => s === WILD).length;
  if (hats === 3) return PAY_TABLE[WILD];
  const coins = r.filter((s) => s === "coin").length;
  const rest = r.filter((s) => s !== WILD && s !== "coin");
  if (coins > 0) return coins + hats;
  const uniq = [...new Set(rest)];
  if (uniq.length === 1) return PAY_TABLE[uniq[0]] || 0;
  if (hats === 1 && uniq.length === 2) return 1;
  return 0;
};

const SCREEN_FLAT = false;
const GLOW_DY_ARC = [6.55, -4.14, -2.41];
const GLOW_ROT_ARC = [-3.6, -2.5, 2.4];
const GLOW_DY = GLOW_DY_ARC;
const GLOW_ROT = SCREEN_FLAT ? [0, 0, 0] : GLOW_ROT_ARC;

const DEFAULT_PATH = [
  { x: 13.89, y: 27.03 },
  { x: 17.59, y: 26.86 },
  { x: 21.3, y: 26.69 },
  { x: 28.7, y: 26.43 },
  { x: 36.11, y: 26.22 },
  { x: 43.52, y: 26.05 },
  { x: 50.93, y: 25.96 },
  { x: 58.33, y: 25.92 },
  { x: 65.74, y: 25.96 },
  { x: 73.15, y: 26.05 },
  { x: 80.56, y: 26.22 },
  { x: 85.19, y: 26.3 },
  { x: 85.19, y: 33.28 },
  { x: 80.56, y: 33.11 },
  { x: 73.15, y: 33.02 },
  { x: 65.74, y: 32.89 },
  { x: 58.33, y: 32.85 },
  { x: 50.93, y: 32.89 },
  { x: 43.52, y: 32.98 },
  { x: 36.11, y: 33.15 },
  { x: 28.7, y: 33.32 },
  { x: 21.3, y: 33.62 },
  { x: 17.59, y: 33.75 },
  { x: 13.89, y: 33.92 },
];

const SPARK_DUR = 1.25;

const GLOW_SYMBOL = "coin";

const RIDE_PERIOD = { wheel: 3, carousel: 3 };

const RIDE_RUN_MS = 9000;
const RIDE_SOUND = { carousel: `${ASSET}/CAROUSEL.wav`, wheel: `${ASSET}/FERRIS_WHEEL.wav` };
const RIDE_FADE_MS = 500;
const ALWAYS_CRYSTAL = typeof window !== "undefined" && new URLSearchParams(window.location.search).get("crystal") === "1";

function loadJson(key, fallback) {
  try {
    const raw = localStorage.getItem(key);
    if (raw) return JSON.parse(raw);
  } catch {
    return fallback;
  }
  return fallback;
}

function saveJson(key, value) {
  try {
    localStorage.setItem(key, JSON.stringify(value));
  } catch {
    /* storage unavailable */
  }
}

const PBS = PARK_ASSET + "/Piramid_Bg_Select";
const YF = `${PBS}/Yacht_Fountain`;
const DECO_EDIT_EVENT = "bonuspark-deco-edit";
const DECO_DUMP_EVENT = "bonuspark-deco-dump";

const DECO_UP_KEY = "bonuspark.decoUp3";
const DEFAULT_DECO_UP = [
  { id: "hat", src: `${PBS}/Gold_Hat_map.png`, x: 39.94, y: 62.16, w: 22.78 },
  { id: "people", src: `${PBS}/People_Up.png`, x: -6.11, y: 34.61, w: 116.26 },
  { id: "fountain1", src: `${YF}/fountain_1.png`, x: 25.77, y: 34.6, w: 5.95 },
  { id: "fountain2a", src: `${YF}/fountain_2.png`, x: 29.34, y: 42.49, w: 4.41 },
  { id: "fountain2b", src: `${YF}/fountain_2.png`, x: 83.49, y: 33.3, w: 6.49 },
  { id: "fountain2c", src: `${YF}/fountain_2.png`, x: 69.65, y: 42.28, w: 6.88 },
  { id: "fountain3", src: `${YF}/fountain_3.png`, x: 46.42, y: 33.11, w: 4.58 },
  { id: "yacht1", src: `${YF}/Yacht_1.png`, x: 56.37, y: 12.67, w: 10.51 },
  { id: "yacht2", src: `${YF}/Yacht_2.png`, x: 90.58, y: 22.72, w: 3.93 },
  { id: "yacht3", src: `${YF}/Yacht_3.png`, x: 42.98, y: 12.48, w: 5 },
  { id: "yacht4", src: `${YF}/Yacht_4.png`, x: 2.2, y: 25.86, w: 3.02 },
];

const DECO_DOWN_KEY = "bonuspark.decoDown3";
const DEFAULT_DECO_DOWN = [{ id: "people", src: `${PBS}/People_Down.png`, x: 4.92, y: 1.75, w: 91.81 }];

function loadDeco(key, defaults) {
  const saved = loadJson(key, null);
  if (!Array.isArray(saved)) return defaults.map((d) => ({ ...d }));
  return defaults.map((d) => {
    const hit = saved.find((s) => s && s.id === d.id);
    return hit ? { ...d, x: hit.x, y: hit.y, w: hit.w } : { ...d };
  });
}

function ParkDecor({ storeKey, defaults, hatOn = false, onHat, hatPulse = 0 }) {
  const [items, setItems] = useState(() => loadDeco(storeKey, defaults));
  const [edit, setEdit] = useState(false);
  const [sel, setSel] = useState(null);
  const layerRef = useRef(null);
  const hatRef = useRef(null);
  const hatPress = () => {
    const el = hatRef.current;
    if (!el || !el.animate) return;
    el.animate(
      [
        { transform: "scale(1)" },
        { transform: "scale(0.92)", offset: 0.35 },
        { transform: "scale(1.03)", offset: 0.72 },
        { transform: "scale(1)" },
      ],
      { duration: 300, easing: "ease-out" }
    );
  };
  useEffect(() => {
    if (hatPulse) hatPress();
  }, [hatPulse]);

  useEffect(() => {
    const onEdit = (e) => setEdit(!!e.detail);
    const onDump = () => console.log(storeKey, JSON.stringify(items.map((i) => ({ id: i.id, x: i.x, y: i.y, w: i.w }))));
    window.addEventListener(DECO_EDIT_EVENT, onEdit);
    window.addEventListener(DECO_DUMP_EVENT, onDump);
    return () => {
      window.removeEventListener(DECO_EDIT_EVENT, onEdit);
      window.removeEventListener(DECO_DUMP_EVENT, onDump);
    };
  }, [items, storeKey]);

  useEffect(
    () => () => {
      delete document.body.dataset.deco;
    },
    []
  );

  const patch = (id, next) =>
    setItems((prev) => {
      const out = prev.map((it) => (it.id === id ? { ...it, ...next } : it));
      saveJson(storeKey, out.map((i) => ({ id: i.id, x: i.x, y: i.y, w: i.w })));
      return out;
    });

  const startDrag = (id, kind) => (e) => {
    if (!edit) return;
    e.preventDefault();
    e.stopPropagation();
    setSel(id);
    const mode = kind === "move" && e.shiftKey ? "size" : kind;
    const layer = layerRef.current;
    const host = layer.getBoundingClientRect();
    const k = host.width / (layer.offsetWidth || 1) || 1;
    const node = kind === "move" ? e.currentTarget : e.currentTarget.parentElement;
    const base = items.find((it) => it.id === id);
    const sx = e.clientX;
    const sy = e.clientY;
    let dxp = 0;
    let dyp = 0;
    let nw = base.w;
    try {
      e.currentTarget.setPointerCapture(e.pointerId);
    } catch {
      /* капту не поддержали */
    }
    const move = (ev) => {
      ev.preventDefault();
      const dx = ev.clientX - sx;
      const dy = ev.clientY - sy;
      if (mode === "move") {
        dxp = (dx / host.width) * 100;
        dyp = (dy / host.height) * 100;
        node.style.transform = `translate(${(dx / k).toFixed(2)}px, ${(dy / k).toFixed(2)}px)`;
      } else {
        nw = Math.max(0.5, base.w + (dx / host.width) * 100);
        node.style.width = `${nw.toFixed(2)}%`;
      }
    };
    const up = () => {
      window.removeEventListener("pointermove", move);
      window.removeEventListener("pointerup", up);
      window.removeEventListener("pointercancel", up);
      node.style.transform = "";
      if (mode === "move") patch(id, { x: +(base.x + dxp).toFixed(2), y: +(base.y + dyp).toFixed(2) });
      else patch(id, { w: +nw.toFixed(2) });
    };
    window.addEventListener("pointermove", move, { passive: false });
    window.addEventListener("pointerup", up);
    window.addEventListener("pointercancel", up);
  };

  return (
    <div ref={layerRef} className={`${styles.decoLayer} ${edit ? styles.decoLayerEdit : ""}`}>
      {items.map((it, i) => (
        <span
          key={it.id}
          className={`${styles.decoItem} ${edit ? styles.decoItemEdit : ""} ${sel === it.id ? styles.decoItemOn : ""} ${it.id === "hat" && onHat ? styles.goldHat : ""} ${it.id === "hat" && hatOn ? styles.goldHatOn : ""}`}
          style={{ left: `${it.x}%`, top: `${it.y}%`, width: `${it.w}%`, zIndex: i + 1 }}
          ref={it.id === "hat" ? hatRef : undefined}
          onPointerDown={startDrag(it.id, "move")}
          onClick={
            it.id === "hat" && onHat && !edit
              ? () => {
                  hatPress();
                  onHat();
                }
              : undefined
          }
        >
          <img className={styles.decoImg} src={it.src} alt="" draggable={false} />
          {it.id === "hat" && onHat && (
            <span className={styles.hatClip} style={{ WebkitMaskImage: `url(${it.src})`, maskImage: `url(${it.src})` }}>
              <img className={styles.hatShine} src={`${ASSET}/spin_button/Btn_Spin_Light.png`} alt="" draggable={false} />
              <img className={styles.hatStar} src={`${ASSET}/spin_button/Star_hat.png`} alt="" draggable={false} />
            </span>
          )}
          {edit && <span className={styles.decoHandle} onPointerDown={startDrag(it.id, "size")} />}
          {edit && <span className={styles.decoTag}>{it.id}</span>}
        </span>
      ))}
    </div>
  );
}

const GLOW_KEY = "bonuspark.glow2";
const GLOW_EVENT = "bonuspark.glow2";

function loadGlow() {
  try {
    return localStorage.getItem(GLOW_KEY) !== "0";
  } catch {
    return true;
  }
}

export function setBonusParkGlow(on) {
  const next = !!on;
  try {
    localStorage.setItem(GLOW_KEY, next ? "1" : "0");
  } catch {
    /* storage unavailable */
  }
  window.dispatchEvent(new CustomEvent(GLOW_EVENT, { detail: next }));
}

function loadGraph9() {
  try {
    const raw = localStorage.getItem(GRAPH9_KEY);
    if (raw) return JSON.parse(raw);
  } catch {
    return DEFAULT_GRAPH9;
  }
  return DEFAULT_GRAPH9;
}

const FLAGS_KEY = "bonuspark.flags3";
const DEFAULT_FLAGS = [
  { id: "f1", img: "Flag_1.png", x: 32.03, y: 12.73, w: 3.8 },
  { id: "f2", img: "Flag_2.png", x: 39.74, y: 9.87, w: 3.99 },
  { id: "fc", img: "Flag_centr.png", x: 52.15, y: 9.08, w: 3.917 },
  { id: "f3", img: "Flag_2.png", x: 64.46, y: 10.05, w: 3.99 },
  { id: "f4", img: "Flag_1.png", x: 73.09, y: 11.62, w: 3.619 },
  { id: "lb", img: "Flag_Blue_R.png", x: 15.8, y: 44.55, w: 4.5 },
  { id: "lp", img: "Flag_Purple_L.png", x: 85.92, y: 45.31, w: 4.5 },
];

function loadFlags() {
  try {
    const raw = localStorage.getItem(FLAGS_KEY);
    if (raw) return JSON.parse(raw);
  } catch {
    return DEFAULT_FLAGS;
  }
  return DEFAULT_FLAGS;
}

function WindFlag({ img, x, y, w, delay, dur, onPointerDown, onPointerMove, onPointerUp, className = "" }) {
  const banner = img.includes("Blue") || img.includes("Purple");
  return (
    <img
      className={`${banner ? styles.windBanner : styles.windFlag} ${className}`}
      src={`${ASSET}/${img}`}
      alt=""
      draggable={false}
      onPointerDown={onPointerDown}
      onPointerMove={onPointerMove}
      onPointerUp={onPointerUp}
      onPointerCancel={onPointerUp}
      style={{
        left: `${x}%`,
        top: `${y}%`,
        width: `${w}%`,
        animationDelay: banner ? "0s" : delay,
        animationDuration: banner ? undefined : dur,
        pointerEvents: onPointerDown ? "auto" : "none",
        cursor: onPointerDown ? "move" : undefined,
      }}
    />
  );
}

function Flask({ x, y, w, fill, onPointerDown }) {
  return (
    <div
      className={styles.flask}
      onPointerDown={onPointerDown}
      style={{
        left: `${x}%`,
        top: `${y}%`,
        width: `${w}%`,
        pointerEvents: onPointerDown ? "auto" : undefined,
        cursor: onPointerDown ? "move" : undefined,
      }}
    >
      <div className={styles.flaskGlass}>
        <img className={styles.flaskBg} src={`${ASSET}/opt/bg_flask.webp`} alt="" />
        <img
          className={styles.flaskFill}
          src={`${ASSET}/opt/inside_flask.webp`}
          alt=""
          style={{ clipPath: `inset(${(1 - fill) * 100}% 0 0 0)` }}
        />
      </div>
      <img className={styles.flaskFront} src={`${ASSET}/opt/front_flask.webp`} alt="" />
    </div>
  );
}

const HUD2 = `${ASSET}/Ui_Exchange_HUD`;

const FLASK_MARK_LV = [100, 57.6, 33.12, 5.8, 0];

function ParkFlask({ x, y, w, fill, tone, icon, label, steps = 0, chipDy = 0, edit, picked, onGrab, onMove, onDrop }) {
  const clamped = Math.max(0, Math.min(1, fill));
  const step = steps ? Math.max(0, Math.min(steps, Math.round(clamped * steps))) : 0;
  const lv = steps ? FLASK_MARK_LV[step].toFixed(2) : ((1 - clamped) * 100).toFixed(2);
  return (
    <div
      className={`${styles.pflask} ${edit ? styles.editable : ""} ${picked ? styles.picked : ""}`}
      style={{ left: `${x}%`, top: `${y}%`, width: `${w}%` }}
      onPointerDown={edit ? onGrab : undefined}
      onPointerMove={edit ? onMove : undefined}
      onPointerUp={edit ? onDrop : undefined}
      onPointerCancel={edit ? onDrop : undefined}
    >
      <div className={styles.pflaskGlass}>
        <img className={styles.pflaskBg} src={`${ASSET}/opt/bg_flask.webp`} alt="" draggable={false} />
        <img
          className={styles.pflaskFill}
          src={`${HUD2}/inside_flask_${tone}.png`}
          alt=""
          draggable={false}
          style={{ "--lv": `${lv}%` }}
        />
      </div>
      <img className={styles.pflaskFront} src={`${ASSET}/opt/front_flask.webp`} alt="" draggable={false} />
      <span
        className={`${styles.pflaskChip} ${tone === "Purple" ? styles.pflaskChipPurple : ""}`}
        style={{ "--chipdy": `${chipDy}%`, "--chipk": (w / 6.46).toFixed(4) }}
      >
        <img className={styles.pflaskChipIcn} src={`${HUD2}/${icon}`} alt="" draggable={false} />
        <span className={styles.pflaskChipVal}>{label}</span>
      </span>
    </div>
  );
}

const COVER = {
  tl: "opt/Cover_Field.webp",
  ml: "opt/Cover_Field.webp",
  bl: "opt/Cover_Field.webp",
  tr: "opt/Cover_Field_2.webp",
  mr: "opt/Cover_Field_2.webp",
  br: "opt/Cover_Field_2.webp",
};

const DEFAULT_FIELDS = {
  tl: { tl: { x: 27.78, y: 37.4 }, tr: { x: 45.58, y: 37.45 }, br: { x: 45.04, y: 45.69 }, bl: { x: 26, y: 45.64 } },
  tr: { tl: { x: 55.46, y: 37.39 }, tr: { x: 73.43, y: 37.38 }, br: { x: 75.19, y: 45.64 }, bl: { x: 55.86, y: 45.69 } },
  ml: { tl: { x: 25.71, y: 47.24 }, tr: { x: 45.01, y: 47.31 }, br: { x: 44.31, y: 58.12 }, bl: { x: 23.55, y: 58.08 } },
  mr: { tl: { x: 56.1, y: 47.27 }, tr: { x: 75.36, y: 47.27 }, br: { x: 77.49, y: 58.09 }, bl: { x: 56.77, y: 58.12 } },
  bl: { tl: { x: 23.04, y: 60.07 }, tr: { x: 44.28, y: 60.04 }, br: { x: 43.77, y: 69.85 }, bl: { x: 20.97, y: 69.82 } },
  br: { tl: { x: 56.77, y: 60.13 }, tr: { x: 77.85, y: 60.13 }, br: { x: 79.97, y: 69.86 }, bl: { x: 57.22, y: 69.88 } },
};

function shrinkQuad(q, pct) {
  if (!pct) return q;
  const k = 1 - pct / 100;
  const cx = (q.tl.x + q.tr.x + q.br.x + q.bl.x) / 4;
  const cy = (q.tl.y + q.tr.y + q.br.y + q.bl.y) / 4;
  const f = (p) => ({ x: cx + (p.x - cx) * k, y: cy + (p.y - cy) * k });
  return { tl: f(q.tl), tr: f(q.tr), br: f(q.br), bl: f(q.bl) };
}

const FIELDS_KEY = "bonuspark.fields3";

function loadFields() {
  try {
    const raw = localStorage.getItem(FIELDS_KEY);
    if (raw) return { ...DEFAULT_FIELDS, ...JSON.parse(raw) };
  } catch {
    return DEFAULT_FIELDS;
  }
  return DEFAULT_FIELDS;
}

const DECOR_KEY = "bonuspark.decor3";
const DEFAULT_DECOR = [
  { id: "b1", type: "balloon", x: 1.41, y: 56.17, s: 0.784 },
  { id: "b2", type: "balloon", x: 76.59, y: 55.32, s: 0.784 },
  { id: "f1", type: "flag", x: 66.97, y: 75.4, rot: 6, flip: false },
  { id: "f2", type: "flag", x: 34.92, y: 75.48, rot: 6, flip: true },
];

function loadDecor() {
  try {
    const raw = localStorage.getItem(DECOR_KEY);
    if (raw) return JSON.parse(raw);
  } catch {
    return DEFAULT_DECOR;
  }
  return DEFAULT_DECOR;
}

const ZONE_KEY = "bonuspark.zones3";
const DEFAULT_ZONES = {
  tl: { tl: { x: 18.97, y: 36.78 }, tr: { x: 47.41, y: 36.85 }, br: { x: 43.73, y: 47.29 }, bl: { x: 15.15, y: 47.22 } },
  tr: { tl: { x: 56.49, y: 36.85 }, tr: { x: 84.99, y: 36.85 }, br: { x: 84.99, y: 47.2 }, bl: { x: 56.38, y: 47.11 } },
  ml: { tl: { x: 16.36, y: 46.2 }, tr: { x: 47.15, y: 46.11 }, br: { x: 42.85, y: 59.74 }, bl: { x: 11.68, y: 59.64 } },
  mr: { tl: { x: 57.29, y: 46.15 }, tr: { x: 88, y: 46.22 }, br: { x: 88.41, y: 59.69 }, bl: { x: 57.23, y: 59.7 } },
  bl: { tl: { x: 12.53, y: 59.06 }, tr: { x: 46.33, y: 59.12 }, br: { x: 41.61, y: 71.23 }, bl: { x: 7.89, y: 71.2 } },
  br: { tl: { x: 58.35, y: 59.11 }, tr: { x: 92.01, y: 59.1 }, br: { x: 91.81, y: 71.26 }, bl: { x: 58.17, y: 71.15 } },
};

function loadZones() {
  try {
    const raw = localStorage.getItem(ZONE_KEY);
    if (raw) return { ...DEFAULT_ZONES, ...JSON.parse(raw) };
  } catch {
    return DEFAULT_ZONES;
  }
  return DEFAULT_ZONES;
}

const TILECFG_KEY = "bonuspark.tilecfg";
const DEFAULT_TILECFG = { scaleX: 1.15, scaleY: 1.4 };

function loadTileCfg() {
  try {
    const raw = localStorage.getItem(TILECFG_KEY);
    if (raw) return { ...DEFAULT_TILECFG, ...JSON.parse(raw) };
  } catch {
    return DEFAULT_TILECFG;
  }
  return DEFAULT_TILECFG;
}


const COVERS_KEY = "bonuspark.covers3";
const DEFAULT_COVERS = {
  tl: { tl: { x: 26.06, y: 37.4 }, tr: { x: 45.58, y: 37.33 }, br: { x: 45.44, y: 45.73 }, bl: { x: 25.93, y: 45.6 } },
  tr: { tl: { x: 53.52, y: 37.36 }, tr: { x: 73.37, y: 37.39 }, br: { x: 75.5, y: 45.58 }, bl: { x: 55.97, y: 45.69 } },
  ml: { tl: { x: 23.7, y: 47.24 }, tr: { x: 45.14, y: 47.21 }, br: { x: 44.85, y: 58.09 }, bl: { x: 23.49, y: 58 } },
  mr: { tl: { x: 53.87, y: 47.22 }, tr: { x: 75.35, y: 47.19 }, br: { x: 78.01, y: 58.08 }, bl: { x: 56.67, y: 58.15 } },
  bl: { tl: { x: 21.07, y: 60.06 }, tr: { x: 44.18, y: 60.07 }, br: { x: 44.17, y: 69.85 }, bl: { x: 21, y: 69.81 } },
  br: { tl: { x: 54.29, y: 60.08 }, tr: { x: 77.83, y: 60.13 }, br: { x: 80.51, y: 69.75 }, bl: { x: 57.22, y: 69.79 } },
};

function loadCovers() {
  try {
    const raw = localStorage.getItem(COVERS_KEY);
    if (raw) return { ...DEFAULT_COVERS, ...JSON.parse(raw) };
  } catch {
    return { ...DEFAULT_COVERS };
  }
  return { ...DEFAULT_COVERS };
}

const hasMouse = () =>
  typeof window !== "undefined" && typeof window.matchMedia === "function"
    ? window.matchMedia("(hover: hover) and (pointer: fine)").matches
    : false;

function Overview({ onPick }) {
  const [armed, setArmed] = useState(null);

  const tap = (key) => {
    if (hasMouse()) {
      onPick(key);
      return;
    }
    if (armed !== key) {
      setArmed(key);
      return;
    }
    onPick(key);
  };

  return (
    <div className={styles.overview}>
      <img className={styles.overviewImg} src={`${ASSET}/main_vertical.webp`} alt="Bonus location" />
      <button
        type="button"
        className={`${styles.half} ${styles.top} ${armed === "park" ? styles.halfArmed : ""}`}
        onClick={() => tap("park")}
      >
        <span className={styles.halfLabel}>Park</span>
      </button>
      <button
        type="button"
        className={`${styles.half} ${styles.bottom} ${armed === "grid" ? styles.halfArmed : ""}`}
        onClick={() => tap("grid")}
      >
        <span className={styles.halfLabel}>Fields</span>
      </button>
    </div>
  );
}

const IDLE_LEAVE_MS = 5 * 60 * 1000;
const REFILL_START_MS = 400;
const REFILL_ROW_MS = 90;
const REFILL_POP_MS = 460;
const REMOTE_DRAIN_MS = 1200;
const BANK_REFILL_DELAY_MS = 1500;

function GridGame({ onPopup, onZoomState, onCoinRing }) {
  const wallet = useWallet();
  const park = useParkServer();
  const client = park?.client || null;
  const stageRef = useRef(null);

  useEffect(() => {
    const ph = document.querySelector("[class*='phone']");
    if (!ph) return undefined;
    ph.dataset.framed = "grid";
    document.body.dataset.frame = "grid";
    return () => {
      delete ph.dataset.framed;
      delete document.body.dataset.frame;
    };
  }, []);
  const [fields, setFields] = useState(loadFields);
  const [decor, setDecor] = useState(loadDecor);
  const [selectedZone, setSelectedZone] = useState(() => (client ? null : urlSector()));
  const [zoomZone, setZoomZone] = useState(() => (client ? null : urlSector()));
  const [lit, setLit] = useState(null);
  const [destroyed, setDestroyed] = useState(() => new Set());
  const [zones, setZones] = useState(loadZones);
  const [cfg] = useState(loadTileCfg);
  const [covers, setCovers] = useState(loadCovers);
  const [tankFlashes, setTankFlashes] = useState([]);
  const [hookKey, setHookKey] = useState(0);
  const [hookOn, setHookOn] = useState(false);
  const hookTimer = useRef(null);
  const flashId = useRef(0);
  const safeSum = useRef(0);
  const particlesRef = useRef(null);
  const [flask, setFlask] = useState(loadFlask);
  const [flags, setFlags] = useState(loadFlags);
  const [coinFill, setCoinFill] = useState(0);
  const [coinPulse, setCoinPulse] = useState(0);
  const [coinFullKey, setCoinFullKey] = useState(0);
  const [energy, setEnergy] = useState(0);
  const [safes, setSafes] = useState(0);
  const [bank, setBank] = useState(SAFE_START);
  const [bankBase, setBankBase] = useState(SAFE_START);
  const [bankDrain, setBankDrain] = useState(null);
  const yellowShown = client ? (bankDrain != null ? (bankDrain > 0 ? Math.round(((bankBase + bankDrain) * energy) / bankDrain) : 0) : bankBase + energy) : energy;
  const bankRef = useRef(bank);
  bankRef.current = bank;
  const poolRef = useRef({});
  const wheelPoolRef = useRef({ list: shuffleArr(WHEEL_DOC_POOL.slice()), i: 0 });
  const jokerRef = useRef({ i: 0, next: {} });
  const [coin, setCoin] = useState(loadCoin);
  const [edit, setEdit] = useState(false);
  const [sel, setSel] = useState(null);
  const [layer, setLayer] = useState("field");
  const [view, setView] = useState({ z: 1, x: 50, y: 50 });

  const dragRef = useRef(null);
  const [tilePopup, setTilePopup] = useState(null);
  const popupCb = useRef(onPopup);
  popupCb.current = onPopup;

  useEffect(() => {
    popupCb.current?.(!!tilePopup);
  }, [tilePopup]);
  const [marks, setMarks] = useState({});
  const [boardSeed, setBoardSeed] = useState(0);
  const purpleFound = useRef(0);
  const restartPending = useRef(false);
  const outcomeIdx = useRef(0);
  const gameIdx = useRef(0);
  const [gaps, setGaps] = useState(loadGaps);

  const coinRef = useRef(coin);
  coinRef.current = coin;

  const bankMineRef = useRef(true);
  useEffect(() => {
    if (!client || !park?.balance) return;
    const b = park.balance;
    setEnergy(Math.max(0, b.energy || 0));
    setSafes(Math.max(0, Math.min(SAFE_GOAL - 1, b.safe || 0)));
    setCoinFill(Math.max(0, Math.min(1, b.coin_fill || 0)));
    if (typeof b.safe_bank === "number" && typeof b.energy === "number") setBankBase(b.safe_bank - b.energy);
  }, [client, park?.balance]);

  useEffect(() => {
    if (!client || !park?.sector) return;
    const key = KEY_OF_SECTOR[String(park.sector)];
    if (!key) return;
    setSelectedZone(key);
    setZoomZone(key);
  }, [client, park?.sector]);

  useEffect(() => {
    if (!client || park?.sector) return;
    setZoomZone(null);
    setLit(null);
  }, [client, park?.sector]);

  const autoLeaveRef = useRef(false);
  const inflightRef = useRef(0);
  const mySentCellsRef = useRef(new Set());
  const openQueueRef = useRef(Promise.resolve());
  const [flightTick, setFlightTick] = useState(0);

  const lastOpenRef = useRef(Date.now());
  useEffect(() => {
    if (!client || !park?.sector) return undefined;
    lastOpenRef.current = Date.now();
    const check = (why) => {
      if (Date.now() - lastOpenRef.current < IDLE_LEAVE_MS) return;
      console.info("[park] 5 минут без плиток, выхожу с поля", why);
      setZoomZone(null);
      setLit(null);
      park.leaveSector?.();
    };
    const iv = setInterval(() => check("таймер"), 15000);
    autoLeaveRef.current = false;
    const onVisible = () => {
      if (document.visibilityState === "visible") check("вернулись в приложение");
    };
    document.addEventListener("visibilitychange", onVisible);
    window.addEventListener("focus", onVisible);
    window.addEventListener("pageshow", onVisible);
    return () => {
      clearInterval(iv);
      document.removeEventListener("visibilitychange", onVisible);
      window.removeEventListener("focus", onVisible);
      window.removeEventListener("pageshow", onVisible);
    };
  }, [client, park?.sector]);

  const saveJson = (key, val) => {
    try {
      localStorage.setItem(key, JSON.stringify(val));
    } catch {
      /* хранилище недоступно */
    }
  };

  const editStart = (kind, id, corner) => (e) => {
    if (!edit) return;
    e.stopPropagation();
    e.preventDefault();
    const host = stageRef.current;
    if (!host) return;
    const r = host.getBoundingClientRect();
    setSel({ kind: kind === "corner" ? "quad" : kind, id });
    dragRef.current = { kind, id, corner, r, sx: e.clientX, sy: e.clientY, moved: false };
    e.currentTarget.setPointerCapture?.(e.pointerId);
  };

  const editMove = (e) => {
    const d = dragRef.current;
    if (!d) return;
    const dx = ((e.clientX - d.sx) / d.r.width) * 100;
    const dy = ((e.clientY - d.sy) / d.r.height) * 100;
    if (Math.abs(dx) < 0.05 && Math.abs(dy) < 0.05) return;
    d.moved = true;
    d.sx = e.clientX;
    d.sy = e.clientY;
    if (d.kind === "flask") {
      setFlask((f) => {
        const pos = f.pos.map((p, i) => (i === d.id ? { x: +(p.x + dx).toFixed(2), y: +(p.y + dy).toFixed(2) } : p));
        const next = { ...f, pos };
        saveJson(FLASK_KEY, next);
        return next;
      });
    } else if (d.kind === "coin") {
      setCoin((c) => {
        const next = { ...c, x: +(c.x + dx).toFixed(2), y: +(c.y + dy).toFixed(2) };
        saveJson(COIN_KEY, next);
        return next;
      });
    } else if (d.kind === "decor") {
      setDecor((list) => {
        const next = list.map((it) =>
          it.id === d.id ? { ...it, x: +(it.x + dx).toFixed(2), y: +(it.y + dy).toFixed(2) } : it
        );
        saveJson(DECOR_KEY, next);
        return next;
      });
    } else if (d.kind === "flag") {
      setFlags((list) => {
        const next = list.map((it) =>
          it.id === d.id ? { ...it, x: +(it.x + dx).toFixed(2), y: +(it.y + dy).toFixed(2) } : it
        );
        saveJson(FLAGS_KEY, next);
        return next;
      });
    } else if (d.kind === "quad") {
      moveQuad(d.id, dx, dy);
    } else if (d.kind === "corner") {
      moveCorner(d.id, d.corner, dx, dy);
    }
  };

  const editEnd = () => {
    dragRef.current = null;
  };

  const sizeSel = (k) => {
    if (!sel) return;
    if (sel.kind === "quad") {
      scaleQuad(sel.id, k);
    } else if (sel.kind === "flask") {
      setFlask((f) => {
        const next = { ...f, w: +(f.w * k).toFixed(3) };
        saveJson(FLASK_KEY, next);
        return next;
      });
    } else if (sel.kind === "coin") {
      setCoin((c) => {
        const next = { ...c, w: +(c.w * k).toFixed(3) };
        saveJson(COIN_KEY, next);
        return next;
      });
    } else if (sel.kind === "decor") {
      setDecor((list) => {
        const next = list.map((it) => (it.id === sel.id ? { ...it, s: +((it.s || 1) * k).toFixed(3) } : it));
        saveJson(DECOR_KEY, next);
        return next;
      });
    } else if (sel.kind === "flag") {
      setFlags((list) => {
        const next = list.map((it) => (it.id === sel.id ? { ...it, w: +(it.w * k).toFixed(3) } : it));
        saveJson(FLAGS_KEY, next);
        return next;
      });
    }
  };

  const LAYERS = {
    field: { get: () => fields, set: setFields, key: FIELDS_KEY },
    cover: { get: () => covers, set: setCovers, key: COVERS_KEY },
    zone: { get: () => zones, set: setZones, key: ZONE_KEY },
  };

  const quadOf = (id) => LAYERS[layer].get()[id];

  const putQuad = (id, q) => {
    const L = LAYERS[layer];
    L.set((prev) => {
      const next = { ...prev, [id]: q };
      saveJson(L.key, next);
      return next;
    });
  };

  const moveQuad = (id, dx, dy) => {
    const q = quadOf(id);
    if (!q) return;
    const shift = (p) => ({ x: +(p.x + dx).toFixed(2), y: +(p.y + dy).toFixed(2) });
    putQuad(id, { tl: shift(q.tl), tr: shift(q.tr), br: shift(q.br), bl: shift(q.bl) });
  };

  const moveCorner = (id, corner, dx, dy) => {
    const q = quadOf(id);
    if (!q) return;
    putQuad(id, { ...q, [corner]: { x: +(q[corner].x + dx).toFixed(2), y: +(q[corner].y + dy).toFixed(2) } });
  };

  const scaleQuad = (id, k) => {
    const q = quadOf(id);
    if (!q) return;
    const cx = (q.tl.x + q.tr.x + q.br.x + q.bl.x) / 4;
    const cy = (q.tl.y + q.tr.y + q.br.y + q.bl.y) / 4;
    const s = (p) => ({ x: +(cx + (p.x - cx) * k).toFixed(2), y: +(cy + (p.y - cy) * k).toFixed(2) });
    putQuad(id, { tl: s(q.tl), tr: s(q.tr), br: s(q.br), bl: s(q.bl) });
  };

  const onZoomWheel = (e) => {
    const host = stageRef.current;
    if (!host) return;
    e.preventDefault();
    const r = host.getBoundingClientRect();
    const cx = ((e.clientX - r.left) / r.width) * 100;
    const cy = ((e.clientY - r.top) / r.height) * 100;
    const up = e.deltaY < 0;
    setView((v) => {
      const z1 = Math.max(1, Math.min(6, v.z * (up ? 1.18 : 1 / 1.18)));
      if (z1 === v.z) return v;
      if (z1 <= 1.001) return { z: 1, x: 50, y: 50 };
      const half = 50 / z1;
      const clamp = (t) => Math.min(100 - half, Math.max(half, t));
      return {
        z: z1,
        x: +clamp(cx - (v.z / z1) * (cx - v.x)).toFixed(2),
        y: +clamp(cy - (v.z / z1) * (cy - v.y)).toFixed(2),
      };
    });
  };

  const dumpAll = () => {
    console.log(
      JSON.stringify(
        { flask, coin, decor, flags, fields, gaps, covers, zones },
        null,
        1
      )
    );
  };

  const flaskTarget = (i) => ({ x: flask.pos[i].x, y: flask.pos[i].y + flask.w * 0.154 });

  const flyTarget = (inner, t) => {
    const rect = inner.getBoundingClientRect();
    return { x: rect.left + (t.x / 100) * rect.width, y: rect.top + (t.y / 100) * rect.height };
  };

  const tilePopupRef = useRef(null);
  tilePopupRef.current = tilePopup;

  const lossTimers = useRef([]);
  useEffect(() => () => lossTimers.current.forEach(clearTimeout), []);

  const popOutTimer = useRef(0);

  const closePopup = () => {
    const pop = tilePopupRef.current;
    if (!pop || pop.closing) return;
    setTilePopup({ ...pop, closing: true });
    clearTimeout(popOutTimer.current);
    popOutTimer.current = setTimeout(() => setTilePopup(null), POP_OUT_MS);
  };

  useEffect(() => () => clearTimeout(popOutTimer.current), []);

  const closeCash = () => {
    const pop = tilePopupRef.current;
    if (!pop || pop.closing) return;
    const at = centerOf('[class*="bareBody"]') || pop.from;
    closePopup();
    if (pop.type === "cash") wallet?.award("cash", pop.value, at);
  };

  useEffect(() => {
    if (tilePopup?.type !== "cash") return undefined;
    const t = setTimeout(closeCash, CASH_SHOW_MS);
    return () => clearTimeout(t);
  }, [tilePopup]);

  const energyRef = useRef(energy);
  energyRef.current = energy;
  const coinFillRef = useRef(coinFill);
  coinFillRef.current = coinFill;
  const bankBusy = useRef(false);
  const bankRaf = useRef(0);
  const bankTimers = useRef([]);
  const [bankFocus, setBankFocus] = useState(false);
  const [safeFill, setSafeFill] = useState(null);
  const [coinLive, setCoinLive] = useState(false);
  const [coinDrop, setCoinDrop] = useState(false);

  const restartBoard = () => {
    purpleFound.current = 0;
    restartPending.current = false;
    setLit(null);
    setDestroyed(new Set());
    setMarks({});
    setBoardSeed((n) => n + 1);
  };

  const restartRef = useRef(restartBoard);
  restartRef.current = restartBoard;

  useEffect(() => {
    if (!client || park?.status !== "open") return undefined;
    let dead = false;
    client
      .request({ action: "get_field" })
      .then((r) => {
        if (dead) return;
        if (Array.isArray(r.cells) && r.cells.length === 0 && destroyed.size >= 20) {
          refillRef.current("сервер вернул пустое поле");
          return;
        }
        if (refillBusyRef.current) return;
        if (typeof r.energy === "number") setEnergy(Math.max(0, r.energy));
        if (typeof r.safe === "number") setSafes(Math.max(0, Math.min(SAFE_GOAL - 1, r.safe)));
        if (typeof r.safe_bank === "number" && typeof r.energy === "number") setBankBase(r.safe_bank - r.energy);
        if (!Array.isArray(r.cells)) return;
        const ids = new Set();
        const mk = {};
        r.cells.forEach((c) => {
          ids.add(c.cell_id);
          if (c.outcome === "money") mk[c.cell_id] = { kind: "money", value: c.value };
          else if (c.outcome === "cash") mk[c.cell_id] = { kind: "cash", value: c.value };
          else if (c.outcome === "game" && c.game) mk[c.cell_id] = { kind: "game", game: c.game };
        });
        purpleFound.current = r.cells.filter((c) => c.outcome === "money").length;
        setDestroyed(ids);
        setMarks(mk);
      })
      .catch(() => {});
    const offReset = client.on("field_reset", () => {
      console.info("[park] field_reset: поле обновляется");
      refillRef.current("field_reset");
    });
    const offState = client.on("field_state", (m) => remoteRef.current(m));
    const offBal = client.on("balance", (b) => {
      if (b && typeof b.safe_bank === "number" && typeof b.energy === "number") setBankBase(b.safe_bank - b.energy);
    });
    const offBank = client.on("bank", (m) => {
      console.info("[park] bank пакет", JSON.stringify(m));
      const mine = m && m.user_id != null ? String(m.user_id) === String(park?.userId) : true;
      bankMineRef.current = mine;
      if (!mine) return;
      if (!m || !(m.coins_added > 0)) return;
      if (typeof m.coins === "number") {
        wallet?.applyTruth({ coins: m.coins });
        wallet?.hold("coins", m.coins_added);
      } else {
        wallet?.credit("coins", m.coins_added);
      }
    });
    return () => {
      dead = true;
      offReset();
      offState();
      offBal();
      offBank();
    };
  }, [client, park?.status, park?.sector]);

  const [popIds, setPopIds] = useState(() => new Set());
  const refillBusyRef = useRef(false);
  const runRefill = (why) => {
    if (refillBusyRef.current) return;
    refillBusyRef.current = true;
    console.info("[park] рефил поля:", why);
    if (park?.sector) {
      setZoomZone(null);
      setLit(null);
      park.leaveSector?.();
    }
    purpleFound.current = 0;
    setMarks({});
    const rows = new Map();
    destroyed.forEach((id) => {
      const f = FIELDS.find((x) => id >= x.offset && id < x.offset + x.cols * x.rows);
      if (!f) return;
      const row = f.rows - 1 - Math.floor((id - f.offset) / f.cols);
      if (!rows.has(row)) rows.set(row, []);
      rows.get(row).push(id);
    });
    const order = [...rows.keys()].sort((a, b) => a - b);
    if (!order.length) {
      refillBusyRef.current = false;
      return;
    }
    order.forEach((row, i) => {
      bankTimers.current.push(
        setTimeout(() => {
          const ids = rows.get(row);
          setPopIds((p) => {
            const next = new Set(p);
            ids.forEach((id) => next.add(id));
            return next;
          });
          setDestroyed((prev) => {
            const next = new Set(prev);
            ids.forEach((id) => next.delete(id));
            return next;
          });
          if (i === order.length - 1) {
            bankTimers.current.push(
              setTimeout(() => {
                setPopIds(new Set());
                refillBusyRef.current = false;
              }, REFILL_POP_MS + 100)
            );
          }
        }, REFILL_START_MS + i * REFILL_ROW_MS)
      );
    });
  };
  const refillRef = useRef(runRefill);
  refillRef.current = runRefill;

  const applyRemote = (m) => {
    if (!m || !client) return;
    if (m.user_id != null && String(m.user_id) === String(park?.userId)) {
      const own = Number(m.cell_id);
      if (Number.isInteger(own) && own >= 0) {
        setDestroyed((prev) => {
          if (prev.has(own)) return prev;
          console.info("[park] своя плитка подтверждена сервером после задержки", own);
          const next = new Set(prev);
          next.add(own);
          return next;
        });
        if (m.outcome === "money") setMarks((mk) => (mk[own] ? mk : { ...mk, [own]: { kind: "money", value: m.value || 0 } }));
        else if (m.outcome === "cash") setMarks((mk) => (mk[own] ? mk : { ...mk, [own]: { kind: "cash", value: m.value || 0 } }));
        else if (m.outcome === "game" && m.game) setMarks((mk) => (mk[own] ? mk : { ...mk, [own]: { kind: "game", game: m.game } }));
      }
      return;
    }
    console.info("[park] field_state", JSON.stringify(m));
    const remoteBank = typeof m.safe === "number" && m.safe >= SAFE_GOAL;
    if (remoteBank) drainRemote();
    else if (!drainRef.current && !bankBusy.current) {
      if (typeof m.energy === "number") setEnergy(Math.max(0, m.energy));
      if (typeof m.safe === "number") setSafes(Math.max(0, Math.min(SAFE_GOAL - 1, m.safe)));
      if (typeof m.safe_bank === "number" && typeof m.energy === "number") setBankBase(m.safe_bank - m.energy);
    }
    const cellId = Number(m.cell_id);
    if (!Number.isInteger(cellId) || cellId < 0) return;
    const field = FIELDS.find((f) => cellId >= f.offset && cellId < f.offset + f.cols * f.rows);
    if (!field) return;
    setDestroyed((prev) => {
      if (prev.has(cellId)) return prev;
      const next = new Set(prev);
      next.add(cellId);
      return next;
    });
    if (m.outcome === "money") setMarks((mk) => ({ ...mk, [cellId]: { kind: "money", value: m.value || 0 } }));
    else if (m.outcome === "cash") setMarks((mk) => ({ ...mk, [cellId]: { kind: "cash", value: m.value || 0 } }));
    else if (m.outcome === "game" && m.game) setMarks((mk) => ({ ...mk, [cellId]: { kind: "game", game: m.game } }));
    const cells = document.querySelectorAll("[class*='cell_']");
    const el = cells[cellId];
    if (el && particlesRef.current) {
      const r = el.getBoundingClientRect();
      if (r.width) particlesRef.current.burst(r.left + r.width / 2, r.top + r.height / 2, r.width);
    }
  };
  const remoteRef = useRef(applyRemote);
  remoteRef.current = applyRemote;

  const drainRef = useRef(false);
  const drainRemote = () => {
    if (drainRef.current) return;
    if (tilePopup) {
      console.info("[park] чужой банк ждёт закрытия попапа");
      remoteDrainPendingRef.current = true;
      return;
    }
    drainRef.current = true;
    console.info("[park] чужой банк: сливаю колбы без анимации");
    setSafes(SAFE_GOAL - 1);
    setSafeFill(SAFE_GOAL);
    const total = energyRef.current;
    setBankDrain(total);
    bankTimers.current.push(
      setTimeout(() => {
        const t0 = performance.now();
        const step = () => {
          const k = Math.min(1, (performance.now() - t0) / REMOTE_DRAIN_MS);
          setEnergy(Math.max(0, Math.round(total * (1 - k))));
          setSafeFill(SAFE_GOAL * (1 - k));
          if (k < 1) {
            bankTimers.current.push(setTimeout(step, 16));
            return;
          }
          setEnergy(0);
          setSafes(0);
          setSafeFill(null);
          drainRef.current = false;
          bankTimers.current.push(setTimeout(() => setBankDrain(null), BANK_REFILL_DELAY_MS));
        };
        step();
      }, BANK_HOLD_MS)
    );
  };

  const runBank = () => {
    const inner = stageRef.current;
    const at = inner ? flyTarget(inner, coinRef.current) : null;
    const total = energyRef.current;
    let left = total;
    let coin = coinFillRef.current;

    const done = () => {
      setEnergy(0);
      setSafes(0);
      setSafeFill(null);
      setCoinLive(false);
      setCoinDrop(false);
      if (client) wallet?.settle("coins");
      bankTimers.current.push(setTimeout(() => setBankDrain(null), BANK_REFILL_DELAY_MS));
      bankTimers.current.push(
        setTimeout(() => {
          setBankFocus(false);
          bankBusy.current = false;
          if (restartPending.current) restartBoard();
        }, 900)
      );
    };

    const pour = () => {
      const need = (1 - coin) * COIN_UNITS;
      const take = Math.min(need, left);
      if (take <= 0.001) {
        done();
        return;
      }
      const fromCoin = coin;
      const fromLeft = left;
      const dur = Math.max(BANK_MIN_MS, (take / COIN_UNITS) * BANK_COIN_MS);
      const t0 = performance.now();
      const step = () => {
        const k = Math.min(1, (performance.now() - t0) / dur);
        const moved = take * k;
        setEnergy(Math.max(0, Math.round(fromLeft - moved)));
        setSafeFill(total > 0 ? (SAFE_GOAL * (fromLeft - moved)) / total : 0);
        setCoinFill(Math.min(1, fromCoin + moved / COIN_UNITS));
        if (k < 1) {
          bankRaf.current = requestAnimationFrame(step);
          return;
        }
        left = fromLeft - take;
        coin = fromCoin + take / COIN_UNITS;
        if (coin < 0.999) {
          done();
          return;
        }
        setCoinFullKey((x) => x + 1);
        if (!client || bankMineRef.current) wallet?.award("coins", COIN_FULL_PAY, at);
        bankTimers.current.push(
          setTimeout(() => {
            coin = 0;
            setCoinDrop(true);
            setCoinFill(0);
          }, BANK_FLASH_MS / 2)
        );
        bankTimers.current.push(setTimeout(() => setCoinDrop(false), BANK_FLASH_MS / 2 + BANK_DROP_MS));
        bankTimers.current.push(setTimeout(pour, BANK_FLASH_MS));
      };
      bankRaf.current = requestAnimationFrame(step);
    };

    setBankDrain(total);
    setCoinLive(true);
    pour();
  };

  useEffect(() => {
    if (safes < SAFE_GOAL || bankBusy.current) return undefined;
    if (tilePopup) {
      console.info("[park] банк ждёт закрытия попапа");
      return undefined;
    }
    bankBusy.current = true;
    safeSum.current = 0;
    setLit(null);
    setBankFocus(true);
    bankTimers.current.push(setTimeout(runBank, BANK_HOLD_MS));
    return undefined;
  }, [safes, tilePopup]);

  const remoteDrainPendingRef = useRef(false);
  useEffect(() => {
    if (tilePopup || !remoteDrainPendingRef.current) return;
    remoteDrainPendingRef.current = false;
    drainRemote();
  }, [tilePopup]);

  useEffect(
    () => () => {
      cancelAnimationFrame(bankRaf.current);
      bankTimers.current.forEach(clearTimeout);
    },
    []
  );

  const triggerHook = () => {
    setHookKey((k) => k + 1);
    setHookOn(true);
    if (hookTimer.current) clearTimeout(hookTimer.current);
    hookTimer.current = setTimeout(() => setHookOn(false), 900);
  };

  const flashAt = (target, tone) => {
    flashId.current += 1;
    const id = flashId.current;
    setTankFlashes((list) => [...list, { id, x: target.x, y: target.y, coin: false, tone }]);
    setTimeout(() => setTankFlashes((list) => list.filter((f) => f.id !== id)), 900);
  };

  const flashCoin = (amount) => {
    setCoinPulse((p) => p + 1);
    setCoinFill((f) => Math.min(1, f + amount));
  };

  const flashCoinTo = (v) => {
    setCoinPulse((p) => p + 1);
    setCoinFill(Math.max(0, Math.min(1, v)));
  };

  const coinFullRef = useRef(false);

  useEffect(() => {
    if (coinFill < 1) {
      coinFullRef.current = false;
      return undefined;
    }
    if (coinFullRef.current) return undefined;
    coinFullRef.current = true;
    setCoinFullKey((k) => k + 1);
    const inner = stageRef.current;
    const at = inner ? flyTarget(inner, coinRef.current) : null;
    const t1 = setTimeout(() => wallet?.award("coins", COIN_FULL_PAY, at), 520);
    const t2 = setTimeout(() => setCoinFill(0), 780);
    return () => {
      clearTimeout(t1);
      clearTimeout(t2);
    };
  }, [coinFill]);

  const isFieldFull = (f) => {
    const size = f.cols * f.rows;
    if (destroyed.size < size) return false;
    for (let i = f.offset; i < f.offset + size; i += 1) if (!destroyed.has(i)) return false;
    return true;
  };

  useEffect(() => {
    if (!client || !park?.sector || autoLeaveRef.current) return undefined;
    if (tilePopup || bankFocus || inflightRef.current > 0) return undefined;
    const myField = FIELDS.find((f) => f.sector === Number(park.sector));
    const full = myField ? isFieldFull(myField) : false;
    const broke = !!wallet && wallet.tickets <= 0;
    if (!full && !broke) return undefined;
    const t = setTimeout(() => {
      autoLeaveRef.current = true;
      console.info("[park] выхожу с поля:", full ? "все плитки открыты" : "билеты кончились");
      setZoomZone(null);
      setLit(null);
      park.leaveSector?.();
    }, 1000);
    return () => clearTimeout(t);
  }, [client, park?.sector, destroyed, wallet?.tickets, tilePopup, bankFocus, flightTick]);

  const handleTileClick = (zoneKey, index, cellId, rect, gone = false) => {
    if (bankFocus) return;
    const popFrom = rect ? { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2, w: rect.width } : null;
    if (zoomZone !== zoneKey) {
      if (selectedZone !== zoneKey) {
        setSelectedZone(zoneKey);
        return;
      }
      if (client && !park?.sector) {
        const want = SECTOR_OF[zoneKey];
        if (park?.sectors && (Number(park.sectors[String(want)]) || 0) > 0) {
          console.warn("[park] поле", want, "занято, вход не отправляю");
          return;
        }
        const fld = FIELDS.find((f) => f.key === zoneKey);
        if (fld && isFieldFull(fld)) {
          console.warn("[park] поле", want, "полностью открыто, вход не отправляю");
          return;
        }
        park?.joinSector?.(want);
        return;
      }
      setZoomZone(zoneKey);
      setLit(null);
      return;
    }
    if (gone) return;
    if (lit !== index) {
      setLit(index);
      return;
    }
    if (client && park?.sector && SECTOR_OF[zoneKey] !== Number(park.sector)) return;
    if (client && !client.open) {
      console.info("[park] нет связи, плитку не трогаю");
      return;
    }
    let spentTicket = true;
    if (wallet && !wallet.spend("tickets", TILE_COST)) {
      if (client || wallet.cash < PAID_TRY_CASH) return;
      spentTicket = false;
    }
    if (client) {
      if (rect) particlesRef.current?.burst(rect.left + rect.width / 2, rect.top + rect.height / 2, rect.width);
      setDestroyed((prev) => {
        const next = new Set(prev);
        next.add(cellId);
        return next;
      });
      setLit(null);
      lastOpenRef.current = Date.now();
      inflightRef.current += 1;
      mySentCellsRef.current.add(cellId);
      const queued = openQueueRef.current.then(() => client.request({ action: "open_cell", cell_id: cellId }));
      openQueueRef.current = queued.catch(() => {});
      queued
        .then((r) => {
          wallet?.applyTruth({ tickets: r.tickets });
          applyOutcome(zoneKey, cellId, rect, popFrom, r.outcome, r.value, r.game, r, false);
        })
        .catch((e) => {
          console.warn("[park] open_cell", cellId, e && e.code, e && e.message);
          wallet?.unspend("tickets", TILE_COST);
          if (e && e.code === "cell_already_open") return;
          if (e && e.code === "timeout") {
            console.info("[park] сервер не ответил вовремя, сверяю плитку", cellId, "через get_field");
            const verify = (attempt) =>
              client
                .request({ action: "get_field" })
                .then((r) => {
                  const cell = Array.isArray(r.cells) ? r.cells.find((c) => Number(c.cell_id) === cellId) : null;
                  if (cell) {
                    if (cell.outcome === "money") setMarks((mk) => ({ ...mk, [cellId]: { kind: "money", value: cell.value || 0 } }));
                    else if (cell.outcome === "cash") setMarks((mk) => ({ ...mk, [cellId]: { kind: "cash", value: cell.value || 0 } }));
                    else if (cell.outcome === "game" && cell.game) setMarks((mk) => ({ ...mk, [cellId]: { kind: "game", game: cell.game } }));
                    return;
                  }
                  if (attempt < 2) {
                    setTimeout(() => verify(attempt + 1), 5000);
                    return;
                  }
                  console.info("[park] плитка", cellId, "так и не открылась на сервере, возвращаю");
                  setDestroyed((prev) => {
                    const next = new Set(prev);
                    next.delete(cellId);
                    return next;
                  });
                })
                .catch(() => {});
            verify(1);
            return;
          }
          setDestroyed((prev) => {
            const next = new Set(prev);
            next.delete(cellId);
            return next;
          });
        })
        .finally(() => {
          inflightRef.current = Math.max(0, inflightRef.current - 1);
          if (inflightRef.current === 0) setFlightTick((t) => t + 1);
        });
      return;
    }
    const testGame = TEST_FIRST_GAMES[outcomeIdx.current] || null;
    outcomeIdx.current += 1;
    let outcome = "game";
    let gameId = testGame;
    let value = 0;
    let forced;
    if (!testGame) {
      const field = FIELDS.find((f) => f.key === zoneKey);
      const size = field ? field.cols * field.rows : 100;
      if (!poolRef.current[zoneKey] || !poolRef.current[zoneKey].length) poolRef.current[zoneKey] = buildLocalPool(size);
      const item = poolRef.current[zoneKey].shift();
      if (item === "joker") {
        gameId = JOKER_GAMES[jokerRef.current.i % JOKER_GAMES.length];
        jokerRef.current.i += 1;
        const nx = jokerRef.current.next;
        if (typeof nx[gameId] !== "boolean") nx[gameId] = Math.random() < 0.5;
        forced = nx[gameId];
        nx[gameId] = !nx[gameId];
      } else if (item === "cards" || item === "pyramid") {
        gameId = item;
      } else if (item === "wheel") {
        gameId = "wheel";
        const wp = wheelPoolRef.current;
        if (wp.i >= wp.list.length) {
          wp.list = shuffleArr(WHEEL_DOC_POOL.slice());
          wp.i = 0;
        }
        forced = wp.list[wp.i];
        wp.i += 1;
      } else {
        outcome = item;
        if (item === "cash") value = weightedCash();
      }
    }
    if (outcome === "money" && safes + 1 < SAFE_GOAL) {
      if (wallet && spentTicket) wallet.unspend("tickets", TILE_COST);
    } else if (wallet && !spentTicket) {
      wallet.spend("cash", PAID_TRY_CASH);
    }
    applyOutcome(zoneKey, cellId, rect, popFrom, outcome, value, gameId, null, true, forced);
  };

  const applyOutcome = (zoneKey, cellId, rect, popFrom, outcome, value, gameId, reply, burst, forced) => {
    if (rect) {
      const cx = rect.left + rect.width / 2;
      const cy = rect.top + rect.height / 2;
      if (burst) particlesRef.current?.burst(cx, cy, rect.width);

      if (stageRef.current) {
        const inner = stageRef.current.getBoundingClientRect();
        const shoot = (i, tone, onArrive) => {
          const tank = flaskTarget(i);
          const tx = inner.left + (tank.x / 100) * inner.width;
          const ty = inner.top + (tank.y / 100) * inner.height;
          particlesRef.current?.flyEnergy(
            cx,
            cy,
            tx,
            ty,
            () => {
              flashAt(tank, tone === "purple" ? "purple" : undefined);
              onArrive();
            },
            tone
          );
        };
        const shootPurple = (i, onArrive) => shoot(i, "purple", onArrive);
        if (outcome === "energy") {
          shoot(0, "gold", () => {
            setEnergy((e) => (reply ? Math.max(e, reply.energy || 0) : e + 1));
            if (!reply) setBank((b) => b + 1);
          });
          const cn = coinRef.current;
          const cp = { x: inner.left + (cn.x / 100) * inner.width, y: inner.top + (cn.y / 100) * inner.height };
          const wrapped = reply && typeof reply.coin_fill === "number" && reply.coin_fill < coinFillRef.current - 0.5;
          if (wrapped) wallet?.hold("coins", 1);
          lossTimers.current.push(
            setTimeout(
              () =>
                particlesRef.current?.flyEnergy(cx, cy, cp.x, cp.y, () => {
                  if (!reply || typeof reply.coin_fill !== "number") {
                    flashCoin(0.01);
                    return;
                  }
                  if (!wrapped) {
                    flashCoinTo(reply.coin_fill);
                    return;
                  }
                  flashCoinTo(1);
                  lossTimers.current.push(setTimeout(() => setCoinFill(reply.coin_fill), 900));
                }),
              260
            )
          );
        }
        if (outcome === "money") {
          shootPurple(1, () => {
            setSafes((sv) => {
              const next = Math.min(SAFE_GOAL, reply ? Math.max(sv, reply.safe || 0) : sv + 1);
              if (!reply && next >= SAFE_GOAL && sv < SAFE_GOAL) {
                const paid = bankRef.current;
                const tank = flaskTarget(1);
                const at = stageRef.current ? flyTarget(stageRef.current, tank) : null;
                bankTimers.current.push(setTimeout(() => wallet?.award("cash", paid, at), 350));
                setBank(SAFE_START);
              }
              return next;
            });
          });
        }
      }
    }
    if (outcome === "energy") {
      if (zoneKey !== "tl" && zoneKey !== "tr") {
        triggerHook();
      }
    } else if (outcome === "money") {
      const v = reply ? value || 0 : 50 + Math.floor(Math.random() * 450);
      safeSum.current += v;
      setMarks((m) => ({ ...m, [cellId]: { kind: "money", value: v } }));
      purpleFound.current += 1;
      if (purpleFound.current >= PURPLE_TOTAL && !client) {
        if (bankBusy.current) restartPending.current = true;
        else bankTimers.current.push(setTimeout(restartBoard, 900));
      }
    } else if (outcome === "cash") {
      const v = reply ? value || 0 : value || CASH_NOMS[Math.floor(Math.random() * CASH_NOMS.length)];
      if (reply && v > 0) {
        if (typeof reply.cash === "number") {
          wallet?.applyTruth({ cash: reply.cash });
          wallet?.hold("cash", v);
        } else {
          wallet?.credit("cash", v);
        }
      }
      setMarks((m) => ({ ...m, [cellId]: { kind: "cash", value: v } }));
      setTilePopup({ type: "cash", value: v, from: popFrom });
    } else {
      let game = gameId ? MINI_GAMES.find((g) => g.id === gameId) : null;
      if (!game) {
        game = MINI_GAMES[gameIdx.current % MINI_GAMES.length];
        gameIdx.current += 1;
      }
      setTilePopup({ type: "game", game, tile: cellId, stage: "play", from: popFrom, forced });
      setMarks((m) => ({ ...m, [cellId]: { kind: "game", game: game.id } }));
    }
    setDestroyed((prev) => {
      const next = new Set(prev);
      next.add(cellId);
      return next;
    });
    setLit(null);
  };

  const flyLoss = (from) => {
    const inner = stageRef.current;
    if (!inner || !particlesRef.current || !from) return;
    const at = (t) => flyTarget(inner, t);
    const tank = flaskTarget(0);
    const tp = at(tank);
    particlesRef.current.flyEnergy(
      from.x,
      from.y,
      tp.x,
      tp.y,
      () => {
        flashAt(tank);
        setEnergy((e) => e + 1);
        if (!client) setBank((b) => b + 1);
      },
      "gold"
    );
    const cp = at(coinRef.current);
    lossTimers.current.push(
      setTimeout(() => particlesRef.current?.flyEnergy(from.x, from.y, cp.x, cp.y, () => flashCoin(0.01)), 260)
    );
  };

  const finishGame = (res) => {
    const pop = tilePopupRef.current;
    if (pop?.closing) return;
    const at = centerOf('[class*="bareBody"]') || pop?.from;
    closePopup();
    if (!pop || !pop.game) return;
    if (res == null) return;
    const id = pop.game.id;
    if (JOKERS.has(id)) {
      if (res) wallet?.award("cash", JOKER_PAY, at);
      else flyLoss(pop.from);
      return;
    }
    if (id === "wheel") wallet?.award("tickets", res, at);
    else wallet?.award("cash", res, at);
  };

  useEffect(() => {
    particlesRef.current?.clearFlights();
  }, [zoomZone]);

  useEffect(() => {
    setUrlSector(zoomZone);
  }, [zoomZone]);

  useEffect(() => {
    onCoinRing?.(coinFill);
  }, [coinFill, onCoinRing]);

  const exitRef = useRef(null);

  useEffect(() => {
    exitRef.current = exitZoom;
  });

  useEffect(() => {
    onZoomState?.({ zoomed: !!zoomZone, busy: bankFocus, exit: () => exitRef.current?.() });
  }, [zoomZone, bankFocus, onZoomState]);

  useEffect(() => {
    const onPop = () => {
      if (client) return;
      const key = urlSector();
      setZoomZone(key);
      setSelectedZone(key);
      setLit(null);
    };
    window.addEventListener("popstate", onPop);
    return () => {
      window.removeEventListener("popstate", onPop);
      setUrlSector(null);
    };
  }, []);

  const exitZoom = () => {
    setZoomZone(null);
    setLit(null);
  };

  const zoomStyle = (() => {
    if (bankFocus) {
      const pts = [flask.pos[0], flask.pos[1], { x: coin.x, y: coin.y }];
      const xs = pts.map((p) => p.x);
      const ys = pts.map((p) => p.y);
      const minX = Math.min(...xs) - 12;
      const maxX = Math.max(...xs) + 12;
      const minY = Math.min(...ys) - 12;
      const maxY = Math.max(...ys) + 12;
      const cx = (minX + maxX) / 2;
      const cy = (minY + maxY) / 2;
      const z = Math.max(1.05, Math.min(2, Math.max(93 / (maxX - minX), 93 / (maxY - minY), 1.95)));
      const lim = 49 * (z - 1);
      const clamp = (v) => Math.max(-lim, Math.min(lim, v));
      const tx = clamp(z * (50 - cx));
      const ty = clamp(z * (50 - cy));
      return { transform: `translate(${tx.toFixed(2)}%, ${ty.toFixed(2)}%) scale(${z.toFixed(3)})` };
    }
    if (!zoomZone) {
      if (edit) {
        return {
          transform:
            view.z === 1
              ? "none"
              : `translate(${view.z * (50 - view.x)}%, ${view.z * (50 - view.y)}%) scale(${view.z})`,
          transition: "none",
        };
      }
      return undefined;
    }
    const c = fields[zoomZone];
    const xs = [c.tl.x, c.tr.x, c.br.x, c.bl.x];
    const ys = [c.tl.y, c.tr.y, c.br.y, c.bl.y];
    const minX = Math.min(...xs);
    const maxX = Math.max(...xs);
    const minY = Math.min(...ys);
    const maxY = Math.max(...ys);
    const cx = (minX + maxX) / 2;
    const cy = (minY + maxY) / 2;
    const z = Math.min(72 / (maxX - minX), 72 / (maxY - minY), 3.4);
    return { transform: `translate(${z * (50 - cx)}%, ${z * (50 - cy)}%) scale(${z})` };
  })();

  return (
    <>
      <div className={styles.grid}>
      <Particles ref={particlesRef} />
      {hookOn && <div key={hookKey} className={styles.screenHook} />}
      {tilePopup?.type === "cash" && (
        <GamePopup bare tapClose from={tilePopup.from} closing={!!tilePopup.closing} onClose={closeCash}>
          <CashWin value={tilePopup.value} />
        </GamePopup>
      )}
            {tilePopup?.type === "game" && tilePopup.stage === "play" && (
        <GamePopup
          title={tilePopup.game.title}
          bare={tilePopup.game.bare}
          closeBottom={tilePopup.game.id === "cards"}
          from={tilePopup.from}
          closing={!!tilePopup.closing}
          onClose={closePopup}
        >
          <tilePopup.game.Comp onFinish={finishGame} forced={tilePopup.forced} />
        </GamePopup>
      )}
      <div className={styles.parkEditBar} onPointerDown={(e) => e.stopPropagation()}>
        {edit && (
          <>
          <button
            type="button"
            className={styles.parkEditBtn}
            onClick={() => {
              setEdit(false);
              setSel(null);
              setView({ z: 1, x: 50, y: 50 });
            }}
          >
            ✎ выкл
          </button>
          <button type="button" onClick={() => sizeSel(1 / 1.05)} disabled={!sel}>−</button>
          <button type="button" onClick={() => sizeSel(1.05)} disabled={!sel}>+</button>
          {[
            ["field", "плитки"],
            ["cover", "затемнение"],
            ["zone", "свечение"],
          ].map(([k, t]) => (
            <button
              key={k}
              type="button"
              className={layer === k ? styles.parkEditOn : ""}
              onClick={() => {
                setLayer(k);
                setSel(null);
              }}
            >
              {t}
            </button>
          ))}
          <button type="button" onClick={() => setView({ z: 1, x: 50, y: 50 })}>сброс зума</button>
          <button type="button" onClick={dumpAll}>данные</button>
          <span className={styles.parkEditWho}>{sel ? `${sel.kind} ${sel.id}` : "ничего не выбрано"}</span>
          </>
        )}
      </div>
      <div className={styles.gridStage} style={zoomStyle} onWheel={edit ? onZoomWheel : undefined}>
        <div className={`${styles.gridInner} ${styles.rasterPinOn}`} ref={stageRef}>
          <img className={styles.gridBg} src={`${ASSET}/Down_Bg_3.webp`} alt="Castle fields" draggable={false} />
          <ParkDecor storeKey={DECO_DOWN_KEY} defaults={DEFAULT_DECO_DOWN} />
          {flags.map((f, i) => (
            <WindFlag
              key={f.id}
              img={f.img}
              x={f.x}
              y={f.y}
              w={f.w}
              delay={`${-(i * 0.73).toFixed(2)}s`}
              dur={`${(3.4 + (i % 4) * 0.55).toFixed(2)}s`}
              className={`${edit ? styles.editable : ""} ${
                sel && sel.kind === "flag" && sel.id === f.id ? styles.picked : ""
              }`}
              onPointerDown={edit ? editStart("flag", f.id) : undefined}
              onPointerMove={edit ? editMove : undefined}
              onPointerUp={edit ? editEnd : undefined}
            />
          ))}
          <ParkFlask
            x={flask.pos[0].x}
            y={flask.pos[0].y}
            w={flask.w}
            tone="Yellow"
            icon="Icn_Energy_Yellow.png"
            fill={Math.min(1, yellowShown / ENERGY_GOAL)}
            label={yellowShown}
            chipDy={-6.7}
            edit={edit}
            picked={sel && sel.kind === "flask" && sel.id === 0}
            onGrab={editStart("flask", 0)}
            onMove={editMove}
            onDrop={editEnd}
          />
          <ParkFlask
            x={flask.pos[1].x}
            y={flask.pos[1].y}
            w={flask.w}
            tone="Purple"
            icon="Icn_Energy_Purple.png"
            fill={(safeFill ?? safes) / SAFE_GOAL}
            steps={SAFE_GOAL}
            label={client ? `${Math.round(safeFill ?? safes)}/${SAFE_GOAL}` : `${bank}`}
            edit={edit}
            picked={sel && sel.kind === "flask" && sel.id === 1}
            onGrab={editStart("flask", 1)}
            onMove={editMove}
            onDrop={editEnd}
          />
          <CoinFlash
            className={`${edit ? styles.editable : ""} ${
              sel && sel.kind === "coin" ? styles.picked : ""
            }`}
            fill={coinFill}
            live={coinLive}
            drop={coinDrop}
            pulseKey={coinPulse}
            fullKey={coinFullKey}
            style={{ left: `${coin.x}%`, top: `${coin.y}%`, width: `${coin.w}%` }}
            onPointerDown={edit ? editStart("coin", 0) : undefined}
            onPointerMove={edit ? editMove : undefined}
            onPointerUp={edit ? editEnd : undefined}
          />
          {tankFlashes.map((f) => (
            <span
              key={f.id}
              className={`${f.coin ? styles.coinFlash : styles.tankFlash} ${
                f.tone === "purple" ? styles.tankFlashPurple : ""
              }`}
              style={{ left: `${f.x}%`, top: `${f.y}%` }}
            />
          ))}
          {FIELDS.map((f) => {
            const active = zoomZone === f.key;
            const highlighted = !zoomZone && selectedZone === f.key;
            const foreign = !!client && !!park?.sector && SECTOR_OF[f.key] !== Number(park.sector);
            const busyFree = !!client && !park?.sector && !!park?.sectors && (Number(park.sectors[String(SECTOR_OF[f.key])]) || 0) > 0;
            const full = !!client && !active && isFieldFull(f);
            const taken = TAKEN_FIELDS.has(f.key) || foreign || busyFree || full;
            const interactive = (!zoomZone || active) && !taken;
            return (
              <TileField
                key={f.key}
                corners={shrinkQuad(fields[f.key], gaps[f.key].inset)}
                glowCorners={zones[f.key]}
                coverCorners={covers[f.key]}
                cols={f.cols}
                rows={f.rows}
                seed={f.seed + boardSeed * 11}
                offset={f.offset}
                gapX={0}
                gapY={0}
                scaleX={1 - gaps[f.key].w * 0.02}
                scaleY={1 - gaps[f.key].d * 0.06}
                pop={popIds}
                destroyed={destroyed}
                marks={marks}
                selected={active}
                taken={taken}
                showGlow={edit ? layer === "zone" && sel && sel.kind === "quad" && sel.id === f.key : highlighted}
                litTile={active ? lit : null}
                coverSrc={`${ASSET}/${COVER[f.key]}`}
                onTileClick={
                  interactive ? (i, cellId, rect, gone) => handleTileClick(f.key, i, cellId, rect, gone) : undefined
                }
              />
            );
          })}
          {edit &&
            FIELDS.map((f) => {
              const q = LAYERS[layer].get()[f.key];
              if (!q) return null;
              const poly = `polygon(${q.tl.x}% ${q.tl.y}%, ${q.tr.x}% ${q.tr.y}%, ${q.br.x}% ${q.br.y}%, ${q.bl.x}% ${q.bl.y}%)`;
              const on = sel && sel.kind === "quad" && sel.id === f.key;
              return (
                <span
                  key={`pick-${f.key}`}
                  className={`${styles.quadPick} ${on ? styles.quadPickOn : ""}`}
                  style={{ clipPath: poly, WebkitClipPath: poly }}
                  onPointerDown={editStart("quad", f.key)}
                  onPointerMove={editMove}
                  onPointerUp={editEnd}
                  onPointerCancel={editEnd}
                />
              );
            })}
          {edit &&
            sel &&
            sel.kind === "quad" &&
            LAYERS[layer].get()[sel.id] &&
            ["tl", "tr", "br", "bl"].map((k) => {
              const p = LAYERS[layer].get()[sel.id][k];
              return (
                <span
                  key={`h-${k}`}
                  className={styles.quadHandle}
                  style={{ left: `${p.x}%`, top: `${p.y}%` }}
                  onPointerDown={editStart("corner", sel.id, k)}
                  onPointerMove={editMove}
                  onPointerUp={editEnd}
                  onPointerCancel={editEnd}
                />
              );
            })}
          {decor.map((d) => {
            const st = {
              left: `${d.x}%`,
              top: `${d.y}%`,
              pointerEvents: edit ? "auto" : "none",
              cursor: edit ? "move" : undefined,
              outline: edit && sel && sel.kind === "decor" && sel.id === d.id ? "1px dashed #ffd24e" : undefined,
            };
            const grab = edit
              ? {
                  onPointerDown: editStart("decor", d.id),
                  onPointerMove: editMove,
                  onPointerUp: editEnd,
                  onPointerCancel: editEnd,
                }
              : null;
            return d.type === "balloon" ? (
              <Balloons key={d.id} scale={0.42 * (d.s || 1)} style={st} {...grab} />
            ) : (
              <Flag key={d.id} scale={0.6 * (d.s || 1)} rot={d.rot} flip={d.flip} style={st} {...grab} />
            );
          })}
        </div>
      </div>
      </div>
    </>
  );
}

export function GamePopup({ title, onClose, bare = false, closeBottom = false, from, closing = false, tapClose = false, children }) {
  const boxRef = useRef(null);
  const fitRef = useRef(null);
  const [popFit, setPopFit] = useState(1);
  const [popReady, setPopReady] = useState(false);
  const armedRef = useRef(false);
  useEffect(() => {
    const t = setTimeout(() => {
      armedRef.current = true;
    }, POPUP_ARM_MS);
    return () => clearTimeout(t);
  }, []);
  usePopFrom(boxRef, from, (!bare || popReady) && !closing);
  usePopTo(boxRef, from, closing);

  useLayoutEffect(() => {
    if (!bare || closing) return undefined;
    const el = fitRef.current;
    const ov = el && el.parentElement;
    if (!el || !ov) return undefined;
    const frames = [];
    let lastFit = -1;
    let revealed = false;
    const reveal = () => {
      if (revealed) return;
      revealed = true;
      setPopReady(true);
    };
    const apply = () => {
      const cs = getComputedStyle(ov);
      const avail = ov.clientHeight - (parseFloat(cs.paddingTop) || 0) - (parseFloat(cs.paddingBottom) || 0);
      const need = el.offsetHeight;
      const next = need > 0 ? Math.min(1, avail / need) : 1;
      setPopFit((prev) => Math.min(prev, next));
      if (need > 0 && Math.abs(next - lastFit) < 0.002) reveal();
      lastFit = next;
    };
    apply();
    const step = (n) => {
      apply();
      if (n > 0) frames.push(requestAnimationFrame(() => step(n - 1)));
      else reveal();
    };
    frames.push(requestAnimationFrame(() => step(6)));
    const guard = setTimeout(reveal, 500);
    const ro = new ResizeObserver(apply);
    ro.observe(el);
    ro.observe(ov);
    return () => {
      clearTimeout(guard);
      frames.forEach(cancelAnimationFrame);
      ro.disconnect();
    };
  }, [bare, closing]);

  if (bare) {
    return (
      <div
        className={`${styles.popupOverlay} ${styles.popupBare} ${closing ? styles.popupFade : ""}`}
        onClick={() => {
          if (tapClose && armedRef.current) onClose?.();
        }}
      >
        <div
          ref={fitRef}
          className={styles.bareFit}
          style={{
            transform: popFit < 1 ? `scale(${popFit.toFixed(3)})` : undefined,
            visibility: popReady ? undefined : "hidden",
          }}
        >
          <div ref={boxRef} className={styles.bareBody}>
            {children}
          </div>
        </div>
      </div>
    );
  }
  return (
    <div className={styles.popupOverlay} onClick={onClose}>
      <div ref={boxRef} className={styles.popup} onClick={(e) => e.stopPropagation()}>
        <div className={styles.popupHead}>
          <h3>{title}</h3>

        </div>
        <div className={styles.popupBody}>{children}</div>
      </div>
    </div>
  );
}

const UPARK_KEY = "bonuspark.upark6";
const DEFAULT_UPARK = {
  wheel: { x: 6.2, y: 40.61, w: 28.64 },
  plat: { x: 7.23, y: 45.4, w: 20.91 },
  bush: { x: -4.44, y: 52.73, w: 24.72 },
  carousel: { x: 71.65, y: 44.42, w: 35.02 },
  crP: { x: 47.45, y: 37.59, w: 7.54 },
  crR: { x: 47.27, y: 43.41, w: 8 },
  crY: { x: 47.03, y: 49.66, w: 8.24 },
  crB: { x: 47.08, y: 56.32, w: 8.24 },
  coin: { x: 46.4, y: 79.5, w: 10 },
  flask1: { x: 24.1, y: 89.31, w: 5.67 },
  flask2: { x: 78.1, y: 89.11, w: 5.67 },
  star1: { x: 60.12, y: 56.89, w: 7.5 },
  star2: { x: 63.26, y: 55.66, w: 7.5 },
  star3: { x: 65.15, y: 54.86, w: 7.5 },
  star4: { x: 35.28, y: 57.1, w: 7.5 },
  star5: { x: 31.68, y: 55.7, w: 7.5 },
  star6: { x: 28.38, y: 54.82, w: 7.5 },
  uf1: { x: 27.8, y: 73.6, w: 3.4 },
  uf2: { x: 37.25, y: 71.13, w: 3.4 },
  ufc: { x: 52.71, y: 70.54, w: 4 },
  uf3: { x: 66.98, y: 71.47, w: 2.9 },
  uf4: { x: 78.29, y: 72.76, w: 3.4 },
};

const UPARK_FLAGS = {
  uf1: "Flag_1.png",
  uf2: "Flag_2.png",
  ufc: "Flag_centr.png",
  uf3: "Flag_2.png",
  uf4: "Flag_1.png",
};

const TILEPHOTO_KEY = "bonuspark.tilesphoto3";
const DEFAULT_TILEPHOTO = {
  a: {
    tl: { x: 55.2, y: 92.8 },
    tr: { x: 78.3, y: 92.8 },
    br: { x: 80.9, y: 99.8 },
    bl: { x: 57.9, y: 99.8 },
  },
  b: {
    tl: { x: 22.8, y: 92.8 },
    tr: { x: 45.8, y: 92.8 },
    br: { x: 45.6, y: 99.9 },
    bl: { x: 22.9, y: 99.9 },
  },
};

function loadTilePhoto() {
  try {
    const raw = localStorage.getItem(TILEPHOTO_KEY);
    if (raw) {
      const p = JSON.parse(raw);
      if (p.a && p.b) return p;
    }
  } catch {
    return { ...DEFAULT_TILEPHOTO };
  }
  return { ...DEFAULT_TILEPHOTO };
}

function homographyMatrix(w, h, p) {
  const adj = (m) => [
    m[4] * m[8] - m[5] * m[7], m[2] * m[7] - m[1] * m[8], m[1] * m[5] - m[2] * m[4],
    m[5] * m[6] - m[3] * m[8], m[0] * m[8] - m[2] * m[6], m[2] * m[3] - m[0] * m[5],
    m[3] * m[7] - m[4] * m[6], m[1] * m[6] - m[0] * m[7], m[0] * m[4] - m[1] * m[3],
  ];
  const mult = (a, b) => {
    const c = [];
    for (let i = 0; i < 3; i += 1) {
      for (let j = 0; j < 3; j += 1) {
        let s = 0;
        for (let k = 0; k < 3; k += 1) s += a[3 * i + k] * b[3 * k + j];
        c[3 * i + j] = s;
      }
    }
    return c;
  };
  const multV = (m, v) => [
    m[0] * v[0] + m[1] * v[1] + m[2] * v[2],
    m[3] * v[0] + m[4] * v[1] + m[5] * v[2],
    m[6] * v[0] + m[7] * v[1] + m[8] * v[2],
  ];
  const basis = (x1, y1, x2, y2, x3, y3, x4, y4) => {
    const m = [x1, x2, x3, y1, y2, y3, 1, 1, 1];
    const v = multV(adj(m), [x4, y4, 1]);
    return mult(m, [v[0], 0, 0, 0, v[1], 0, 0, 0, v[2]]);
  };
  const s = basis(0, 0, w, 0, 0, h, w, h);
  const d = basis(p[0].x, p[0].y, p[1].x, p[1].y, p[3].x, p[3].y, p[2].x, p[2].y);
  const t = mult(d, adj(s));
  const n = t.map((v) => v / t[8]);
  return `matrix3d(${n[0]},${n[3]},0,${n[6]},${n[1]},${n[4]},0,${n[7]},0,0,1,0,${n[2]},${n[5]},0,1)`;
}

function TilesPhoto({ quad, onPointerDown }) {
  const ref = useRef(null);
  const [dim, setDim] = useState({ w: 0, h: 0 });

  useEffect(() => {
    const host = ref.current.parentElement;
    const measure = () => setDim({ w: host.clientWidth, h: host.clientHeight });
    measure();
    const ro = new ResizeObserver(measure);
    ro.observe(host);
    return () => ro.disconnect();
  }, []);

  const pts = [quad.tl, quad.tr, quad.br, quad.bl].map((p) => ({
    x: (p.x / 100) * dim.w,
    y: (p.y / 100) * dim.h,
  }));
  const tf = dim.w > 0 ? homographyMatrix(dim.w, dim.h, pts) : "none";

  return (
    <div
      ref={ref}
      onPointerDown={onPointerDown}
      style={{
        position: "absolute",
        left: 0,
        top: 0,
        width: "100%",
        height: "100%",
        transformOrigin: "0 0",
        transform: tf,
        pointerEvents: "none",
        zIndex: 2,
      }}
    >
      <img
        src={`${ASSET}/Tiles/field_cut.webp`}
        alt=""
        draggable={false}
        style={{
          width: "100%",
          height: "100%",
          display: "block",
          pointerEvents: onPointerDown ? "auto" : "none",
          cursor: onPointerDown ? "move" : undefined,
        }}
      />
    </div>
  );
}

const UPARK_Z = { star1: 8, star2: 7, star3: 6, star4: 8, star5: 7, star6: 6 };

const CRYSTAL_CFG = {
  crP: {
    off: "crystal/crystal_Purple_off.png",
    on: "crystal/crystal_Purple_on.png",
    ray: "crystal/rays crystal_Purple.png",
    onBox: { left: -43.86, top: -42.84, w: 193.82, h: 140.77 },
    hue: 118,
    burst: ["255, 246, 255", "226, 170, 255", "168, 78, 255", "110, 30, 220"],
  },
  crR: {
    off: "crystal/crystal_Red_off.png",
    on: "crystal/crystal_Red_on.png",
    ray: "crystal/rays crystal_Red.png",
    onBox: { left: -51, top: -43.15, w: 199.94, h: 141.08 },
    hue: 188,
    burst: ["255, 244, 238", "255, 168, 138", "255, 78, 44", "205, 30, 10"],
  },
  crY: {
    off: "crystal/crystal_Yellow_off.png",
    on: "crystal/crystal_Yellow_on.png",
    ray: "crystal/rays_crystal_Yellow.png",
    onBox: { left: -70.55, top: -51.36, w: 245.35, h: 149.29 },
    hue: 258,
    burst: ["255, 253, 226", "255, 236, 140", "255, 208, 62", "225, 150, 20"],
  },
  crB: {
    off: "crystal/crystal_Blue_off.png",
    on: "crystal/crystal_Blue_on.png",
    ray: "crystal/rays crystal_Blue.png",
    onBox: { left: -54.75, top: -46.2, w: 197.97, h: 145.51 },
    hue: 0,
    burst: ["236, 252, 255", "150, 226, 255", "48, 168, 255", "20, 96, 220"],
  },
};

const STAR_KEYS = ["star1", "star2", "star3", "star4", "star5", "star6"];
const STAR_MIRROR = new Set(["star4", "star5", "star6"]);

function CrystalSpot({ x, y, w, cfg, on }) {
  const [flash, setFlash] = useState(0);
  const prev = useRef(on);

  useEffect(() => {
    if (on && !prev.current) setFlash((n) => n + 1);
    prev.current = on;
  }, [on]);

  return (
    <div style={{ position: "absolute", left: `${x}%`, top: `${y}%`, width: `${w}%`, zIndex: 2, pointerEvents: "none" }}>
      {flash > 0 && (
        <span
          key={flash}
          className={styles.crystalBurst}
          style={{
            "--b0": cfg.burst?.[0] || "255, 253, 226",
            "--b1": cfg.burst?.[1] || "255, 236, 140",
            "--b2": cfg.burst?.[2] || "255, 208, 62",
            "--b3": cfg.burst?.[3] || "225, 150, 20",
          }}
        />
      )}
      <img className={styles.groundShadow} src={`${ASSET}/${cfg.off}`} alt="" draggable={false} />
      <img src={`${ASSET}/${cfg.off}`} alt="" draggable={false} style={{ width: "100%", display: "block" }} />
      <div className={styles.crystalOnWrap} style={{ opacity: on ? 1 : 0 }}>
        <img className={styles.crystalRays} src={`${ASSET}/${cfg.ray}`} alt="" draggable={false} />
        <img
          src={`${ASSET}/${cfg.on}`}
          alt=""
          draggable={false}
          style={{
            position: "absolute",
            left: `${cfg.onBox.left}%`,
            top: `${cfg.onBox.top}%`,
            width: `${cfg.onBox.w}%`,
            height: `${cfg.onBox.h}%`,
            maxWidth: "none",
          }}
        />
      </div>
    </div>
  );
}

function StarSpot({ x, y, w, on, mirror, z }) {
  const flip = mirror ? "scaleX(-1)" : undefined;
  return (
    <div style={{ position: "absolute", left: `${x}%`, top: `${y}%`, width: `${w}%`, zIndex: z, pointerEvents: "none" }}>
      <img className={styles.groundShadow} src={`${ASSET}/Bg_Over/Star_Off.png`} alt="" draggable={false} />
      <img
        src={`${ASSET}/Bg_Over/Star_Off.png`}
        alt=""
        draggable={false}
        style={{ width: "100%", display: "block", transform: flip }}
      />
      <img
        src={`${ASSET}/Bg_Over/Star_On.png`}
        alt=""
        draggable={false}
        className={styles.starOnImg}
        style={{ opacity: on ? 1 : 0, transform: flip }}
      />
    </div>
  );
}

const CRYSTAL_KEYS = ["crP", "crR", "crY", "crB"];

function loadUpark() {
  try {
    const raw = localStorage.getItem(UPARK_KEY);
    if (raw) return { ...DEFAULT_UPARK, ...JSON.parse(raw) };
  } catch {
    return DEFAULT_UPARK;
  }
  return DEFAULT_UPARK;
}

const SCENE_AR = 0.63981;

function Fountain({ x, y, w }) {
  return (
    <img
      className={styles.fountain}
      src={`${ASSET}/Fountain.png`}
      alt=""
      draggable={false}
      style={{ left: `${x}%`, top: `${y}%`, width: `${w}%` }}
    />
  );
}

export function UpperPark({ provider = false }) {
  const wallet = useWallet();
  const park = useParkServer();
  const client = park?.client || null;
  const bonusRef = useRef(null);
  const spinBusy = useRef(false);
  const spinPayRef = useRef(null);
  const spinCrystalsRef = useRef(null);
  const unpaidRef = useRef(0);
  useEffect(() => {
    const ph = document.querySelector("[class*='phone']");
    if (!ph) return undefined;
    ph.dataset.framed = "1";
    document.body.dataset.frame = "1";
    return () => {
      delete ph.dataset.framed;
      delete document.body.dataset.frame;
    };
  }, []);

  const [graph] = useState(loadGraph9);
  const [upark, setUpark] = useState(loadUpark);
  const [editC, setEditC] = useState(false);
  const [decoOn, setDecoOn] = useState(false);

  useEffect(() => {
    const onDeco = (e) => {
      setDecoOn(!!e.detail);
      if (!e.detail) {
        setZoom(1);
        setZorg({ x: 50, y: 50 });
      }
    };
    window.addEventListener(DECO_EDIT_EVENT, onDeco);
    return () => window.removeEventListener(DECO_EDIT_EVENT, onDeco);
  }, []);
  const [zoom, setZoom] = useState(1);
  const [zorg, setZorg] = useState({ x: 50, y: 50 });
  const dragC = useRef(null);
  const [selC, setSelC] = useState(null);

  const sizeCrystal = (f) => {
    const k = selC;
    if (!k) return;
    setUpark((prev) => {
      const b = prev[k];
      const nw = +(b.w * f).toFixed(2);
      const nh = (b.w - nw) * 0.5;
      const next = { ...prev, [k]: { ...b, w: nw, x: +(b.x + (b.w - nw) / 2).toFixed(2), y: +(b.y + nh * 0.62).toFixed(2) } };
      try { localStorage.setItem(UPARK_KEY, JSON.stringify(next)); } catch (err) { return next; }
      return next;
    });
  };

  const moveCrystal = (k, dx, dy) =>
    setUpark((prev) => {
      const b = prev[k];
      const next = { ...prev, [k]: { ...b, x: +(b.x + dx).toFixed(2), y: +(b.y + dy).toFixed(2) } };
      try { localStorage.setItem(UPARK_KEY, JSON.stringify(next)); } catch (err) { return next; }
      return next;
    });

  const crDown = (e, k) => {
    e.preventDefault();
    e.stopPropagation();
    const host = e.currentTarget.parentElement.getBoundingClientRect();
    let lx = e.clientX;
    let ly = e.clientY;
    const move = (ev) => {
      ev.preventDefault();
      const dx = ((ev.clientX - lx) / host.width) * 100;
      const dy = ((ev.clientY - ly) / host.height) * 100;
      lx = ev.clientX;
      ly = ev.clientY;
      moveCrystal(k, dx, dy);
    };
    const up = () => {
      window.removeEventListener("pointermove", move);
      window.removeEventListener("pointerup", up);
      window.removeEventListener("pointercancel", up);
      dragC.current = null;
    };
    dragC.current = { k };
    setSelC(k);
    window.addEventListener("pointermove", move, { passive: false });
    window.addEventListener("pointerup", up);
    window.addEventListener("pointercancel", up);
  };

  const zoomWheel = (e) => {
    if (!editC && !decoOn) return;
    const host = e.currentTarget.getBoundingClientRect();
    const px = +(((e.clientX - host.left) / host.width) * 100).toFixed(1);
    const py = +(((e.clientY - host.top) / host.height) * 100).toFixed(1);
    setZoom((z) => {
      const next = Math.max(1, Math.min(6, +(z * (e.deltaY < 0 ? 1.14 : 1 / 1.14)).toFixed(3)));
      if (z === 1 && next > 1) setZorg({ x: px, y: py });
      if (next === 1) setZorg({ x: 50, y: 50 });
      return next;
    });
  };
  const [tilePhoto] = useState(loadTilePhoto);
  const [keysGot, setKeysGot] = useState(0);
  const ridesPending = useRef(false);
  const [rides, setRides] = useState({ carousel: false, wheel: false });
  const rideTimers = useRef([]);
  const wheelTimers = useRef([]);
  const rideAudio = useRef({});
  const rideFade = useRef({});
  useEffect(() => {
    const made = {};
    Object.keys(RIDE_SOUND).forEach((k) => {
      const a = new Audio(RIDE_SOUND[k]);
      a.preload = "auto";
      a.loop = false;
      made[k] = a;
    });
    rideAudio.current = made;
    return () => {
      Object.values(made).forEach((a) => {
        a.pause();
        a.removeAttribute("src");
        a.load();
      });
      Object.values(rideFade.current).forEach(clearInterval);
    };
  }, []);
  const playRide = (key) => {
    const a = rideAudio.current[key];
    if (!a) return;
    clearInterval(rideFade.current[key]);
    a.volume = 1;
    a.currentTime = 0;
    console.info("[park] звук:", key, "старт");
    a.play().catch(() => {});
  };
  const stopRide = (key) => {
    const a = rideAudio.current[key];
    if (!a || a.paused) return;
    clearInterval(rideFade.current[key]);
    const t0 = performance.now();
    console.info("[park] звук:", key, "стоп");
    rideFade.current[key] = setInterval(() => {
      const k = Math.min(1, (performance.now() - t0) / RIDE_FADE_MS);
      a.volume = 1 - k;
      if (k >= 1) {
        clearInterval(rideFade.current[key]);
        a.pause();
        a.currentTime = 0;
        a.volume = 1;
      }
    }, 40);
  };
  const trainRef = useRef(null);
  const prizeIdx = useRef(0);
  const wheelIdx = useRef(0);
  const carDone = useRef(null);
  const wheelDone = useRef(null);
  const [carStop, setCarStop] = useState(null);
  const [wheelStop, setWheelStop] = useState(null);
  const [carouselWin, setCarouselWin] = useState(null);
  const [salute, setSalute] = useState(null);

  const foreignRef = useRef(false);
  const prizeGivenRef = useRef(false);
  const carsRef = useRef(0);
  const stateRef = useRef(null);
  const rideBusyRef = useRef(false);

  const applyParkState = () => {
    const s = stateRef.current;
    if (!s || rideBusyRef.current) return;
    const mine = s.user_id != null && String(s.user_id) === String(park?.userId);
    if (mine) {
      console.info("[park] park_state мой, кристаллы и вагоны уже учтены локально");
      return;
    }
    if (typeof s.crystals === "number") setKeysGot(Math.max(0, Math.min(CRYSTAL_ORDER.length - 1, s.crystals)));
    if (typeof s.cars_total === "number") {
      const want = Math.max(0, Math.min(TRAIN_FULL - 1, Math.floor(s.cars_total)));
      const delta = want - carsRef.current;
      if (delta > 0) {
        console.info("[park] вагоны из park_state", carsRef.current, "->", want);
        carsRef.current = want;
        trainRef.current?.addCars(delta);
      }
    }
  };

  useEffect(() => {
    if (!client) return undefined;
    return client.on("park_state", (m) => {
      console.info("[park] park_state", JSON.stringify(m));
      stateRef.current = m;
      applyParkState();
    });
  }, [client]);

  const crSynced = useRef(false);
  useEffect(() => {
    if (!client || !park?.balance || crSynced.current) return;
    crSynced.current = true;
    setKeysGot(Math.max(0, Math.min(CRYSTAL_ORDER.length - 1, park.balance.crystals || 0)));
    const cars = Math.max(0, Math.min(TRAIN_FULL - 1, Math.floor(park.balance.cars_total || 0)));
    if (cars > 0) {
      console.info("[park] вагоны с сервера", cars);
      carsRef.current = cars;
      rideTimers.current.push(setTimeout(() => trainRef.current?.addCars(cars), 600));
    }
  }, [client, park?.balance]);

  useEffect(() => {
    if (!client) return undefined;
    return client.on("bonus", (m) => {
      console.info("[park] bonus пакет", JSON.stringify(m));
      const foreign = m && m.user_id != null && String(m.user_id) !== String(park?.userId);
      if (foreign) {
        if (rideBusyRef.current) {
          console.info("[park] чужая карусель пришла во время своей, пропускаю анимацию");
          return;
        }
        console.info("[park] чужая карусель/колесо: только анимация, без приза");
        foreignRef.current = true;
        bonusRef.current = m;
        startRides();
        return;
      }
      foreignRef.current = false;
      bonusRef.current = m;
      if (typeof m.coins === "number") wallet?.applyTruth({ coins: m.coins });
      else wallet?.credit("coins", (m.carousel || 0) + (m.wheel || 0));
      if (typeof m.coins === "number") wallet?.hold("coins", (m.carousel || 0) + (m.wheel || 0));
    });
  }, [client]);

  const givePrize = (forced) => {
    if (client && prizeGivenRef.current) {
      console.warn("[park] карусель: повторный приз в том же заезде, пропускаю");
      return;
    }
    prizeGivenRef.current = true;
    const fixed = typeof forced === "number" ? forced : null;
    const prize = fixed ?? CAR_PRIZE_SEQ[prizeIdx.current % CAR_PRIZE_SEQ.length];
    if (fixed == null) prizeIdx.current += 1;
    const bonus = client ? bonusRef.current : null;
    const cars = bonus && typeof bonus.cars === "number" ? bonus.cars : CAR_CARS[prize];
    console.info("[park] карусель приз", prize, "вагонов", cars, bonus ? "с сервера" : "локально", foreignRef.current ? "(чужая)" : "");
    if (!foreignRef.current) {
      setCarouselWin({ v: prize, key: Date.now() });
      rideTimers.current.push(
        setTimeout(() => wallet?.award("coins", prize, centerOf('[class*="carouselWinNum"]')), 700)
      );
      rideTimers.current.push(setTimeout(() => setCarouselWin(null), 2300));
    }
    if (cars > 0) {
      carsRef.current += cars;
      trainRef.current?.addCars(cars);
    }
    if (carsRef.current < TRAIN_FULL) {
      rideBusyRef.current = false;
      if (client) {
        bonusRef.current = null;
        foreignRef.current = false;
      }
      applyParkState();
    }
  };

  const runCarousel = () => {
    const bonus = client ? bonusRef.current : null;
    const fromServer = !!bonus && CAR_STOP_AT[bonus.carousel] !== undefined;
    const prize = fromServer ? bonus.carousel : CAR_PRIZE_SEQ[prizeIdx.current % CAR_PRIZE_SEQ.length];
    if (!fromServer) prizeIdx.current += 1;
    setCarStop(pickStop(CAR_STOP_AT[prize]));
    setRides({ carousel: true, wheel: false });
    playRide("carousel");
    for (let i = 0; i < CRYSTAL_ORDER.length; i += 1) {
      rideTimers.current.push(
        setTimeout(() => setKeysGot((n) => Math.max(0, Math.min(n, CRYSTAL_ORDER.length - 1 - i))), 700 + i * 650)
      );
    }
    rideTimers.current.push(
      setTimeout(() => {
        carDone.current = () => {
          carDone.current = null;
          rideTimers.current.push(setTimeout(() => givePrize(prize), 500));
        };
        setRides((r) => ({ ...r, carousel: false }));
      }, RIDE_RUN_MS)
    );
  };

  const onTrainDeparted = () => {
    const bonus = client ? bonusRef.current : null;
    const fromServer = !!bonus && WHEEL_STOP_AT[bonus.wheel] !== undefined;
    const prize = fromServer ? bonus.wheel : WHEEL_PRIZE_SEQ[wheelIdx.current % WHEEL_PRIZE_SEQ.length];
    if (!fromServer) wheelIdx.current += 1;
    const foreign = foreignRef.current;
    console.info("[park] колесо приз", prize, fromServer ? "с сервера" : "локально", foreign ? "(чужое)" : "");
    rideBusyRef.current = true;
    carsRef.current = 0;
    setWheelStop(WHEEL_STOP_AT[prize]);
    setRides((r) => ({ ...r, wheel: true }));
    playRide("wheel");
    const finishRide = () => {
      if (client) bonusRef.current = null;
      foreignRef.current = false;
      rideBusyRef.current = false;
      applyParkState();
    };
    wheelTimers.current.push(
      setTimeout(() => {
        wheelDone.current = () => {
          wheelDone.current = null;
          if (foreign) {
            finishRide();
            return;
          }
          wheelTimers.current.push(
            setTimeout(() => {
              setSalute(prize);
              wheelTimers.current.push(
                setTimeout(() => wallet?.award("coins", prize, centerOf('[class*="saluteCoin"]')), 1500)
              );
              wheelTimers.current.push(
                setTimeout(() => {
                  setSalute(null);
                  if (client) wallet?.settle("coins");
                  finishRide();
                }, 6200)
              );
            }, 500)
          );
        };
        setRides((r) => ({ ...r, wheel: false }));
      }, RIDE_RUN_MS)
    );
  };

  useEffect(
    () => () => {
      rideTimers.current.forEach(clearTimeout);
      wheelTimers.current.forEach(clearTimeout);
      if (client) {
        console.info("[park] верхний парк закрыт, снимаю удержание монет");
        wallet?.settle("coins");
      }
    },
    []
  );

  useEffect(() => {
    if (!ridesPending.current) return;
    ridesPending.current = false;
    startRides();
  }, [keysGot]);

  const startRides = () => {
    rideBusyRef.current = true;
    prizeGivenRef.current = false;
    rideTimers.current.forEach(clearTimeout);
    rideTimers.current = [setTimeout(runCarousel, 800)];
  };
  const lit = Object.fromEntries(CRYSTAL_ORDER.slice(0, keysGot).map((k) => [k, true]));
  const [glowOn, setGlowOn] = useState(loadGlow);
  const [ui, setUi] = useState(() => ({ ...DEFAULT_UI, ...loadJson(UI_KEY, {}) }));
  const [path, setPath] = useState(() => loadJson(PATH_KEY, DEFAULT_PATH));
  const [mode, setMode] = useState("off");

  useEffect(() => {
    const apply = (e) => setGlowOn(!!e.detail);
    window.addEventListener(GLOW_EVENT, apply);
    return () => {
      window.removeEventListener(GLOW_EVENT, apply);
    };
  }, []);

  const setBox = (key) => (updater) =>
    setUi((prev) => {
      const next = { ...prev, [key]: updater(prev[key]) };
      saveJson(UI_KEY, next);
      return next;
    });

  const setPathPts = (updater) =>
    setPath((prev) => {
      const next = updater(prev);
      saveJson(PATH_KEY, next);
      return next;
    });


  const moving = mode === "move";
  const pathing = mode === "path";

  const demoRef = useRef(true);
  const demoStep = useRef(0);

  useEffect(() => {
    window.slotDemo = (on = true) => {
      demoRef.current = !!on;
      demoStep.current = 0;
      return on ? "демо: комбинации по очереди" : "рандом";
    };
    return () => {
      delete window.slotDemo;
    };
  }, []);

  const [spinKey, setSpinKey] = useState(0);
  const [stopKey, setStopKey] = useState(0);
  const winNumCache = useRef([]);
  const [winPay, setWinPay] = useState(null);
  const [winShowMs, setWinShowMs] = useState(WIN_SHOW_MS);
  const [winOut, setWinOut] = useState(false);

  useEffect(() => {
    const keep = [];
    ["+", 1, 2, 3, 5, 10, 15, 25, 50, 100, 200, 500].forEach((v) => {
      const img = new Image();
      img.src = `${ASSET}/Win_Num_T/Win_Number_${v}.png`;
      img.decode?.().catch(() => {});
      keep.push(img);
    });
    winNumCache.current = keep;
  }, []);
  const [reel, setReel] = useState(["ice", "crown", "ice"]);
  const [shineCols, setShineCols] = useState([false, false, false]);
  const [spinning, setSpinning] = useState(false);
  const [spinLock, setSpinLock] = useState(false);
  const [landed, setLanded] = useState(true);
  const [winStage, setWinStage] = useState(0);
  const [flash, setFlash] = useState(0);
  const winTimers = useRef([]);

  useEffect(() => () => winTimers.current.forEach(clearTimeout), []);

  const rollLose = () => {
    for (let i = 0; i < 40; i += 1) {
      const r = [rnd(PLAIN), rnd(PLAIN), rnd(PLAIN)];
      if (!(r[0] === r[1] && r[1] === r[2])) return r;
    }
    return ["ice", "hat", "castle"];
  };

  const rollResult = () => {
    if (ALWAYS_CRYSTAL && !client) return COMBOS[COMBOS.length - 1].build();
    if (demoRef.current) {
      const item = DEMO_SEQ[demoStep.current % DEMO_SEQ.length];
      demoStep.current += 1;
      if (Array.isArray(item)) return item.slice();
      if (item === "lose") return rollLose();
      return COMBOS[item].build();
    }
    if (Math.random() < WIN_CHANCE) {
      const combo = COMBOS[Math.floor(Math.random() * COMBOS.length)];
      return combo.build();
    }
    return rollLose();
  };

  const [auto, setAuto] = useState(false);
  const [betOpen, setBetOpen] = useState(false);
  const [betClosing, setBetClosing] = useState(false);
  const [bet, setBet] = useState(1);
  const [hatPulse, setHatPulse] = useState(0);
  const betTimer = useRef(0);
  useEffect(() => () => clearTimeout(betTimer.current), []);
  const closeBet = () => {
    if (!betOpen || betClosing) return;
    setBetClosing(true);
    clearTimeout(betTimer.current);
    betTimer.current = setTimeout(() => {
      setBetOpen(false);
      setBetClosing(false);
    }, BET_OUT_MS);
  };
  const toggleBet = () => {
    if (betOpen) closeBet();
    else setBetOpen(true);
  };
  const betBoxRef = useRef(null);
  const onBetOverlay = (e) => {
    const hat = document.querySelector("[class*='goldHat']");
    const onHat = !!hat && document.elementsFromPoint(e.clientX, e.clientY).includes(hat);
    if (!onHat && betBoxRef.current && betBoxRef.current.contains(e.target)) return;
    if (onHat) setHatPulse((v) => v + 1);
    closeBet();
  };

  const doSpin = () => {
    if (spinning) {
      setStopKey((k) => k + 1);
      return;
    }
    if (client && spinBusy.current) return;
    if (wallet && !wallet.spend("coins", SPIN_COST)) return;
    if (client) {
      spinBusy.current = true;
      client
        .request({ action: "spin" })
        .then((r) => {
          spinBusy.current = false;
          console.info("[park] spin ответ", JSON.stringify(r));
          wallet?.applyTruth({ coins: r.coins });
          if (r.pay > 0) wallet?.hold("coins", r.pay);
          spinPayRef.current = typeof r.pay === "number" ? r.pay : null;
          spinCrystalsRef.current = typeof r.crystals === "number" ? r.crystals : null;
          startSpin(Array.isArray(r.reels) && r.reels.length === 3 ? r.reels.slice() : rollResult());
        })
        .catch((e) => {
          console.warn("[park] spin", e && e.code, e && e.message);
          spinBusy.current = false;
          wallet?.unspend("coins", SPIN_COST);
        });
      return;
    }
    startSpin(rollResult());
  };

  const startSpin = (res) => {
    winTimers.current.forEach(clearTimeout);
    winTimers.current = [];
    if (unpaidRef.current > 0) {
      console.info("[park] спин во время анимации, выигрыш", unpaidRef.current, "начисляю сразу");
      wallet?.award("coins", unpaidRef.current, null);
      unpaidRef.current = 0;
    }
    setWinStage(0);
    setWinPay(null);
    setWinOut(false);
    setLanded(false);
    setSpinning(true);
    setReel(res);
    setSpinKey((k) => k + 1);
  };

  const reelRef = useRef(reel);
  reelRef.current = reel;

  const onLanded = () => {
    setSpinning(false);
    setLanded(true);
    const r = reelRef.current;
    if (client) console.info("[park] барабаны встали", r.join(","), "выплата по таблице", payFor(r), "сервер", spinPayRef.current);
    const hasCoin = r.includes(GLOW_SYMBOL);
    const same = lineOf(r) !== null;
    if (r.includes("crystal")) {
      winTimers.current.push(
        setTimeout(() => {
          setKeysGot((n) => {
            const srv = client ? spinCrystalsRef.current : null;
            let next = Math.min(CRYSTAL_ORDER.length, n + 1);
            if (srv != null) next = srv >= CRYSTAL_ORDER.length || (srv === 0 && n >= CRYSTAL_ORDER.length - 1) ? CRYSTAL_ORDER.length : Math.min(CRYSTAL_ORDER.length - 1, srv);
            if (client) console.info("[park] кристаллы: было", n, "сервер", srv, "ставлю", next);
            if (n === CRYSTAL_ORDER.length - 1 && next === CRYSTAL_ORDER.length) ridesPending.current = true;
            return next;
          });
        }, CRYSTAL_GLOW_MS)
      );
    }
    const lit = [0, 1, 2].map((c) => same || r[c] === "coin" || r[c] === "crystal" || r[c] === "hat");
    const shownKeys = r.filter((k, i) => lit[i]);
    const showMs = shownKeys.length
      ? Math.max(...shownKeys.map((k) => ICON_SHOW_MS[k] ?? WIN_SHOW_MS))
      : WIN_SHOW_MS;
    setWinShowMs(showMs);
    setWinOut(false);
    const pay = client && spinPayRef.current != null ? spinPayRef.current : payFor(r);
    if (pay > 0) {
      unpaidRef.current = pay;
      const shown = WIN_NUMS.includes(pay);
      if (!shown) console.warn("[park] выплата", pay, "без картинки числа, монеты начислены, цифра не показана; есть только", WIN_NUMS.join(","));
      if (shown) winTimers.current.push(setTimeout(() => setWinPay(pay), WIN_NUM_IN));
      winTimers.current.push(
        setTimeout(() => {
          unpaidRef.current = 0;
          wallet?.award("coins", pay, centerOf('[class*="slotWinNum"]'));
        }, 1150)
      );
      if (shown) {
        winTimers.current.push(setTimeout(() => setWinOut(true), Math.max(400, showMs - WIN_NUM_OUT)));
        winTimers.current.push(
          setTimeout(() => {
            setWinPay(null);
            setWinOut(false);
          }, showMs)
        );
      }
    }
    if (hasCoin || same || r.includes("crystal") || r.includes("hat")) {
      setSpinLock(true);
      setWinStage(1);
      winTimers.current.push(setTimeout(() => setSpinLock(false), showMs + WIN_LOCK_GAP));
      winTimers.current.push(setTimeout(() => setWinStage(0), Math.max(250, showMs - WIN_OUT_MS)));
    }
  };

  const toggleAuto = () => setAuto((v) => !v);

  useEffect(() => {
    if (!auto) return undefined;
    if (spinning) {
      const t = setTimeout(() => setStopKey((k) => k + 1), AUTO_STOP_MS);
      return () => clearTimeout(t);
    }
    const t = setTimeout(() => doSpin(), AUTO_HOLD_MS);
    return () => clearTimeout(t);
  }, [auto, spinning, spinKey]);

  const isWin = lineOf(reel) !== null;
  const fxOn = winStage >= 1;
  const sparksOn = pathing || (fxOn && isWin);
  const glowCols = [0, 1, 2].map((c) => glowOn && shineCols[c] && reel[c] === GLOW_SYMBOL);
  const winCols = [0, 1, 2].map(
    (c) => fxOn && (isWin || reel[c] === "coin" || reel[c] === "crystal" || reel[c] === "hat")
  );

  return (
    <div className={styles.upark}>
      <div
        className={styles.uparkScene}
        onWheel={editC || decoOn ? zoomWheel : undefined}
        style={{
          width: "var(--scene-w)",
          height: "var(--scene-h)",
          "--iel": 1,
          top: "calc(var(--crop-top, 0px) + var(--fr-v, 0px))",
          transform: zoom !== 1 ? `translateX(-50%) scale(${zoom})` : undefined,
          transformOrigin: zoom !== 1 ? `${zorg.x}% ${zorg.y}%` : undefined,
        }}
      >
        <img className={styles.uparkBg} src={`${ASSET}/Up_Bg_3.webp`} alt="Park" draggable={false} />
      <div className={`${styles.uparkInner} ${styles.uparkPinOn}`}>
        <ParkDecor storeKey={DECO_UP_KEY} defaults={DEFAULT_DECO_UP} hatOn={betOpen && !betClosing} onHat={toggleBet} hatPulse={hatPulse} />
        <TrainRide ref={trainRef} onDeparted={onTrainDeparted} />
        {winPay && (
          <span
            key={`wp${winPay}-${spinKey}`}
            className={`${styles.slotWinNum} ${winOut ? styles.slotWinOut : ""}`}
          >
            <img className={styles.slotWinPlus} src={`${ASSET}/Win_Num_T/Win_Number_+.png`} alt="" draggable={false} />
            <span className={styles.slotWinValBox}>
              <img className={styles.slotWinVal} src={`${ASSET}/Win_Num_T/Win_Number_${winPay}.png`} alt="" draggable={false} />
            </span>
          </span>
        )}
        {carouselWin && (
          <img
            key={carouselWin.key}
            className={styles.carouselWinNum}
            src={`${ASSET}/Win_Num_T/Win_Number_${carouselWin.v}.png`}
            alt=""
            draggable={false}
            style={{
              left: `${upark.carousel.x + upark.carousel.w * 0.36}%`,
              top: `${upark.carousel.y - 3.2}%`,
            }}
          />
        )}
        <img
          className={styles.uparkWheel}
          src={`${ASSET}/Wheel_video/cor_01.png`}
          alt=""
          draggable={false}
          style={{ left: `${upark.plat.x}%`, top: `${upark.plat.y}%`, width: `${upark.plat.w}%`, zIndex: 1 }}
        />
        <FerrisSprite
          spin={rides.wheel}
          stopAt={wheelStop}
          onStopped={() => {
            stopRide("wheel");
            wheelDone.current?.();
          }}
          preload={keysGot > 0}
          period={RIDE_PERIOD.wheel}
          className={styles.uparkWheel}
          style={{ left: `${upark.wheel.x}%`, top: `${upark.wheel.y}%`, width: `${upark.wheel.w}%`, zIndex: 2 }}
        />
        <img
          className={styles.uparkWheel}
          src={`${ASSET}/Wheel_video/cor_02.png`}
          alt=""
          draggable={false}
          style={{ left: `${upark.plat.x}%`, top: `${upark.plat.y}%`, width: `${upark.plat.w}%`, zIndex: 3 }}
        />
        <CarouselSprite
          spin={rides.carousel}
          stopAt={carStop}
          onStopped={() => {
            stopRide("carousel");
            carDone.current?.();
          }}
          preload={keysGot > 0}
          period={RIDE_PERIOD.carousel}
          className={styles.uparkWheel}
          style={{ left: `${upark.carousel.x}%`, top: `${upark.carousel.y}%`, width: `${upark.carousel.w}%` }}
        />
        <img
          className={styles.uparkBush}
          src={`${ASSET}/bush_wheel.png`}
          alt=""
          draggable={false}
          style={{ left: `${upark.bush.x}%`, top: `${upark.bush.y}%`, width: `${upark.bush.w}%` }}
        />
        <ParkBox box={ui.spin} edit={moving} onChange={setBox("spin")} z={14} label="SPIN" interactive>
          <span
            className={`${styles.spinBtn} ${spinning ? "" : styles.spinIdle}`}
            onClick={moving ? undefined : doSpin}
          >
            <img className={styles.spinBase} src={`${ASSET}/spin_button/Btn_Spin.png`} alt="" draggable={false} />
            <img
              className={styles.spinLabel}
              src={`${ASSET}/spin_button/Btn_Spin_Front.png`}
              alt=""
              draggable={false}
            />
            <span className={styles.spinClip}>
              <img
                className={styles.spinShine}
                src={`${ASSET}/spin_button/Btn_Spin_Light.png`}
                alt=""
                draggable={false}
              />
              <img
                className={styles.spinStar}
                src={`${ASSET}/spin_button/Star_hat.png`}
                alt=""
                draggable={false}
              />
            </span>
          </span>
        </ParkBox>
        <button
          type="button"
          className={`${styles.autoRow} ${auto ? styles.autoOn : ""}`}
          style={{ left: `${ui.spin.x}%`, top: `${ui.spin.y + 8.21}%`, width: `${ui.spin.w}%` }}
          onClick={toggleAuto}
        >
          <span className={styles.autoBox}>{auto ? "✓" : ""}</span>
          <span className={styles.autoText}>Auto Spin</span>
        </button>
        {Object.keys(CRYSTAL_CFG).map((k) => (
          <CrystalSpot key={k} x={upark[k].x} y={upark[k].y} w={upark[k].w} cfg={CRYSTAL_CFG[k]} on={!!lit[k]} />
        ))}
        {editC &&
          Object.keys(CRYSTAL_CFG).map((k) => (
            <div
              key={`crhit-${k}`}
              className={styles.crHit}
              style={{
                left: `${upark[k].x}%`,
                top: `${upark[k].y}%`,
                width: `${upark[k].w}%`,
                height: `${(upark[k].w * 0.75).toFixed(2)}%`,
              }}
              onPointerDown={(e) => crDown(e, k)}
            />
          ))}
        {STAR_KEYS.map((k) => (
          <StarSpot
            key={k}
            x={upark[k].x}
            y={upark[k].y}
            w={upark[k].w}
            on={!!lit[k]}
            mirror={STAR_MIRROR.has(k)}
            z={UPARK_Z[k]}
          />
        ))}
        {provider ? (
          <>
            <Fountain x={upark.flask1.x} y={upark.flask1.y} w={upark.flask1.w * FOUNTAIN_K} />
            <Fountain x={upark.flask2.x} y={upark.flask2.y} w={upark.flask2.w * FOUNTAIN_K} />
          </>
        ) : (
          <>
            <Flask x={upark.flask1.x} y={upark.flask1.y} w={upark.flask1.w} fill={0} />
            <Flask x={upark.flask2.x} y={upark.flask2.y} w={upark.flask2.w} fill={0} />
          </>
        )}
        {Object.keys(UPARK_FLAGS).map((k, i) => (
          <WindFlag
            key={k}
            img={UPARK_FLAGS[k]}
            x={upark[k].x}
            y={upark[k].y}
            w={upark[k].w}
            delay={`${-(i * 0.73).toFixed(2)}s`}
            dur={`${(3.4 + (i % 4) * 0.55).toFixed(2)}s`}
          />
        ))}



        {glowCols.map((on, c) => (
          <ParkBox
            key={`glow${c}`}
            box={{
              x: ui.monitor.x + (ui.monitor.w * (c * 2 + 1)) / 6 - ui.glow.w / 2,
              y: ui.glow.y + (GLOW_DY[c] * ui.monitor.h) / 100,
              w: ui.glow.w,
              h: ui.glow.h,
            }}
            edit={moving && c === 1}
           
           
            onChange={setBox("glow")}
            z={11}
            label="Огонь"
          >
            <CenterGlow on={on} flash={flash} tilt={GLOW_ROT[c]} />
          </ParkBox>
        ))}

        <ParkBox box={ui.monitor} edit={moving} onChange={setBox("monitor")} z={12} label="Монитор">
          <MonitorScreen spinKey={spinKey} stopKey={stopKey} result={reel} onLanded={onLanded} onShineChange={setShineCols} win={winCols} />
        </ParkBox>

      </div>
      </div>
      {salute && (
        <div className={styles.saluteWrap} onClick={() => setSalute(null)}>
          <img className={styles.saluteConfetti} src={`${ASSET}/Win/Win_Confeti.png`} alt="" draggable={false} />
          <span className={styles.saluteTitleBox}>
            <img className={styles.saluteRays} src={`${ASSET}/Win/Win_Light_Fx.png`} alt="" draggable={false} />
            <img className={styles.saluteTitle} src={`${ASSET}/Win/Win_Title.png`} alt="" draggable={false} />
          </span>
          <span className={styles.saluteCoinBox}>
            <img className={styles.saluteGlow} src={`${ASSET}/Win/Win_Light_Fx_Yellow.png`} alt="" draggable={false} />
            <img className={styles.saluteCoin} src={`${ASSET}/Win/Coin_Front_XL.png`} alt="" draggable={false} />
          </span>
          <img className={styles.saluteNum} src={`${ASSET}/Win_Num_T/Win_Number_${salute}.png`} alt="" draggable={false} />
        </div>
      )}
      {betOpen && (
        <div className={styles.betOverlay} onClick={onBetOverlay}>
          <div ref={betBoxRef} className={`${styles.betBox} ${betClosing ? styles.betBoxOut : ""}`}>
            <ParkPopupFrame title="Bet">
              <div className={styles.betRow}>
                {[1, 10, 100].map((v) => {
                  const locked = BET_LOCKED.has(v);
                  return (
                    <button
                      key={v}
                      type="button"
                      className={`${styles.betBtn} ${bet === v ? styles.betBtnOn : ""} ${locked ? styles.betBtnOff : ""}`}
                      disabled={locked}
                      onClick={locked ? undefined : () => setBet(v)}
                    >
                      x{v}
                    </button>
                  );
                })}
              </div>
            </ParkPopupFrame>
          </div>
        </div>
      )}
    </div>
  );
}

const FOUNTAIN_K = 2.2;

const OPEN_KIND = { coinhand: "joker", coinflip: "joker", matches: "joker", cards: "cards", pyramid: "pyramid", wheel: "wheel" };
const PARKS_BUILD = "2026-10-10b";

const VIEW_BY_PAGE = { parkmap: "overview", parktop: "park", parkbot: "grid" };
const PAGE_BY_VIEW = { overview: "parkmap", park: "parktop", grid: "parkbot" };

const startView = () => {
  const p = new URLSearchParams(window.location.search).get("page");
  return VIEW_BY_PAGE[p] || "overview";
};

export default function BonusParkPage() {
  const { navigate } = useApp();
  const [popupOpen, setPopupOpen] = useState(false);
  const [needLogin, setNeedLogin] = useState(() => parkServerEnabled() && !parkAuth());
  const [sessionBusy, setSessionBusy] = useState(false);
  const [client] = useState(() => (parkServerEnabled() && parkAuth() ? new ParkSocket(parkConfig()) : null));
  const [loginErr, setLoginErr] = useState("");
  const [srv, setSrv] = useState({ status: "idle", balance: null, sector: null, players: [], sectors: null });
  const wasOpenRef = useRef(false);
  const retriedRef = useRef(false);

  useEffect(() => {
    console.info("[park] parks build", PARKS_BUILD);
  }, []);

  useEffect(() => {
    if (!client) return undefined;
    const offS = client.on("status", (st) => {
      if (st === "open") wasOpenRef.current = true;
      if (st === "retry") retriedRef.current = true;
      if (st === "busy") {
        console.warn("[park] сервер отбил вход: аккаунт уже занят другим соединением");
        setSessionBusy(true);
        client.close();
      }
      if (st === "unauthorized" && parkAuth() && parkAuth().login) {
        clearParkAuth();
        setLoginErr("Wrong login or password");
        setNeedLogin(true);
      }
      setSrv((v) => ({ ...v, status: st }));
      if (st !== "open") {
        if (st === "unauthorized") setSrv((v) => (v.sector ? { ...v, sector: null } : v));
        else if (st === "retry") console.info("[park] связь потеряна, поле держу до ответа lobby после реконнекта");
        return;
      }
      client
        .request({ action: "lobby" })
        .then((r) => {
          if (r.user_id != null) setParkSelfId(r.user_id);
          else if (r.me != null) setParkSelfId(r.me);
          const meId = String(parkSelfId());
          const mine = Array.isArray(r.players) ? r.players.find((pl) => String(pl.user_id) === meId) : null;
          const fromPlayers = mine && mine.sector != null ? mine.sector : null;
          if (r.sector == null && fromPlayers != null) console.info("[park] lobby: sector null, но в players я на поле", fromPlayers, "— считаю себя привязанным");
          setSrv((v) => ({
            ...v,
            sector: r.sector != null ? r.sector : fromPlayers != null ? fromPlayers : r.sector !== undefined ? r.sector : v.sector,
            sectors: r.sectors && typeof r.sectors === "object" ? r.sectors : v.sectors,
            players: Array.isArray(r.players)
              ? r.players.map((pl) => ({ id: `u${pl.user_id}`, name: pl.name, avatar: pl.avatar || "" }))
              : v.players,
          }));
        })
        .catch(() => {});
      client
        .request({ action: "get_balance" })
        .then((b) => setSrv((v) => ({ ...v, balance: b })))
        .catch(() => {});
    });
    const offB = client.on("balance", (b) => setSrv((v) => (v.balance ? v : { ...v, balance: b })));
    const onSectors = (m) => {
      if (!m || typeof m !== "object") return;
      const hasSectors = !!m.sectors && typeof m.sectors === "object";
      const hasPlayers = Array.isArray(m.players);
      if (!hasSectors && !hasPlayers) return;
      if (hasSectors) console.info("[park] sectors", JSON.stringify(m.sectors));
      if (hasPlayers) console.info("[park] players", JSON.stringify(m.players));
      setSrv((v) => ({
        ...v,
        sectors: hasSectors ? m.sectors : v.sectors,
        players: hasPlayers ? m.players.map((pl) => ({ id: `u${pl.user_id}`, name: pl.name, avatar: pl.avatar || "" })) : v.players,
      }));
    };
    const offL = client.on("lobby", onSectors);
    const offX = client.on("sectors", onSectors);
    const onSeen = (m) => {
      if (!m || m.user_id == null) return;
      if (String(m.user_id) === String(parkSelfId())) return;
      const id = `u${m.user_id}`;
      setSrv((v) => {
        if (v.players.some((p) => p.id === id)) return v;
        console.info("[park] игрок из field_state, в lobby его не было:", m.user_id);
        return { ...v, players: [...v.players, { id, name: m.name || `Player_${m.user_id}`, avatar: m.avatar || "" }] };
      });
    };
    const offSeen = client.on("field_state", onSeen);
    client.connect();
    window.parkWs = client;
    return () => {
      offS();
      offB();
      offL();
      offX();
      offSeen();
      client.close();
      if (window.parkWs === client) delete window.parkWs;
    };
  }, [client]);

  const joinSector = useCallback(
    (sector, retried = false) => {
      if (!client) return Promise.resolve(null);
      console.info("[park] join_sector", sector);
      return client
        .request({ action: "join_sector", sector })
        .then((r) => {
          setSrv((v) => ({
            ...v,
            sector: r.sector ?? sector,
            sectors: r.sectors && typeof r.sectors === "object" ? r.sectors : v.sectors,
            players: Array.isArray(r.players)
              ? r.players.map((pl) => ({ id: `u${pl.user_id}`, name: pl.name, avatar: pl.avatar || "" }))
              : v.players,
          }));
          return r;
        })
        .catch((e) => {
          const code = e && e.code;
          console.warn("[park] join_sector", sector, code);
          if (!retried && (code === "already_joined" || code === "sector_taken")) {
            console.info("[park] сервер считает меня привязанным, освобождаю старое место и пробую снова");
            return client
              .request({ action: "leave_sector" })
              .catch(() => null)
              .then(() => joinSector(sector, true));
          }
          client
            .request({ action: "lobby" })
            .then((r) => setSrv((v) => ({ ...v, sector: r.sector ?? v.sector, sectors: r.sectors || v.sectors })))
            .catch(() => {});
          return null;
        });
    },
    [client]
  );

  const leaveSector = useCallback(() => {
    if (!client) return Promise.resolve(null);
    console.info("[park] leave_sector");
    return client
      .request({ action: "leave_sector" })
      .then((r) => {
        setSrv((v) => ({ ...v, sector: null, sectors: r.sectors && typeof r.sectors === "object" ? r.sectors : v.sectors }));
        client
          .request({ action: "lobby" })
          .then((l) => setSrv((v) => ({ ...v, sector: l.sector ?? null, sectors: l.sectors || v.sectors })))
          .catch(() => {});
        return r;
      })
      .catch((e) => {
        console.warn("[park] leave_sector", e && e.code, "переподключаюсь, сервер освободит поле по разрыву");
        setSrv((v) => ({ ...v, sector: null }));
        client.close();
        client.connect();
        return null;
      });
  }, [client]);

  const [leaveAsk, setLeaveAsk] = useState(false);
  useEffect(() => {
    if (popupOpen && leaveAsk) setLeaveAsk(false);
  }, [popupOpen, leaveAsk]);

  const serverCtx = useMemo(
    () =>
      client
        ? {
            client,
            status: srv.status,
            balance: srv.balance,
            sector: srv.sector,
            players: srv.players,
            sectors: srv.sectors,
            userId: parkSelfId(),
            joinSector,
            leaveSector,
          }
        : null,
    [client, srv, joinSector, leaveSector]
  );
  const subscribeWins = useCallback(
    (push) =>
      client
        ? client.on("player_win", (m) => {
            if (m && m.user_id != null && String(m.user_id) === String(parkSelfId())) return;
            push({ id: `u${m.user_id}`, name: m.name, avatar: m.avatar, amount: m.prize ?? m.amount ?? m.win });
          })
        : undefined,
    [client]
  );
  const [openEvent, setOpenEvent] = useState(null);
  const openShown = useCallback((key) => setOpenEvent((cur) => (cur && cur.key === key ? null : cur)), []);

  useEffect(() => {
    if (!client) return undefined;
    let dead = false;
    const emit = (m, game, value) => {
      let kind = null;
      if (m.outcome === "game") kind = OPEN_KIND[game] || null;
      else if (m.outcome === "money") kind = "safe";
      else if (m.outcome === "cash") kind = "cash";
      console.info("[park] иконка игрока", m.user_id, m.outcome, game || "", value ?? "", "->", kind || "ничего");
      if (!kind || dead) return;
      setOpenEvent({
        key: `${m.user_id}-${m.cell_id}-${Date.now()}`,
        id: `u${m.user_id}`,
        name: m.name || `Player_${m.user_id}`,
        avatar: m.avatar || "",
        game: kind,
        value: kind === "cash" ? Number(value) || 0 : undefined,
      });
    };
    const off = client.on("field_state", (m) => {
      if (!m || m.user_id == null) return;
      if (String(m.user_id) === String(parkSelfId())) return;
      const cellId = Number(m.cell_id);
      if (!Number.isInteger(cellId) || cellId < 0) return;
      if (m.outcome !== "game" && m.outcome !== "cash") {
        emit(m, m.game, m.value);
        return;
      }
      const hasGame = m.outcome === "game" && typeof m.game === "string" && m.game;
      const hasValue = m.outcome === "cash" && typeof m.value === "number";
      if (hasGame || hasValue) {
        emit(m, m.game, m.value);
        return;
      }
      client
        .request({ action: "get_field" })
        .then((r) => {
          const cell = Array.isArray(r.cells) ? r.cells.find((c) => Number(c.cell_id) === cellId) : null;
          console.info("[park] в field_state нет game/value, взял из get_field:", JSON.stringify(cell));
          emit(m, cell ? cell.game : m.game, cell ? cell.value : m.value);
        })
        .catch(() => emit(m, m.game, m.value));
    });
    return () => {
      dead = true;
      off();
    };
  }, [client]);
  const [zoom, setZoom] = useState({ zoomed: false, busy: false, exit: null });
  const [coinRing, setCoinRing] = useState(0);
  const [loadView, setLoadView] = useState(startView);

  useEffect(() => {
    window.scrollTo(0, 0);
    window.dispatchEvent(new Event("bonuspark-refit"));
  }, []);
  useEffect(() => {
    const onWheel = (e) => {
      if (e.ctrlKey) e.preventDefault();
    };
    const onGesture = (e) => e.preventDefault();
    window.addEventListener("wheel", onWheel, { passive: false });
    window.addEventListener("gesturestart", onGesture, { passive: false });
    window.addEventListener("gesturechange", onGesture, { passive: false });
    window.addEventListener("gestureend", onGesture, { passive: false });
    return () => {
      window.removeEventListener("wheel", onWheel);
      window.removeEventListener("gesturestart", onGesture);
      window.removeEventListener("gesturechange", onGesture);
      window.removeEventListener("gestureend", onGesture);
    };
  }, []);

  useEffect(() => {
    const keep = GAME_PRELOAD.map((src) => {
      const img = new Image();
      img.decoding = "async";
      img.src = src;
      return img;
    });
    if (document.fonts && document.fonts.load) GAME_FONTS.forEach((f) => document.fonts.load(f));
    return () => {
      keep.length = 0;
    };
  }, []);

  useEffect(() => {
    if (popupOpen) document.body.dataset.pop = "1";
    else delete document.body.dataset.pop;
    return () => {
      delete document.body.dataset.pop;
    };
  }, [popupOpen]);

  const [view, setView] = useState(startView);
  const [anim, setAnim] = useState("hold");
  const [wheel, setWheel] = useState(null);
  const timers = useRef([]);

  useEffect(() => {
    window.openWheel = (target) => {
      setWheel({ key: Date.now(), target: target ?? null });
      return "ok";
    };
    return () => {
      delete window.openWheel;
    };
  }, []);

  const go = (v) => {
    timers.current.forEach(clearTimeout);
    timers.current = [];
    setAnim("in");
    const t1 = setTimeout(() => {
      setAnim("hold");
      setView(v);
      setLoadView(v);
    }, 420);
    timers.current.push(t1);
  };

  useEffect(() => {
    const page = PAGE_BY_VIEW[view];
    if (!page) return;
    const u = new URL(window.location.href);
    const lobbied = page === "parktop" || page === "parkbot";
    const same = u.searchParams.get("page") === page && (!lobbied || u.searchParams.get("lobby"));
    if (same) return;
    u.searchParams.set("page", page);
    if (lobbied && !u.searchParams.get("lobby")) u.searchParams.set("lobby", "1");
    if (!lobbied) u.searchParams.delete("lobby");
    window.history.replaceState(null, "", u.toString());
  }, [view]);

  const onLoaded = () => {
    setLoadView(null);
    setAnim("out");
    const t = setTimeout(() => setAnim(null), 520);
    timers.current.push(t);
  };

  useEffect(() => {
    const list = timers.current;
    return () => list.forEach(clearTimeout);
  }, []);

  const pick = (v) => go(v);

  return (
    <ParkServerContext.Provider value={serverCtx}>
    <WalletProvider server={client}>
    <div className={styles.page}>
      {view === "overview" && <Overview onPick={pick} />}
      {view === "grid" && (
        <GridGame onBack={() => go("overview")} onPopup={setPopupOpen} onZoomState={setZoom} onCoinRing={setCoinRing} />
      )}
      {view === "park" && <UpperPark provider={parkMode() === "provider"} />}

      <ParkHud tickets={view !== "park"} coinProgress={coinRing} />
      {!popupOpen && view !== "overview" && (
        <PlayerRow
          icons={view === "grid"}
          players={client ? srv.players.filter((p) => p.id !== `u${parkSelfId()}`) : []}
          subscribe={client ? subscribeWins : undefined}
          openEvent={client ? openEvent : null}
          onOpenShown={client ? openShown : undefined}
          demo={client ? false : undefined}
          placeholders
          ghostEmpty={!!client}
        />
      )}

      {!popupOpen && !zoom.busy && (
        <button
          type="button"
          className={styles.exit}
          onClick={() => {
            if (view === "grid" && zoom.zoomed) {
              if (client && srv.sector) {
                setLeaveAsk(true);
                return;
              }
              zoom.exit?.();
              return;
            }
            if (view === "overview") navigate("home");
            else go("overview");
          }}
        >
          <img className={styles.exitBg} src={`${ASSET}/Exchange/Btn_round_Red_c.png`} alt="" draggable={false} />
          <img className={styles.exitIcn} src={`${ASSET}/Exchange/Icn_Arrow_Back.png`} alt="" draggable={false} />
        </button>
      )}

      {sessionBusy && (
        <div className={styles.leaveOverlay}>
          <div className={styles.leaveBox}>
            <ParkPopupFrame title="Account busy">
              <div className={styles.leaveText}>This account is already open on another device or tab.</div>
              <div className={styles.leaveRow}>
                <button type="button" className={styles.leaveBtn} onClick={() => window.location.reload()}>
                  Retry
                </button>
                <button
                  type="button"
                  className={`${styles.leaveBtn} ${styles.leaveBtnNo}`}
                  onClick={() => {
                    clearParkAuth();
                    window.location.reload();
                  }}
                >
                  Logout
                </button>
              </div>
            </ParkPopupFrame>
          </div>
        </div>
      )}

      {needLogin && (
        <div className={styles.leaveOverlay}>
          <div className={styles.leaveBox}>
            <ParkPopupFrame title="Login">
              <form
                className={styles.loginForm}
                onSubmit={(e) => {
                  e.preventDefault();
                  const fd = new FormData(e.currentTarget);
                  const login = String(fd.get("login") || "").trim();
                  const password = String(fd.get("password") || "");
                  if (!login || !password) return;
                  saveParkAuth({ login, password });
                  window.location.reload();
                }}
              >
                <input className={styles.loginInput} name="login" placeholder="Login" autoComplete="username" autoCapitalize="none" />
                <input className={styles.loginInput} name="password" type="password" placeholder="Password" autoComplete="current-password" />
                {loginErr && <div className={styles.loginErr}>{loginErr}</div>}
                <button type="submit" className={styles.leaveBtn}>
                  Enter
                </button>
              </form>
            </ParkPopupFrame>
          </div>
        </div>
      )}

      {leaveAsk && !popupOpen && (
        <div className={styles.leaveOverlay} onClick={() => setLeaveAsk(false)}>
          <div className={styles.leaveBox} onClick={(e) => e.stopPropagation()}>
            <ParkPopupFrame title="Leave field">
              <div className={styles.leaveText}>Do you really want to leave the field?</div>
              <div className={styles.leaveRow}>
                <button
                  type="button"
                  className={styles.leaveBtn}
                  onClick={() => {
                    setLeaveAsk(false);
                    zoom.exit?.();
                    leaveSector();
                  }}
                >
                  Yes
                </button>
                <button type="button" className={`${styles.leaveBtn} ${styles.leaveBtnNo}`} onClick={() => setLeaveAsk(false)}>
                  No
                </button>
              </div>
            </ParkPopupFrame>
          </div>
        </div>
      )}

      {client && (srv.status === "retry" || srv.status === "unauthorized" || (srv.status === "connecting" && retriedRef.current)) && (
        <div className={styles.netBadge}>
          <span className={`${styles.netDot} ${srv.status === "unauthorized" ? styles.netDotOff : ""}`} />
          <span>{srv.status === "unauthorized" ? "No access" : wasOpenRef.current ? "Reconnecting…" : "Connecting to server…"}</span>
        </div>
      )}

      {loadView && <ParkLoader view={loadView} onDone={onLoaded} />}
      {anim && (
        <div
          className={`${styles.cover} ${anim === "in" ? styles.coverIn : anim === "hold" ? styles.coverHold : styles.coverOut}`}
        />
      )}
    </div>
    </WalletProvider>
    </ParkServerContext.Provider>
  );
}
