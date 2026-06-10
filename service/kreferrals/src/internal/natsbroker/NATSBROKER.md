# Документация пакета NATS Broker

## Обзор

Пакет `natsbroker` представляет собой брокер сообщений на основе NATS для реферальной системы. Он обеспечивает асинхронную обработку событий пользователей и синхронную обработку запросов через NATS Streaming и обычные NATS сообщения.

## Архитектура

Пакет использует две модели обмена сообщениями:
- **Request-Reply**: Синхронные запросы через топики `referrals.*`
- **Pub-Sub с JetStream**: Асинхронная обработка событий пользователей через топики `userevents.*`

---

# Основной модуль

## Файл: [`natsbroker.go`](natsbroker.go)

### Глобальные переменные

```go
var UserEventsConsumer jetstream.ConsumeContext // Обработчик событий пользователей
var NatsConn *nats.Conn                         // Соединение с NATS
var ReferralSubscription *nats.Subscription     // Подписка на referrals.*
```

### Функция инициализации

#### MustInit
```go
func MustInit(ctx context.Context)
```

**Назначение**: Инициализация NATS брокера и создание всех необходимых подписок.

**Алгоритм инициализации**:

1. **Создание соединения с NATS**:
   ```go
   conn, err := nats.Connect(
       config.Get().NATS.URL,
       nats.Token(config.Get().NATS.Token),
       nats.MaxReconnects(-1), // Бесконечные переподключения
   )
   ```

2. **Настройка обработчиков соединения**:
   - `ReconnectHandler` - логирование переподключений
   - `DisconnectHandler` - логирование отключений

3. **Подписка на синхронные запросы**:
   ```go
   sub, err := NatsConn.Subscribe("referrals.*", cbListener)
   ```

4. **Тест соединения**:
   ```go
   resp, err := NatsConn.Request("referrals.ping", nil, 50*time.Millisecond)
   ```

5. **Создание JetStream потока**:
   ```go
   stream, err := js.CreateOrUpdateStream(ctx, jetstream.StreamConfig{
       Name: "userevents",
       Subjects: []string{
           "userevents.first-dep",
           "userevents.money-spent", 
           "userevents.achievement-completed",
           "userevents.registered",
       },
       MaxBytes: 100 * 1024 * 1024, // 100 MB
       MaxAge:   1 * time.Hour,
   })
   ```

6. **Создание потребителя JetStream**:
   ```go
   cons, err := stream.CreateOrUpdateConsumer(ctx, jetstream.ConsumerConfig{
       Durable: "referrals",
       AckWait: 5 * time.Second,
   })
   ```

### Функция остановки

#### Stop
```go
func Stop()
```

**Назначение**: Корректная остановка всех компонентов NATS брокера.

**Алгоритм**:
1. Остановка потребителя событий пользователей
2. Отписка от синхронных запросов
3. Закрытие соединения с NATS

### Обработчик синхронных запросов

#### cbListener
```go
func cbListener(msg *nats.Msg)
```

**Назначение**: Маршрутизация входящих синхронных запросов к соответствующим обработчикам.

**Поддерживаемые топики**:

| Топик | Обработчик | Описание |
|-------|------------|----------|
| `referrals.ping` | `callbacks.Ping` | Тест соединения |
| `referrals.click` | `callbacks.Click` | Клик по реферальной ссылке |
| `referrals.transferRefMoney` | `callbacks.TransferRefMoney` | Перевод реферальных денег |
| `referrals.transferAccumMoney` | `callbacks.TransferAccumulatedMoney` | Перевод накопительных денег |
| `referrals.transferAllRefAccumMoney` | `callbacks.TransferAllRefAccumMoney` | Массовый перевод |
| `referrals.getRefCode` | `callbacks.GetRefCode` | Получение реферального кода |
| `referrals.getRefConfig` | `callbacks.GetRefCofig` | Получение конфигурации |
| `referrals.getAchievementRewards` | `callbacks.GetAchievementRewards` | Получение наград |
| `referrals.getReferralUsers` | `callbacks.GetReferralUsers` | Получение пользователей |
| `referrals.getReferralUsersCountWithDeps` | `callbacks.GetReferralUsersCountwithDeps` | Подсчет с депозитами |
| `referrals.getReferralEvents` | `callbacks.GetReferralEvents` | Получение событий |
| `referrals.reload` | `callbacks.ReloadService` | Перезагрузка сервиса |

**Особенности**:
- Обработка паники с логированием стека вызовов
- Логирование всех входящих запросов
- Ответ "unknown request" для неизвестных топиков

---

# NATS RefWallet

## Файл: [`natsrefwallet/natsrefwallet.go`](natsrefwallet/natsrefwallet.go)

### Структура NatsRefWallet

```go
type NatsRefWallet struct {
    natsConn *nats.Conn
}
```

**Назначение**: Реализация интерфейса `container.RefWallet` через NATS запросы к сервису `usermanager`.

### Конструктор

#### NewNatsRefWallet
```go
func NewNatsRefWallet(conn *nats.Conn) *NatsRefWallet
```

**Особенности**:
- Тестирует соединение с `usermanager` через `usermanager.ping`
- Паникует при неудачном тесте соединения

### Методы управления балансом

#### ChangeRefBalance
```go
func (n *NatsRefWallet) ChangeRefBalance(userID uint64, amount float64, currency string) error
```

**Назначение**: Изменение основного реферального баланса пользователя.

**NATS запрос**: `usermanager.change_ref_money`

**Структура запроса**:
```go
type changeRefMoneyRequest struct {
    UserID   uint64  `json:"user_id"`
    Amount   float64 `json:"amount"`   // Может быть отрицательной
    Currency string  `json:"currency"`
}
```

#### ChangeAccumulativeBalance
```go
func (n *NatsRefWallet) ChangeAccumulativeBalance(userID uint64, amount float64, currency string) error
```

**Назначение**: Изменение накопительного баланса пользователя.

**NATS запрос**: `usermanager.change_bonus_money`

**Структура запроса**:
```go
type changeAccumulationMoneyRequest struct {
    UserID   uint64  `json:"user_id"`
    Amount   float64 `json:"amount"`
    Currency string  `json:"currency"`
}
```

### Методы переводов

#### TransferRefMoney
```go
func (n *NatsRefWallet) TransferRefMoney(userID uint64, currency string) (transferred float64, err error)
```

**Назначение**: Перевод реферальных денег на основной счет пользователя.

**NATS запрос**: `usermanager.transfer_ref_money`

**Возвращает**: Сумму переведенных средств

#### TransferAccumMoney
```go
func (n *NatsRefWallet) TransferAccumMoney(userID uint64, currency string) (transferred float64, err error)
```

**Назначение**: Перевод накопительных денег на основной счет пользователя.

**NATS запрос**: `usermanager.transfer_accum_money`

#### TransferAllAccumulatedMoney
```go
func (n *NatsRefWallet) TransferAllAccumulatedMoney(userID uint64) (container.TransferAllAccumMoneyResponse, error)
```

**Назначение**: Перевод всех накопленных средств пользователя во всех валютах.

**NATS запрос**: `usermanager.transfer_all_accum_money`

**Структура ответа**:
```go
type TransferAllAccumMoneyResponse struct {
    Error     string          `json:"err"`
    ErrorCode int             `json:"cd"`
    UserID    int64           `json:"user_id"`
    Amounts   map[int]float64 `json:"amounts"` // По валютам
}
```

### Структуры ответов

#### ErrorResponse
```go
type ErrorResponse struct {
    Code  int    `json:"cd"`
    Error string `json:"err"`
}
```

#### AmountResponse
```go
type AmountResponse struct {
    Code     int     `json:"cd"`
    Error    string  `json:"err"`
    Value    float64 `json:"v"`
    CurrName string  `json:"cn"`
    CurrId   int8    `json:"ci"`
}
```

---

# Callbacks - Обработчики синхронных запросов

## Ping - Тест соединения

### Файл: [`callbacks/ping.go`](callbacks/ping.go)

```go
func Ping(msg *nats.Msg)
```

**Назначение**: Простой обработчик для тестирования соединения.

**Ответ**: `"pong"`

---

## Click - Обработка кликов

### Файл: [`callbacks/click.go`](callbacks/click.go)

```go
func Click(msg *nats.Msg)
```

**Назначение**: Обработка кликов по реферальным ссылкам.

**Структура события**:
```go
type ClickEvent struct {
    RefCode     string  `json:"ref_code"`
    Referer     string  `json:"referer"`
    Fingerprint *string `json:"fingerprint"`
}
```

**Делегирует**: `usecases.RefClick()`

---

## GetRefCode - Получение реферального кода

### Файл: [`callbacks/get_ref_code.go`](callbacks/get_ref_code.go)

```go
func GetRefCode(msg *nats.Msg)
```

**Структура запроса**:
```go
type getRefCodeRequest struct {
    UserID uint64 `json:"user_id"`
}
```

**Структура ответа**:
```go
type getRefCodeResponse struct {
    Code    int    `json:"cd"`
    Error   string `json:"err"`
    UserID  uint64 `json:"user_id"`
    RefCode string `json:"ref_code"`
    RefID   uint64 `json:"ref_id"`
}
```

**Особенности**:
- Создает новый код, если не существует
- Возвращает существующий код, если найден
- Обрабатывает ошибки с соответствующими HTTP кодами

---

## GetRefConfig - Получение конфигурации

### Файл: [`callbacks/get_ref_config.go`](callbacks/get_ref_config.go)

```go
func GetRefConfig(msg *nats.Msg)
```

**Структура запроса**:
```go
type getRefCofigRequest struct {
    UserID   uint64 `json:"user_id"`
    Currency string `json:"currency"`
}
```

**Структура ответа**:
```go
type getRefCofigResponse struct {
    Code   int     `json:"cd"`
    Error  string  `json:"err"`
    GgrPrc int     `json:"ggr_prc"`
    FdSum  float64 `json:"fd_sum"`
}
```

---

## GetAchievementRewards - Получение наград

### Файл: [`callbacks/get_achieve_rewards.go`](callbacks/get_achieve_rewards.go)

```go
func GetAchievementRewards(msg *nats.Msg)
```

**Структура запроса**:
```go
type achieveRewardsRequest struct {
    Currency string `json:"currency"`
}
```

**Структура ответа**:
```go
type achieveRewardsResponse struct {
    Code    int                `json:"cd"`
    Error   string             `json:"err"`
    Rewards map[uint64]float64 `json:"rewards"` // ID достижения -> сумма
}
```

---

## GetReferralEvents - Получение событий

### Файл: [`callbacks/get_ref_events.go`](callbacks/get_ref_events.go)

```go
func GetReferralEvents(msg *nats.Msg)
```

**Структура запроса**:
```go
type getRefEventsRequest struct {
    RefID   uint64 `json:"ref_id"`
    Page    uint64 `json:"page"`
    PerPage uint64 `json:"per_page"`
}
```

**Структура ответа**:
```go
type getRefEventsResponse struct {
    Code   int                     `json:"cd"`
    Error  string                  `json:"err"`
    Pages  int                     `json:"pages"`
    Page   int                     `json:"page"`
    Rows   uint64                  `json:"total_rows"`
    Events []*models.ReferralEvent `json:"events"`
}
```

**Особенности**:
- Автоматический расчет пагинации
- Расчет общего количества страниц

---

## GetReferralUsers - Получение пользователей

### Файл: [`callbacks/get_ref_users.go`](callbacks/get_ref_users.go)

```go
func GetReferralUsers(msg *nats.Msg)
```

**Структура запроса**:
```go
type getRefUsersRequest struct {
    RefID   uint64 `json:"ref_id"`
    Page    uint64 `json:"page"`
    PerPage uint64 `json:"per_page"`
}
```

**Структура ответа**:
```go
type getRefUsersResponse struct {
    Code       int                        `json:"cd"`
    Error      string                     `json:"err"`
    Pages      int                        `json:"pages"`
    Page       int                        `json:"page"`
    TotalUsers uint64                     `json:"total_users"`
    Rows       uint64                     `json:"total_rows"`
    Users      []*container.ReferralMUser `json:"users"`
}
```

**Особенности**:
- Получает как общее количество пользователей, так и пользователей с прибылью
- Поддерживает пагинацию

---

## GetReferralUsersCountwithDeps - Подсчет с депозитами

### Файл: [`callbacks/get_ref_users_count_with_dep.go`](callbacks/get_ref_users_count_with_dep.go)

```go
func GetReferralUsersCountwithDeps(msg *nats.Msg)
```

**Структура запроса**:
```go
type getRefUsersCountwithDepsRequest struct {
    RefID uint64 `json:"ref_id"`
}
```

**Структура ответа**:
```go
type GetReferralUsersCountwithDepsResponse struct {
    Code  int    `json:"cd"`
    Error string `json:"err"`
    Count uint64 `json:"count"`
}
```

---

## Переводы средств

### TransferRefMoney - Перевод реферальных денег

**Файл**: [`callbacks/transfer_ref_money.go`](callbacks/transfer_ref_money.go)

```go
func TransferRefMoney(msg *nats.Msg)
```

**Структура запроса**:
```go
type transferRefMoneyRequest struct {
    UserID     uint64 `json:"user_id"`
    Currency   string `json:"currency"`
    CurrencyID int    `json:"currency_id"`
}
```

**Структура ответа**:
```go
type transferRefMoneyResponse struct {
    Code     int     `json:"cd"`
    Error    string  `json:"err"`
    Value    float64 `json:"v"`
    CurrName string  `json:"cn"`
}
```

### TransferAccumulatedMoney - Перевод накопительных денег

**Файл**: [`callbacks/transfer_accum_money.go`](callbacks/transfer_accum_money.go)

```go
func TransferAccumulatedMoney(msg *nats.Msg)
```

**Особенности**:
- Использует те же структуры запроса и ответа, что и `TransferRefMoney`
- Также содержит баг с двойным вызовом `msg.Respond()`

### TransferAllRefAccumMoney - Массовый перевод

**Файл**: [`callbacks/transfer_all_ref_accum_money.go`](callbacks/transfer_all_ref_accum_money.go)

```go
func TransferAllRefAccumMoney(msg *nats.Msg)
```

**Особенности**:
- Не принимает параметров
- Не отправляет ответ (fire-and-forget)
- Только логирует ошибки

---

## ReloadService - Перезагрузка сервиса

### Файл: [`callbacks/reload.go`](callbacks/reload.go)

```go
func ReloadService(msg *nats.Msg)
```

**Структура ответа**:
```go
type reloadServiceResponse struct {
    Code  int    `json:"code"`
    Error string `json:"error,omitempty"`
}
```

**Особенности**:
- Вызывает `usecases.Reload()`
- Возвращает статус выполнения операции

---

# User Events - Обработчики асинхронных событий

## FirstDep - Первый депозит

### Файл: [`userevents/firstdep.go`](userevents/firstdep.go)

```go
func FirstDepUserEvent(msg jetstream.Msg)
```

**Структура события**:
```go
type FirstDepEvent struct {
    UserID      int     `json:"user_id"`
    Amount      float64 `json:"amount"`
    Currency    string  `json:"currency"`
    CurrencyID  int     `json:"currency_id"`
    ReferralID  uint64  `json:"referral_id"`
    ClickID     uint64  `json:"click_id"`
    Fingerprint *string `json:"fingerprint"`
}
```

**Топик**: `userevents.first-dep`

**Делегирует**: `usecases.FirstDep()`

---

## AchiveDone - Завершение достижения

### Файл: [`userevents/achive_done.go`](userevents/achive_done.go)

```go
func AchiveDoneUserEvent(msg jetstream.Msg)
```

**Структура события**:
```go
type AchiveDoneEvent struct {
    UserID      uint64  `json:"user_id"`
    AchID       uint64  `json:"ach_id"`
    Currency    string  `json:"currency"`
    CurrencyID  int     `json:"currency_id"`
    ReferralID  uint64  `json:"referral_id"`
    ClickID     uint64  `json:"click_id"`
    Fingerprint *string `json:"fingerprint"`
}
```

**Топик**: `userevents.achievement-completed`

**Делегирует**: `usecases.AchiveDone()`

---

## MoneySpent - Трата денег

### Файл: [`userevents/money_spent.go`](userevents/money_spent.go)

```go
func MoneySpentUserEvent(msg jetstream.Msg)
```

**Структура события**:
```go
type MoneySpentEvent struct {
    UserID      uint64  `json:"user_id"`
    Amount      float64 `json:"amount"`
    Currency    string  `json:"currency"`
    CurrencyID  int     `json:"currency_id"`
    ReferralID  uint64  `json:"referral_id"`
    ClickID     uint64  `json:"click_id"`
    Fingerprint *string `json:"fingerprint"`
}
```

**Топик**: `userevents.money-spent`

**Делегирует**: `usecases.MoneySpent()`

---

## UserRegistered - Регистрация пользователя

### Файл: [`userevents/user_registered.go`](userevents/user_registered.go)

```go
func UserRegistered(msg jetstream.Msg)
```

**Структура события**:
```go
type UserRegisteredEvent struct {
    RefID       uint64  `json:"referral_id"`
    UserID      uint64  `json:"user_id"`
    Fingerprint *string `json:"fingerprint"`
}
```

**Топик**: `userevents.registered`

**Делегирует**: `usecases.UserRegistered()`

---

# Диаграмма архитектуры

```
┌─────────────────────────────────────────────────────────────────┐
│                        NATS Broker                             │
├─────────────────────────────────────────────────────────────────┤
│                    Синхронные запросы                           │
│                     (Request-Reply)                             │
│  ┌─────────────────┐    ┌─────────────────┐    ┌──────────────┐ │
│  │   Callbacks     │    │   NatsRefWallet │    │   Ping       │ │
│  │   • GetRefCode  │    │   • ChangeRef   │    │   • Test     │ │
│  │   • GetConfig   │    │   • Transfer    │    │   Connection │ │
│  │   • GetUsers    │    │   • Accumulate  │    │              │ │
│  │   • GetEvents   │    │                 │    │              │ │
│  └─────────────────┘    └─────────────────┘    └──────────────┘ │
├─────────────────────────────────────────────────────────────────┤
│                   Асинхронные события                           │
│                    (JetStream Pub-Sub)                         │
│  ┌─────────────────┐    ┌─────────────────┐    ┌──────────────┐ │
│  │   UserEvents    │    │   FirstDep      │    │  MoneySpent  │ │
│  │   • Registered  │    │   • Amount      │    │  • Amount    │ │
│  │   • Achievement │    │   • Currency    │    │  • Currency  │ │
│  │   • Fingerprint │    │   • ReferralID  │    │  • RefID     │ │
│  └─────────────────┘    └─────────────────┘    └──────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                        Use Cases                                │
│  • RefClick        • FirstDep         • MoneySpent             │
│  • GetRefCode      • AchiveDone       • UserRegistered         │
│  • TransferMoney   • GetUsers         • GetEvents              │
└─────────────────────────────────────────────────────────────────┘
```

# Конфигурация NATS

## JetStream поток

**Название**: `userevents`

**Топики**:
- `userevents.first-dep`
- `userevents.money-spent`
- `userevents.achievement-completed`
- `userevents.registered`

**Параметры**:
- `MaxBytes`: 100 MB
- `MaxAge`: 1 час
- `Consumer`: "referrals" (durable)
- `AckWait`: 5 секунд

## Синхронные топики

**Префикс**: `referrals.*`

**Timeout**: 50-500 мс в зависимости от операции

# Обработка ошибок

## Общие принципы

1. **Логирование**: Все ошибки логируются с контекстом
2. **Panic Recovery**: Все обработчики защищены от паники
3. **Стандартные ответы**: Единообразные структуры ошибок
4. **HTTP коды**: Использование стандартных HTTP кодов статуса

## Структура ошибок

```go
type ErrorResponse struct {
    Code  int    `json:"cd"`   // HTTP код
    Error string `json:"err"`  // Сообщение об ошибке
}
```

**Стандартные коды**:
- `200` - Успех
- `500` - Внутренняя ошибка сервера

# Особенности реализации

## Положительные стороны

1. **Надежность**: Автоматические переподключения к NATS
2. **Масштабируемость**: JetStream для асинхронной обработки
3. **Мониторинг**: Подробное логирование всех операций
4. **Отказоустойчивость**: Обработка паники и ошибок

## Проблемы и улучшения

### Рекомендации по улучшению

1. **Валидация**: Добавить валидацию входящих данных
2. **Метрики**: Добавить метрики производительности
3. **Трейсинг**: Добавить distributed tracing
4. **Тестирование**: Покрыть тестами все обработчики
5. **Документация**: Добавить OpenAPI спецификацию для запросов
6. **Типизация**: Использовать строгую типизацию для валют и статусов
7. **Контекст**: Добавить поддержку context.Context для отмены операций
8. **Ретраи**: Добавить механизм повторных попыток для критических операций

# Примеры использования

## Синхронные запросы

```go
// Получение реферального кода
request := `{"user_id": 123}`
resp, err := natsConn.Request("referrals.getRefCode", []byte(request), time.Second)

// Перевод денег
request := `{"user_id": 123, "currency": "USD", "currency_id": 1}`
resp, err := natsConn.Request("referrals.transferRefMoney", []byte(request), time.Second)
```

## Асинхронные события

```go
// Публикация события первого депозита
event := `{
    "user_id": 123,
    "amount": 100.0,
    "currency": "USD",
    "currency_id": 1,
    "referral_id": 456
}`
js.Publish("userevents.first-dep", []byte(event))

// Публикация события завершения достижения
event := `{
    "user_id": 123,
    "ach_id": 1,
    "currency": "USD",
    "currency_id": 1,
    "referral_id": 456
}`
js.Publish("userevents.achievement-completed", []byte(event))
```

Пакет `natsbroker` обеспечивает надежную и масштабируемую интеграцию реферальной системы с NATS, поддерживая как синхронные запросы, так и асинхронную обработку событий пользователей.