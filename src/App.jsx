import { useState, useEffect } from "react";
import { useApp } from "@/context/AppContext";
import TopBar from "@/components/layout/TopBar";
import BottomNav from "@/components/layout/BottomNav";
import Drawer from "@/components/layout/Drawer";
import SupportPopup from "@/components/layout/SupportPopup";
import StatusOverlay from "@/components/ui/StatusOverlay";
import Notice from "@/components/ui/Notice";
import GameFrame from "@/components/layout/GameFrame";
import HomePage from "@/components/pages/HomePage";
import GamesPage from "@/components/pages/GamesPage";
import PromotionsPage from "@/components/pages/PromotionsPage";
import BonusPage from "@/components/pages/BonusPage";
import SportPage from "@/components/pages/SportPage";
import DepositPage from "@/components/pages/DepositPage";
import GamePage from "@/components/pages/GamePage";
import BonusParkPage from "@/components/pages/BonusParkPage";
import Lunopark from "@/components/pages/Lunopark";
import FerrisWheel from "@/components/pages/FerrisWheel";
import MiniGamesPage from "@/components/pages/MiniGamesPage";
import ExchangePage from "@/components/pages/ExchangePage";
import styles from "./App.module.css";

const PAGES = {
  home: HomePage,
  games: GamesPage,
  promotions: PromotionsPage,
  bonus: BonusPage,
  bonuspark: BonusParkPage,
  parkmap: BonusParkPage,
  parktop: BonusParkPage,
  parkbot: BonusParkPage,
  lunopark: Lunopark,
  ferris: FerrisWheel,
  minigames: MiniGamesPage,
  exchange: ExchangePage,
  sport: SportPage,
  deposit: DepositPage,
  game: GamePage,
};

function PageRouter() {
  const { route } = useApp();
  const Page = PAGES[route] || HomePage;

  return (
    <div className={`${styles.content} ${route === "bonus" ? styles.bonusMode : ""}`}>
      <Page />
    </div>
  );
}

const BASE_W = 390;
const BASE_H = 844;
const MAX_STAGE_W = 480;

const FRAME_T = 12;
const FRAME_MIN = 15;
const DESK_PAD = 18;

const IS_IOS =
  typeof navigator !== "undefined" &&
  (/iP(hone|ad|od)/.test(navigator.userAgent) ||
    (navigator.platform === "MacIntel" && (navigator.maxTouchPoints || 0) > 1));

const IOS_LIFT = IS_IOS ? 26 : 0;
const SCENE_AR = 0.63981;
const CAR_R = 0.9608;
const CAR_GAP = 3;
const GRID_AR = 0.64;

function measureViewport() {
  const probe = document.createElement("div");
  probe.style.cssText =
    "position:fixed;top:0;left:0;width:0;height:0;padding-top:env(safe-area-inset-top);visibility:hidden;pointer-events:none";
  document.body.appendChild(probe);
  const safeTopRaw = parseFloat(getComputedStyle(probe).paddingTop) || 0;
  probe.remove();
  const w = Math.round(window.visualViewport?.width || window.innerWidth);
  const h = Math.round(window.visualViewport?.height || window.innerHeight);
  const effW = Math.min(w, MAX_STAGE_W);
  const wideWin = w > MAX_STAGE_W;
  const visW = BASE_W;
  const visH = BASE_H;
  const innerW = visW - 2 * FRAME_T;
  const innerH = visH - 2 * FRAME_T;
  const needW = (innerW / 2 - CAR_GAP) / (CAR_R - 0.5);
  const capW = visW - 2 * FRAME_MIN;
  const sceneH = Math.max(120, Math.min(needW / SCENE_AR, innerH, capW / SCENE_AR, visH - 2 * FRAME_MIN));
  const sceneWv = sceneH * SCENE_AR;
  const gridH = Math.min(visH - 2 * FRAME_MIN, (visW - 2 * FRAME_MIN) / GRID_AR);
  const gridW = gridH * GRID_AR;

  const contentH = Math.max(sceneH, gridH);
  const contentW = Math.max(sceneWv, gridW);
  const contentTop = (BASE_H - contentH) / 2;
  const scale = wideWin
    ? Math.min((w - 2 * DESK_PAD) / contentW, (h - 2 * DESK_PAD) / contentH)
    : Math.max(effW / BASE_W, h / BASE_H);
  const left = wideWin
    ? (w - contentW * scale) / 2 - ((BASE_W - contentW) / 2) * scale
    : (w - BASE_W * scale) / 2;
  const top = wideWin ? (h - contentH * scale) / 2 - contentTop * scale : (h - BASE_H * scale) / 2;

  const wvW = w / scale;
  const wvH = h / scale;
  const wide = w > MAX_STAGE_W;
  const frameT = wide ? (BASE_W - sceneWv) / 2 : Math.max(FRAME_MIN, (wvW - sceneWv) / 2);
  const frameV = wide ? (BASE_H - sceneH) / 2 : Math.max(FRAME_MIN, (wvH - sceneH) / 2);
  const gridT = wide ? (BASE_W - gridW) / 2 : Math.max(FRAME_MIN, (wvW - gridW) / 2);
  const gridV = wide ? (BASE_H - gridH) / 2 : Math.max(FRAME_MIN, (wvH - gridH) / 2);
  const wNeedW = (wvW - 2 * FRAME_T) / 2 - CAR_GAP;
  const wSceneH = Math.max(
    120,
    Math.min(wNeedW / (CAR_R - 0.5) / SCENE_AR, wvH - 2 * FRAME_T, (wvW - 2 * FRAME_MIN) / SCENE_AR, wvH - 2 * FRAME_MIN)
  );
  const wGridH = Math.min(wvH - 2 * FRAME_MIN, (wvW - 2 * FRAME_MIN) / GRID_AR);
  return {
    frameSceneW: wide ? sceneWv : wSceneH * SCENE_AR,
    frameSceneH: wide ? sceneH : wSceneH,
    frameGridW: wide ? gridW : wGridH * GRID_AR,
    frameGridH: wide ? gridH : wGridH,
    scale,
    left,
    top,
    cropTop: wideWin ? 0 : Math.max(0, -top) / scale,
    cropBottom: wideWin ? 0 : Math.max(0, -top) / scale,
    popTop: Math.max(0, -top) / scale,
    popBottom: Math.max(0, top + BASE_H * scale - h) / scale,
    safeTop: safeTopRaw / scale,
    overX: Math.max(0, (w / scale - BASE_W) / 2),
    cropX: Math.max(0, (BASE_W - w / scale) / 2),
    overY: Math.max(0, (h / scale - BASE_H) / 2),
    winW: w,
    winH: h,
    vvTop: Math.round(window.visualViewport?.offsetTop || 0),
    vvLeft: Math.round(window.visualViewport?.offsetLeft || 0),
    frameT,
    frameV,
    sceneW: sceneWv,
    sceneH,
    gridW,
    gridH,
    gridT,
    gridV,
  };
}

function useFitScale() {
  const [view, setView] = useState(measureViewport);
  useEffect(() => {
    let timer = 0;
    const wake = [];
    const apply = () => {
      setView((prev) => {
        const next = measureViewport();
        const same = Object.keys(next).every((k) => Math.abs((prev?.[k] ?? NaN) - next[k]) < 0.01);
        return same ? prev : next;
      });
    };
    const onResize = () => {
      clearTimeout(timer);
      timer = setTimeout(apply, 120);
    };
    const clearWake = () => {
      while (wake.length) clearTimeout(wake.pop());
    };
    const onWake = () => {
      if (document.visibilityState === "hidden") return;
      clearWake();
      apply();
      wake.push(setTimeout(apply, 180));
      wake.push(setTimeout(apply, 600));
    };
    apply();
    window.addEventListener("resize", onResize);
    window.addEventListener("orientationchange", onResize);
    window.visualViewport?.addEventListener("resize", onResize);
    window.visualViewport?.addEventListener("scroll", onResize);
    document.addEventListener("visibilitychange", onWake);
    window.addEventListener("pageshow", onWake);
    window.addEventListener("focus", onWake);
    return () => {
      clearTimeout(timer);
      clearWake();
      window.removeEventListener("resize", onResize);
      window.removeEventListener("orientationchange", onResize);
      window.visualViewport?.removeEventListener("resize", onResize);
      window.visualViewport?.removeEventListener("scroll", onResize);
      document.removeEventListener("visibilitychange", onWake);
      window.removeEventListener("pageshow", onWake);
      window.removeEventListener("focus", onWake);
    };
  }, []);
  return view;
}

export default function App() {
  const { loading, error, retry } = useApp();
  const {
    scale, left, top, cropTop, cropBottom, popTop, popBottom, safeTop, frameT, frameV, sceneW, sceneH, gridW, gridH, gridT, gridV,
    overX, cropX, overY, winW, winH, vvTop, vvLeft, frameSceneW, frameSceneH, frameGridW, frameGridH,
  } = useFitScale();

  return (
    <>
      <div className={styles.scrollSpace} />
      <div
        className={styles.stageWrap}
        style={{
          top: `${vvTop}px`,
          left: `${vvLeft}px`,
          right: "auto",
          bottom: "auto",
          width: `${winW}px`,
          height: `${winH}px`,
          "--fr-s-px": `${(winW > MAX_STAGE_W
            ? (winW - frameSceneW * scale) / 2
            : Math.max(15 * scale, (winW - frameSceneW * scale) / 2)
          ).toFixed(2)}px`,
          "--fr-v-px": `${(winW > MAX_STAGE_W
            ? (winH - frameSceneH * scale) / 2
            : Math.max(15 * scale, (winH - frameSceneH * scale) / 2)
          ).toFixed(2)}px`,
          "--gr-s-px": `${(winW > MAX_STAGE_W
            ? (winW - frameGridW * scale) / 2
            : Math.max(15 * scale, (winW - frameGridW * scale) / 2)
          ).toFixed(2)}px`,
          "--gr-v-px": `${(winW > MAX_STAGE_W
            ? (winH - frameGridH * scale) / 2
            : Math.max(15 * scale, (winH - frameGridH * scale) / 2)
          ).toFixed(2)}px`,
        }}
      >
      <div
        className={styles.phone}
        data-desk={winW > MAX_STAGE_W ? "1" : undefined}
        style={{
          transform: `translate(${left}px, ${top}px) scale(${scale})`,
          "--safe-top": `${safeTop}px`,
          "--crop-top": `${cropTop}px`,
          "--crop-bottom": `${cropBottom}px`,
          "--pop-top": `${popTop.toFixed(2)}px`,
          "--pop-bottom": `${popBottom.toFixed(2)}px`,
          "--crop-x": `${cropX.toFixed(2)}px`,
          "--over-x": `${overX.toFixed(2)}px`,
          "--over-y": `${overY.toFixed(2)}px`,
          "--ios-lift": `${IOS_LIFT}px`,
          "--top-extra": winW > MAX_STAGE_W ? "-6px" : "0px",
          "--pin-k": winW > MAX_STAGE_W ? Math.min(3, 1.2 * scale).toFixed(2) : "1.2",
          "--fr-t-raw": `${frameT.toFixed(2)}px`,
          "--fr-v-raw": `${frameV.toFixed(2)}px`,
          "--gr-t-raw": `${gridT.toFixed(2)}px`,
          "--gr-v-raw": `${gridV.toFixed(2)}px`,
          "--gr-w": `${gridW.toFixed(2)}px`,
          "--gr-h": `${gridH.toFixed(2)}px`,
          "--scene-w": `${frameSceneW.toFixed(2)}px`,
          "--scene-h": `${frameSceneH.toFixed(2)}px`,
        }}
      >
      <div className={styles.screen}>
        <PageRouter />
        <TopBar />
        <BottomNav />
        <Drawer />
        <SupportPopup />
        <StatusOverlay loading={loading} error={error} onRetry={retry} />
        <Notice />
      </div>
      </div>
      <GameFrame />
      </div>
    </>
  );
}
