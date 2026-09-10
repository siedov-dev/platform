const fs = require("fs");
const p = "src/components/pages/BonusParkPage.jsx";
let b = fs.readFileSync(p, "utf8");

b = b.replace(`const WHEEL_RIG = { w: 0.7406, dx: 0.0938, dy: 0.375 };`, `const WHEEL_RIG = { w: 0.7406, dx: 0.0938, dy: 0.375 };
const PARK_AR = 390 / 844;`);

b = b.replace(new RegExp(`            top: \\`\\$\{upark\.wheel\.y \+ upark\.wheel\.w \* WHEEL_RIG\.dy\}%\\`,`, "g"), `            top: \`\${upark.wheel.y + upark.wheel.w * WHEEL_RIG.dy * PARK_AR}%\`,`);

fs.writeFileSync(p, b);
const hits = (b.match(/WHEEL_RIG\.dy \* PARK_AR/g) || []).length;
console.log("fixed offsets:", hits);
