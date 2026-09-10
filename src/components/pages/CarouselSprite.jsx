import { useEffect, useRef, useState } from "react";

const OPT = true;
const BASE = OPT ? "/bonus-park/Carousel_opt2" : "/bonus-park/Carousel";
const ATLAS = "carousel";
const ATLASES = 4;
const TOTAL_FRAMES = 79;
const PERIOD_SEC = 3.5;

export const CAROUSEL_FRAMES = TOTAL_FRAMES;

const VIDEO_DIR = "/bonus-park/Carousel_video";
const VIDEO_PERIOD = 10.04;

const pickVideo = () => {
  if (typeof document === "undefined") return null;
  const ua = navigator.userAgent;
  const isSafari = /^((?!chrome|android|crios|fxios).)*safari/i.test(ua) || /iPad|iPhone|iPod/.test(ua);
  return isSafari ? `${VIDEO_DIR}/carousel_250_new.mov` : `${VIDEO_DIR}/0000-0250.webm`;
};

export default function CarouselSprite(props) {
  const src = pickVideo();
  return <CarouselVideo {...props} src={src} onFail={() => console.warn("[carousel] video failed")} />;
}

const RAMP_UP_MS = 3400;
const RAMP_DOWN_MS = 1800;
const RATE_MIN = 0.5;
const RATE_STEP_MS = 110;

function CarouselVideo({ className, style, onPointerDown, period = VIDEO_PERIOD, spin = true, stopAt = null, onStopped, src, onFail }) {
  const ref = useRef(null);
  const rafRef = useRef(0);

  useEffect(() => {
    const el = ref.current;
    if (!el) return undefined;
    const full = Math.max(RATE_MIN, Math.min(4, VIDEO_PERIOD / Math.max(0.1, period)));
    cancelAnimationFrame(rafRef.current);

    let applied = -1;
    let lastSet = 0;
    const setRate = (v, now) => {
      const r = Math.min(4, Math.max(RATE_MIN, v));
      if (Math.abs(r - applied) < 0.02 && applied >= 0) return;
      if (now && now - lastSet < RATE_STEP_MS && r !== RATE_MIN && r !== full) return;
      try {
        el.playbackRate = r;
        applied = r;
        lastSet = now || 0;
      } catch (err) {
        /* rate not supported */
      }
    };

    let cancelled = false;
    let t0 = 0;
    const from = spin ? RATE_MIN : el.playbackRate || full;
    const to = spin ? full : RATE_MIN;

    const step = (now) => {
      if (cancelled) return;
      if (!t0) t0 = now;
      const dur = spin ? RAMP_UP_MS : RAMP_DOWN_MS;
      const t = Math.min(1, (now - t0) / dur);
      const e = spin ? t * t * t * (t * (t * 6 - 15) + 10) : t * t * (3 - 2 * t);
      setRate(from + (to - from) * e, now);
      if (t < 1) {
        rafRef.current = requestAnimationFrame(step);
      } else if (!spin) {
        el.pause();
      }
    };

    if (!spin && stopAt != null) {
      const P = VIDEO_PERIOD;
      const target = ((stopAt % P) + P) % P;
      const T = 1800;
      const v0 = Math.max(0.5, el.playbackRate || full);
      const D = (v0 * T) / 2000;
      let phase = 0;
      let t0 = 0;
      let armed = false;
      const land = (now) => {
        if (cancelled) return;
        const cur = el.currentTime % P;
        if (phase === 0) {
          const rem = ((target - cur + P) % P);
          if (rem > D + 0.12) armed = true;
          if (armed && rem <= D) {
            phase = 1;
            t0 = now;
          }
          rafRef.current = requestAnimationFrame(land);
          return;
        }
        const t = Math.min(1, (now - t0) / T);
        const left = Math.max(0.001, (T - (now - t0)) / 1000);
        let rem = (target - (el.currentTime % P) + P) % P;
        if (rem > P * 0.7) rem = 0;
        const want = (2 * rem) / left;
        try {
          el.playbackRate = Math.max(0.06, Math.min(v0, want));
        } catch (err) {
          /* rate */
        }
        if (t < 1 && rem > 0.012) {
          rafRef.current = requestAnimationFrame(land);
          return;
        }
        try {
          el.pause();
        } catch (err) {
          /* stop */
        }
        onStopped?.();
      };
      const go = () => {
        if (!cancelled) rafRef.current = requestAnimationFrame(land);
      };
      const pr = el.play();
      if (pr && pr.then) pr.then(go).catch(go);
      else go();
      return () => {
        cancelled = true;
        cancelAnimationFrame(rafRef.current);
      };
    }
    if (spin) {
      setRate(RATE_MIN);
      const begin = () => {
        if (cancelled) return;
        rafRef.current = requestAnimationFrame(step);
      };
      const p = el.play();
      if (p && p.then) p.then(begin).catch(begin);
      else begin();
    } else {
      rafRef.current = requestAnimationFrame(step);
    }

    return () => {
      cancelled = true;
      cancelAnimationFrame(rafRef.current);
    };
  }, [spin, period, stopAt]);

  useEffect(() => {
    const el = ref.current;
    if (!el) return undefined;
    let tries = 0;
    const kick = () => {
      if (el.readyState >= 2 || tries > 4) return;
      tries += 1;
      try {
        el.load();
      } catch (err) {
        /* ignore */
      }
    };
    const onErr = () => {
      tries = 0;
      kick();
    };
    kick();
    el.addEventListener("error", onErr);
    const iv = setInterval(kick, 900);
    const stop = setTimeout(() => clearInterval(iv), 6000);
    return () => {
      el.removeEventListener("error", onErr);
      clearInterval(iv);
      clearTimeout(stop);
    };
  }, [src]);

  return (
    <video
      ref={ref}
      className={className}
      style={style}
      onPointerDown={onPointerDown}
      src={src}
      muted
      loop
      playsInline
      preload="auto"
      onError={onFail}
      disablePictureInPicture
    />
  );
}

function CarouselCanvas({ className, style, onPointerDown, period = PERIOD_SEC, spin = true, preload = false }) {
  const canvasRef = useRef(null);
  const dataRef = useRef({ frames: new Array(TOTAL_FRAMES), images: new Array(ATLASES) });
  const fullRef = useRef(false);
  const [ready, setReady] = useState(false);

  const loadAtlas = async (i) => {
    const res = await fetch(`${BASE}/${ATLAS}-${i}.json`);
    const json = await res.json();
    Object.entries(json.frames).forEach(([name, data]) => {
      dataRef.current.frames[parseInt(name, 10)] = { atlas: i, ...data.frame };
    });
    const img = new Image();
    img.src = `${BASE}/${ATLAS}-${i}.png`;
    await img.decode();
    dataRef.current.images[i] = img;
  };

  useEffect(() => {
    let cancelled = false;
    loadAtlas(0).then(() => {
      if (!cancelled) setReady(true);
    });
    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    if ((!spin && !preload) || fullRef.current) return;
    fullRef.current = true;
    (async () => {
      for (let i = 1; i < ATLASES; i += 1) await loadAtlas(i);
    })();
  }, [spin, preload]);

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
      if (!f || !images[f.atlas]) return;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.drawImage(images[f.atlas], f.x, f.y, f.w, f.h, 0, 0, canvas.width, canvas.height);
    };

    const framesPerMs = TOTAL_FRAMES / (Math.max(0.05, period) * 1000);

    draw(0);
    if (!spin) return undefined;
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
  }, [ready, period, spin]);

  return (
    <canvas
      ref={canvasRef}
      className={className}
      onPointerDown={onPointerDown}
      style={{ ...style, opacity: ready ? 1 : 0, transition: "opacity 0.4s ease" }}
    />
  );
}
