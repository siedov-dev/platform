import asyncio
import websockets
import json
import urllib.parse
import random
import uuid
import time
import sqlite3

# --- КОНФИГ ЭКОНОМИКИ И ПАРКА ---
CONFIG = {
    "SPIN_COST": 1,             # Стоимость одного спина (в монетах)
    "TICKET_COST": 1,           # Стоимость открытия ячейки на поле (в билетах)
    "SAFE_BANK_START": 5,       # Стартовое значение желтой колбы (банка)
    "COIN_PRICE": 10.0,         # Цена одной монеты
}

# Путь к нашей базе данных
DB_PATH = '/var/www/www-root/data/www/test.splmn1.com/service/ksocket/casino.db'

# --- ГЛОБАЛЬНОЕ СОСТОЯНИЕ ---
USERS = {}
ACTIVE_GAMES = {}
CLIENTS = {}            # ws -> user_id
CLIENT_LOCKS = {}       # ws -> asyncio.Lock()
DISCONNECT_TIMERS = {}  # user_id -> asyncio.Task

SYMBOLS = ["ice", "hat", "cocktail", "key", "mask", "castle", "crown", "coin", "crystal"]
PAY_TABLE = {"crown": 100, "castle": 50, "mask": 15, "key": 10, "cocktail": 5, "ice": 3}
GAMES = ["coinhand", "coinflip", "matches", "cards", "pyramid", "wheel"]

PYRAMID_ITEMS = {"castle": 100, "shot": 30, "burger": 15, "wheel": 12, "carousel": 10}
PYRAMID_POOL = (
    ["castle"] * 5 +    
    ["carousel"] * 10 + 
    ["wheel"] * 15 +     
    ["burger"] * 30 +    
    ["shot"] * 40        
)

def generate_wheel_pool():
    pool = [1, 2, 2, 2, 3, 3, 3, 5]
    random.shuffle(pool)
    return pool

def generate_field_deck():
    deck = []
    deck.extend([{"outcome": "energy", "value": 0, "game": None, "win": None}] * 442)
    deck.extend([{"outcome": "money", "value": random.randint(50, 499), "game": None, "win": None} for _ in range(29)])
    
    cash_vals = [100]*1 + [50]*1 + [25]*2 + [15]*3 + [13]*4 + [10]*20 + [8]*27 + [7]*11 + [5]*14
    deck.extend([{"outcome": "cash", "value": v, "game": None, "win": None} for v in cash_vals])
    
    for g in ["coinhand", "coinflip", "matches"]:
        deck.append({"outcome": "game", "value": 0, "game": g, "win": True})
        deck.append({"outcome": "game", "value": 0, "game": g, "win": False})
        
    deck.extend([{"outcome": "game", "value": 0, "game": "cards", "win": None}] * 55)
    deck.extend([{"outcome": "game", "value": 0, "game": "pyramid", "win": None}] * 17)
    deck.extend([{"outcome": "game", "value": 0, "game": "wheel", "win": None}] * 8)
    
    random.shuffle(deck)
    return deck

def generate_shared_cards():
    ranks = {"2":2, "3":3, "4":4, "5":5, "6":6, "7":7, "8":8, "9":9, "10":10, "Jack":10, "Queen":10, "King":10, "Ace":11}
    suits = ["Spades", "Hearts", "Diamonds", "Clubs"]
    deck = [f"{r}_{s}" for r in ranks.keys() for s in suits]
    deck.extend(["Ace_Gold", "Joker_Red", "Joker_Dark"])
    random.shuffle(deck)
    return deck

def get_card_prize(card_name):
    if card_name == "Ace_Gold": return 100
    if card_name in ["Joker_Red", "Joker_Dark"]: return 50
    rank = card_name.split("_")[0]
    ranks = {"2":2, "3":3, "4":4, "5":5, "6":6, "7":7, "8":8, "9":9, "10":10, "Jack":10, "Queen":10, "King":10, "Ace":11}
    return ranks.get(rank, 0)

def get_carousel_outcome(current_cars):
    roll = random.randint(0, 65535)
    if current_cars == 0:
        if roll <= 920: return 100, 0
        elif roll <= 17116: return 50, 1
        else: return 25, 2
    elif current_cars == 1:
        if roll <= 2864: return 100, 0
        elif roll <= 29006: return 50, 1
        else: return 25, 2
    elif current_cars == 2:
        if roll <= 5282: return 100, 0
        elif roll <= 41176: return 50, 1
        else: return 25, 2
    elif current_cars == 3:
        if roll <= 11317: return 100, 0
        elif roll <= 44965: return 50, 1
        else: return 25, 2
    elif current_cars == 4:
        if roll <= 22154: return 100, 0
        elif roll <= 50974: return 50, 1
        else: return 25, 2
    else:
        if roll <= 31105: return 100, 0
        elif roll <= 36197: return 50, 1
        else: return 25, 2

def get_wheel_bonus_outcome():
    roll = random.randint(0, 65535)
    if roll <= 30500: return 200
    elif roll <= 60944: return 100
    else: return 500

def get_spin_outcome():
    n = random.randint(0, 65535)
    if 0 <= n <= 881: outcome = 'BONUS'
    elif 882 <= n <= 887: outcome = 'WIN_200'
    elif 888 <= n <= 922: outcome = 'WIN_100'
    elif 923 <= n <= 992: outcome = 'WIN_50'
    elif 993 <= n <= 1481: outcome = 'WIN_15'
    elif 1482 <= n <= 2258: outcome = 'WIN_10'
    elif 2259 <= n <= 2999: outcome = 'WIN_5'
    elif 3000 <= n <= 4499: outcome = 'WIN_3'
    elif 4500 <= n <= 7761: outcome = 'WIN_2'
    elif 7762 <= n <= 13761: outcome = 'WIN_1'
    else: outcome = 'LOSE'
    
    sym_map = {
        'WIN_100': 'crown', 'WIN_50': 'castle',
        'WIN_15': 'mask', 'WIN_10': 'key', 'WIN_5': 'cocktail', 'WIN_3': 'ice'
    }
    
    pool = ["ice", "cocktail", "key", "mask", "castle", "crown"]
    reels = []
    
    if outcome == 'BONUS':
        fillers = random.sample(pool, 2)
        reels = [fillers[0], fillers[1], "crystal"]
    elif outcome == 'LOSE':
        loss_type = random.choice(["3_diff", "2_same"])
        if loss_type == "3_diff": reels = random.sample(pool, 3)
        else:
            syms = random.sample(pool, 2)
            reels = [syms[0], syms[0], syms[1]]
    elif outcome == 'WIN_2':
        fillers = random.sample(pool, 1)
        reels = ["coin", "coin", fillers[0]]
    elif outcome == 'WIN_1':
        fillers = random.sample(pool, 2)
        reels = ["coin", fillers[0], fillers[1]]
    elif outcome == 'WIN_200':
        reels = ["hat", "hat", "hat"]
    else:
        win_sym = sym_map[outcome]
        win_type = random.choice(["3_pure", "2_pure_1_hat", "1_pure_2_hat"])
        if win_type == "3_pure": reels = [win_sym, win_sym, win_sym]
        elif win_type == "2_pure_1_hat": reels = [win_sym, win_sym, "hat"]
        elif win_type == "1_pure_2_hat": reels = [win_sym, "hat", "hat"]
            
    random.shuffle(reels)
    return reels

def calculate_spin(reels):
    counts = {s: reels.count(s) for s in set(reels)}
    hats = counts.get("hat", 0)
    coins = counts.get("coin", 0)
    crystals = counts.get("crystal", 0)
    
    if hats == 3: return 200, crystals  
        
    max_pay = 0
    for s, count in counts.items():
        if s in ["hat", "crystal", "coin"]: continue
        if count + hats >= 3:
            val = PAY_TABLE.get(s, 0)
            if val > max_pay: max_pay = val
                
    coin_pay = (coins + hats) if (coins > 0 or hats > 0) else 0
    return max(max_pay, coin_pay), crystals

# ЕДИНОЕ СОСТОЯНИЕ КЛАСТЕРА (Общее поле и общие сейфы на весь стол)
GLOBAL_LOBBY_STATE = {
    "energy": 0,
    "safe": 0,
    "safe_bank": CONFIG["SAFE_BANK_START"],
    "crystals": 0,
    "cars_total": 0,
    "cells": {},
    "deck": generate_field_deck(),
    "cards_deck": generate_shared_cards(),
    "cards_opened": {},
    "is_processing_bonus": False
}

SECTORS_STATE = {
    i: { "wheel_pool": generate_wheel_pool() } for i in range(1, 8)
}

def get_sector_counts():
    counts = {i: 0 for i in range(1, 8)}
    for u in USERS.values():
        if u.get("sector") is not None:
            counts[u["sector"]] += 1
    return counts

def build_lobby_state():
    players = []
    active_uids = set(CLIENTS.values())
    for uid in active_uids:
        u_state = USERS.get(uid, {})
        players.append({
            "user_id": int(uid) if str(uid).isdigit() else uid,
            "name": f"Player_{uid}",
            "avatar": "",
            "sector": u_state.get("sector")
        })
    
    return {
        "action": "lobby",
        "players": players,
        "sectors": get_sector_counts(),
        "crystals": GLOBAL_LOBBY_STATE["crystals"],
        "cars_total": GLOBAL_LOBBY_STATE["cars_total"]
    }

def get_state(user_id):
    if user_id not in USERS:
        USERS[user_id] = {
            "coins": 500, "tickets": 1000, "cash": 999,
            "coin_fill": 0.0,
            "sector": None,
            "last_wheel_sector": 0,
            "bonus_until": 0,
            "next_spin_allow": 0,
            "spin_lock": False,
            "last_activity": time.time(),
            "is_db_user": False
        }
    return USERS[user_id]

def get_wheel_sector(prize, last_sector):
    opts = []
    if prize == 5: return 1
    elif prize == 1: return 5
    elif prize == 3: opts = [2, 4, 7]
    elif prize == 2: opts = [3, 6, 8]
    else: return 1
    if last_sector in opts and len(opts) > 1: opts.remove(last_sector)
    return random.choice(opts)

def add_coin_fill(state, amount):
    state["coin_fill"] += amount
    if state["coin_fill"] >= 1.0:
        extra = int(state["coin_fill"])
        state["coins"] += extra
        state["coin_fill"] = round(state["coin_fill"] - extra, 2)
    else:
        state["coin_fill"] = round(state["coin_fill"], 2)

async def push_balance(ws, lock, state):
    async with lock:
        await ws.send(json.dumps({
            "action": "balance", "coins": state["coins"], "tickets": state["tickets"],
            "cash": state["cash"], "energy": GLOBAL_LOBBY_STATE["energy"], "safe": GLOBAL_LOBBY_STATE["safe"],
            "safe_bank": GLOBAL_LOBBY_STATE["safe_bank"], "coin_fill": state["coin_fill"], 
            "crystals": GLOBAL_LOBBY_STATE["crystals"], "cars_total": GLOBAL_LOBBY_STATE["cars_total"]
        }))

async def broadcast_lobby():
    base_state = build_lobby_state()
    for ws, uid in list(CLIENTS.items()):
        try:
            u_state = USERS.get(uid, {})
            personalized = base_state.copy()
            personalized["sector"] = u_state.get("sector")
            
            lock = CLIENT_LOCKS.get(ws)
            if lock:
                async with lock:
                    await ws.send(json.dumps(personalized))
        except: pass

async def broadcast_park_state(user_id):
    msg = json.dumps({
        "action": "park_state",
        "crystals": GLOBAL_LOBBY_STATE["crystals"],
        "cars_total": GLOBAL_LOBBY_STATE["cars_total"],
        "user_id": int(user_id) if str(user_id).isdigit() else user_id
    })
    for ws in list(CLIENTS.keys()):
        try: await ws.send(msg)
        except: pass

async def broadcast_field_state(sector, cell_data, user_id):
    msg = json.dumps({
        "action": "field_state", "sector": sector,
        "energy": GLOBAL_LOBBY_STATE["energy"], "safe": GLOBAL_LOBBY_STATE["safe"],
        "safe_bank": GLOBAL_LOBBY_STATE["safe_bank"],
        "cell_id": cell_data["cell_id"], "user_id": user_id, "outcome": cell_data["outcome"],
        "game": cell_data.get("game"), "value": cell_data.get("value", 0)
    })
    for ws in list(CLIENTS.keys()):
        try: await ws.send(msg)
        except: pass

async def broadcast_bank(sector, user_id, energy_used, coins_added, payout_cash):
    msg = json.dumps({
        "action": "bank", "sector": sector, "user_id": user_id,
        "energy_used": energy_used, "coins_added": coins_added,
        "payout_cash": payout_cash, "safe_bank": GLOBAL_LOBBY_STATE["safe_bank"]
    })
    for ws in list(CLIENTS.keys()):
        try: await ws.send(msg)
        except: pass

async def broadcast_field_reset(sector):
    msg = json.dumps({"action": "field_reset", "sector": sector})
    for ws in list(CLIENTS.keys()):
        try: await ws.send(msg)
        except: pass

async def background_game_worker():
    while True:
        await asyncio.sleep(1)
        now = time.time()
        
        expired_games = [gid for gid, g in ACTIVE_GAMES.items() if now > g.get("deadline", now + 999)]
        for game_id in expired_games:
            g_state = ACTIVE_GAMES.pop(game_id, None)
            if not g_state: continue
            
            user_id = g_state["user_id"]
            game_name = g_state["game"]
            state = get_state(user_id)
            
            user_ws = None
            for ws, uid in CLIENTS.items():
                if uid == user_id:
                    user_ws = ws
                    break
            
            resp = {"action": "game_play", "status": "ok", "game": game_name, "auto": True}
            
            if game_name in ["coinhand", "coinflip", "matches"]:
                win = g_state.get("win", False)
                if win: state["cash"] += 50
                else:
                    GLOBAL_LOBBY_STATE["energy"] += 1
                    GLOBAL_LOBBY_STATE["safe_bank"] += 1
                    add_coin_fill(state, 0.01)
                resp.update({"choice": 0, "win": win, "prize": 50 if win else 0, "cash": state["cash"], "safe_bank": GLOBAL_LOBBY_STATE["safe_bank"]})
                
            elif game_name == "cards":
                available = [i for i in range(55) if i not in GLOBAL_LOBBY_STATE["cards_opened"]]
                if available:
                    choice = random.choice(available)
                    card_name = GLOBAL_LOBBY_STATE["cards_deck"][choice]
                    prize = get_card_prize(card_name)
                    GLOBAL_LOBBY_STATE["cards_opened"][choice] = {"card": card_name, "prize": prize}
                    state["cash"] += prize
                    resp.update({"choice": choice, "card": card_name, "prize": prize, "cash": state["cash"]})
                    
                    uid_int = int(user_id) if str(user_id).isdigit() else 1
                    card_msg = json.dumps({"action": "card_opened", "user_id": uid_int, "choice": choice, "card": card_name, "prize": prize})
                    for c_ws in list(CLIENTS.keys()):
                        try: await c_ws.send(card_msg)
                        except: pass
                        
                    if len(GLOBAL_LOBBY_STATE["cards_opened"]) >= 55:
                        GLOBAL_LOBBY_STATE["cards_deck"] = generate_shared_cards()
                        GLOBAL_LOBBY_STATE["cards_opened"].clear()
                        reset_msg = json.dumps({"action": "cards_reset"})
                        for c_ws in list(CLIENTS.keys()):
                            try: await c_ws.send(reset_msg)
                            except: pass

            elif game_name == "pyramid":
                prize = g_state.get("current_prize", 0)
                state["cash"] += prize
                resp.update({"done": True, "prize": prize, "cash": state["cash"]})
                
            elif game_name == "wheel":
                sector = state["sector"] if state["sector"] is not None else 1
                wheel_pool = SECTORS_STATE[sector]["wheel_pool"]
                if not wheel_pool:
                    SECTORS_STATE[sector]["wheel_pool"] = generate_wheel_pool()
                    wheel_pool = SECTORS_STATE[sector]["wheel_pool"]
                prize = wheel_pool.pop()
                state["tickets"] += prize
                w_sector = get_wheel_sector(prize, state["last_wheel_sector"])
                state["last_wheel_sector"] = w_sector
                resp.update({"game": "wheel", "sector": w_sector, "prize": prize, "tickets": state["tickets"]})

            if user_ws:
                lock = CLIENT_LOCKS.get(user_ws)
                if lock:
                    async with lock:
                        try: await user_ws.send(json.dumps(resp))
                        except: pass
                await push_balance(user_ws, lock, state)

async def handler(websocket):
    bg_tasks = set()
    send_lock = asyncio.Lock()
    CLIENT_LOCKS[websocket] = send_lock
    
    async def safe_send(data_dict):
        async with send_lock:
            await websocket.send(json.dumps(data_dict))
            
    try:
        path = websocket.request.path
        query_params = urllib.parse.parse_qs(urllib.parse.urlparse(path).query)
        
        login = query_params.get("login", [""])[0]
        password = query_params.get("password", [""])[0]
        user_id_qs = query_params.get("user_id", [""])[0]
        token = query_params.get("token", [""])[0]
        
        # --- ГИБРИДНАЯ АВТОРИЗАЦИЯ ---
        if login and password:
            try:
                conn = sqlite3.connect(DB_PATH)
                c = conn.cursor()
                c.execute("SELECT login, coins, tickets, cash FROM users WHERE login=? AND password=?", (login, password))
                db_user = c.fetchone()
                conn.close()
            except Exception:
                db_user = None
                
            if not db_user:
                await websocket.close(code=4000, reason="Invalid login or password")
                return
                
            user_id = login
            if user_id not in USERS:
                USERS[user_id] = {
                    "coins": db_user[1], "tickets": db_user[2], "cash": db_user[3],
                    "coin_fill": 0.0, "sector": None, "last_wheel_sector": 0,
                    "bonus_until": 0, "next_spin_allow": 0, "spin_lock": False,
                    "last_activity": time.time(), "is_db_user": True
                }
            else:
                USERS[user_id]["coins"] = db_user[1]
                USERS[user_id]["tickets"] = db_user[2]
                USERS[user_id]["cash"] = db_user[3]
        else:
            if not user_id_qs:
                user_id_qs = str(random.randint(1000, 9999))
                token = f"test-park-{user_id_qs}"

            if token != f"test-park-{user_id_qs}":
                await websocket.close(code=4000, reason="Unauthorized")
                return
                
            user_id = user_id_qs
            if user_id not in USERS:
                USERS[user_id] = {
                    "coins": 500, "tickets": 1000, "cash": 999,
                    "coin_fill": 0.0, "sector": None, "last_wheel_sector": 0,
                    "bonus_until": 0, "next_spin_allow": 0, "spin_lock": False,
                    "last_activity": time.time(), "is_db_user": False
                }

        if user_id in DISCONNECT_TIMERS:
            DISCONNECT_TIMERS[user_id].cancel()
            del DISCONNECT_TIMERS[user_id]

        CLIENTS[websocket] = user_id
        state = USERS[user_id]
        uid_int = int(user_id) if str(user_id).isdigit() else 1

        await push_balance(websocket, send_lock, state)
        await safe_send({
            "action": "get_field",
            "cells": list(GLOBAL_LOBBY_STATE["cells"].values()),
            "energy": GLOBAL_LOBBY_STATE["energy"],
            "safe": GLOBAL_LOBBY_STATE["safe"],
            "safe_bank": GLOBAL_LOBBY_STATE["safe_bank"]
        })
        await safe_send({
            "action": "park_state",
            "crystals": GLOBAL_LOBBY_STATE["crystals"],
            "cars_total": GLOBAL_LOBBY_STATE["cars_total"],
            "user_id": uid_int
        })
        asyncio.create_task(broadcast_lobby())

        async def process_message(message):
            try: data = json.loads(message)
            except: 
                await safe_send({"error": "bad_request"})
                return

            action = data.get("action")
            if not action:
                await safe_send({"error": "bad_request"})
                return

            resp = {"action": action, "status": "ok"}

            if action == "get_balance":
                resp_dict = {k: state[k] for k in ["coins", "tickets", "cash", "coin_fill"]}
                resp_dict.update({
                    "energy": GLOBAL_LOBBY_STATE["energy"], 
                    "safe": GLOBAL_LOBBY_STATE["safe"],
                    "safe_bank": GLOBAL_LOBBY_STATE["safe_bank"],
                    "crystals": GLOBAL_LOBBY_STATE["crystals"],
                    "cars_total": GLOBAL_LOBBY_STATE["cars_total"]
                })
                resp.update(resp_dict)
                await safe_send(resp)

            elif action == "lobby":
                resp_state = build_lobby_state()
                resp_state["sector"] = state.get("sector")
                await safe_send(resp_state)

            elif action == "get_field":
                resp["cells"] = list(GLOBAL_LOBBY_STATE["cells"].values())
                resp.update({
                    "energy": GLOBAL_LOBBY_STATE["energy"],
                    "safe": GLOBAL_LOBBY_STATE["safe"],
                    "safe_bank": GLOBAL_LOBBY_STATE["safe_bank"]
                })
                await safe_send(resp)

            elif action == "join_sector":
                req_sector = data.get("sector")
                
                if req_sector not in range(1, 8):
                    resp.update({"status": "error", "error": "invalid_sector"})
                    await safe_send(resp)
                    return
                
                state["last_activity"] = time.time()
                
                if state["sector"] == req_sector:
                    resp.update({"status": "ok", "sector": req_sector, "sectors": get_sector_counts()})
                    await safe_send(resp)
                    return
                
                if state["sector"] is not None and state["sector"] != req_sector:
                    resp.update({"status": "error", "error": "already_joined"})
                    await safe_send(resp)
                    return
                
                sector_occupied_by_other = False
                for uid, u_data in USERS.items():
                    if uid != user_id and u_data.get("sector") == req_sector:
                        sector_occupied_by_other = True
                        break

                if sector_occupied_by_other:
                    resp.update({"status": "error", "error": "sector_taken"})
                    await safe_send(resp)
                    return

                counts = get_sector_counts()
                if state["sector"] is None and counts.get(req_sector, 0) >= 1:
                    resp.update({"status": "error", "error": "sector_taken"})
                    await safe_send(resp)
                else:
                    state["sector"] = req_sector
                    resp.update({"status": "ok", "sector": req_sector, "sectors": get_sector_counts()})
                    await safe_send(resp)
                    await push_balance(websocket, send_lock, state)
                    asyncio.create_task(broadcast_lobby())

            elif action == "leave_sector":
                state["last_activity"] = time.time()
                if state["sector"] is None:
                    resp.update({"status": "error", "error": "sector_not_joined"})
                    await safe_send(resp)
                else:
                    state["sector"] = None
                    resp.update({"status": "ok", "sectors": get_sector_counts()})
                    await safe_send(resp)
                    await push_balance(websocket, send_lock, state)
                    asyncio.create_task(broadcast_lobby())

            elif action == "spin":
                if state.get("spin_lock", False):
                    resp.update({"status": "error", "error": "spin_in_progress"})
                    await safe_send(resp)
                    return

                if time.time() < state.get("next_spin_allow", 0):
                    resp.update({"status": "error", "error": "spin_cooldown"})
                    await safe_send(resp)
                    return

                if time.time() < state.get("bonus_until", 0):
                    resp.update({"status": "error", "error": "bonus_in_progress"})
                    await safe_send(resp)
                    return

                if state["coins"] < CONFIG["SPIN_COST"]:
                    resp.update({"status": "error", "error": "not_enough_coins"})
                    await safe_send(resp)
                    return

                state["spin_lock"] = True
                try:
                    state["next_spin_allow"] = time.time() + 0.4
                    state["coins"] -= CONFIG["SPIN_COST"]
                    reels = get_spin_outcome()
                    pay, added_crystals = calculate_spin(reels)
                    
                    state["coins"] += pay
                    
                    bonus_triggered = False
                    carousel_prize = 0
                    cars_added = 0
                    wheel_prize = 0
                    
                    if added_crystals > 0:
                        GLOBAL_LOBBY_STATE["crystals"] += added_crystals
                        
                        if GLOBAL_LOBBY_STATE["crystals"] >= 4:
                            bonus_triggered = True
                            GLOBAL_LOBBY_STATE["crystals"] = 0
                            
                            carousel_prize, cars_added = get_carousel_outcome(GLOBAL_LOBBY_STATE["cars_total"])
                            state["coins"] += carousel_prize
                            GLOBAL_LOBBY_STATE["cars_total"] += cars_added
                            
                            if GLOBAL_LOBBY_STATE["cars_total"] >= 6:
                                wheel_prize = get_wheel_bonus_outcome()
                                state["coins"] += wheel_prize
                                GLOBAL_LOBBY_STATE["cars_total"] = 0
                            
                            state["bonus_until"] = time.time() + 5.0

                    resp.update({
                        "reels": reels, 
                        "pay": pay, 
                        "prize": pay, 
                        "win": bool(pay > 0),
                        "coins": state["coins"], 
                        "crystals": GLOBAL_LOBBY_STATE["crystals"]
                    })
                    await safe_send(resp)
                    await push_balance(websocket, send_lock, state)
                    
                    if added_crystals > 0:
                        await broadcast_park_state(uid_int)
                        
                    if bonus_triggered:
                        bonus_msg = json.dumps({
                            "action": "bonus",
                            "user_id": uid_int,
                            "carousel": carousel_prize,
                            "cars": cars_added,
                            "cars_total": GLOBAL_LOBBY_STATE["cars_total"] if wheel_prize == 0 else 6,
                            "wheel": wheel_prize if wheel_prize > 0 else None,
                            "coins": state["coins"],
                            "crystals": 0
                        })
                        for ws_c in list(CLIENTS.keys()):
                            try: await ws_c.send(bonus_msg)
                            except: pass
                            
                        await push_balance(websocket, send_lock, state)
                        await broadcast_park_state(uid_int)
                finally:
                    state["spin_lock"] = False

            else:
                if state["sector"] is None:
                    resp.update({"status": "error", "error": "sector_not_joined"})
                    await safe_send(resp)
                    return
                
                sector = state["sector"]

                if action == "open_cell":
                    state["last_activity"] = time.time()
                    
                    if GLOBAL_LOBBY_STATE["is_processing_bonus"]:
                        resp.update({"status": "error", "error": "bonus_processing"})
                        await safe_send(resp)
                        return

                    cell_id = data.get("cell_id")
                    if cell_id is None or not isinstance(cell_id, int) or not (0 <= cell_id <= 639):
                        resp.update({"status": "error", "error": "bad_request"})
                        await safe_send(resp)
                    elif cell_id in GLOBAL_LOBBY_STATE["cells"]:
                        resp.update({"status": "error", "error": "cell_already_open"})
                        await safe_send(resp)
                    elif state["tickets"] < CONFIG["TICKET_COST"]:
                        resp.update({"status": "error", "error": "not_enough_tickets"})
                        await safe_send(resp)
                    else:
                        state["tickets"] -= CONFIG["TICKET_COST"]
                        if not GLOBAL_LOBBY_STATE["deck"]:
                            GLOBAL_LOBBY_STATE["deck"] = generate_field_deck()
                        cell_template = GLOBAL_LOBBY_STATE["deck"].pop()
                        
                        outcome = cell_template["outcome"]
                        cell_data = {"cell_id": cell_id, "outcome": outcome, "value": cell_template["value"], "game": cell_template["game"]}
                        
                        if outcome == "energy": 
                            GLOBAL_LOBBY_STATE["energy"] += 1
                            GLOBAL_LOBBY_STATE["safe_bank"] += 1
                            add_coin_fill(state, 0.01)
                        elif outcome == "money": 
                            GLOBAL_LOBBY_STATE["safe"] += 1
                        elif outcome == "cash": 
                            state["cash"] += cell_data["value"]
                        elif outcome == "game":
                            if cell_template.get("win") is not None:
                                cell_data["win"] = cell_template["win"]

                        GLOBAL_LOBBY_STATE["cells"][cell_id] = cell_data
                        
                        resp_data = {k:v for k,v in cell_data.items() if k != "win"}
                        resp.update(resp_data)
                        resp.update({
                            "tickets": state["tickets"], "energy": GLOBAL_LOBBY_STATE["energy"], 
                            "safe": GLOBAL_LOBBY_STATE["safe"], "coin_fill": state["coin_fill"], "cash": state["cash"],
                            "safe_bank": GLOBAL_LOBBY_STATE["safe_bank"]
                        })
                        await safe_send(resp)
                        await push_balance(websocket, send_lock, state)
                        await broadcast_field_state(sector, cell_data, uid_int)

                        if GLOBAL_LOBBY_STATE["safe"] >= 4:
                            GLOBAL_LOBBY_STATE["is_processing_bonus"] = True
                            try:
                                GLOBAL_LOBBY_STATE["safe"] = 0  
                                
                                payout_cash = GLOBAL_LOBBY_STATE["safe_bank"]
                                state["cash"] += payout_cash 
                                energy_used = GLOBAL_LOBBY_STATE["energy"]
                                coins_added = energy_used // 100
                                add_coin_fill(state, (energy_used % 100) / 100.0)
                                state["coins"] += coins_added
                                
                                GLOBAL_LOBBY_STATE["energy"] = 0
                                GLOBAL_LOBBY_STATE["safe_bank"] = CONFIG["SAFE_BANK_START"]
                                
                                await asyncio.sleep(0.05)
                                await broadcast_bank(sector, uid_int, energy_used, coins_added, payout_cash)
                                await asyncio.sleep(0.05)
                                await push_balance(websocket, send_lock, state)
                            finally:
                                GLOBAL_LOBBY_STATE["is_processing_bonus"] = False

                        if sum(1 for c in GLOBAL_LOBBY_STATE["cells"].values() if c["outcome"] == "money") >= 29:
                            GLOBAL_LOBBY_STATE["cells"].clear()
                            GLOBAL_LOBBY_STATE["deck"] = generate_field_deck() 
                            await broadcast_field_reset(sector)

                elif action == "game_start":
                    game = data.get("game")
                    game_id = str(uuid.uuid4())
                    timeout = 10 if game == "pyramid" else (15 if game == "cards" else 10)
                    
                    is_win = False
                    for c in GLOBAL_LOBBY_STATE["cells"].values():
                        if c.get("game") == game and "win" in c:
                            is_win = c["win"]
                            break
                            
                    ACTIVE_GAMES[game_id] = {
                        "game": game, 
                        "user_id": user_id, 
                        "rolls_left": 3, 
                        "current_prize": 0, 
                        "win": is_win,
                        "deadline": time.time() + timeout
                    }
                    
                    resp.update({"game": game, "game_id": game_id, "timeout": timeout})
                    if game == "cards":
                        opened_list = [{"choice": k, "card": v["card"], "prize": v["prize"]} for k, v in GLOBAL_LOBBY_STATE["cards_opened"].items()]
                        resp.update({"opened": opened_list})
                    await safe_send(resp)

                elif action == "game_play":
                    game_id = data.get("game_id")
                    g_state = ACTIVE_GAMES.get(game_id)
                    
                    if not g_state:
                        resp.update({"status": "error", "error": "game_not_active_or_timeout"})
                        await safe_send(resp)
                    else:
                        game = g_state["game"]
                        choice = data.get("choice", 0)

                        if game in ["coinhand", "coinflip", "matches"]:
                            win = g_state.get("win", False)
                            if win: state["cash"] += 50
                            else: 
                                GLOBAL_LOBBY_STATE["energy"] += 1
                                GLOBAL_LOBBY_STATE["safe_bank"] += 1
                                add_coin_fill(state, 0.01)
                            resp.update({"game": game, "choice": choice, "auto": False, "win": win, "prize": 50 if win else 0, "cash": state["cash"], "safe_bank": GLOBAL_LOBBY_STATE["safe_bank"]})
                            del ACTIVE_GAMES[game_id]

                        elif game == "cards":
                            if choice is None or not isinstance(choice, int) or not (0 <= choice <= 54):
                                resp.update({"status": "error", "error": "invalid_choice"})
                                await safe_send(resp)
                            elif choice in GLOBAL_LOBBY_STATE["cards_opened"]:
                                resp.update({"status": "error", "error": "card_opened"})
                                await safe_send(resp)
                            else:
                                card_name = GLOBAL_LOBBY_STATE["cards_deck"][choice]
                                prize = get_card_prize(card_name)
                                GLOBAL_LOBBY_STATE["cards_opened"][choice] = {"card": card_name, "prize": prize}
                                state["cash"] += prize
                                
                                resp.update({"game": "cards", "choice": choice, "card": card_name, "prize": prize, "cash": state["cash"], "auto": False})
                                del ACTIVE_GAMES[game_id]
                                
                                await safe_send(resp)
                                await push_balance(websocket, send_lock, state)
                                
                                card_msg = json.dumps({"action": "card_opened", "user_id": uid_int, "choice": choice, "card": card_name, "prize": prize})
                                for c_ws in list(CLIENTS.keys()):
                                    try: await c_ws.send(card_msg)
                                    except: pass
                                    
                                if len(GLOBAL_LOBBY_STATE["cards_opened"]) >= 55:
                                    GLOBAL_LOBBY_STATE["cards_deck"] = generate_shared_cards()
                                    GLOBAL_LOBBY_STATE["cards_opened"].clear()
                                    reset_msg = json.dumps({"action": "cards_reset"})
                                    for c_ws in list(CLIENTS.keys()):
                                        try: await c_ws.send(reset_msg)
                                        except: pass
                                return

                        elif game == "pyramid":
                            action_type = data.get("pyramid_action", "open")
                            if action_type == "collect":
                                state["cash"] += g_state["current_prize"]
                                resp.update({"game": "pyramid", "done": True, "prize": g_state["current_prize"], "cash": state["cash"]})
                                del ACTIVE_GAMES[game_id]
                            else:
                                if g_state["rolls_left"] > 0:
                                    g_state["rolls_left"] -= 1
                                    item = random.choice(PYRAMID_POOL)
                                    val = PYRAMID_ITEMS[item]
                                    g_state["current_prize"] = val
                                    
                                    if g_state["rolls_left"] == 0:
                                        state["cash"] += g_state["current_prize"]
                                        resp.update({"game": "pyramid", "done": True, "prize": g_state["current_prize"], "item": item, "cash": state["cash"]})
                                        del ACTIVE_GAMES[game_id]
                                    else:
                                        g_state["deadline"] = time.time() + 10 
                                        resp.update({"game": "pyramid", "done": False, "current_prize": g_state["current_prize"], "item": item})
                                else:
                                    resp.update({"status": "error", "error": "no_rolls_left"})
                                    await safe_send(resp)
                                    return

                        elif game == "wheel":
                            wheel_pool = SECTORS_STATE[sector]["wheel_pool"]
                            if not wheel_pool:
                                SECTORS_STATE[sector]["wheel_pool"] = generate_wheel_pool()
                                wheel_pool = SECTORS_STATE[sector]["wheel_pool"]
                            prize = wheel_pool.pop()
                            state["tickets"] += prize
                            w_sector = get_wheel_sector(prize, state["last_wheel_sector"])
                            state["last_wheel_sector"] = w_sector
                            resp.update({"game": "wheel", "sector": w_sector, "prize": prize, "tickets": state["tickets"]})
                            del ACTIVE_GAMES[game_id]

                        await safe_send(resp)
                        await push_balance(websocket, send_lock, state)

        async for message in websocket:
            task = asyncio.create_task(process_message(message))
            bg_tasks.add(task)

    except websockets.exceptions.ConnectionClosed:
        pass
    except Exception as e:
        pass
    finally:
        if websocket in CLIENTS:
            uid = CLIENTS[websocket]
            del CLIENTS[websocket]
            if websocket in CLIENT_LOCKS:
                del CLIENT_LOCKS[websocket]
            
            if uid in USERS and USERS[uid].get("is_db_user"):
                try:
                    conn = sqlite3.connect(DB_PATH)
                    c = conn.cursor()
                    c.execute("UPDATE users SET coins=?, tickets=?, cash=? WHERE login=?", 
                              (USERS[uid]["coins"], USERS[uid]["tickets"], USERS[uid]["cash"], uid))
                    conn.commit()
                    conn.close()
                except: pass

            async def delayed_sector_release(uid_to_release):
                await asyncio.sleep(45)
                if uid_to_release in USERS and USERS[uid_to_release].get("sector") is not None:
                    USERS[uid_to_release]["sector"] = None
                    asyncio.create_task(broadcast_lobby())
                if uid_to_release in DISCONNECT_TIMERS:
                    del DISCONNECT_TIMERS[uid_to_release]
                    
            if uid in USERS and USERS[uid].get("sector") is not None:
                if uid in DISCONNECT_TIMERS:
                    DISCONNECT_TIMERS[uid].cancel()
                DISCONNECT_TIMERS[uid] = asyncio.create_task(delayed_sector_release(uid))
                
        for t in bg_tasks:
            t.cancel()

async def main():
    asyncio.create_task(background_game_worker())
    async with websockets.serve(handler, "0.0.0.0", 8084):
        await asyncio.Future()

if __name__ == "__main__":
    asyncio.run(main())