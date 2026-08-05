import { createContext, useContext, useReducer, useState, useCallback, useMemo, useEffect } from "react";
import { COMMAND_TYPES } from "@/constants";
import { sendEvent, onCommand } from "@/services/eventBus";
import { api } from "@/api/endpoints";

const initialState = {
  user: null,
  balance: { main: 0, bonus: 0, coins: 0 },
  games: [],
  popularGames: [],
  categories: [],
  providers: [],
  promotions: [],
  matches: [],
  paymentMethods: [],
  favorites: new Set(),
  userBonuses: [],
  transactions: [],
  betSlip: [],
  activeGame: null,
  notice: null,
  route: "home",
  prevRoute: null,
  loading: true,
  error: null,
  gamesLoading: false,
  matchesLoading: false,
  bonusesLoading: false,
  transactionsLoading: false,
};

function appReducer(state, action) {
  switch (action.type) {
    case COMMAND_TYPES.SET_USER:
      return { ...state, user: action.payload };
    case COMMAND_TYPES.SET_BALANCE:
      return { ...state, balance: action.payload };
    case COMMAND_TYPES.SET_GAMES:
      return { ...state, games: action.payload };
    case COMMAND_TYPES.SET_POPULAR_GAMES:
      return { ...state, popularGames: action.payload };
    case COMMAND_TYPES.SET_CATEGORIES:
      return { ...state, categories: action.payload };
    case COMMAND_TYPES.SET_PROVIDERS:
      return { ...state, providers: action.payload };
    case COMMAND_TYPES.SET_PROMOTIONS:
      return { ...state, promotions: action.payload };
    case COMMAND_TYPES.SET_MATCHES:
      return { ...state, matches: action.payload };
    case COMMAND_TYPES.SET_PAYMENT_METHODS:
      return { ...state, paymentMethods: action.payload };
    case COMMAND_TYPES.SET_FAVORITES:
      return { ...state, favorites: new Set(action.payload) };
    case COMMAND_TYPES.TOGGLE_FAVORITE: {
      const next = new Set(state.favorites);
      next.has(action.payload) ? next.delete(action.payload) : next.add(action.payload);
      return { ...state, favorites: next };
    }
    case COMMAND_TYPES.SET_USER_BONUSES:
      return { ...state, userBonuses: action.payload };
    case COMMAND_TYPES.SET_TRANSACTIONS:
      return { ...state, transactions: action.payload };
    case COMMAND_TYPES.SET_BET_SLIP:
      return { ...state, betSlip: action.payload };
    case COMMAND_TYPES.SET_ACTIVE_GAME:
      return { ...state, activeGame: action.payload };
    case COMMAND_TYPES.SET_NOTICE:
      return { ...state, notice: action.payload };
    case COMMAND_TYPES.NAVIGATE:
      return { ...state, prevRoute: state.route, route: action.payload };
    case COMMAND_TYPES.SET_LOADING:
      return { ...state, loading: action.payload };
    case COMMAND_TYPES.SET_ERROR:
      return { ...state, error: action.payload };
    case COMMAND_TYPES.SET_GAMES_LOADING:
      return { ...state, gamesLoading: action.payload };
    case COMMAND_TYPES.SET_MATCHES_LOADING:
      return { ...state, matchesLoading: action.payload };
    case COMMAND_TYPES.SET_BONUSES_LOADING:
      return { ...state, bonusesLoading: action.payload };
    case COMMAND_TYPES.SET_TRANSACTIONS_LOADING:
      return { ...state, transactionsLoading: action.payload };
    default:
      return state;
  }
}

const AppContext = createContext(null);

export function AppProvider({ children }) {
  const [state, dispatch] = useReducer(appReducer, initialState);
  const [drawerOpen, setDrawerOpen] = useState(false);
  const [supportOpen, setSupportOpen] = useState(false);

  const handleCommand = useCallback(
    (cmd) => cmd?.type && COMMAND_TYPES[cmd.type] && dispatch(cmd),
    []
  );

  useEffect(() => onCommand(handleCommand), [handleCommand]);

  const setNotice = useCallback((notice) => {
    dispatch({ type: COMMAND_TYPES.SET_NOTICE, payload: notice });
  }, []);

  const bootstrap = useCallback(async () => {
    dispatch({ type: COMMAND_TYPES.SET_LOADING, payload: true });
    dispatch({ type: COMMAND_TYPES.SET_ERROR, payload: null });
    const safe = (p, fallback) => p.catch(() => fallback);
    const [user, balance, categories, providers, games, popularGames, promotions, paymentMethods, matches, favorites] =
      await Promise.all([
        safe(api.getUser(), null),
        safe(api.getBalance(), null),
        safe(api.getCategories(), []),
        safe(api.getProviders(), []),
        safe(api.getGames({ provider: "all", category: "popular" }), { items: [] }),
        safe(api.getPopularGames(), []),
        safe(api.getPromotions(), []),
        safe(api.getPaymentMethods(), []),
        safe(api.getMatches(), []),
        safe(api.getFavorites(), { ids: [] }),
      ]);
    if (user) dispatch({ type: COMMAND_TYPES.SET_USER, payload: user });
    if (balance) dispatch({ type: COMMAND_TYPES.SET_BALANCE, payload: balance });
    dispatch({ type: COMMAND_TYPES.SET_CATEGORIES, payload: categories });
    dispatch({ type: COMMAND_TYPES.SET_PROVIDERS, payload: providers });
    dispatch({ type: COMMAND_TYPES.SET_GAMES, payload: games?.items || [] });
    dispatch({ type: COMMAND_TYPES.SET_POPULAR_GAMES, payload: popularGames });
    dispatch({ type: COMMAND_TYPES.SET_PROMOTIONS, payload: promotions });
    dispatch({ type: COMMAND_TYPES.SET_MATCHES, payload: matches });
    dispatch({ type: COMMAND_TYPES.SET_PAYMENT_METHODS, payload: paymentMethods });
    dispatch({ type: COMMAND_TYPES.SET_FAVORITES, payload: favorites?.ids || [] });
    dispatch({ type: COMMAND_TYPES.SET_LOADING, payload: false });
  }, []);

  useEffect(() => {
    bootstrap();
  }, [bootstrap]);

  const loadGames = useCallback(async (params = {}) => {
    dispatch({ type: COMMAND_TYPES.SET_GAMES_LOADING, payload: true });
    try {
      const res = await api.getGames(params);
      dispatch({ type: COMMAND_TYPES.SET_GAMES, payload: res.items });
    } catch (err) {
      dispatch({ type: COMMAND_TYPES.SET_ERROR, payload: err.message });
    } finally {
      dispatch({ type: COMMAND_TYPES.SET_GAMES_LOADING, payload: false });
    }
  }, []);

  const loadMatches = useCallback(async (sport) => {
    dispatch({ type: COMMAND_TYPES.SET_MATCHES_LOADING, payload: true });
    try {
      const res = await api.getMatches(sport);
      dispatch({ type: COMMAND_TYPES.SET_MATCHES, payload: res });
    } catch (err) {
      dispatch({ type: COMMAND_TYPES.SET_ERROR, payload: err.message });
    } finally {
      dispatch({ type: COMMAND_TYPES.SET_MATCHES_LOADING, payload: false });
    }
  }, []);

  const refreshBalance = useCallback(async () => {
    try {
      const balance = await api.getBalance();
      dispatch({ type: COMMAND_TYPES.SET_BALANCE, payload: balance });
    } catch (err) {
      dispatch({ type: COMMAND_TYPES.SET_ERROR, payload: err.message });
    }
  }, []);

  const navigate = useCallback((to) => {
    setSupportOpen(false);
    setDrawerOpen(false);
    dispatch({ type: COMMAND_TYPES.NAVIGATE, payload: to });
    sendEvent("NAVIGATION", { to });
  }, []);

  const toggleFavorite = useCallback(async (gameId) => {
    dispatch({ type: COMMAND_TYPES.TOGGLE_FAVORITE, payload: gameId });
    sendEvent("TOGGLE_FAVORITE", { gameId });
    try {
      await api.toggleFavorite(gameId);
    } catch {
      dispatch({ type: COMMAND_TYPES.TOGGLE_FAVORITE, payload: gameId });
    }
  }, []);

  const openGame = useCallback(async (gameId, mode = "real") => {
    dispatch({ type: COMMAND_TYPES.SET_ACTIVE_GAME, payload: { gameId, mode, loading: true } });
    dispatch({ type: COMMAND_TYPES.NAVIGATE, payload: "game" });
    try {
      const res = await api.launchGame(gameId, mode);
      dispatch({ type: COMMAND_TYPES.SET_ACTIVE_GAME, payload: { ...res, loading: false } });
    } catch (err) {
      dispatch({ type: COMMAND_TYPES.SET_ACTIVE_GAME, payload: { gameId, mode, loading: false, error: err.message } });
    }
  }, []);

  const closeGame = useCallback(() => {
    dispatch({ type: COMMAND_TYPES.SET_ACTIVE_GAME, payload: null });
    dispatch({ type: COMMAND_TYPES.NAVIGATE, payload: "games" });
  }, []);

  const activatePromo = useCallback(async (promoId) => {
    try {
      const res = await api.activatePromotion(promoId);
      setNotice({ kind: "success", text: res.message || "Promotion activated" });
    } catch (err) {
      setNotice({ kind: "error", text: err.message });
    }
  }, [setNotice]);

  const loadUserBonuses = useCallback(async () => {
    dispatch({ type: COMMAND_TYPES.SET_BONUSES_LOADING, payload: true });
    try {
      const res = await api.getUserBonuses();
      dispatch({ type: COMMAND_TYPES.SET_USER_BONUSES, payload: res });
    } catch (err) {
      dispatch({ type: COMMAND_TYPES.SET_ERROR, payload: err.message });
    } finally {
      dispatch({ type: COMMAND_TYPES.SET_BONUSES_LOADING, payload: false });
    }
  }, []);

  const activateBonus = useCallback(async (bonusId) => {
    try {
      await api.activateBonus(bonusId);
      await loadUserBonuses();
      setNotice({ kind: "success", text: "Bonus activated" });
    } catch (err) {
      setNotice({ kind: "error", text: err.message });
    }
  }, [loadUserBonuses, setNotice]);

  const cancelBonus = useCallback(async (bonusId) => {
    try {
      await api.cancelBonus(bonusId);
      await loadUserBonuses();
      setNotice({ kind: "success", text: "Bonus canceled" });
    } catch (err) {
      setNotice({ kind: "error", text: err.message });
    }
  }, [loadUserBonuses, setNotice]);

  const loadTransactions = useCallback(async (type = "all") => {
    dispatch({ type: COMMAND_TYPES.SET_TRANSACTIONS_LOADING, payload: true });
    try {
      const res = await api.getTransactions(type);
      dispatch({ type: COMMAND_TYPES.SET_TRANSACTIONS, payload: res.items });
    } catch (err) {
      dispatch({ type: COMMAND_TYPES.SET_ERROR, payload: err.message });
    } finally {
      dispatch({ type: COMMAND_TYPES.SET_TRANSACTIONS_LOADING, payload: false });
    }
  }, []);

  const cancelTransaction = useCallback(async (id) => {
    try {
      await api.cancelTransaction(id);
      await loadTransactions();
      setNotice({ kind: "success", text: "Transaction canceled" });
    } catch (err) {
      setNotice({ kind: "error", text: err.message });
    }
  }, [loadTransactions, setNotice]);

  const submitDeposit = useCallback(async (methodId, amount) => {
    try {
      const res = await api.deposit(methodId, amount);
      setNotice({ kind: "success", text: "Deposit created" });
      return res;
    } catch (err) {
      setNotice({ kind: "error", text: err.message });
      return null;
    }
  }, [setNotice]);

  const submitWithdraw = useCallback(async (methodId, amount, requisites) => {
    try {
      const res = await api.withdraw(methodId, amount, requisites);
      setNotice({ kind: "success", text: "Withdrawal requested" });
      return res;
    } catch (err) {
      setNotice({ kind: "error", text: err.message });
      return null;
    }
  }, [setNotice]);

  const addSelection = useCallback((selection) => {
    const next = state.betSlip.filter((s) => s.matchId !== selection.matchId);
    next.push(selection);
    dispatch({ type: COMMAND_TYPES.SET_BET_SLIP, payload: next });
  }, [state.betSlip]);

  const removeSelection = useCallback((matchId) => {
    dispatch({ type: COMMAND_TYPES.SET_BET_SLIP, payload: state.betSlip.filter((s) => s.matchId !== matchId) });
  }, [state.betSlip]);

  const clearSlip = useCallback(() => {
    dispatch({ type: COMMAND_TYPES.SET_BET_SLIP, payload: [] });
  }, []);

  const placeBet = useCallback(async (stake) => {
    try {
      const res = await api.placeBet(state.betSlip, stake);
      dispatch({ type: COMMAND_TYPES.SET_BET_SLIP, payload: [] });
      setNotice({ kind: "success", text: `Bet accepted, potential win ${res.potentialWin}` });
      return res;
    } catch (err) {
      setNotice({ kind: "error", text: err.message });
      return null;
    }
  }, [state.betSlip, setNotice]);

  const value = useMemo(
    () => ({
      ...state,
      dispatch,
      sendEvent,
      navigate,
      toggleFavorite,
      loadGames,
      loadMatches,
      refreshBalance,
      openGame,
      closeGame,
      activatePromo,
      loadUserBonuses,
      activateBonus,
      cancelBonus,
      loadTransactions,
      cancelTransaction,
      submitDeposit,
      submitWithdraw,
      addSelection,
      removeSelection,
      clearSlip,
      placeBet,
      setNotice,
      retry: bootstrap,
      drawerOpen,
      setDrawerOpen,
      supportOpen,
      setSupportOpen,
    }),
    [state, navigate, toggleFavorite, loadGames, loadMatches, refreshBalance, openGame, closeGame, activatePromo, loadUserBonuses, activateBonus, cancelBonus, loadTransactions, cancelTransaction, submitDeposit, submitWithdraw, addSelection, removeSelection, clearSlip, placeBet, setNotice, bootstrap, drawerOpen, supportOpen]
  );

  return <AppContext.Provider value={value}>{children}</AppContext.Provider>;
}

export function useApp() {
  const ctx = useContext(AppContext);
  if (!ctx) throw new Error("useApp must be inside AppProvider");
  return ctx;
}
