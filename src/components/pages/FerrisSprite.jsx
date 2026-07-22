import { useEffect, useRef, useState } from "react";

const BASE = "/bonus-park/Ferris_60";
const ATLAS = "Ferris_60_2k";
const ATLASES = 3;
const TOTAL_FRAMES = 60;
const FPS = 12;

export default function FerrisSprite({ className, style, onPointerDown }) {
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
            frames[parseInt(name, 10) - 1] = { atlas: i, ...data.frame };
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

    const draw = (index) => {
      const f = frames[index];
      if (!f) return;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(images[f.atlas], f.x, f.y, f.w, f.h, 0, 0, canvas.width, canvas.height);
    };

    const step = 1000 / FPS;
    let frame = 0;
    let last = performance.now();
    let raf = 0;

    draw(0);
    const loop = (now) => {
      raf = requestAnimationFrame(loop);
      if (now - last >= step) {
        last = now - ((now - last) % step);
        frame = (frame + 1) % TOTAL_FRAMES;
        draw(frame);
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
