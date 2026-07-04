import { apiClient } from "./client";

function toQuery(params) {
  const entries = Object.entries(params).filter(
    ([, v]) => v !== undefined && v !== null && v !== ""
  );
  if (!entries.length) return "";
  return "?" + new URLSearchParams(entries).toString();
}

export const api = {
  getUser: () => apiClient.get("/user"),
  getBalance: () => apiClient.get("/balance"),
  getProviders: () => apiClient.get("/providers"),
  getCategories: () => apiClient.get("/categories"),
  getGames: (params = {}) => apiClient.get(`/games${toQuery(params)}`),
  getPopularGames: () => apiClient.get("/games/popular"),
  getFavorites: () => apiClient.get("/favorites"),
  toggleFavorite: (gameId) => apiClient.post(`/favorites/${gameId}/toggle`),
  getPromotions: () => apiClient.get("/promotions"),
  activatePromotion: (promoId) => apiClient.post(`/promotions/${promoId}/activate`),
  getPaymentMethods: (type = "deposit") => apiClient.get(`/payment-methods${toQuery({ type })}`),
  getPaymentRequisites: (methodId) => apiClient.get(`/payment-methods/${methodId}/requisites`),
  getMatches: (sport = "soccer") => apiClient.get(`/sport/matches${toQuery({ sport })}`),
  launchGame: (gameId, mode = "real") => apiClient.post(`/games/${gameId}/launch`, { mode }),
  getUserBonuses: () => apiClient.get("/user/bonuses"),
  activateBonus: (bonusId) => apiClient.post(`/user/bonuses/${bonusId}/activate`),
  cancelBonus: (bonusId) => apiClient.post(`/user/bonuses/${bonusId}/cancel`),
  getTransactions: (type = "all", page = 1) => apiClient.get(`/user/transactions${toQuery({ type, page })}`),
  cancelTransaction: (id) => apiClient.post(`/user/transactions/${id}/cancel`),
  deposit: (methodId, amount) => apiClient.post("/deposit", { methodId, amount }),
  withdraw: (methodId, amount, requisites) => apiClient.post("/withdraw", { methodId, amount, requisites }),
  placeBet: (selections, stake) => apiClient.post("/sport/betslip", { selections, stake }),
};
