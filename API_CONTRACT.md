# Casi Casino — Frontend API Contract (v1)

REST/JSON contract the frontend builds against. In development these endpoints are
served by **MSW** (mock service worker) from `src/mocks`. In production the same
requests hit the real backend defined by `VITE_API_URL`.

- Base path: `/api`
- All responses are `application/json`.
- Auth (later): `Authorization: Bearer <token>`. The mock ignores it.
- Errors use a uniform envelope and a proper HTTP status:

```json
{ "error": { "code": "NOT_FOUND", "message": "Game not found" } }
```

Common error codes: `BAD_REQUEST` (400), `UNAUTHORIZED` (401), `NOT_FOUND` (404),
`SERVER_ERROR` (500).

---

## Conventions

- Money values are numbers in major units (e.g. `1000.50`), not cents.
- Paginated list responses use this envelope:

```json
{ "items": [ ... ], "page": 1, "pageSize": 12, "total": 240, "hasMore": true }
```

- IDs are strings.
- `nameKey` fields are i18n keys resolved on the client.

---

## Endpoints

### Auth & profile

#### `GET /api/user`
Current user profile.
```json
{
  "id": "355117",
  "username": "Vip8881313",
  "vipLevel": "KING",
  "avatar": null
}
```

#### `GET /api/balance`
Balances for the active wallet.
```json
{
  "currency": "USD",
  "main": 0,
  "bonus": 34,
  "coins": 1
}
```

---

### Games

#### `GET /api/providers`
Game providers with per-category availability.
```json
[
  { "id": "all", "name": "All", "gameCount": 1248, "categories": ["popular", "new", "..."] },
  { "id": "pragmatic", "name": "Pragmatic", "gameCount": 287, "categories": ["popular", "new"] }
]
```

#### `GET /api/categories`
Game categories for the sidebar.
```json
[
  { "id": "popular", "nameKey": "cat.popular", "icon": "★" },
  { "id": "live", "nameKey": "cat.live", "icon": "●" }
]
```

#### `GET /api/games`
Game catalog. Paginated.

Query params:
| Param | Type | Default | Notes |
|-------|------|---------|-------|
| `provider` | string | `all` | provider id |
| `category` | string | `popular` | category id |
| `q` | string | — | search by name |
| `page` | int | `1` | 1-based |
| `pageSize` | int | `12` | |

```json
{
  "items": [
    { "id": "all-popular-0", "name": "Fortune Tiger", "provider": "all", "category": "popular", "colors": ["#7a1f1f", "#1a0608"] }
  ],
  "page": 1,
  "pageSize": 12,
  "total": 36,
  "hasMore": true
}
```

#### `GET /api/games/popular`
Curated popular rail for the home screen (not paginated).
```json
[
  { "id": "pg-1", "name": "Fortune Tiger", "colors": ["#7a1f1f", "#1a0608"] }
]
```

---

### Favorites

#### `GET /api/favorites`
List of favorite game ids for the current user.
```json
{ "ids": ["all-popular-0", "all-popular-1"] }
```

#### `POST /api/favorites/:gameId/toggle`
Toggle a game in favorites. Returns the new state.
```json
{ "gameId": "all-popular-0", "favorite": true }
```

---

### Promotions & bonuses

#### `GET /api/promotions`
Promotions for the Promotions page.
```json
[
  { "id": "p1", "badge": "TOURNAMENT", "title": "Drops & Wins", "description": "...", "ctaKey": "promo.join" }
]
```

---

### Payments

#### `GET /api/payment-methods`
Query: `type` = `deposit` (default) | `withdraw`.
```json
[
  { "id": "crypto", "name": "Crypto", "type": "crypto", "minDeposit": 250, "maxDeposit": 20000000, "commission": 0 }
]
```

---

### Sport

#### `GET /api/sport/matches`
Query: `sport` = `soccer` (default) | `tennis` | ...
```json
[
  { "id": "m1", "league": "UEFA Champions League", "teamA": "Real Madrid", "teamB": "Bayern", "odds": { "home": 1.85, "draw": 3.40, "away": 4.20 } }
]
```

---

### Game launch

#### `POST /api/games/:gameId/launch`
Body: `{ "mode": "real" | "demo" }`. Returns the iframe URL and session info.
```json
{
  "gameId": "all-popular-0",
  "name": "Fortune Tiger",
  "mode": "real",
  "url": "https://launch.example/game/all-popular-0?mode=real",
  "sessionId": "sess_abc123"
}
```

---

### User bonuses

#### `GET /api/user/bonuses`
Active and available bonuses of the current user.
```json
[
  {
    "id": "ub1",
    "type": "deposit",
    "status": 1,
    "statusName": "Active",
    "name": "Welcome Pack",
    "description": "100% up to 500 + 100 FS",
    "imgUrl": "/img/bonuses/welcome.png",
    "progressPercents": 35,
    "balanceString": "120.00",
    "balanceName": "money",
    "isActive": true,
    "isPending": false,
    "isCancelable": true
  }
]
```

#### `POST /api/user/bonuses/:bonusId/activate`
```json
{ "bonusId": "ub1", "status": 1, "statusName": "Active" }
```

#### `POST /api/user/bonuses/:bonusId/cancel`
```json
{ "bonusId": "ub1", "status": 3, "statusName": "Canceled" }
```

---

### Transactions

#### `GET /api/user/transactions`
Query: `type` = `all` (default) | `deposit` | `withdraw`. Paginated.
```json
{
  "items": [
    { "id": "t1", "type": "deposit", "amount": 100.0, "currency": "USD", "status": "done", "statusName": "Done", "method": "Crypto", "date": "2026-06-01T12:00:00Z" }
  ],
  "page": 1, "pageSize": 20, "total": 1, "hasMore": false
}
```

#### `POST /api/user/transactions/:id/cancel`
Cancel a pending withdrawal.
```json
{ "id": "t1", "status": "canceled" }
```

---

### Promotions

#### `POST /api/promotions/:promoId/activate`
```json
{ "promoId": "p1", "activated": true, "message": "Promotion activated" }
```

---

### Payments (deposit / withdraw)

#### `GET /api/payment-methods/:methodId/requisites`
Form/requisites for a chosen method.
```json
{
  "methodId": "crypto",
  "fields": [
    { "name": "amount", "label": "Amount", "type": "number", "min": 250, "max": 20000000, "required": true }
  ]
}
```

#### `POST /api/deposit`
Body: `{ "methodId": "crypto", "amount": 1000 }`.
```json
{ "transactionId": "dep_1", "status": "pending", "redirectUrl": "https://pay.example/dep_1" }
```

#### `POST /api/withdraw`
Body: `{ "methodId": "visa", "amount": 500, "requisites": { "card": "..." } }`.
```json
{ "transactionId": "wd_1", "status": "pending" }
```

---

### Bet slip (sport)

#### `POST /api/sport/betslip`
Body: `{ "selections": [ { "matchId": "m1", "type": "1", "odds": 1.85 } ], "stake": 100 }`.
```json
{ "betId": "bet_1", "status": "accepted", "stake": 100, "totalOdds": 1.85, "potentialWin": 185.0 }
```

---

## Implementation notes

- Mock handlers live in `src/mocks/handlers.js`, data in `src/mocks/data/`.
- The client (`src/api/client.js`) prefixes requests with `import.meta.env.VITE_API_URL`
  (empty in dev → same-origin → intercepted by MSW).
- To point the app at a real backend, set `VITE_API_URL` and disable MSW
  (`VITE_ENABLE_MOCKS=false`).
