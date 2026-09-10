import { useEffect, useRef, useState } from "react";
import styles from "./SparkFrame.module.css";

const POS_KEY = "bonuspark.sparkframe";

function buildPath(w, h, r, step = 2) {
  const pts = [];
  const push = (x, y) => pts.push({ x, y });
  const arc = (cx, cy, a0, a1) => {
    const n = Math.max(4, Math.round((Math.abs(a1 - a0) * r) / step));
    for (let i = 0; i <= n; i += 1) {
      const a = a0 + (a1 - a0) * (i / n);
      push(cx + Math.cos(a) * r, cy + Math.sin(a) * r);
    }
  };
  const line = (x0, y0, x1, y1) => {
    const n = Math.max(2, Math.round(Math.hypot(x1 - x0, y1 - y0) / step));
    for (let i = 1; i <= n; i += 1) push(x0 + (x1 - x0) * (i / n), y0 + (y1 - y0) * (i / n));
  };
  push(r, 0);
  line(r, 0, w - r, 0);
  arc(w - r, r, -Math.PI / 2, 0);
  line(w, r, w, h - r);
  arc(w - r, h - r, 0, Math.PI / 2);
  line(w - r, h, r, h);
  arc(r, h - r, Math.PI / 2, Math.PI);
  line(0, h - r, 0, r);
  arc(r, r, Math.PI, Math.PI * 1.5);
  return pts;
}

export default function SparkFrame({ x, y, w, h, dur = 5, draggable = false, children }) {
  const hostRef = useRef(null);
  const canvasRef = useRef(null);
  const [size, setSize] = useState({ w: 0, h: 0 });
  const [pos, setPos] = useState(() => {
    try {
      const raw = localStorage.getItem(POS_KEY);
      if (raw) return JSON.parse(raw);
    } catch {
      return { x, y };
    }
    return { x, y };
  });

  useEffect(() => {
    const el = hostRef.current;
    const measure = () => {
      const b = el.getBoundingClientRect();
      setSize((s) =>
        Math.abs(s.w - b.width) < 0.5 && Math.abs(s.h - b.height) < 0.5 ? s : { w: b.width, h: b.height }
      );
    };
    measure();
    const ro = new ResizeObserver(measure);
    ro.observe(el);
    return () => ro.disconnect();
  }, []);

  useEffect(() => {
    if (!size.w || !size.h) return undefined;
    const canvas = canvasRef.current;
    const ctx = canvas.getContext("2d");
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    const pad = 26;
    const W = size.w;
    const H = size.h;
    canvas.width = Math.round((W + pad * 2) * dpr);
    canvas.height = Math.round((H + pad * 2) * dpr);
    canvas.style.width = `${W + pad * 2}px`;
    canvas.style.height = `${H + pad * 2}px`;
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

    const rr = Math.min(16, H * 0.3);
    const pts = buildPath(W, H, rr);
    const N = pts.length;
    const TAIL = Math.floor(N / 2);
    const ox = pad;
    const oy = pad;
    const at = (i) => pts[((i % N) + N) % N];

    const hairs = [];
    let raf = 0;
    const t0 = performance.now();

    const nrm = (i) => {
      const p = at(i);
      const q = at(i + 2);
      const tx = q.x - p.x;
      const ty = q.y - p.y;
      const L = Math.hypot(tx, ty) || 1;
      return { nx: -ty / L, ny: tx / L, tx: tx / L, ty: ty / L };
    };

    const wob = (k, t, seed) =>
      Math.sin(k * 0.23 + t * 5.5 + seed) * 0.62 +
      Math.sin(k * 0.077 - t * 3.1 + seed * 2.3) * 0.38 +
      Math.sin(k * 0.51 + t * 9.4 + seed * 3.7) * 0.2;

    const STRANDS = [
      { seed: 0.0, amp: 3.2, w: 1.8, a: 0.5, bias: 0 },
      { seed: 1.9, amp: 2.4, w: 1.5, a: 0.44, bias: 2.4 },
      { seed: 3.6, amp: 4.2, w: 1.3, a: 0.36, bias: -2.8 },
      { seed: 5.2, amp: 2.0, w: 1.1, a: 0.32, bias: 5.2 },
      { seed: 7.4, amp: 5.0, w: 1.0, a: 0.26, bias: -5.6 },
      { seed: 9.1, amp: 3.0, w: 0.9, a: 0.22, bias: 8.4 },
      { seed: 11.3, amp: 4.4, w: 0.9, a: 0.2, bias: -8.8 },
    ];

    const drawTail = (headIdx, el) => {
      ctx.lineCap = "round";
      ctx.lineJoin = "round";
      const STEP = 2;

      ctx.globalCompositeOperation = "lighter";
      for (let k = TAIL; k >= 0; k -= STEP) {
        const f = 1 - k / TAIL;
        const p = at(headIdx - k);
        const q = at(headIdx - k + STEP);
        const bright = 0.1 + 0.9 * Math.pow(f, 3.2);
        ctx.beginPath();
        ctx.moveTo(ox + p.x, oy + p.y);
        ctx.lineTo(ox + q.x, oy + q.y);
        ctx.strokeStyle = `rgba(255, 128, 16, ${0.12 + 0.46 * bright})`;
        ctx.lineWidth = 9.5 + 3.5 * f;
        ctx.stroke();
        ctx.strokeStyle = `rgba(255, 186, 60, ${0.13 + 0.5 * bright})`;
        ctx.lineWidth = 4.8 + 1.8 * f;
        ctx.stroke();
      }

      STRANDS.forEach((st) => {
        ctx.beginPath();
        for (let k = TAIL; k >= 0; k -= 3) {
          const f = 1 - k / TAIL;
          const p = at(headIdx - k);
          const nv = nrm(headIdx - k);
          const amp = st.amp * 0.72 * (0.6 + 0.4 * f);
          const o = wob(k, el, st.seed) * amp + st.bias * 0.36 * (0.65 + 0.35 * f);
          const px = p.x + nv.nx * o;
          const py = p.y + nv.ny * o;
          if (k === TAIL) ctx.moveTo(ox + px, oy + py);
          else ctx.lineTo(ox + px, oy + py);
        }
        ctx.strokeStyle = `rgba(255, 232, 150, ${st.a * 0.85})`;
        ctx.lineWidth = st.w;
        ctx.stroke();
      });

      ctx.globalCompositeOperation = "source-over";
      for (let k = TAIL; k >= 0; k -= STEP) {
        const f = 1 - k / TAIL;
        const bright = 0.1 + 0.9 * Math.pow(f, 3.2);
        const p = at(headIdx - k);
        const q = at(headIdx - k + STEP);
        const nv = nrm(headIdx - k);
        const o = wob(k, el, 7.1) * 1.3 * (0.6 + 0.4 * f);
        const g1 = 150 + Math.round(105 * Math.pow(bright, 0.8));
        const b1 = 40 + Math.round(200 * Math.pow(bright, 2));
        ctx.beginPath();
        ctx.moveTo(ox + p.x + nv.nx * o, oy + p.y + nv.ny * o);
        ctx.lineTo(ox + q.x + nv.nx * o, oy + q.y + nv.ny * o);
        ctx.strokeStyle = `rgba(255, ${g1}, ${b1}, ${0.42 + 0.58 * bright})`;
        ctx.lineWidth = 2 + 0.9 * f;
        ctx.stroke();
      }
      ctx.globalCompositeOperation = "lighter";

      const hp = at(headIdx);
      const flare = 13 + 2 * Math.sin(el * 18);
      const g = ctx.createRadialGradient(ox + hp.x, oy + hp.y, 0, ox + hp.x, oy + hp.y, flare);
      g.addColorStop(0, "rgba(255, 255, 255, 1)");
      g.addColorStop(0.12, "rgba(255, 252, 226, 0.9)");
      g.addColorStop(0.32, "rgba(255, 226, 130, 0.5)");
      g.addColorStop(1, "rgba(255, 158, 30, 0)");
      ctx.fillStyle = g;
      ctx.beginPath();
      ctx.arc(ox + hp.x, oy + hp.y, flare, 0, Math.PI * 2);
      ctx.fill();

      const puls = 0.82 + 0.18 * Math.sin(el * 7.3);
      const rays = [
        { a: 0, len: 30, w: 2.6, o: 0.95 },
        { a: Math.PI, len: 30, w: 2.6, o: 0.95 },
        { a: -Math.PI / 2, len: 17, w: 2.1, o: 0.8 },
        { a: Math.PI / 2, len: 17, w: 2.1, o: 0.8 },
        { a: Math.PI / 4, len: 11, w: 1.2, o: 0.5 },
        { a: -Math.PI / 4, len: 11, w: 1.2, o: 0.5 },
        { a: (Math.PI * 3) / 4, len: 11, w: 1.2, o: 0.5 },
        { a: (-Math.PI * 3) / 4, len: 11, w: 1.2, o: 0.5 },
      ];
      rays.forEach((r) => {
        const ex = hp.x + Math.cos(r.a) * r.len * puls;
        const ey = hp.y + Math.sin(r.a) * r.len * puls;
        const lg = ctx.createLinearGradient(ox + hp.x, oy + hp.y, ox + ex, oy + ey);
        lg.addColorStop(0, `rgba(255, 255, 246, ${r.o})`);
        lg.addColorStop(0.35, `rgba(255, 234, 150, ${r.o * 0.4})`);
        lg.addColorStop(1, "rgba(255, 176, 40, 0)");
        ctx.strokeStyle = lg;
        ctx.lineWidth = r.w;
        ctx.beginPath();
        ctx.moveTo(ox + hp.x, oy + hp.y);
        ctx.lineTo(ox + ex, oy + ey);
        ctx.stroke();
      });

      const ring = ctx.createRadialGradient(ox + hp.x, oy + hp.y, 1, ox + hp.x, oy + hp.y, 8 * puls);
      ring.addColorStop(0, "rgba(255, 255, 255, 0.95)");
      ring.addColorStop(0.6, "rgba(255, 244, 190, 0.45)");
      ring.addColorStop(1, "rgba(255, 200, 80, 0)");
      ctx.fillStyle = ring;
      ctx.beginPath();
      ctx.arc(ox + hp.x, oy + hp.y, 8 * puls, 0, Math.PI * 2);
      ctx.fill();

      ctx.globalCompositeOperation = "source-over";
      ctx.beginPath();
      ctx.arc(ox + hp.x, oy + hp.y, 2, 0, Math.PI * 2);
      ctx.fillStyle = "rgba(255, 255, 255, 1)";
      ctx.fill();
      ctx.globalCompositeOperation = "lighter";
    };

    const spawnHair = (headIdx) => {
      const k0 = Math.floor(Math.random() * TAIL * 0.45);
      hairs.push({
        k0,
        span: 16 + Math.floor(Math.random() * 34),
        side: Math.random() < 0.5 ? 1 : -1,
        drift: Math.random() < 0.35 ? 7 + Math.random() * 11 : 2 + Math.random() * 4.5,
        start: 0.6 + Math.random() * 3.4,
        seed: Math.random() * 10,
        life: 1,
        decay: 0.03 + Math.random() * 0.045,
      });
    };

    const drawHair = (hz, headIdx, el) => {
      const a = Math.pow(hz.life, 1.25);
      const steps = 6;
      ctx.beginPath();
      for (let i = 0; i <= steps; i += 1) {
        const t = i / steps;
        const k = hz.k0 + hz.span * t;
        const p = at(headIdx - k);
        const nv = nrm(headIdx - k);
        const off =
          hz.side * (hz.start + hz.drift * Math.pow(t, 1.4)) +
          wob(k, el, hz.seed) * 1.3 * t;
        const px = p.x + nv.nx * off;
        const py = p.y + nv.ny * off;
        if (i === 0) ctx.moveTo(ox + px, oy + py);
        else ctx.lineTo(ox + px, oy + py);
      }
      ctx.strokeStyle = `rgba(255, 196, 78, ${a * 0.55})`;
      ctx.lineWidth = 1.4;
      ctx.stroke();
      ctx.strokeStyle = `rgba(255, 250, 220, ${a * 0.9})`;
      ctx.lineWidth = 0.65;
      ctx.stroke();
    };

    const drawArcs = (headIdx, el) => {
      const hp = at(headIdx);
      const n = 3;
      for (let s = 0; s < n; s += 1) {
        const seed = s * 2.4 + Math.floor(el * 26) * 1.7;
        const ang = seed * 2.399;
        const len = 5 + ((Math.sin(seed * 3.1) + 1) / 2) * 9;
        const segs = 4;
        ctx.beginPath();
        ctx.moveTo(ox + hp.x, oy + hp.y);
        let cx = hp.x;
        let cy = hp.y;
        for (let i = 1; i <= segs; i += 1) {
          const t = i / segs;
          const wig = Math.sin(seed * 5 + i * 2.7) * 3.2 * t;
          cx = hp.x + Math.cos(ang) * len * t + Math.cos(ang + 1.57) * wig;
          cy = hp.y + Math.sin(ang) * len * t + Math.sin(ang + 1.57) * wig;
          ctx.lineTo(ox + cx, oy + cy);
        }
        const a = 0.35 + 0.4 * ((Math.sin(seed * 7.7) + 1) / 2);
        ctx.strokeStyle = `rgba(255, 216, 120, ${a * 0.6})`;
        ctx.lineWidth = 1.3;
        ctx.stroke();
        ctx.strokeStyle = `rgba(255, 253, 240, ${a})`;
        ctx.lineWidth = 0.6;
        ctx.stroke();
      }
    };

    const loop = (now) => {
      raf = requestAnimationFrame(loop);
      const el = (now - t0) / 1000;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.globalCompositeOperation = "lighter";

      const base = (el / dur) % 1;
      const heads = [Math.floor(base * N), Math.floor(((base + 0.5) % 1) * N)];

      heads.forEach((hi) => drawTail(hi, el));

      heads.forEach((hi, idx) => {
        if (Math.random() < 0.5) {
          spawnHair(hi);
          hairs[hairs.length - 1].hi = idx;
        }
        drawArcs(hi, el + idx * 0.37);
      });

      for (let i = hairs.length - 1; i >= 0; i -= 1) {
        const hz = hairs[i];
        hz.life -= hz.decay;
        if (hz.life <= 0) {
          hairs.splice(i, 1);
          continue;
        }
        drawHair(hz, heads[hz.hi || 0], el);
      }

      ctx.globalCompositeOperation = "source-over";
    };
    raf = requestAnimationFrame(loop);
    return () => cancelAnimationFrame(raf);
  }, [size.w, size.h, dur]);

  const startDrag = (e) => {
    if (!draggable) return;
    e.preventDefault();
    const parent = hostRef.current.parentElement.getBoundingClientRect();
    let lastX = e.clientX;
    let lastY = e.clientY;
    const move = (ev) => {
      const dx = ((ev.clientX - lastX) / parent.width) * 100;
      const dy = ((ev.clientY - lastY) / parent.height) * 100;
      lastX = ev.clientX;
      lastY = ev.clientY;
      setPos((p) => {
        const n = { x: Math.round((p.x + dx) * 100) / 100, y: Math.round((p.y + dy) * 100) / 100 };
        localStorage.setItem(POS_KEY, JSON.stringify(n));
        return n;
      });
    };
    const up = () => {
      window.removeEventListener("pointermove", move);
      window.removeEventListener("pointerup", up);
    };
    window.addEventListener("pointermove", move);
    window.addEventListener("pointerup", up);
  };

  return (
    <div
      ref={hostRef}
      className={styles.host}
      onPointerDown={startDrag}
      style={{
        left: `${pos.x}%`,
        top: `${pos.y}%`,
        width: `${w}%`,
        height: `${h}%`,
        pointerEvents: draggable ? "auto" : "none",
        cursor: draggable ? "move" : undefined,
      }}
    >
      <canvas ref={canvasRef} className={styles.canvas} />
      {children}
    </div>
  );
}
