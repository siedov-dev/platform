import { useEffect, useRef, useState } from "react";
import { loadHumanAtlas, pickDir, chaikin } from "./humanAtlas";

const STRIDE = 1.0;
const TURN_DEG_PER_MS = 0.22;

export default function CrowdWalkers({ people }) {
  const canvasRef = useRef(null);
  const hostRef = useRef(null);
  const atlasRef = useRef(null);
  const walkersRef = useRef([]);
  const [ready, setReady] = useState(false);

  useEffect(() => {
    let cancelled = false;
    loadHumanAtlas().then((m) => {
      if (cancelled) return;
      atlasRef.current = m;
      setReady(true);
    });
    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    walkersRef.current = people.map((p) => {
      const spath = p.path.length >= 3 ? chaikin(p.path, 2) : p.path;
      const last = spath.length - 1;
      const ph = ((p.phase || 0) % 1) * last;
      return {
        spath,
        w: p.w,
        speed: p.speed,
        seg: Math.min(Math.floor(ph), last - 1),
        t: ph - Math.floor(ph),
        frame: Math.floor((p.phase || 0) * 36) % 36,
        facc: 0,
        facing: null,
      };
    });
  }, [people]);

  useEffect(() => {
    if (!ready) return undefined;
    const canvas = canvasRef.current;
    const host = hostRef.current;
    const ctx = canvas.getContext("2d");
    const map = atlasRef.current;

    let raf = 0;
    const sizeCanvas = () => {
      const dpr = Math.min(window.devicePixelRatio || 1, 2);
      canvas.width = Math.round(host.clientWidth * dpr);
      canvas.height = Math.round(host.clientHeight * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    };
    sizeCanvas();
    const ro = new ResizeObserver(sizeCanvas);
    ro.observe(host);

    let last = performance.now();
    const draws = [];
    const loop = (now) => {
      raf = requestAnimationFrame(loop);
      const W = host.clientWidth;
      const H = host.clientHeight;
      const dt = Math.min(64, now - last);
      last = now;

      ctx.clearRect(0, 0, W, H);
      draws.length = 0;

      for (const wk of walkersRef.current) {
        const path = wk.spath;
        if (!path || path.length < 2) continue;
        const movePx = wk.speed * (dt / 16.7);
        const lastSeg = path.length - 2;

        if (wk.seg > lastSeg) wk.seg = 0;
        const a = path[wk.seg];
        const b = path[wk.seg + 1];
        const segLen = Math.max(0.5, Math.hypot(((b.x - a.x) / 100) * W, ((b.y - a.y) / 100) * H));

        wk.t += movePx / segLen;
        while (wk.t >= 1) {
          wk.t -= 1;
          wk.seg += 1;
          if (wk.seg > lastSeg) wk.seg = 0;
        }

        const a2 = path[wk.seg];
        const b2 = path[wk.seg + 1];
        const px = ((a2.x + (b2.x - a2.x) * wk.t) / 100) * W;
        const py = ((a2.y + (b2.y - a2.y) * wk.t) / 100) * H;
        const ndx = ((b2.x - a2.x) / 100) * W;
        const ndy = ((b2.y - a2.y) / 100) * H;
        const targetAng = ((Math.atan2(ndy, ndx) * 180) / Math.PI + 360) % 360;
        if (wk.facing == null) wk.facing = targetAng;
        const diff = ((((targetAng - wk.facing) % 360) + 540) % 360) - 180;
        const maxStep = TURN_DEG_PER_MS * dt;
        wk.facing = (wk.facing + Math.max(-maxStep, Math.min(maxStep, diff)) + 360) % 360;

        const dir = map[pickDir(wk.facing).name];
        if (!dir) continue;

        const charH = (wk.w / 100) * W;
        const pxPerFrame = Math.max(0.5, (STRIDE * charH) / dir.frames.length);
        wk.facc += movePx;
        if (wk.facc >= pxPerFrame) {
          wk.frame = (wk.frame + Math.floor(wk.facc / pxPerFrame)) % dir.frames.length;
          wk.facc %= pxPerFrame;
        }

        const f = dir.frames[wk.frame % dir.frames.length];
        const dh = charH;
        const dw = dh * (f.w / f.h);
        draws.push({ img: dir.img, f, x: px - dw / 2, y: py - dh, dw, dh, py });
      }

      draws.sort((p, q) => p.py - q.py);
      for (const d of draws) {
        ctx.drawImage(d.img, d.f.x, d.f.y, d.f.w, d.f.h, d.x, d.y, d.dw, d.dh);
      }
    };
    raf = requestAnimationFrame(loop);
    return () => {
      cancelAnimationFrame(raf);
      ro.disconnect();
    };
  }, [ready]);

  return (
    <div ref={hostRef} style={{ position: "absolute", inset: 0, pointerEvents: "none", zIndex: 3 }}>
      <canvas ref={canvasRef} style={{ width: "100%", height: "100%", display: "block" }} />
    </div>
  );
}
