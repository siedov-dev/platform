import { forwardRef, useEffect, useImperativeHandle, useRef, useState } from "react";
import styles from "./TrainRide.module.css";
import { PARK_ASSET } from "./parkAssetBase";

const A = PARK_ASSET + "/Train";
const MAX_CARS = 6;
const HEAD_W = 12.3;
const CAR_W = 9.9;
const GAP = 0;
const SPEED = 34;
const RAIL_DY = 0.45;

const RAIL_PTS = [
  [-45, 21.9], [-20, 22.7], [0, 23.4], [6, 23.61], [9, 23.82], [24, 24.12], [31, 24.47], [42, 24.29],
  [48, 24.17], [54, 23.95], [60, 23.74], [66, 23.44], [72, 23.18], [81, 22.75],
  [87, 22.5], [93, 22.27], [100, 22.02], [130, 21.3], [155, 20.6],
];

const railY = (x) => {
  const p = RAIL_PTS;
  if (x <= p[0][0]) return p[0][1] + RAIL_DY;
  for (let i = 1; i < p.length; i += 1) {
    if (x <= p[i][0]) {
      const t = (x - p[i - 1][0]) / (p[i][0] - p[i - 1][0]);
      return p[i - 1][1] + (p[i][1] - p[i - 1][1]) * t + RAIL_DY;
    }
  }
  return p[p.length - 1][1] + RAIL_DY;
};

const railRot = (x) => {
  const dy = railY(x + 2) - railY(x - 2);
  return Math.atan2((dy / 4) * 8.44, 3.9) * (180 / Math.PI);
};

const slotX = (idx) => {
  const total = HEAD_W + (MAX_CARS - 1) * (CAR_W + GAP);
  const headCx = 50 - total / 2 + HEAD_W / 2;
  if (idx === 0) return headCx;
  return headCx + HEAD_W / 2 + GAP + CAR_W / 2 + (idx - 1) * (CAR_W + GAP);
};

const TrainRide = forwardRef(function TrainRide({ onDeparted }, ref) {
  const [, force] = useState(0);
  const st = useRef({
    parked: 0,
    arriving: null,
    leaving: null,
    queue: 0,
    sparks: [],
    sparkId: 0,
  });
  const rafRef = useRef(0);
  const prevRef = useRef(0);
  const departedRef = useRef(onDeparted);
  departedRef.current = onDeparted;

  const kick = () => {
    if (!rafRef.current) {
      prevRef.current = 0;
      rafRef.current = requestAnimationFrame(tick);
    }
  };

  useImperativeHandle(ref, () => ({
    addCars(n) {
      st.current.queue += n;
      kick();
    },
  }));

  useEffect(() => () => cancelAnimationFrame(rafRef.current), []);

  const spawnSparks = (x, count) => {
    const s = st.current;
    for (let i = 0; i < count; i += 1) {
      s.sparkId += 1;
      s.sparks.push({
        id: s.sparkId,
        x: x + (Math.random() - 0.5) * 2,
        y: railY(x) + 0.15,
        dx: (Math.random() * 0.9 + 0.4) * (Math.random() < 0.3 ? -1 : 1),
        dy: Math.random() * 0.28 + 0.06,
        life: 1,
      });
    }
  };

  const tick = (now) => {
    const s = st.current;
    const dt = prevRef.current ? Math.min(0.09, (now - prevRef.current) / 1000) : 0.016;
    prevRef.current = now;
    let busy = false;

    if (s.leaving) {
      busy = true;
      s.leaving.x -= SPEED * 1.25 * dt;
      for (let i = 0; i < MAX_CARS; i += 1) {
        const cx = s.leaving.x + HEAD_W / 2 + (i === 0 ? 0 : HEAD_W / 2 + GAP + CAR_W / 2 + (i - 1) * (CAR_W + GAP));
        if (cx > -4 && cx < 104 && Math.random() < 0.3) spawnSparks(cx, 1);
      }
      if (s.leaving.x < -(HEAD_W + MAX_CARS * (CAR_W + GAP) + 14)) {
        s.leaving = null;
        s.parked = 0;
        departedRef.current?.();
      }
    } else if (s.arriving) {
      busy = true;
      s.arriving.x -= SPEED * dt;
      if (Math.random() < 0.65) spawnSparks(s.arriving.x, 1);
      if (s.arriving.x <= s.arriving.target) {
        s.parked += 1;
        s.arriving = null;
        if (s.parked >= MAX_CARS) {
          s.leaving = { x: slotX(0) - HEAD_W / 2 };
        }
      }
    } else if (s.queue > 0) {
      busy = true;
      s.queue -= 1;
      const idx = s.parked;
      s.arriving = { idx, x: 116, target: slotX(idx) };
    }

    for (let i = s.sparks.length - 1; i >= 0; i -= 1) {
      const p = s.sparks[i];
      p.life -= dt * 2.4;
      p.x += p.dx * dt * 8;
      p.y += p.dy * dt * 14;
      if (p.life <= 0) s.sparks.splice(i, 1);
    }

    force((v) => v + 1);

    if (busy || s.sparks.length > 0) {
      rafRef.current = requestAnimationFrame(tick);
    } else {
      rafRef.current = 0;
    }
  };

  const s = st.current;

  const renderSection = (idx, x) => {
    const head = idx === 0;
    return (
      <img
        key={`car${idx}`}
        className={styles.car}
        src={`${A}/${head ? "Train_Head.png" : "Train_Body.png"}`}
        alt=""
        draggable={false}
        style={{
          left: `${x}%`,
          top: `${railY(x)}%`,
          width: `${head ? HEAD_W : CAR_W}%`,
          transform: `translate(-50%, -100%) rotate(${railRot(x)}deg)`,
        }}
      />
    );
  };

  const sections = [];
  if (s.leaving) {
    for (let i = 0; i < MAX_CARS; i += 1) {
      const x = s.leaving.x + HEAD_W / 2 + (i === 0 ? 0 : HEAD_W / 2 + GAP + CAR_W / 2 + (i - 1) * (CAR_W + GAP));
      sections.push(renderSection(i, x));
    }
  } else {
    for (let i = 0; i < s.parked; i += 1) sections.push(renderSection(i, slotX(i)));
    if (s.arriving) sections.push(renderSection(s.arriving.idx, s.arriving.x));
  }

  return (
    <div className={styles.layer}>
      {sections}
      {s.sparks.map((p) => (
        <span
          key={p.id}
          className={styles.spark}
          style={{ left: `${p.x}%`, top: `${p.y}%`, opacity: Math.max(0, p.life) }}
        />
      ))}
    </div>
  );
});

export default TrainRide;
