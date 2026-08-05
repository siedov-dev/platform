const BASE = "/bonus-park/Human_10";

export const DIRS = [
  { name: "right", angle: 0 },
  { name: "15_right_down", angle: 20 },
  { name: "45_right_down", angle: 55 },
  { name: "front", angle: 90 },
  { name: "45_left_down", angle: 125 },
  { name: "15_left_down", angle: 160 },
  { name: "left", angle: 180 },
  { name: "15_left_up", angle: 200 },
  { name: "45_left_up", angle: 235 },
  { name: "back", angle: 270 },
  { name: "45_right_up", angle: 305 },
  { name: "15_right_up", angle: 340 },
];

let cache = null;

export function loadHumanAtlas() {
  if (cache) return cache;
  cache = (async () => {
    const map = {};
    await Promise.all(
      DIRS.map(async ({ name }) => {
        const res = await fetch(`${BASE}/${name}/${name}.json`);
        const json = await res.json();
        const frames = Object.entries(json.frames)
          .sort((a, b) => parseInt(a[0], 10) - parseInt(b[0], 10))
          .map(([, d]) => d.frame);
        const img = new Image();
        img.src = `${BASE}/${name}/${name}.png`;
        await img.decode();
        map[name] = { frames, img };
      })
    );
    return map;
  })();
  return cache;
}

export function pickDir(deg) {
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

export function chaikin(pts, iterations) {
  let cur = pts;
  for (let it = 0; it < iterations; it += 1) {
    const out = [cur[0]];
    for (let i = 0; i < cur.length - 1; i += 1) {
      const p = cur[i];
      const q = cur[i + 1];
      out.push({ x: p.x * 0.75 + q.x * 0.25, y: p.y * 0.75 + q.y * 0.25 });
      out.push({ x: p.x * 0.25 + q.x * 0.75, y: p.y * 0.25 + q.y * 0.75 });
    }
    out.push(cur[cur.length - 1]);
    cur = out;
  }
  return cur;
}
