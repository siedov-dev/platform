const fs = require("fs");
const cols = [
  [19.91, 23.95, 31.71], [22.16, 23.72, 31.56], [24.48, 23.48, 31.41], [26.74, 23.37, 31.3], [29.05, 23.24, 31.17],
  [31.31, 23.13, 31.06], [33.56, 23.06, 30.98], [35.88, 22.94, 30.87], [38.14, 22.85, 30.8], [40.45, 22.78, 30.72],
  [42.71, 22.7, 30.65], [44.97, 22.67, 30.59], [47.28, 22.59, 30.56], [49.54, 22.56, 30.52], [51.85, 22.54, 30.5],
  [54.11, 22.5, 30.48], [56.37, 22.5, 30.5], [58.68, 22.52, 30.5], [60.94, 22.54, 30.56], [63.25, 22.57, 30.57],
  [65.51, 22.61, 30.61], [67.77, 22.65, 30.67], [70.08, 22.7, 30.74], [72.34, 22.76, 30.81], [74.65, 22.85, 30.91],
  [76.91, 22.96, 30.98],
];
const MX = 19.91;
const MW = 76.91 - 19.91;
const MY = Math.min(...cols.map((c) => c[1]));
const MH = Math.max(...cols.map((c) => c[2])) - MY;

const lx = (sx) => +(((sx - MX) / MW) * 100).toFixed(2);
const ly = (sy) => +(((sy - MY) / MH) * 100).toFixed(2);
const top = cols.map(([sx, t]) => [lx(sx), ly(t)]);
const bot = cols.map(([sx, , b]) => [lx(sx), ly(b)]).reverse();
const d =
  "M " + top.map(([x, y], i) => (i ? "L " : "") + x + " " + y).join(" ") +
  " " + bot.map(([x, y]) => "L " + x + " " + y).join(" ") + " Z";

const interp = (x, idx) => {
  for (let i = 0; i < cols.length - 1; i += 1) {
    if (x >= cols[i][0] && x <= cols[i + 1][0]) {
      const t = (x - cols[i][0]) / (cols[i + 1][0] - cols[i][0]);
      return cols[i][idx] + t * (cols[i + 1][idx] - cols[i][idx]);
    }
  }
  return x < cols[0][0] ? cols[0][idx] : cols[cols.length - 1][idx];
};
const third = MW / 3;
const band = [0, 1, 2].map((c) => {
  const cx = MX + third * (c + 0.5);
  const t = interp(cx, 1);
  const b = interp(cx, 2);
  const dyPx = (interp(cx + third * 0.4, 1) - interp(cx - third * 0.4, 1)) * 8.44;
  const dxPx = third * 0.8 * 5.4;
  return {
    top: +(((t - MY) / MH) * 100).toFixed(2),
    bot: +(((b - MY) / MH) * 100).toFixed(2),
    rot: +((Math.atan2(dyPx, dxPx) * 180) / Math.PI).toFixed(2),
  };
});
const dy = band.map((b) => +((b.top + b.bot) / 2 - 50).toFixed(2));
console.log(JSON.stringify({ MX, MY, MW, MH, band, dy, monOld: { x: +((MX - 8.371) / 0.8224).toFixed(2), y: +((MY + 6.95) / 1.139).toFixed(2), w: +(MW / 0.8224).toFixed(2), h: +(MH / 1.139).toFixed(2) } }));

const m = "src/components/pages/MonitorScreen.jsx";
let u = fs.readFileSync(m, "utf8");
u = u.replace(/const SCR_D =\n {2}"[^"]*";/, 'const SCR_D =\n  "' + d + '";');
u = u.replace(/const COLS_ARC = \[\n(?:.*\n){3}\];/, `const COLS_ARC = [
  { dy: ${dy[0]}, rot: ${band[0].rot} },
  { dy: ${dy[1]}, rot: ${band[1].rot} },
  { dy: ${dy[2]}, rot: ${band[2].rot} },
];`);
u = u.replace(/const COL_BAND = \[\n(?:.*\n){3}\];/, `const COL_BAND = [
  { top: ${band[0].top}, bot: ${band[0].bot} },
  { top: ${band[1].top}, bot: ${band[1].bot} },
  { top: ${band[2].top}, bot: ${band[2].bot} },
];`);
u = u.replace(/  iconScale = [\d.]+,/, `  iconScale = ${(6.2 / (MH / 1.139)).toFixed(3)},`);
fs.writeFileSync(m, u);
console.log("applied");
