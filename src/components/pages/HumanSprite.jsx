import { useEffect, useRef, useState } from "react";

const BASE = "/bonus-park/human_01";
const ATLAS = "human_01";
const ATLASES = 3;

export default function HumanSprite({ className, style, onPointerDown, frame = 0, animate = false, from = 0, to = 208, fps = 15 }) {
  const canvasRef = useRef(null);
  const dataRef = useRef({ frames: [] });
  const [ready, setReady] = useState(false);

  useEffect(() => {
    let cancelled = false;

    const load = async () => {
      const collected = [];
      const images = new Array(ATLASES);

      await Promise.all(
        Array.from({ length: ATLASES }, async (_, i) => {
          const res = await fetch(`${BASE}/${ATLAS}-${i}.json`);
          const json = await res.json();
          Object.entries(json.frames).forEach(([name, data]) => {
            collected.push({ n: parseInt(name, 10), atlas: i, ...data.frame });
          });
          const img = new Image();
          img.src = `${BASE}/${ATLAS}-${i}.png`;
          await img.decode();
          images[i] = img;
        })
      );

      if (cancelled) return;
      collected.sort((a, b) => a.n - b.n);
      dataRef.current = { frames: collected, images };
      setReady(true);
    };

    load();
    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    if (!ready) return undefined;
    const canvas = canvasRef.current;
    const ctx = canvas.getContext("2d");
    const { frames, images } = dataRef.current;
    canvas.width = frames[0].w;
    canvas.height = frames[0].h;

    const draw = (idx) => {
      const f = frames[idx];
      if (!f) return;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(images[f.atlas], f.x, f.y, f.w, f.h, 0, 0, canvas.width, canvas.height);
    };

    if (!animate) {
      draw(Math.max(0, Math.min(frames.length - 1, frame)));
      return undefined;
    }

    const lo = Math.max(0, from);
    const hi = Math.min(frames.length, to);
    const step = 1000 / fps;
    let i = lo;
    let last = performance.now();
    let raf = 0;
    draw(lo);
    const loop = (now) => {
      raf = requestAnimationFrame(loop);
      if (now - last >= step) {
        last = now - ((now - last) % step);
        i += 1;
        if (i >= hi) i = lo;
        draw(i);
      }
    };
    raf = requestAnimationFrame(loop);
    return () => cancelAnimationFrame(raf);
  }, [ready, frame, animate, from, to, fps]);

  return (
    <canvas
      ref={canvasRef}
      className={className}
      onPointerDown={onPointerDown}
      style={{ ...style, opacity: ready ? 1 : 0, transition: "opacity 0.3s ease" }}
    />
  );
}
