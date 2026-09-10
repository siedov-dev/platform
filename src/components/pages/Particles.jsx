import { forwardRef, useImperativeHandle, useLayoutEffect, useRef } from "react";
import styles from "./BonusParkPage.module.css";

const CONFETTI_COLORS = ["#ff4d6d", "#4dd2ff", "#ffd93d", "#7bed6f", "#c56bff", "#ff9f43", "#fff0f0"];

const rnd = (a, b) => a + Math.random() * (b - a);
const pick = (arr) => arr[(Math.random() * arr.length) | 0];

const Particles = forwardRef(function Particles(_, ref) {
  const canvasRef = useRef(null);
  const itemsRef = useRef([]);
  const rafRef = useRef(0);
  const prevTsRef = useRef(0);

  useLayoutEffect(() => {
    const canvas = canvasRef.current;
    const resize = () => {
      const dpr = Math.min(window.devicePixelRatio || 1, 2);
      canvas.width = Math.round(canvas.clientWidth * dpr);
      canvas.height = Math.round(canvas.clientHeight * dpr);
      canvas.getContext("2d").setTransform(dpr, 0, 0, dpr, 0, 0);
    };
    const ro = new ResizeObserver(resize);
    ro.observe(canvas);
    resize();
    return () => {
      ro.disconnect();
      cancelAnimationFrame(rafRef.current);
    };
  }, []);

  const tick = (now) => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext("2d");
    const items = itemsRef.current;
    const k = prevTsRef.current ? Math.min(10, (now - prevTsRef.current) / (1000 / 60)) : 1;
    prevTsRef.current = now;

    ctx.clearRect(0, 0, canvas.clientWidth, canvas.clientHeight);

    for (let i = items.length - 1; i >= 0; i -= 1) {
      const p = items[i];
      p.life -= p.decay * k;
      p.t += k;

      if (p.life <= 0) {
        items.splice(i, 1);
        continue;
      }

      if (p.type === "flash") {
        const r = p.r0 * (1 + (1 - p.life) * 1.2);
        const g = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, r);
        g.addColorStop(0, `rgba(255,247,220,${p.life * 0.8})`);
        g.addColorStop(1, "rgba(255,247,220,0)");
        ctx.fillStyle = g;
        ctx.beginPath();
        ctx.arc(p.x, p.y, r, 0, Math.PI * 2);
        ctx.fill();
        continue;
      }

      if (p.type === "orb") {
        if (!p.start) p.start = now;
        p.p = (now - p.start) / p.dur;
        if (p.p >= 1) {
          if (p.onArrive) p.onArrive();
          items.splice(i, 1);
          continue;
        }
        const u = p.p;
        const inv = 1 - u;
        p.x = inv * inv * p.x0 + 2 * inv * u * p.cx + u * u * p.x1;
        p.y = inv * inv * p.y0 + 2 * inv * u * p.cy + u * u * p.y1;

        if ((p.t | 0) % 2 === 0) {
          items.push({
            type: "trail",
            x: p.x + rnd(-2, 2),
            y: p.y + rnd(-2, 2),
            s: p.s * rnd(0.3, 0.6),
            life: 1,
            decay: 0.07,
            t: 0,
            tone: p.tone,
          });
        }

        const r = p.s * 2.4;
        const g = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, r);
        if (p.tone === "purple") {
          g.addColorStop(0, "rgba(255,246,255,1)");
          g.addColorStop(0.22, "rgba(228,158,255,0.95)");
          g.addColorStop(0.55, "rgba(182,78,242,0.45)");
          g.addColorStop(1, "rgba(158,52,232,0)");
        } else {
          g.addColorStop(0, "rgba(255,255,236,1)");
          g.addColorStop(0.22, "rgba(255,233,138,0.95)");
          g.addColorStop(0.55, "rgba(255,198,62,0.45)");
          g.addColorStop(1, "rgba(255,190,50,0)");
        }
        ctx.fillStyle = g;
        ctx.beginPath();
        ctx.arc(p.x, p.y, r, 0, Math.PI * 2);
        ctx.fill();
        continue;
      }

      if (p.type === "trail") {
        const r = p.s * p.life * 2;
        ctx.globalAlpha = p.life * 0.8;
        const g = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, r);
        if (p.tone === "purple") {
          g.addColorStop(0, "rgba(243,206,255,0.9)");
          g.addColorStop(1, "rgba(196,96,246,0)");
        } else {
          g.addColorStop(0, "rgba(255,242,186,0.9)");
          g.addColorStop(1, "rgba(255,210,90,0)");
        }
        ctx.fillStyle = g;
        ctx.beginPath();
        ctx.arc(p.x, p.y, r, 0, Math.PI * 2);
        ctx.fill();
        ctx.globalAlpha = 1;
        continue;
      }

      p.vy += p.g * k;
      p.x += (p.vx + Math.sin(p.t * 0.12) * 0.5) * k;
      p.y += p.vy * k;
      p.rot += p.vr * k;

      const flip = Math.abs(Math.cos(p.t * 0.22));
      ctx.globalAlpha = Math.min(1, p.life * 1.6);
      ctx.save();
      ctx.translate(p.x, p.y);
      ctx.rotate(p.rot);
      ctx.fillStyle = p.color;
      ctx.fillRect(-p.s / 2, (-p.s * 0.55) / 2, p.s * flip + 0.6, p.s * 0.55);
      ctx.restore();
      ctx.globalAlpha = 1;
    }

    if (items.length > 0) {
      rafRef.current = requestAnimationFrame(tick);
    } else {
      rafRef.current = 0;
    }
  };

  const start = () => {
    if (!rafRef.current) {
      prevTsRef.current = 0;
      rafRef.current = requestAnimationFrame(tick);
    }
  };

  useImperativeHandle(ref, () => ({
    clearFlights() {
      const items = itemsRef.current;
      for (let i = items.length - 1; i >= 0; i -= 1) {
        const p = items[i];
        if (p.type !== "orb") continue;
        items.splice(i, 1);
        if (p.onArrive) p.onArrive();
      }
    },

    flash(clientX, clientY, size) {
      const canvas = canvasRef.current;
      if (!canvas) return;
      const rect = canvas.getBoundingClientRect();
      const sx = rect.width ? canvas.clientWidth / rect.width : 1;
      const sy = rect.height ? canvas.clientHeight / rect.height : 1;
      itemsRef.current.push({
        type: "flash",
        x: (clientX - rect.left) * sx,
        y: (clientY - rect.top) * sy,
        t: 0,
        rot: 0,
        vr: 0,
        life: 1,
        r0: Math.max(12, size * sx),
        decay: 0.07,
      });
      start();
    },

    flyEnergy(fx, fy, tx, ty, onArrive, tone) {
      const canvas = canvasRef.current;
      if (!canvas) return;
      const rect = canvas.getBoundingClientRect();
      const sx = rect.width ? canvas.clientWidth / rect.width : 1;
      const sy = rect.height ? canvas.clientHeight / rect.height : 1;
      const x0 = (fx - rect.left) * sx;
      const y0 = (fy - rect.top) * sy;
      const x1 = (tx - rect.left) * sx;
      const y1 = (ty - rect.top) * sy;

      itemsRef.current.push({
        type: "orb",
        x: x0,
        y: y0,
        x0,
        y0,
        x1,
        y1,
        cx: (x0 + x1) / 2 + (y1 - y0) * 0.14,
        cy: (y0 + y1) / 2 - Math.abs(x1 - x0) * 0.28 - 25,
        p: 0,
        start: 0,
        dur: 1500,
        s: 6,
        life: 1,
        decay: 0,
        t: 0,
        tone,
        onArrive,
      });

      start();
    },

    burst(clientX, clientY, size) {
      const canvas = canvasRef.current;
      if (!canvas) return;
      const rect = canvas.getBoundingClientRect();
      const sx = rect.width ? canvas.clientWidth / rect.width : 1;
      const sy = rect.height ? canvas.clientHeight / rect.height : 1;
      const x = (clientX - rect.left) * sx;
      const y = (clientY - rect.top) * sy;
      const items = itemsRef.current;
      const s = Math.max(8, size * sx);

      items.push({ x, y, t: 0, rot: 0, vr: 0, life: 1, type: "flash", r0: s * 0.5, decay: 0.09 });

      for (let i = 0; i < 30; i += 1) {
        const a = rnd(0, Math.PI * 2);
        const sp = rnd(1.1, 3.8);
        items.push({
          x,
          y,
          t: 0,
          life: 1,
          type: "confetti",
          vx: Math.cos(a) * sp,
          vy: Math.sin(a) * sp - rnd(1, 2.4),
          g: 0.075,
          s: s * rnd(0.1, 0.22),
          vr: rnd(-0.26, 0.26),
          rot: rnd(0, Math.PI * 2),
          decay: rnd(0.006, 0.011),
          color: pick(CONFETTI_COLORS),
        });
      }

      start();
    },
  }));

  return <canvas ref={canvasRef} className={styles.particles} />;
});

export default Particles;
