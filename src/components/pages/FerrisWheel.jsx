import { useEffect, useRef, useState } from "react";
import { useApp } from "@/context/AppContext";
import styles from "./FerrisWheel.module.css";

const BASE = "/ferris";
const ATLASES = 6;
const TOTAL_FRAMES = 120;
const FPS = 24;

export default function FerrisWheel() {
  const { navigate } = useApp();
  const canvasRef = useRef(null);
  const dataRef = useRef({ frames: [], images: [] });

  const [ready, setReady] = useState(false);
  const [progress, setProgress] = useState(0);

  useEffect(() => {
    let cancelled = false;

    const load = async () => {
      const frames = new Array(TOTAL_FRAMES);
      const images = new Array(ATLASES);
      let loaded = 0;

      await Promise.all(
        Array.from({ length: ATLASES }, async (_, i) => {
          const res = await fetch(`${BASE}/Ferris_wheel-${i}.json`);
          const json = await res.json();
          Object.entries(json.frames).forEach(([name, data]) => {
            frames[parseInt(name, 10) - 1] = { atlas: i, ...data.frame };
          });

          const img = new Image();
          img.src = `${BASE}/Ferris_wheel-${i}.png`;
          await img.decode();
          images[i] = img;

          loaded += 1;
          if (!cancelled) setProgress(Math.round((loaded / ATLASES) * 100));
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
    <div className={styles.page}>
      <button className={styles.back} onClick={() => navigate("home")}>← Back</button>

      <div className={styles.stage}>
        {!ready && <div className={styles.loading}>Загрузка атласов… {progress}%</div>}
        <canvas ref={canvasRef} className={styles.canvas} style={{ opacity: ready ? 1 : 0 }} />
      </div>
    </div>
  );
}
