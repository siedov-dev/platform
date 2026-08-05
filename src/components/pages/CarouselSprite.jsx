import { useEffect, useRef, useState } from "react";

const OPT = true;
const BASE = OPT ? "/bonus-park/Carousel_opt" : "/bonus-park/Carousel";
const ATLAS = "carousel";
const ATLASES = 4;
const TOTAL_FRAMES = 79;
const RPM = 8;

export default function CarouselSprite({ className, style, onPointerDown }) {
  const canvasRef = useRef(null);
  const dataRef = useRef({ frames: [], images: [] });
  const [ready, setReady] = useState(false);

  useEffect(() => {
    let cancelled = false;

    const load = async () => {
      const frames = new Array(TOTAL_FRAMES);
      const images = new Array(ATLASES);

      await Promise.all(
        Array.from({ length: ATLASES }, async (_, i) => {
          const res = await fetch(`${BASE}/${ATLAS}-${i}.json`);
          const json = await res.json();
          Object.entries(json.frames).forEach(([name, data]) => {
            frames[parseInt(name, 10)] = { atlas: i, ...data.frame };
          });
          const img = new Image();
          img.src = `${BASE}/${ATLAS}-${i}.png`;
          await img.decode();
          images[i] = img;
        })
      );

      if (cancelled) return;
      dataRef.current = { frames, images };
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

    let pos = 0;
    let shown = 0;
    let last = performance.now();
    let raf = 0;

    const draw = (idx) => {
      const f = frames[idx];
      if (!f) return;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(images[f.atlas], f.x, f.y, f.w, f.h, 0, 0, canvas.width, canvas.height);
    };

    const framesPerMs = ((RPM / 60) * TOTAL_FRAMES) / 1000;

    draw(0);
    const loop = (now) => {
      raf = requestAnimationFrame(loop);
      const dt = now - last;
      last = now;
      pos = (pos + dt * framesPerMs) % TOTAL_FRAMES;
      const idx = Math.floor(pos);
      if (idx !== shown) {
        shown = idx;
        draw(idx);
      }
    };
    raf = requestAnimationFrame(loop);
    return () => cancelAnimationFrame(raf);
  }, [ready]);

  return (
    <canvas
      ref={canvasRef}
      className={className}
      onPointerDown={onPointerDown}
      style={{ ...style, opacity: ready ? 1 : 0, transition: "opacity 0.4s ease" }}
    />
  );
}
