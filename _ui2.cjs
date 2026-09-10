const fs = require("fs");
const rep = (f, x, y) => { let s = fs.readFileSync(f, "utf8"); if (!s.includes(x)) throw new Error("miss in " + f + ": " + x.slice(0, 45)); fs.writeFileSync(f, s.replace(x, y)); };

rep("src/App.jsx", "    frameT,", "    uiKTop: Math.min(1, (BASE_W - 2 * frameT) / 366),\n    uiKGrid: Math.min(1, (BASE_W - 2 * gridT) / 366),\n    frameT,");
rep("src/App.jsx", ", gridT, gridV, overX, cropX, overY } = useFitScale();", ", gridT, gridV, overX, cropX, overY, uiKTop, uiKGrid } = useFitScale();");
rep("src/App.jsx", '          "--fr-t-raw": ', '          "--ui-k-top": uiKTop.toFixed(4),\n          "--ui-k-grid": uiKGrid.toFixed(4),\n          "--fr-t-raw": ');

rep("src/styles/global.css",
`[data-framed="1"] {
  --fr-t: var(--fr-t-raw, 0px);
  --fr-v: var(--fr-v-raw, 0px);
}`,
`[data-framed="1"] {
  --fr-t: var(--fr-t-raw, 0px);
  --fr-v: var(--fr-v-raw, 0px);
  --ui-k: var(--ui-k-top, 1);
}`);
rep("src/styles/global.css",
`[data-framed="grid"] {
  --fr-t: var(--gr-t-raw, 0px);
  --fr-v: var(--gr-v-raw, 0px);
}`,
`[data-framed="grid"] {
  --fr-t: var(--gr-t-raw, 0px);
  --fr-v: var(--gr-v-raw, 0px);
  --ui-k: var(--ui-k-grid, 1);
}`);

rep("src/components/pages/ParkHud.module.css",
  "  padding: 0 calc(var(--fr-t, 0px) + 6px);",
  "  padding: 0 calc(var(--fr-t, 0px) + 6px);\n  transform: scale(var(--ui-k, 1));\n  transform-origin: 50% 0;");

rep("src/components/pages/PlayerRow.module.css",
  "  padding: 0 calc(var(--fr-t, 0px) + 6px);",
  "  padding: 0 calc(var(--fr-t, 0px) + 6px);\n  transform: scale(var(--ui-k, 1));\n  transform-origin: 50% 0;");

rep("src/components/pages/BonusParkPage.module.css",
  "  top: calc(var(--safe-top, 0px) + var(--crop-top, 0px) + var(--fr-v, 0px) + 58px);\n  left: 12px;\n  z-index: 410;",
  "  top: calc(var(--safe-top, 0px) + var(--crop-top, 0px) + var(--fr-v, 0px) + 58px);\n  left: 12px;\n  z-index: 440;");

rep("src/components/pages/BonusParkPage.module.css",
  "  top: calc(104px + var(--safe-top, 0px) + var(--crop-top, 0px));\n  left: 12px;\n  z-index: 410;",
  "  top: calc(104px - var(--ios-lift, 0px) + var(--safe-top, 0px) + var(--crop-top, 0px));\n  left: 12px;\n  z-index: 440;");
console.log("ok");
