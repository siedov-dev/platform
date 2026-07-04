import gameNames from "./data/gameNames.json";
import palettes from "./data/palettes.json";

const CATALOG_SIZE = 36;

function buildCatalog(provider, category) {
  const seed = (provider + ":" + category)
    .split("")
    .reduce((a, c) => a * 31 + c.charCodeAt(0), 0);
  let s = Math.abs(seed) || 1;
  const out = [];
  const used = new Set();

  for (let i = 0; i < CATALOG_SIZE; i++) {
    s = (s * 9301 + 49297) % 233280;
    let idx = s % gameNames.length;
    while (used.has(idx)) idx = (idx + 1) % gameNames.length;
    used.add(idx);
    const pal = palettes[(idx + s) % palettes.length];
    out.push({
      id: `${provider}-${category}-${i}`,
      name: gameNames[idx],
      provider,
      category,
      colors: pal,
    });
  }

  return out;
}

export function queryGames({ provider = "all", category = "popular", q = "", page = 1, pageSize = 12 }) {
  let items = buildCatalog(provider, category);

  if (q) {
    const needle = q.toLowerCase();
    items = items.filter((g) => g.name.toLowerCase().includes(needle));
  }

  const total = items.length;
  const start = (page - 1) * pageSize;
  const pageItems = items.slice(start, start + pageSize);

  return {
    items: pageItems,
    page,
    pageSize,
    total,
    hasMore: start + pageSize < total,
  };
}
