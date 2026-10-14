import { createContext, useContext } from "react";

const DEFAULT_URL = "wss://test.splmn1.com/ws";
const AUTH_KEY = "park.auth";

export const parkAuth = () => {
  const q = query();
  if (q.get("login") && q.get("password")) return { login: q.get("login"), password: q.get("password") };
  if (q.get("user_id") && q.get("token")) return { userId: q.get("user_id"), token: q.get("token") };
  try {
    const raw = localStorage.getItem(AUTH_KEY);
    if (raw) {
      const a = JSON.parse(raw);
      if (a && ((a.login && a.password) || (a.userId && a.token))) return a;
    }
  } catch (e) {
    return null;
  }
  if (import.meta.env.VITE_PARK_USER_ID && import.meta.env.VITE_PARK_TOKEN) return { userId: import.meta.env.VITE_PARK_USER_ID, token: import.meta.env.VITE_PARK_TOKEN };
  return null;
};

export const saveParkAuth = (auth) => {
  try {
    localStorage.setItem(AUTH_KEY, JSON.stringify(auth));
  } catch (e) {
    return;
  }
};

export const clearParkAuth = () => {
  try {
    localStorage.removeItem(AUTH_KEY);
  } catch (e) {
    return;
  }
};

let selfId = null;
export const setParkSelfId = (id) => {
  if (id != null) selfId = String(id);
};
export const parkSelfId = () => selfId || parkConfig().userId;
const REQUEST_MS = 8000;
const RETRY_MS = [1000, 2000, 4000, 8000, 15000];

const query = () => (typeof window !== "undefined" ? new URLSearchParams(window.location.search) : new URLSearchParams());

export const parkMode = () => query().get("mode") || "";

export const parkServerEnabled = () => {
  const m = parkMode();
  return m !== "provider" && m !== "local";
};

const TAB_KEY = "park.userId";

const tabUserId = () => {
  try {
    const kept = sessionStorage.getItem(TAB_KEY);
    if (kept) return kept;
    const fresh = String(1000 + Math.floor(Math.random() * 9000));
    sessionStorage.setItem(TAB_KEY, fresh);
    return fresh;
  } catch (e) {
    return String(1000 + Math.floor(Math.random() * 9000));
  }
};

export const parkConfig = () => {
  const q = query();
  const auth = parkAuth();
  const userId = auth && auth.userId ? auth.userId : auth && auth.login ? auth.login : tabUserId();
  return {
    url: q.get("ws") || import.meta.env.VITE_PARK_WS_URL || DEFAULT_URL,
    auth,
    userId,
    token: auth && auth.token ? auth.token : `test-park-${userId}`,
  };
};

export class ParkError extends Error {
  constructor(code, message) {
    super(message || code);
    this.code = code;
  }
}

export class ParkSocket {
  constructor(cfg) {
    this.cfg = cfg;
    this.ws = null;
    this.status = "idle";
    this.waiters = [];
    this.listeners = new Map();
    this.autoPlays = new Map();
    this.retry = 0;
    this.closed = false;
    this.timer = 0;
  }

  get open() {
    return !!this.ws && this.ws.readyState === 1;
  }

  connect() {
    this.closed = false;
    clearTimeout(this.timer);
    if (this.ws) return;
    const { url, userId, token, auth } = this.cfg;
    const creds =
      auth && auth.login
        ? `login=${encodeURIComponent(auth.login)}&password=${encodeURIComponent(auth.password)}`
        : `user_id=${encodeURIComponent(userId)}&token=${encodeURIComponent(token)}`;
    const full = `${url}${url.includes("?") ? "&" : "?"}${creds}`;
    this.setStatus("connecting");
    let ws;
    try {
      ws = new WebSocket(full);
    } catch (e) {
      this.scheduleRetry();
      return;
    }
    this.ws = ws;
    ws.onopen = () => {
      this.retry = 0;
      this.setStatus("open");
    };
    ws.onmessage = (ev) => this.onMessage(ev.data);
    ws.onerror = () => {};
    ws.onclose = (ev) => {
      this.ws = null;
      this.flushWaiters(new ParkError("closed", "socket closed"));
      if (this.closed) {
        this.setStatus("closed");
        return;
      }
      if (ev.code === 4000) {
        this.setStatus("unauthorized");
        return;
      }
      if (ev.code === 4002 || /busy|already/i.test(String(ev.reason || ""))) {
        this.setStatus("busy");
        return;
      }
      this.scheduleRetry();
    };
  }

  scheduleRetry() {
    if (this.closed) return;
    this.setStatus("retry");
    const wait = RETRY_MS[Math.min(this.retry, RETRY_MS.length - 1)];
    this.retry += 1;
    clearTimeout(this.timer);
    this.timer = setTimeout(() => this.connect(), wait);
  }

  close() {
    this.closed = true;
    clearTimeout(this.timer);
    if (this.ws) {
      try {
        this.ws.close();
      } catch (e) {
        this.ws = null;
      }
    }
    this.flushWaiters(new ParkError("closed", "socket closed"));
    this.setStatus("closed");
  }

  setStatus(s) {
    this.status = s;
    console.info("[park] socket", s);
    this.emit("status", s);
  }

  on(action, fn) {
    if (!this.listeners.has(action)) this.listeners.set(action, new Set());
    this.listeners.get(action).add(fn);
    return () => this.listeners.get(action)?.delete(fn);
  }

  emit(action, msg) {
    const set = this.listeners.get(action);
    if (set) set.forEach((fn) => fn(msg));
    const all = this.listeners.get("*");
    if (all && action !== "status") all.forEach((fn) => fn(msg));
  }

  onMessage(raw) {
    let msg;
    console.info("[park] <-", typeof raw === "string" ? raw.slice(0, 400) : raw);
    try {
      msg = JSON.parse(raw);
    } catch (e) {
      return;
    }
    if (!msg || typeof msg !== "object") return;
    const action = msg.action;
    if (action === "game_play" && msg.auto === true) {
      if (msg.game_id) this.autoPlays.set(msg.game_id, msg);
      this.autoPlays.set("last", msg);
      this.emit("game_play", msg);
      return;
    }
    const idx = this.waiters.findIndex((w) => w.action === action || (!action && msg.error && w.action === "*"));
    if (idx >= 0) {
      const w = this.waiters.splice(idx, 1)[0];
      clearTimeout(w.timer);
      if (msg.status === "error") w.reject(new ParkError(msg.error || "error", msg.message));
      else w.resolve(msg);
      return;
    }
    if (!action && msg.error) {
      const w = this.waiters.shift();
      if (w) {
        clearTimeout(w.timer);
        w.reject(new ParkError("bad_action", msg.error));
      }
      return;
    }
    if (action) this.emit(action, msg);
  }

  flushWaiters(err) {
    const list = this.waiters.splice(0);
    list.forEach((w) => {
      clearTimeout(w.timer);
      w.reject(err);
    });
  }

  request(payload, ms = REQUEST_MS) {
    return new Promise((resolve, reject) => {
      if (!this.open) {
        reject(new ParkError("offline", "socket is not open"));
        return;
      }
      const w = { action: payload.action, resolve, reject, timer: 0 };
      w.timer = setTimeout(() => {
        const i = this.waiters.indexOf(w);
        if (i >= 0) this.waiters.splice(i, 1);
        reject(new ParkError("timeout", `${payload.action} timeout`));
      }, ms);
      this.waiters.push(w);
      try {
        console.info("[park] ->", JSON.stringify(payload));
        this.ws.send(JSON.stringify(payload));
      } catch (e) {
        const i = this.waiters.indexOf(w);
        if (i >= 0) this.waiters.splice(i, 1);
        clearTimeout(w.timer);
        reject(new ParkError("send", "send failed"));
      }
    });
  }

  takeAuto(gameId) {
    const m = this.autoPlays.get(gameId) || null;
    if (m) this.autoPlays.delete(gameId);
    return m;
  }

  waitAuto(gameId, ms = 3000) {
    const have = this.takeAuto(gameId);
    if (have) return Promise.resolve(have);
    return new Promise((resolve) => {
      let off = () => {};
      const t = setTimeout(() => {
        off();
        resolve(this.takeAuto(gameId));
      }, ms);
      off = this.on("game_play", (m) => {
        if (m.game_id && m.game_id !== gameId) return;
        clearTimeout(t);
        off();
        this.autoPlays.delete(gameId);
        resolve(m);
      });
    });
  }

  async play(gameId, payload) {
    try {
      return await this.request({ action: "game_play", game_id: gameId, ...payload });
    } catch (e) {
      if (e.code === "game_timeout" || e.code === "game_not_active") {
        const auto = await this.waitAuto(gameId);
        if (auto) return auto;
      }
      throw e;
    }
  }
}

export const ParkServerContext = createContext(null);

export const useParkServer = () => useContext(ParkServerContext);

export function useServerGame() {
  const server = useParkServer();
  if (!server || !server.client) return null;
  const client = server.client;
  return {
    start: (game) => client.request({ action: "game_start", game }),
    play: (gameId, payload) => client.play(gameId, payload),
    client,
  };
}
