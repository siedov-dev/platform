import { useEffect, useRef } from "react";
import { Application, Assets, PerspectiveMesh } from "pixi.js";
import styles from "./BonusParkPage.module.css";
import { PARK_ASSET } from "./parkAssetBase";

const ASSET = PARK_ASSET;
const COLS = 10;
const ROWS = 10;

function lerp(a, b, t) {
  return { x: a.x + (b.x - a.x) * t, y: a.y + (b.y - a.y) * t };
}

function bilerp(c, u, v) {
  const top = lerp(c.tl, c.tr, u);
  const bot = lerp(c.bl, c.br, u);
  return lerp(top, bot, v);
}

function tileFor(r, c) {
  return ((r * 131 + c * 197 + r * c * 59) % 10) + 1;
}

function expand(center, p, f) {
  return { x: center.x + (p.x - center.x) * f, y: center.y + (p.y - center.y) * f };
}

export default function PixiField({ corners, overlap }) {
  const hostRef = useRef(null);
  const appRef = useRef(null);
  const texturesRef = useRef(null);
  const buildRef = useRef(null);
  const cornersRef = useRef(corners);
  const overlapRef = useRef(overlap);
  cornersRef.current = corners;
  overlapRef.current = overlap;

  useEffect(() => {
    const host = hostRef.current;
    let destroyed = false;
    let observer = null;

    const build = () => {
      const app = appRef.current;
      const textures = texturesRef.current;
      if (!app || !textures) return;
      app.stage.removeChildren();
      const W = app.screen.width;
      const H = app.screen.height;
      const f = overlapRef.current;
      const c = cornersRef.current;
      const frac = {
        tl: { x: c.tl.x / 100, y: c.tl.y / 100 },
        tr: { x: c.tr.x / 100, y: c.tr.y / 100 },
        bl: { x: c.bl.x / 100, y: c.bl.y / 100 },
        br: { x: c.br.x / 100, y: c.br.y / 100 },
      };
      for (let r = ROWS - 1; r >= 0; r--) {
        for (let col = 0; col < COLS; col++) {
          const u0 = col / COLS;
          const u1 = (col + 1) / COLS;
          const v0 = r / ROWS;
          const v1 = (r + 1) / ROWS;
          const p00 = bilerp(frac, u0, v0);
          const p10 = bilerp(frac, u1, v0);
          const p11 = bilerp(frac, u1, v1);
          const p01 = bilerp(frac, u0, v1);
          const ctr = {
            x: (p00.x + p10.x + p11.x + p01.x) / 4,
            y: (p00.y + p10.y + p11.y + p01.y) / 4,
          };
          const e00 = expand(ctr, p00, f);
          const e10 = expand(ctr, p10, f);
          const e11 = expand(ctr, p11, f);
          const e01 = expand(ctr, p01, f);
          const mesh = new PerspectiveMesh({
            texture: textures[tileFor(r, col) - 1],
            verticesX: 3,
            verticesY: 3,
            x0: e00.x * W,
            y0: e00.y * H,
            x1: e10.x * W,
            y1: e10.y * H,
            x2: e11.x * W,
            y2: e11.y * H,
            x3: e01.x * W,
            y3: e01.y * H,
          });
          app.stage.addChild(mesh);
        }
      }
    };
    buildRef.current = build;

    const run = async () => {
      const app = new Application();
      await app.init({
        resizeTo: host,
        backgroundAlpha: 0,
        antialias: true,
        resolution: window.devicePixelRatio || 1,
        autoDensity: true,
      });
      if (destroyed) {
        app.destroy(true);
        return;
      }
      appRef.current = app;
      host.appendChild(app.canvas);

      const textures = [];
      for (let i = 1; i <= 10; i++) {
        const tex = await Assets.load(`${ASSET}/Tile_${i}.png`);
        tex.source.autoGenerateMipmaps = true;
        textures.push(tex);
      }
      if (destroyed) {
        app.destroy(true);
        return;
      }
      texturesRef.current = textures;
      build();
      observer = new ResizeObserver(() => build());
      observer.observe(host);
    };
    run();

    return () => {
      destroyed = true;
      buildRef.current = null;
      if (observer) observer.disconnect();
      if (appRef.current) {
        appRef.current.destroy(true, { children: true });
        appRef.current = null;
      }
      texturesRef.current = null;
    };
  }, []);

  useEffect(() => {
    if (buildRef.current) buildRef.current();
  }, [corners, overlap]);

  return <div ref={hostRef} className={styles.pixiHost} />;
}
