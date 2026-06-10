# Документация пакета Container

## Обзор

Пакет `container` представляет собой контейнер зависимостей (Dependency Injection Container) для реферальной системы. Он обеспечивает централизованное управление зависимостями и предоставляет глобальный доступ к репозиториям и сервисам через паттерн Service Locator.

## Архитектура

Пакет использует глобальные переменные для хранения экземпляров зависимостей и предоставляет функции-сеттеры и геттеры для их управления. Все геттеры включают проверки на `nil` с паникой для предотвращения работы с неинициализированными зависимостями.

---

# Основной файл контейнера

## Файл: [`container.go`](container.go)

### Глобальные переменные

```go
var configsRepository ReferralConfigRepository
var achievementRewardsRepository AchievementRewardsRepository
var referralEventRepository ReferralEventRepository
var userReferralRepository UserReferralRepository
var referralMUsersRepository ReferralMUsersRepository
var refWallet RefWallet
var eventsSaver EventsSaver
```

### Функции управления зависимостями

#### SetRepositories
```go
func SetRepositories(
    referralConfigRepo ReferralConfigRepository,
    achievementRewardsRepo AchievementRewardsRepository,
    referralEventRepo ReferralEventRepository,
    userReferralRepo UserReferralRepository,
    referralMUsersRepository ReferralMUsersRepository,
)
```
**Назначение**: Массовая установка всех репозиториев одним вызовом.

#### ReferralConfigRepository

**Сеттер**:
```go
func SetReferralConfigRepository(repo ReferralConfigRepository)
```

**Геттер**:
```go
func GetReferralConfigRepository() ReferralConfigRepository
```
- Паника при `nil`: "ReferralConfigRepository is not set"

#### AchievementRewardsRepository

**Сеттер**:
```go
func SetAchievementRewardsRepository(repo AchievementRewardsRepository)
```

**Геттер**:
```go
func GetAchievementRewardsRepository() AchievementRewardsRepository
```
- Паника при `nil`: "AchievementRewardsRepository is not set"

#### ReferralEventRepository

**Сеттер**:
```go
func SetReferralEventRepository(repo ReferralEventRepository)
```
- Паника при `nil` в сеттере: "ReferralEventRepository is not set"

**Геттер**:
```go
func GetReferralEventRepository() ReferralEventRepository
```
- Паника при `nil`: "ReferralEventRepository is not set"

#### UserReferralRepository

**Сеттер**:
```go
func SetUserReferralRepository(repo UserReferralRepository)
```
- Паника при `nil` в сеттере: "UserReferralRepository is not set"

**Геттер**:
```go
func GetUserReferralRepository() UserReferralRepository
```
- Паника при `nil`: "UserReferralRepository is not set"

#### ReferralMUsersRepository

**Сеттер**:
```go
func SetReferralMUsersRepository(repo ReferralMUsersRepository)
```
- Паника при `nil` в сеттере: "UserReferralRepository is not set"

**Геттер**:
```go
func GetReferralMUsersRepository() ReferralMUsersRepository
```
- Паника при `nil`: "UserReferralRepository is not set"

#### RefWallet

**Сеттер**:
```go
func SetRefWallet(wallet RefWallet)
```
- Паника при `nil` в сеттере: "RefWallet is not set"

**Геттер**:
```go
func GetRefWallet() RefWallet
```
- Паника при `nil`: "RefWallet is not set"

#### EventsSaver

**Сеттер**:
```go
func SetEventsSaver(saver EventsSaver)
```
- Паника при `nil` в сеттере: "EventsSaver is not set"

**Геттер**:
```go
func GetEventsSaver() EventsSaver
```
- Паника при `nil`: "EventsSaver is not set"

---

# Интерфейсы репозиториев

## Файл: [`repositories.go`](repositories.go)

### ReferralConfigRepository

Интерфейс для работы с конфигурациями реферальной системы.

```go
type ReferralConfigRepository interface {
    GetReferralUsersConfigs() ([]*models.ReferralConfig, error)
    GetReferralMainConfig() (*models.ReferralConfig, error)
    GetReferralPromoConfigs() ([]*models.ReferralConfig, error)
    GetReferralActivePromoConfigs() ([]*models.ReferralConfig, error)
}
```

#### Методы

| Метод | Описание |
|-------|----------|
| `GetReferralUsersConfigs()` | Получение пользовательских конфигураций |
| `GetReferralMainConfig()` | Получение основной конфигурации |
| `GetReferralPromoConfigs()` | Получение всех промо-конфигураций |
| `GetReferralActivePromoConfigs()` | Получение активных промо-конфигураций |

### AchievementRewardsRepository

Интерфейс для работы с наградами за достижения.

```go
type AchievementRewardsRepository interface {
    GetAchievementRewards() ([]*models.ReferralAchievementReward, error)
}
```

#### Методы

| Метод | Описание |
|-------|----------|
| `GetAchievementRewards()` | Получение всех наград за достижения |

### ReferralEventRepository

Интерфейс для работы с событиями реферальной системы.

```go
type ReferralEventRepository interface {
    GetReferralEventByID(id uint64) (*models.ReferralEvent, error)
    CreateReferralEvent(event *models.ReferralEvent) error
    UpdateReferralEvent(event *models.ReferralEvent) error
    DeleteReferralEvent(id uint64) error
    GetReferralEvents(ref_id uint64, offset uint64, limit uint64) ([]*models.ReferralEvent, error)
    GetReferralEventsCount(ref_id uint64) (uint64, error)
}
```

#### Методы

| Метод | Описание |
|-------|----------|
| `GetReferralEventByID(id)` | Получение события по ID |
| `CreateReferralEvent(event)` | Создание нового события |
| `UpdateReferralEvent(event)` | Обновление существующего события |
| `DeleteReferralEvent(id)` | Удаление события по ID |
| `GetReferralEvents(ref_id, offset, limit)` | Получение событий с пагинацией |
| `GetReferralEventsCount(ref_id)` | Подсчет количества событий |

### UserReferralRepository

Интерфейс для работы с реферальными кодами пользователей.

```go
type UserReferralRepository interface {
    GetUserReferralByID(id uint64) (*models.UserReferral, error)
    CreateUserReferral(userReferral *models.UserReferral) error
    UpdateUserReferral(userReferral *models.UserReferral) error
    DeleteUserReferral(id uint64) error
    GetUserReferralByUserID(userID uint64) (*models.UserReferral, error)
    GetUserReferralByRefCode(refCode string) (*models.UserReferral, error)
    GetUserReferrals(start int64, limit int64) ([]*models.UserReferral, error)
    UserReferralExists(userID uint64) (bool, error)
}
```

#### Методы

| Метод | Описание |
|-------|----------|
| `GetUserReferralByID(id)` | Получение реферала по ID |
| `CreateUserReferral(userReferral)` | Создание нового реферала |
| `UpdateUserReferral(userReferral)` | Обновление существующего реферала |
| `DeleteUserReferral(id)` | Удаление реферала по ID |
| `GetUserReferralByUserID(userID)` | Получение реферала по ID пользователя |
| `GetUserReferralByRefCode(refCode)` | Получение реферала по коду |
| `GetUserReferrals(start, limit)` | Получение рефералов с пагинацией |
| `UserReferralExists(userID)` | Проверка существования реферала |

### ReferralMUsersRepository

Интерфейс для работы с пользователями реферальной системы.

```go
type ReferralMUsersRepository interface {
    GetReferralUsersCount(ref_id uint64) (uint64, error)
    GetReferralUsers(ref_id uint64, offset uint64, limit uint64) ([]*ReferralMUser, error)
    GetReferralUsersCountWithProfit(ref_id uint64) (uint64, error)
    GetReferralUsersCountwithDeps(ref_id uint64) (uint64, error)
}
```

#### Методы

| Метод | Описание |
|-------|----------|
| `GetReferralUsersCount(ref_id)` | Подсчет пользователей реферала |
| `GetReferralUsers(ref_id, offset, limit)` | Получение пользователей с пагинацией |
| `GetReferralUsersCountWithProfit(ref_id)` | Подсчет пользователей с прибылью |
| `GetReferralUsersCountwithDeps(ref_id)` | Подсчет пользователей с депозитами |

### ReferralMUser

Структура для представления пользователя в реферальной системе.

```go
type ReferralMUser struct {
    ID             uint64  `db:"id" json:"id"`
    Login          string  `db:"login" json:"login"`
    RegDate        string  `db:"reg_date" json:"reg_date"`
    MoneySpent     float64 `db:"money_spent" json:"money_spent"`
    Profit         float64 `db:"profit" json:"profit"`
    CurrencyID     int     `db:"currency_id" json:"currency_id"`
    ProfitCurrency int     `db:"profit_currency" json:"profit_currency"`
}
```

#### Поля

| Поле | Тип | Описание |
|------|-----|----------|
| `ID` | `uint64` | ID пользователя |
| `Login` | `string` | Логин пользователя |
| `RegDate` | `string` | Дата регистрации пользователя |
| `MoneySpent` | `float64` | Сумма денег, потраченная пользователем |
| `Profit` | `float64` | Прибыль пользователя |
| `CurrencyID` | `int` | ID валюты пользователя |
| `ProfitCurrency` | `int` | Валюта прибыли |

---

# Интерфейс RefWallet

## Файл: [`refwallet.go`](refwallet.go)

Интерфейс для работы с реферальным кошельком.

```go
type RefWallet interface {
    ChangeRefBalance(userID uint64, amount float64, currency string) error
    ChangeAccumulativeBalance(userID uint64, amount float64, currency string) error
    TransferRefMoney(userID uint64, currency string) (transferred float64, err error)
    TransferAccumMoney(userID uint64, currency string) (transferred float64, err error)
    TransferAllAccumulatedMoney(userID uint64) (TransferAllAccumMoneyResponse, error)
}
```

## Методы

| Метод | Описание |
|-------|----------|
| `ChangeRefBalance(userID, amount, currency)` | Изменение баланса реферала |
| `ChangeAccumulativeBalance(userID, amount, currency)` | Изменение накопительного баланса реферала |
| `TransferRefMoney(userID, currency)` | Перевод реферальных денег |
| `TransferAccumMoney(userID, currency)` | Перевод накопительных денег |
| `TransferAllAccumulatedMoney(userID)` | Перевод всех накопленных средств |

## TransferAllAccumMoneyResponse

Структура ответа для перевода всех накопленных средств.

```go
type TransferAllAccumMoneyResponse struct {
    Error     string          `json:"err"`
    ErrorCode int             `json:"cd"`
    UserID    int64           `json:"user_id"`
    Amounts   map[int]float64 `json:"amounts"`
}
```

### Поля

| Поле | Тип | Описание |
|------|-----|----------|
| `Error` | `string` | Ошибка, если есть |
| `ErrorCode` | `int` | Код ошибки, если есть |
| `UserID` | `int64` | ID пользователя, которому переводятся все накопленные средства |
| `Amounts` | `map[int]float64` | Сумма переведенных средств по валютам |

---

# Интерфейс EventsSaver

## Файл: [`events_saver.go`](events_saver.go)

Интерфейс для сохранения событий.

```go
type EventsSaver interface {
    SaveReferralEvent(event *models.ReferralEvent) error
}
```

## Методы

| Метод | Описание |
|-------|----------|
| `SaveReferralEvent(event)` | Сохраняет событие реферала в базе данных |

---

# Использование

## Инициализация контейнера

```go
// Инициализация всех репозиториев
container.SetRepositories(
    configRepo,
    achievementRepo,
    eventRepo,
    userRefRepo,
    musersRepo,
)

// Инициализация дополнительных сервисов
container.SetRefWallet(walletService)
container.SetEventsSaver(eventsSaver)
```

## Получение зависимостей

```go
// Получение репозиториев
configRepo := container.GetReferralConfigRepository()
eventRepo := container.GetReferralEventRepository()

// Получение сервисов
wallet := container.GetRefWallet()
saver := container.GetEventsSaver()
```

## Пример использования в сервисе

```go
func SomeBusinessLogic() error {
    // Получение зависимостей из контейнера
    eventRepo := container.GetReferralEventRepository()
    wallet := container.GetRefWallet()
    
    // Использование зависимостей
    event, err := eventRepo.GetReferralEventByID(123)
    if err != nil {
        return err
    }
    
    return wallet.ChangeRefBalance(event.UserID, 100.0, "USD")
}
```

# Особенности и рекомендации

## Безопасность

- Все геттеры включают проверки на `nil` с паникой
- Некоторые сеттеры также проверяют входные параметры на `nil`
- Рекомендуется инициализировать все зависимости при старте приложения


## Паттерны проектирования

Пакет реализует следующие паттерны:

- **Service Locator**: Централизованный доступ к зависимостям
- **Dependency Injection**: Внедрение зависимостей через сеттеры
- **Repository Pattern**: Абстракция доступа к данным через интерфейсы
- **Interface Segregation**: Разделение интерфейсов по функциональности

# Диаграмма зависимостей

```
┌─────────────────┐
│    Container    │
├─────────────────┤
│ Repositories:   │
│ • Config        │
│ • Achievement   │
│ • Event         │
│ • UserReferral  │
│ • MUsers        │
├─────────────────┤
│ Services:       │
│ • RefWallet     │
│ • EventsSaver   │
└─────────────────┘
        │
        ▼
┌─────────────────┐
│  Business Logic │
│   (Use Cases)   │
└─────────────────┘
```

Контейнер служит центральной точкой для управления всеми зависимостями системы и обеспечивает их доступность для бизнес-логики приложения.