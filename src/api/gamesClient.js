const GAMES_BASE = import.meta.env.VITE_GAMES_API_URL || "http://localhost:8000";

export function gameField(response, key) {
  if (!response) return undefined;
  if (response.data && response.data[key] !== undefined) return response.data[key];
  return response[key];
}

async function post(path, body) {
  const response = await fetch(`${GAMES_BASE}/api/game${path}`, {
    method: "POST",
    headers: { "Content-Type": "application/json", Accept: "application/json" },
    redirect: "manual",
    body: body ? JSON.stringify(body) : undefined,
  });

  if (response.type === "opaqueredirect" || (response.status >= 300 && response.status < 400)) {
    throw new Error("Unexpected redirect from games backend");
  }

  const text = await response.text();
  let parsed = null;
  try {
    parsed = text ? JSON.parse(text) : null;
  } catch {
    parsed = { raw: text };
  }

  if (!response.ok) {
    const message =
      parsed?.error?.message || parsed?.message || parsed?.status || response.statusText;
    const error = new Error(message);
    error.status = response.status;
    error.body = parsed;
    throw error;
  }

  return parsed;
}

export const gamesApi = {
  pyramidStart: () => post("/pyramid/start"),
  pyramidAction: (gameId, action) => post("/pyramid/action", { game_id: gameId, action }),
  safeStart: () => post("/safe/start"),
  safeOpen: (gameId, cellIndex) => post("/safe/open", { game_id: gameId, cell_index: cellIndex }),
  wheelStart: () => post("/wheel/start"),
  wheelSpin: (gameId) => post("/wheel/spin", { game_id: gameId }),
  cardsStart: () => post("/cards/start"),
  cardsPlay: (gameId) => post("/cards/play", { game_id: gameId }),
};
