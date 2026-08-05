import { useEffect, useRef, useState } from "react";

const BASE = "/bonus-park/Human_10";
const STRIDE = 1.0;
const TURN_DEG_PER_MS = 0.22;

const DIRS = [
  { name: "right", angle: 0 },
  { name: "15_right_down", angle: 20 },
  { name: "45_right_down", angle: 55 },
  { name: "front", angle: 90 },
  { name: "45_left_down", angle: 125 },
  { name: "15_left_down", angle: 160 },
  { name: "left", angle: 180 },
  { name: "15_left_up", angle: 200 },
  { name: "45_left_up", angle: 235 },
  { name: "back", angle: 270 },
  { name: "45_right_up", angle: 305 },
  { name: "15_right_up", angle: 340 },
];

function pickDir(deg) {
  let best = DIRS[0];
  let bestD = 999;
  for (const d of DIRS) {
    const diff = Math.abs(((deg - d.angle + 540) % 360) - 180);
    if (diff < bestD) {
      bestD = diff;
      best = d;
    }
  }
  return best;
}

function chaikin(pts, iterations) {
  let cur = pts;
  for (let it = 0; it < iterations; it += 1) {
    const out = [cur[0]];
    for (let i = 0; i < cur.length - 1; i += 1) {
      const p = cur[i];
      const q = cur[i + 1];
      out.push({ x: p.x * 0.75 + q.x * 0.25, y: p.y * 0.75 + q.y * 0.25 });
      out.push({ x: p.x * 0.25 + q.x * 0.75, y: p.y * 0.25 + q.y * 0.75 });
    }
    out.push(cur[cur.length - 1]);
    cur = out;
  }
  return cur;
}

export default function HumanWalker({ path, w = 6, speed = 0.2 }) {
  const canvasRef = useRef(null);
  const hostRef = useRef(null);
  const dataRef = useRef({});
  const stateRef = useRef({ seg: 0, t: 0, frame: 0, last: 0, facc: 0, facing: null });
  const [ready, setReady] = useState(false);

  useEffect(() => {
    let cancelled = false;
    const load = async () => {
      const map = {};
      await Promise.all(
        DIRS.map(async ({ name }) => {
          const res = await fetch(`${BASE}/${name}/${name}.json`);
          const json = await res.json();
          const frames = Object.entries(json.frames)
            .sort((a, b) => parseInt(a[0], 10) - parseInt(b[0], 10))
            .map(([, d]) => d.frame);
          const img = new Image();
          img.src = `${BASE}/${name}/${name}.png`;
          await img.decode();
          map[name] = { frames, img };
        })
      );
      if (cancelled) return;
      dataRef.current = map;
      setReady(true);
    };
    load();
    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    if (!ready || !path || path.length < 2) return undefined;
    const canvas = canvasRef.current;
    const host = hostRef.current;
    const ctx = canvas.getContext("2d");
    const map = dataRef.current;
    const st = stateRef.current;
    const spath = path.length >= 3 ? chaikin(path, 2) : path;
    st.seg = Math.min(st.seg, spath.length - 2);

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

    st.last = performance.now();
    const loop = (now) => {
      raf = requestAnimationFrame(loop);
      const W = host.clientWidth;
      const H = host.clientHeight;
      const dt = Math.min(64, now - st.last);
      st.last = now;

      const movePx = speed * (dt / 16.7);
      const lastSeg = spath.length - 2;

      if (st.seg > lastSeg) st.seg = 0;
      const a = spath[st.seg];
      const b = spath[st.seg + 1];
      const segLen = Math.max(0.5, Math.hypot(((b.x - a.x) / 100) * W, ((b.y - a.y) / 100) * H));

      st.t += movePx / segLen;
      while (st.t >= 1) {
        st.t -= 1;
        st.seg += 1;
        if (st.seg > lastSeg) st.seg = 0;
      }

      const a2 = spath[st.seg];
      const b2 = spath[st.seg + 1];
      const px = ((a2.x + (b2.x - a2.x) * st.t) / 100) * W;
      const py = ((a2.y + (b2.y - a2.y) * st.t) / 100) * H;
      const ndx = ((b2.x - a2.x) / 100) * W;
      const ndy = ((b2.y - a2.y) / 100) * H;
      const targetAng = ((Math.atan2(ndy, ndx) * 180) / Math.PI + 360) % 360;
      if (st.facing == null) st.facing = targetAng;
      const diff = ((((targetAng - st.facing) % 360) + 540) % 360) - 180;
      const maxStep = TURN_DEG_PER_MS * dt;
      st.facing = (st.facing + Math.max(-maxStep, Math.min(maxStep, diff)) + 360) % 360;

      const dir = map[pickDir(st.facing).name];
      if (!dir) return;

      const charH = (w / 100) * W;
      const pxPerFrame = Math.max(0.5, (STRIDE * charH) / dir.frames.length);
      st.facc += movePx;
      if (st.facc >= pxPerFrame) {
        st.frame = (st.frame + Math.floor(st.facc / pxPerFrame)) % dir.frames.length;
        st.facc %= pxPerFrame;
      }

      const f = dir.frames[st.frame % dir.frames.length];
      const dh = charH;
      const dw = dh * (f.w / f.h);
      ctx.clearRect(0, 0, W, H);
      ctx.drawImage(dir.img, f.x, f.y, f.w, f.h, px - dw / 2, py - dh, dw, dh);
    };
    raf = requestAnimationFrame(loop);
    return () => {
      cancelAnimationFrame(raf);
      ro.disconnect();
    };
  }, [ready, path, w, speed]);

  return (
    <div ref={hostRef} style={{ position: "absolute", inset: 0, pointerEvents: "none", zIndex: 3 }}>
      <canvas ref={canvasRef} style={{ width: "100%", height: "100%", display: "block" }} />
    </div>
  );
}
