import { useEffect, useRef, useState } from "react";
import styles from "./SparkPath.module.css";

const PAD = 26;
const S = 0.82;
const r2 = (v) => Math.round(v * 100) / 100;

function spline(pts, step = 2) {
  const n = pts.length;
  if (n < 3) return pts.slice();
  const out = [];
  for (let i = 0; i < n; i += 1) {
    const p0 = pts[(i - 1 + n) % n];
    const p1 = pts[i];
    const p2 = pts[(i + 1) % n];
    const p3 = pts[(i + 2) % n];
    const seg = Math.max(2, Math.round(Math.hypot(p2.x - p1.x, p2.y - p1.y) / step));
    for (let j = 0; j < seg; j += 1) {
      const t = j / seg;
      const t2 = t * t;
      const t3 = t2 * t;
      out.push({
        x:
          0.5 *
          (2 * p1.x + (-p0.x + p2.x) * t + (2 * p0.x - 5 * p1.x + 4 * p2.x - p3.x) * t2 + (-p0.x + 3 * p1.x - 3 * p2.x + p3.x) * t3),
        y:
          0.5 *
          (2 * p1.y + (-p0.y + p2.y) * t + (2 * p0.y - 5 * p1.y + 4 * p2.y - p3.y) * t2 + (-p0.y + 3 * p1.y - 3 * p2.y + p3.y) * t3),
      });
    }
  }
  return out;
}

function resample(pts, spacing) {
  if (pts.length < 3) return pts;
  const out = [];
  let prev = pts[0];
  let acc = 0;
  out.push({ x: prev.x, y: prev.y });
  for (let i = 1; i <= pts.length; i += 1) {
    const cur = pts[i % pts.length];
    let d = Math.hypot(cur.x - prev.x, cur.y - prev.y);
    while (acc + d >= spacing) {
      const t = (spacing - acc) / d;
      prev = { x: prev.x + (cur.x - prev.x) * t, y: prev.y + (cur.y - prev.y) * t };
      out.push({ x: prev.x, y: prev.y });
      d = Math.hypot(cur.x - prev.x, cur.y - prev.y);
      acc = 0;
    }
    acc += d;
    prev = cur;
  }
  return out;
}

function nearestSeg(pts, p) {
  let best = 0;
  let bd = Infinity;
  for (let i = 0; i < pts.length; i += 1) {
    const a = pts[i];
    const b = pts[(i + 1) % pts.length];
    const vx = b.x - a.x;
    const vy = b.y - a.y;
    const L = vx * vx + vy * vy || 1;
    let t = ((p.x - a.x) * vx + (p.y - a.y) * vy) / L;
    t = Math.max(0, Math.min(1, t));
    const d = Math.hypot(p.x - (a.x + vx * t), p.y - (a.y + vy * t));
    if (d < bd) {
      bd = d;
      best = i;
    }
  }
  return best;
}

const STRANDS = [
  { seed: 0.0, amp: 3.2, w: 1.5, a: 0.42, bias: 0 },
  { seed: 1.9, amp: 2.4, w: 1.2, a: 0.36, bias: 2.2 },
  { seed: 3.6, amp: 4.2, w: 1.0, a: 0.3, bias: -2.6 },
  { seed: 5.2, amp: 2.0, w: 0.9, a: 0.26, bias: 4.6 },
  { seed: 7.4, amp: 5.0, w: 0.8, a: 0.22, bias: -5 },
];

export default function SparkPath({ pts, power = 1, edit = false, onChange }) {
  const hostRef = useRef(null);
  const canvasRef = useRef(null);
  const [size, setSize] = useState({ w: 0, h: 0 });
  const targetRef = useRef(power);
  targetRef.current = power;

  useEffect(() => {
    const el = hostRef.current;
    const measure = () => {
      const w = el.offsetWidth;
      const h = el.offsetHeight;
      setSize((s) => (Math.abs(s.w - w) < 0.5 && Math.abs(s.h - h) < 0.5 ? s : { w, h }));
    };
    measure();
    const ro = new ResizeObserver(measure);
    ro.observe(el);
    return () => ro.disconnect();
  }, []);

  useEffect(() => {
    if (!size.w || !size.h || pts.length < 3) return undefined;
    const canvas = canvasRef.current;
    const ctx = canvas.getContext("2d");
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    const W = size.w;
    const H = size.h;
    canvas.width = Math.round((W + PAD * 2) * dpr);
    canvas.height = Math.round((H + PAD * 2) * dpr);
    canvas.style.width = `${W + PAD * 2}px`;
    canvas.style.height = `${H + PAD * 2}px`;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    const px = pts.map((p) => ({ x: (p.x / 100) * W, y: (p.y / 100) * H }));
    const path = resample(px, 2);
    const N = path.length;
    const ox = PAD;
    const oy = PAD;
    const at = (i) => path[((Math.round(i) % N) + N) % N];

    const hairs = [];
    let raf = 0;
    let idle = 0;
    const t0 = performance.now();
    let prevTs = t0;
    let pw = targetRef.current;

    const nrm = (i) => {
      const p = at(i);
      const q = at(i + 2);
      const tx = q.x - p.x;
      const ty = q.y - p.y;
      const L = Math.hypot(tx, ty) || 1;
      return { nx: -ty / L, ny: tx / L };
    };

    const wob = (k, t, seed) =>
      Math.sin(k * 0.23 + t * 1.7 + seed) * 0.62 +
      Math.sin(k * 0.077 - t * 1.1 + seed * 2.3) * 0.38 +
      Math.sin(k * 0.51 + t * 2.6 + seed * 3.7) * 0.2;

    const drawRing = (el) => {
      ctx.lineCap = "round";
      ctx.lineJoin = "round";
      const STEP = 2;
      const puls = 0.68 + 0.32 * (0.5 + 0.5 * Math.sin(el * 1.6));

      ctx.globalCompositeOperation = "lighter";
      for (let k = 0; k < N; k += STEP) {
        const p = at(k);
        const q = at(k + STEP);
        const loc = 0.6 + 0.4 * Math.sin(k * 0.055 + el * 1.15);
        const b = puls * loc;
        ctx.beginPath();
        ctx.moveTo(ox + p.x, oy + p.y);
        ctx.lineTo(ox + q.x, oy + q.y);
        ctx.strokeStyle = `rgba(255, 140, 25, ${0.62 * b})`;
        ctx.lineWidth = 12 * S;
        ctx.stroke();
        ctx.strokeStyle = `rgba(255, 205, 90, ${0.82 * b})`;
        ctx.lineWidth = 5.6 * S;
        ctx.stroke();
      }

      STRANDS.forEach((st) => {
        ctx.beginPath();
        for (let k = 0; k <= N; k += 3) {
          const p = at(k);
          const nv = nrm(k);
          const o = wob(k, el, st.seed) * st.amp * S + st.bias * 0.5 * S;
          const x = p.x + nv.nx * o;
          const y = p.y + nv.ny * o;
          if (k === 0) ctx.moveTo(ox + x, oy + y);
          else ctx.lineTo(ox + x, oy + y);
        }
        ctx.strokeStyle = `rgba(255, 244, 190, ${Math.min(1, st.a * 1.75) * puls})`;
        ctx.lineWidth = st.w * S;
        ctx.stroke();
      });

      ctx.globalCompositeOperation = "source-over";
      for (let k = 0; k < N; k += STEP) {
        const p = at(k);
        const q = at(k + STEP);
        const nv = nrm(k);
        const o = wob(k, el, 7.1) * 1.1 * S;
        const loc = 0.6 + 0.4 * Math.sin(k * 0.055 + el * 1.15);
        const g1 = 200 + Math.round(45 * puls * loc);
        ctx.beginPath();
        ctx.moveTo(ox + p.x + nv.nx * o, oy + p.y + nv.ny * o);
        ctx.lineTo(ox + q.x + nv.nx * o, oy + q.y + nv.ny * o);
        ctx.strokeStyle = `rgba(255, ${g1}, 170, ${Math.min(1, 0.8 + 0.4 * puls * loc)})`;
        ctx.lineWidth = 2.6 * S;
        ctx.stroke();
      }
      ctx.globalCompositeOperation = "lighter";
    };

    const spawnHair = () => {
      hairs.push({
        k0: Math.floor(Math.random() * N),
        span: 10 + Math.floor(Math.random() * 22),
        side: Math.random() < 0.5 ? 1 : -1,
        drift: (Math.random() < 0.35 ? 7 + Math.random() * 10 : 2 + Math.random() * 4) * S * 2.2,
        start: (0.5 + Math.random() * 2.4) * S,
        seed: Math.random() * 10,
        life: 1,
        decay: 0.018 + Math.random() * 0.03,
      });
    };

    const drawHair = (hz, el) => {
      const a = Math.pow(hz.life, 1.25);
      const steps = 6;
      ctx.beginPath();
      for (let i = 0; i <= steps; i += 1) {
        const t = i / steps;
        const k = hz.k0 + hz.span * t;
        const p = at(k);
        const nv = nrm(k);
        const off = hz.side * (hz.start + hz.drift * Math.pow(t, 1.4)) + wob(k, el, hz.seed) * 1.1 * S * t;
        const x = p.x + nv.nx * off;
        const y = p.y + nv.ny * off;
        if (i === 0) ctx.moveTo(ox + x, oy + y);
        else ctx.lineTo(ox + x, oy + y);
      }
      ctx.strokeStyle = `rgba(255, 196, 78, ${a * 0.5})`;
      ctx.lineWidth = 1.4 * S;
      ctx.stroke();
      ctx.strokeStyle = `rgba(255, 250, 220, ${a * 0.85})`;
      ctx.lineWidth = 0.7 * S;
      ctx.stroke();
    };

    const loop = (now) => {
      const el = (now - t0) / 1000;
      const dt = Math.min(0.05, (now - prevTs) / 1000);
      prevTs = now;

      const tgt = targetRef.current;
      pw += (tgt - pw) * (1 - Math.pow(0.0002, dt));
      if (pw < 0.004 && tgt <= 0) {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hairs.length = 0;
        idle = setInterval(() => {
          if (targetRef.current > 0) {
            clearInterval(idle);
            idle = 0;
            prevTs = performance.now();
            raf = requestAnimationFrame(loop);
          }
        }, 200);
        return;
      }
      raf = requestAnimationFrame(loop);

      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.globalAlpha = Math.min(1, pw);
      ctx.globalCompositeOperation = "lighter";

      drawRing(el);

      for (let i = hairs.length - 1; i >= 0; i -= 1) {
        const hz = hairs[i];
        hz.life -= hz.decay;
        if (hz.life <= 0) {
          hairs.splice(i, 1);
          continue;
        }
        drawHair(hz, el);
      }

      ctx.globalCompositeOperation = "source-over";
      ctx.globalAlpha = 1;
    };
    raf = requestAnimationFrame(loop);
    return () => {
      cancelAnimationFrame(raf);
      if (idle) clearInterval(idle);
    };
  }, [size.w, size.h, pts]);

  const toPct = (e) => {
    const r = hostRef.current.getBoundingClientRect();
    return { x: ((e.clientX - r.left) / r.width) * 100, y: ((e.clientY - r.top) / r.height) * 100 };
  };

  const grab = (onMove) => {
    const move = (ev) => onMove(toPct(ev));
    const up = () => {
      window.removeEventListener("pointermove", move);
      window.removeEventListener("pointerup", up);
    };
    window.addEventListener("pointermove", move);
    window.addEventListener("pointerup", up);
  };

  const dragPoint = (i) => (e) => {
    e.preventDefault();
    e.stopPropagation();
    grab((p) => onChange((list) => list.map((q, j) => (j === i ? { x: r2(p.x), y: r2(p.y) } : q))));
  };

  const dropPoint = (i) => (e) => {
    e.preventDefault();
    e.stopPropagation();
    onChange((list) => (list.length > 3 ? list.filter((q, j) => j !== i) : list));
  };

  const dragAll = (e) => {
    e.preventDefault();
    let last = toPct(e);
    grab((p) => {
      const dx = p.x - last.x;
      const dy = p.y - last.y;
      last = p;
      onChange((list) => list.map((q) => ({ x: r2(q.x + dx), y: r2(q.y + dy) })));
    });
  };

  const addPoint = (e) => {
    const p = toPct(e);
    onChange((list) => {
      const i = nearestSeg(list, p);
      const next = list.slice();
      next.splice(i + 1, 0, { x: r2(p.x), y: r2(p.y) });
      return next;
    });
  };

  const scaleAll = (e) => {
    const k = e.deltaY < 0 ? 1.03 : 1 / 1.03;
    onChange((list) => {
      const cx = list.reduce((s, p) => s + p.x, 0) / list.length;
      const cy = list.reduce((s, p) => s + p.y, 0) / list.length;
      return list.map((p) => ({ x: r2(cx + (p.x - cx) * k), y: r2(cy + (p.y - cy) * k) }));
    });
  };

  return (
    <div ref={hostRef} className={styles.host}>
      <canvas ref={canvasRef} className={styles.canvas} />
      {edit && (
        <svg
          className={styles.editor}
          viewBox="0 0 100 100"
          preserveAspectRatio="none"
          onPointerDown={dragAll}
          onDoubleClick={addPoint}
          onWheel={scaleAll}
        >
          <polygon
            points={pts.map((p) => `${p.x},${p.y}`).join(" ")}
            fill="rgba(90, 220, 255, 0.08)"
            stroke="rgba(90, 220, 255, 0.9)"
            strokeWidth="0.25"
            vectorEffect="non-scaling-stroke"
          />
        </svg>
      )}
      {edit &&
        pts.map((p, i) => (
          <span
            key={`${i}-${p.x}-${p.y}`}
            className={styles.node}
            style={{ left: `${p.x}%`, top: `${p.y}%` }}
            onPointerDown={dragPoint(i)}
            onContextMenu={dropPoint(i)}
          >
            {i + 1}
          </span>
        ))}
    </div>
  );
}
