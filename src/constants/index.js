export const CATEGORIES = [
  { id: "popular", nameKey: "cat.popular", icon: "★" },
  { id: "new", nameKey: "cat.new", icon: "✷" },
  { id: "theatre", nameKey: "cat.theatre", icon: "♛" },
  { id: "opera", nameKey: "cat.opera", icon: "♫" },
  { id: "kingdom", nameKey: "cat.kingdom", icon: "♚" },
  { id: "roulette", nameKey: "cat.roulette", icon: "◎" },
  { id: "cards", nameKey: "cat.cards", icon: "♣" },
  { id: "live", nameKey: "cat.live", icon: "●" },
  { id: "jackpot", nameKey: "cat.jackpot", icon: "⚜" },
  { id: "crash", nameKey: "cat.crash", icon: "↑" },
  { id: "bonus", nameKey: "cat.bonus", icon: "❖" },
];

export const PROVIDERS = [
  { id: "all", name: "All", gameCount: 1248, categories: CATEGORIES.map((c) => c.id) },
  { id: "pragmatic", name: "Pragmatic", gameCount: 287, categories: ["popular", "new", "theatre", "kingdom", "crash", "bonus"] },
  { id: "netent", name: "NetEnt", gameCount: 195, categories: ["popular", "new", "opera", "kingdom", "jackpot"] },
  { id: "playngo", name: "Play'n GO", gameCount: 162, categories: ["popular", "theatre", "opera", "bonus"] },
  { id: "evolution", name: "Evolution", gameCount: 134, categories: ["popular", "live", "roulette", "cards"] },
  { id: "bgaming", name: "BGaming", gameCount: 118, categories: ["popular", "new", "crash", "jackpot"] },
  { id: "yggdrasil", name: "Yggdrasil", gameCount: 98, categories: ["popular", "kingdom", "jackpot", "bonus"] },
  { id: "spinomenal", name: "Spinomenal", gameCount: 86, categories: ["popular", "theatre", "opera"] },
  { id: "habanero", name: "Habanero", gameCount: 77, categories: ["popular", "new", "cards"] },
];

export const GAME_NAMES = [
  "Fortune Tiger", "Fortune Dragon", "Fortune Rabbit", "Treasure Aztec", "Sweet Bonanza",
  "Gates of Olympus", "Mahjong Ways", "Aviator", "Dog House", "Wild West Gold",
  "Book of Ra", "Big Bass Splash", "Crazy Time", "Wolf Gold", "Starburst", "Mega Moolah",
  "Lucky Lady", "Lightning Roulette", "Blackjack Pro", "Live Baccarat", "Crash X",
  "Pinata Wins", "Doraemon", "Labubu", "Fortune Mouse", "Fortune Ox", "Lucky Crown",
  "Hall of Kings", "Opera Nights", "Royal Theatre", "Crown Jewels", "Maestro Spin",
  "Phantom Stage", "Velvet Curtain", "Throne of Gold", "Ace of Spades",
];

export const PALETTES = [
  ["#5b2a0e", "#1a0608"], ["#6a1530", "#1a0608"], ["#1a3a6e", "#06121e"],
  ["#2a5e2a", "#061a0a"], ["#7a2020", "#1a0408"], ["#7a4a10", "#1a0a04"],
  ["#7a1a4a", "#1a0410"], ["#5a4a1a", "#1a1404"], ["#1a4a5a", "#041014"],
  ["#3a1a6e", "#0a041a"], ["#6e3a1a", "#1a0a04"], ["#5a1a3a", "#14040a"],
];

export const POPULAR_GAMES = [
  { id: "pg-1", name: "Fortune Tiger", colors: ["#7a1f1f", "#1a0608"] },
  { id: "pg-2", name: "Fortune Dragon", colors: ["#7a4a10", "#1a0a04"] },
  { id: "pg-3", name: "Treasure", colors: ["#3a1a6e", "#0a041a"] },
  { id: "pg-4", name: "Pinata Wins", colors: ["#7a1a4a", "#1a0410"] },
  { id: "pg-5", name: "Sweet Bonanza", colors: ["#6a1530", "#1a0608"] },
  { id: "pg-6", name: "Olympus", colors: ["#1a3a6e", "#06121e"] },
  { id: "pg-7", name: "Mahjong", colors: ["#2a5e2a", "#061a0a"] },
  { id: "pg-8", name: "Aviator", colors: ["#5a1a3a", "#14040a"] },
  { id: "pg-9", name: "Dog Tycoon", colors: ["#5a4a1a", "#1a1404"] },
  { id: "pg-10", name: "Wild West", colors: ["#7a3a1a", "#1a0a04"] },
];

export const PROMOTIONS = [
  { id: "p1", badge: "TOURNAMENT", title: "Drops & Wins", description: "€2,000,000 monthly prize pool across selected slots", ctaKey: "promo.join" },
  { id: "p2", badge: "WEEKLY", title: "Cashback 15%", description: "Get back 15% of your losses every Monday", ctaKey: "promo.activate" },
  { id: "p3", badge: "NEW", title: "Friday Free Spins", description: "100 free spins every Friday on top games", ctaKey: "promo.claim" },
  { id: "p4", badge: "VIP", title: "High-Roller Race", description: "Compete with top players for the €50,000 prize", ctaKey: "promo.enter" },
  { id: "p5", badge: "EVENT", title: "Summer 2026", description: "Daily missions, unique drops, exclusive rewards", ctaKey: "promo.seeMore" },
];

export const SPORTS_TABS = [
  { id: "soccer", name: "Soccer", icon: "⚽" },
  { id: "tennis", name: "Tennis", icon: "🎾" },
  { id: "basket", name: "Basket", icon: "🏀" },
  { id: "mma", name: "MMA", icon: "🥊" },
  { id: "hockey", name: "Hockey", icon: "🏒" },
  { id: "nfl", name: "NFL", icon: "🏈" },
];

export const MATCHES = [
  { id: "m1", league: "UEFA Champions League", teamA: "Real Madrid", teamB: "Bayern", odds: { home: 1.85, draw: 3.40, away: 4.20 } },
  { id: "m2", league: "Premier League", teamA: "Arsenal", teamB: "Man City", odds: { home: 2.10, draw: 3.30, away: 3.20 } },
  { id: "m3", league: "La Liga", teamA: "Barcelona", teamB: "Atlético", odds: { home: 1.55, draw: 3.80, away: 5.50 } },
  { id: "m4", league: "Serie A", teamA: "Inter", teamB: "Milan", odds: { home: 2.05, draw: 3.30, away: 3.40 } },
  { id: "m5", league: "Bundesliga", teamA: "Dortmund", teamB: "Leipzig", odds: { home: 2.40, draw: 3.25, away: 2.85 } },
];

export const PAYMENT_METHODS = [
  { id: "crypto", name: "Crypto", type: "crypto", minDeposit: 250, maxDeposit: 20000000, commission: 0 },
  { id: "visa", name: "Visa P2P", type: "card", minDeposit: 4000, maxDeposit: 200000, commission: 0 },
  { id: "kaspi", name: "Kaspi.kz", type: "bank", minDeposit: 4000, maxDeposit: 200000, commission: 0 },
];

export const MENU_ITEMS = [
  { id: "vip", icon: "★", labelKey: "menu.vip" },
  { id: "prizes", icon: "🎁", labelKey: "menu.prizes" },
  { id: "bonuses", icon: "⚡", labelKey: "menu.bonuses" },
  { id: "magnet", icon: "💳", labelKey: "menu.magnet" },
  { id: "drop", icon: "💧", labelKey: "menu.drop" },
  { id: "fame", icon: "👑", labelKey: "menu.fame" },
  { id: "tournaments", icon: "🏆", labelKey: "menu.tournaments" },
  { id: "fortpoint", icon: "🎯", labelKey: "menu.fortpoint" },
  { id: "legends", icon: "♣", labelKey: "menu.legends" },
  { id: "ladies", icon: "♠", labelKey: "menu.ladies" },
  { id: "support", icon: "🛟", labelKey: "menu.support" },
];

export const COMMAND_TYPES = {
  SET_USER: "SET_USER",
  SET_BALANCE: "SET_BALANCE",
  SET_GAMES: "SET_GAMES",
  SET_POPULAR_GAMES: "SET_POPULAR_GAMES",
  SET_CATEGORIES: "SET_CATEGORIES",
  SET_PROVIDERS: "SET_PROVIDERS",
  SET_PROMOTIONS: "SET_PROMOTIONS",
  SET_MATCHES: "SET_MATCHES",
  SET_PAYMENT_METHODS: "SET_PAYMENT_METHODS",
  SET_FAVORITES: "SET_FAVORITES",
  TOGGLE_FAVORITE: "TOGGLE_FAVORITE",
  NAVIGATE: "NAVIGATE",
  SET_LOADING: "SET_LOADING",
  SET_ERROR: "SET_ERROR",
  SET_GAMES_LOADING: "SET_GAMES_LOADING",
  SET_MATCHES_LOADING: "SET_MATCHES_LOADING",
  SET_ACTIVE_GAME: "SET_ACTIVE_GAME",
  SET_USER_BONUSES: "SET_USER_BONUSES",
  SET_BONUSES_LOADING: "SET_BONUSES_LOADING",
  SET_TRANSACTIONS: "SET_TRANSACTIONS",
  SET_TRANSACTIONS_LOADING: "SET_TRANSACTIONS_LOADING",
  SET_BET_SLIP: "SET_BET_SLIP",
  SET_NOTICE: "SET_NOTICE",
};
