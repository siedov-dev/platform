import { http, HttpResponse, delay } from "msw";
import user from "./data/user.json";
import balance from "./data/balance.json";
import providers from "./data/providers.json";
import categories from "./data/categories.json";
import popularGames from "./data/popularGames.json";
import promotions from "./data/promotions.json";
import paymentMethods from "./data/paymentMethods.json";
import matches from "./data/matches.json";
import favorites from "./data/favorites.json";
import userBonuses from "./data/userBonuses.json";
import transactions from "./data/transactions.json";
import { queryGames } from "./catalog";

const LATENCY = 250;
const favoriteIds = new Set(favorites.ids);
const bonusState = userBonuses.map((b) => ({ ...b }));
const transactionState = transactions.map((t) => ({ ...t }));

export const handlers = [
  http.get("/api/user", async () => {
    await delay(LATENCY);
    return HttpResponse.json(user);
  }),

  http.get("/api/balance", async () => {
    await delay(LATENCY);
    return HttpResponse.json(balance);
  }),

  http.get("/api/providers", async () => {
    await delay(LATENCY);
    return HttpResponse.json(providers);
  }),

  http.get("/api/categories", async () => {
    await delay(LATENCY);
    return HttpResponse.json(categories);
  }),

  http.get("/api/games", async ({ request }) => {
    const url = new URL(request.url);
    const result = queryGames({
      provider: url.searchParams.get("provider") || "all",
      category: url.searchParams.get("category") || "popular",
      q: url.searchParams.get("q") || "",
      page: Number(url.searchParams.get("page")) || 1,
      pageSize: Number(url.searchParams.get("pageSize")) || 12,
    });
    await delay(LATENCY);
    return HttpResponse.json(result);
  }),

  http.get("/api/games/popular", async () => {
    await delay(LATENCY);
    return HttpResponse.json(popularGames);
  }),

  http.get("/api/favorites", async () => {
    await delay(LATENCY);
    return HttpResponse.json({ ids: [...favoriteIds] });
  }),

  http.post("/api/favorites/:gameId/toggle", async ({ params }) => {
    const { gameId } = params;
    let favorite;
    if (favoriteIds.has(gameId)) {
      favoriteIds.delete(gameId);
      favorite = false;
    } else {
      favoriteIds.add(gameId);
      favorite = true;
    }
    await delay(LATENCY);
    return HttpResponse.json({ gameId, favorite });
  }),

  http.get("/api/promotions", async () => {
    await delay(LATENCY);
    return HttpResponse.json(promotions);
  }),

  http.get("/api/payment-methods", async () => {
    await delay(LATENCY);
    return HttpResponse.json(paymentMethods);
  }),

  http.get("/api/sport/matches", async ({ request }) => {
    const url = new URL(request.url);
    const sport = url.searchParams.get("sport") || "soccer";
    await delay(LATENCY);
    return HttpResponse.json(matches.filter((m) => m.sport === sport));
  }),

  http.post("/api/games/:gameId/launch", async ({ params, request }) => {
    const body = await request.json().catch(() => ({}));
    const mode = body?.mode === "demo" ? "demo" : "real";
    await delay(LATENCY);
    return HttpResponse.json({
      gameId: params.gameId,
      mode,
      url: `https://launch.example/game/${params.gameId}?mode=${mode}`,
      sessionId: "sess_" + Math.random().toString(36).slice(2, 10),
    });
  }),

  http.get("/api/user/bonuses", async () => {
    await delay(LATENCY);
    return HttpResponse.json(bonusState);
  }),

  http.post("/api/user/bonuses/:bonusId/activate", async ({ params }) => {
    const bonus = bonusState.find((b) => b.id === params.bonusId);
    if (!bonus) {
      return HttpResponse.json({ error: { code: "NOT_FOUND", message: "Bonus not found" } }, { status: 404 });
    }
    bonus.status = 1;
    bonus.statusName = "Active";
    bonus.isActive = true;
    bonus.isPending = false;
    await delay(LATENCY);
    return HttpResponse.json({ bonusId: bonus.id, status: bonus.status, statusName: bonus.statusName });
  }),

  http.post("/api/user/bonuses/:bonusId/cancel", async ({ params }) => {
    const bonus = bonusState.find((b) => b.id === params.bonusId);
    if (!bonus) {
      return HttpResponse.json({ error: { code: "NOT_FOUND", message: "Bonus not found" } }, { status: 404 });
    }
    bonus.status = 3;
    bonus.statusName = "Canceled";
    bonus.isActive = false;
    bonus.isCancelable = false;
    await delay(LATENCY);
    return HttpResponse.json({ bonusId: bonus.id, status: bonus.status, statusName: bonus.statusName });
  }),

  http.get("/api/user/transactions", async ({ request }) => {
    const url = new URL(request.url);
    const type = url.searchParams.get("type") || "all";
    const page = Number(url.searchParams.get("page")) || 1;
    const pageSize = Number(url.searchParams.get("pageSize")) || 20;
    const filtered = type === "all" ? transactionState : transactionState.filter((t) => t.type === type);
    const start = (page - 1) * pageSize;
    await delay(LATENCY);
    return HttpResponse.json({
      items: filtered.slice(start, start + pageSize),
      page,
      pageSize,
      total: filtered.length,
      hasMore: start + pageSize < filtered.length,
    });
  }),

  http.post("/api/user/transactions/:id/cancel", async ({ params }) => {
    const tx = transactionState.find((t) => t.id === params.id);
    if (!tx) {
      return HttpResponse.json({ error: { code: "NOT_FOUND", message: "Transaction not found" } }, { status: 404 });
    }
    tx.status = "canceled";
    tx.statusName = "Canceled";
    await delay(LATENCY);
    return HttpResponse.json({ id: tx.id, status: tx.status });
  }),

  http.post("/api/promotions/:promoId/activate", async ({ params }) => {
    await delay(LATENCY);
    return HttpResponse.json({ promoId: params.promoId, activated: true, message: "Promotion activated" });
  }),

  http.get("/api/payment-methods/:methodId/requisites", async ({ params }) => {
    const method = paymentMethods.find((m) => m.id === params.methodId);
    await delay(LATENCY);
    return HttpResponse.json({
      methodId: params.methodId,
      fields: [
        {
          name: "amount",
          label: "Amount",
          type: "number",
          min: method?.minDeposit ?? 0,
          max: method?.maxDeposit ?? 0,
          required: true,
        },
      ],
    });
  }),

  http.post("/api/deposit", async ({ request }) => {
    const body = await request.json().catch(() => ({}));
    await delay(LATENCY);
    return HttpResponse.json({
      transactionId: "dep_" + Math.random().toString(36).slice(2, 8),
      status: "pending",
      redirectUrl: `https://pay.example/${body?.methodId || "method"}`,
    });
  }),

  http.post("/api/withdraw", async ({ request }) => {
    await request.json().catch(() => ({}));
    await delay(LATENCY);
    return HttpResponse.json({
      transactionId: "wd_" + Math.random().toString(36).slice(2, 8),
      status: "pending",
    });
  }),

  http.post("/api/sport/betslip", async ({ request }) => {
    const body = await request.json().catch(() => ({}));
    const selections = Array.isArray(body?.selections) ? body.selections : [];
    const stake = Number(body?.stake) || 0;
    const totalOdds = selections.reduce((acc, s) => acc * (Number(s.odds) || 1), 1);
    await delay(LATENCY);
    return HttpResponse.json({
      betId: "bet_" + Math.random().toString(36).slice(2, 8),
      status: "accepted",
      stake,
      totalOdds: Number(totalOdds.toFixed(2)),
      potentialWin: Number((stake * totalOdds).toFixed(2)),
    });
  }),
];
