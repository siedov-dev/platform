import { useEffect, useRef, useState } from "react";

const BASE = "/bonus-park/Human_9";
const STRIDE = 1.0;
const TURN_DEG_PER_MS = 0.22;
const STAND_FPS = 30;
const MS_PER_STAND = 1000 / STAND_FPS;
const POSE_MIN = 3000;
const POSE_MAX = 15000;
const REPOSE_PROB = 0.5;

const DIRS = [
  { name: "H9_right", angle: 0 },
  { name: "H9_45_right_down", angle: 45 },
  { name: "H9_front", angle: 90 },
  { name: "H9_45_left_down", angle: 135 },
  { name: "H9_left", angle: 180 },
  { name: "H9_45_left_up", angle: 225 },
  { name: "H9_back", angle: 270 },
  { name: "H9_45_right_up", angle: 315 },
];

const POSES = ["H9_front_static", "H9_back_static", "H9_left_static", "H9_right_static"];
const STANDS = ["H9_standing_01", "H9_standing_02", "H9_standing_03", "H9_standing_04", "H9_standing_05", "H9_standing_06"];

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

function buildAdj(nodes, edges) {
  const adj = nodes.map(() => []);
  for (const [a, b] of edges) {
    if (adj[a] && adj[b]) {
      adj[a].push(b);
      adj[b].push(a);
    }
  }
  return adj;
}

function pickNext(cur, prev, adj) {
  const ns = adj[cur];
  if (!ns || ns.length === 0) return cur;
  const opts = ns.length > 1 ? ns.filter((n) => n !== prev) : ns;
  const pool = opts.length ? opts : ns;
  return pool[Math.floor(Math.random() * pool.length)];
}

async function loadAtlas(name) {
  const res = await fetch(`${BASE}/${name}/${name}.json`);
  const json = await res.json();
  const frames = Object.entries(json.frames)
    .sort((a, b) => parseInt(a[0], 10) - parseInt(b[0], 10))
    .map(([, d]) => d.frame);
  const img = new Image();
  img.src = `${BASE}/${name}/${name}.png`;
  await img.decode();
  return { frames, img };
}

async function loadImg(name) {
  const img = new Image();
  img.src = `${BASE}/${name}.png`;
  await img.decode();
  return img;
}

export default function Human9Walker({ nodes, edges, stops = [], w = 6, speed = 0.2 }) {
  const canvasRef = useRef(null);
  const hostRef = useRef(null);
  const dataRef = useRef({});
  const stateRef = useRef({
    mode: "walk",
    cur: 0,
    nxt: 1,
    prev: -1,
    t: 0,
    frame: 0,
    last: 0,
    facc: 0,
    facing: null,
    poseName: null,
    poseUntil: 0,
    standName: null,
    standFrame: 0,
    standAcc: 0,
  });
  const [ready, setReady] = useState(false);

  useEffect(() => {
    let cancelled = false;
    const load = async () => {
      const walk = {};
      const pose = {};
      const stand = {};
      await Promise.all([
        ...DIRS.map(async ({ name }) => {
          walk[name] = await loadAtlas(name);
        }),
        ...POSES.map(async (name) => {
          pose[name] = await loadImg(name);
        }),
        ...STANDS.map(async (name) => {
          stand[name] = await loadAtlas(name);
        }),
      ]);
      if (cancelled) return;
      dataRef.current = { walk, pose, stand };
      setReady(true);
    };
    load();
    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    if (!ready || !nodes || nodes.length < 2 || !edges || edges.length < 1) return undefined;
    const canvas = canvasRef.current;
    const host = hostRef.current;
    const ctx = canvas.getContext("2d");
    const map = dataRef.current;
    const st = stateRef.current;
    const adj = buildAdj(nodes, edges);
    const stopSet = new Set(stops);

    let start = 0;
    for (let i = 0; i < adj.length; i += 1) {
      if (adj[i].length) {
        start = i;
        break;
      }
    }
    st.mode = "walk";
    st.cur = start;
    st.prev = -1;
    st.nxt = pickNext(start, -1, adj);
    st.t = 0;

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

    const startPose = (now) => {
      st.poseName = POSES[Math.floor(Math.random() * POSES.length)];
      st.poseUntil = now + POSE_MIN + Math.random() * (POSE_MAX - POSE_MIN);
      st.mode = "pose";
    };
    const startIdle = () => {
      st.standName = STANDS[Math.floor(Math.random() * STANDS.length)];
      st.standFrame = 0;
      st.standAcc = 0;
      st.mode = "idle";
    };
    const leaveStop = () => {
      st.nxt = pickNext(st.cur, st.prev, adj);
      st.t = 0;
      st.facing = null;
      st.mode = "walk";
    };

    st.last = performance.now();
    const loop = (now) => {
      raf = requestAnimationFrame(loop);
      const W = host.clientWidth;
      const H = host.clientHeight;
      const dt = Math.min(64, now - st.last);
      st.last = now;
      const charH = (w / 100) * W;
      ctx.clearRect(0, 0, W, H);

      if (st.mode === "pose") {
        const node = nodes[st.cur];
        const px = (node.x / 100) * W;
        const py = (node.y / 100) * H;
        const img = map.pose[st.poseName];
        if (img) {
          const dh = charH;
          const dw = dh * (img.naturalWidth / img.naturalHeight);
          ctx.drawImage(img, px - dw / 2, py - dh, dw, dh);
        }
        if (now >= st.poseUntil) startIdle();
        return;
      }

      if (st.mode === "idle") {
        const anim = map.stand[st.standName];
        const node = nodes[st.cur];
        const px = (node.x / 100) * W;
        const py = (node.y / 100) * H;
        if (!anim) {
          leaveStop();
          return;
        }
        st.standAcc += dt;
        while (st.standAcc >= MS_PER_STAND) {
          st.standAcc -= MS_PER_STAND;
          st.standFrame += 1;
        }
        if (st.standFrame >= anim.frames.length) {
          const f = anim.frames[anim.frames.length - 1];
          const dh = charH;
          const dw = dh * (f.w / f.h);
          ctx.drawImage(anim.img, f.x, f.y, f.w, f.h, px - dw / 2, py - dh, dw, dh);
          if (Math.random() < REPOSE_PROB) startPose(now);
          else leaveStop();
          return;
        }
        const f = anim.frames[st.standFrame];
        const dh = charH;
        const dw = dh * (f.w / f.h);
        ctx.drawImage(anim.img, f.x, f.y, f.w, f.h, px - dw / 2, py - dh, dw, dh);
        return;
      }

      const a = nodes[st.cur];
      const b = nodes[st.nxt];
      if (!a || !b) return;
      const movePx = speed * (dt / 16.7);
      const segLen = Math.max(0.5, Math.hypot(((b.x - a.x) / 100) * W, ((b.y - a.y) / 100) * H));

      st.t += movePx / segLen;
      if (st.t >= 1) {
        st.t = 0;
        st.prev = st.cur;
        st.cur = st.nxt;
        if (stopSet.has(st.cur)) {
          startPose(now);
          const node = nodes[st.cur];
          const px = (node.x / 100) * W;
          const py = (node.y / 100) * H;
          const img = map.pose[st.poseName];
          if (img) {
            const dh = charH;
            const dw = dh * (img.naturalWidth / img.naturalHeight);
            ctx.drawImage(img, px - dw / 2, py - dh, dw, dh);
          }
          return;
        }
        st.nxt = pickNext(st.cur, st.prev, adj);
      }

      const c = nodes[st.cur];
      const n = nodes[st.nxt];
      const px = ((c.x + (n.x - c.x) * st.t) / 100) * W;
      const py = ((c.y + (n.y - c.y) * st.t) / 100) * H;
      const ndx = ((n.x - c.x) / 100) * W;
      const ndy = ((n.y - c.y) / 100) * H;
      const targetAng = ((Math.atan2(ndy, ndx) * 180) / Math.PI + 360) % 360;
      if (st.facing == null) st.facing = targetAng;
      const diff = ((((targetAng - st.facing) % 360) + 540) % 360) - 180;
      const maxStep = TURN_DEG_PER_MS * dt;
      st.facing = (st.facing + Math.max(-maxStep, Math.min(maxStep, diff)) + 360) % 360;

      const dir = map.walk[pickDir(st.facing).name];
      if (!dir) return;

      const pxPerFrame = Math.max(0.5, (STRIDE * charH) / dir.frames.length);
      st.facc += movePx;
      if (st.facc >= pxPerFrame) {
        st.frame = (st.frame + Math.floor(st.facc / pxPerFrame)) % dir.frames.length;
        st.facc %= pxPerFrame;
      }

      const f = dir.frames[st.frame % dir.frames.length];
      const dh = charH;
      const dw = dh * (f.w / f.h);
      ctx.drawImage(dir.img, f.x, f.y, f.w, f.h, px - dw / 2, py - dh, dw, dh);
    };
    raf = requestAnimationFrame(loop);
    return () => {
      cancelAnimationFrame(raf);
      ro.disconnect();
    };
  }, [ready, nodes, edges, stops, w, speed]);

  return (
    <div ref={hostRef} style={{ position: "absolute", inset: 0, pointerEvents: "none", zIndex: 3 }}>
      <canvas ref={canvasRef} style={{ width: "100%", height: "100%", display: "block" }} />
    </div>
  );
}
