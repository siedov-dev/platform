# Документация пакета Repository

## Обзор

Пакет `repository` содержит реализации репозиториев для работы с базой данных в реферальной системе. Все репозитории реализуют соответствующие интерфейсы из пакета `container` и используют библиотеку `sqlx` для выполнения SQL-запросов.

## Архитектура

Пакет следует паттерну Repository, обеспечивая абстракцию доступа к данным. Каждый репозиторий:
- Реализует соответствующий интерфейс из пакета `container`
- Использует единое подключение к базе данных через `db.Instance()`
- Предоставляет методы для CRUD операций и специализированных запросов

---

# 1. AchievementRewardsDBRepository

## Файл: [`achievement_rewards_rep.go`](achievement_rewards_rep.go)

### Описание
Репозиторий для работы с наградами за достижения в реферальной системе.

### Структура
```go
type AchievementRewardsDBRepository struct {
    db *sqlx.DB
}
```

### Конструктор
```go
func NewAchievementRewardsDBRepository() *AchievementRewardsDBRepository
```
**Назначение**: Создает новый экземпляр репозитория с подключением к базе данных.

### Методы

#### GetAchievementRewards
```go
func (r *AchievementRewardsDBRepository) GetAchievementRewards() ([]*models.ReferralAchievementReward, error)
```
**Назначение**: Возвращает список всех наград достижений из базы данных.

**SQL-запрос**:
```sql
SELECT * FROM referral_achievements_rewards
```

**Возвращает**:
- `[]*models.ReferralAchievementReward` - список наград
- `error` - ошибка выполнения запроса

### Интерфейс
Реализует `container.AchievementRewardsRepository`.

---

# 2. ReferralConfigDbRepository

## Файл: [`referral_config_rep.go`](referral_config_rep.go)

### Описание
Репозиторий для работы с конфигурациями реферальной системы.

### Структура
```go
type ReferralConfigDbRepository struct {
    db *sqlx.DB
}
```

### Конструктор
```go
func NewReferralConfigDbRepository() *ReferralConfigDbRepository
```
**Назначение**: Создает новый экземпляр репозитория с подключением к базе данных.

### Методы

#### GetReferralUsersConfigs
```go
func (r *ReferralConfigDbRepository) GetReferralUsersConfigs() ([]*models.ReferralConfig, error)
```
**Назначение**: Возвращает список конфигураций рефералов для пользователей.

**SQL-запрос**:
```sql
SELECT * FROM referral_configs WHERE type = '2'
```

#### GetReferralMainConfig
```go
func (r *ReferralConfigDbRepository) GetReferralMainConfig() (*models.ReferralConfig, error)
```
**Назначение**: Возвращает основную конфигурацию рефералов.

**SQL-запрос**:
```sql
SELECT * FROM referral_configs WHERE type = '0'
```

#### GetReferralPromoConfigs
```go
func (r *ReferralConfigDbRepository) GetReferralPromoConfigs() ([]*models.ReferralConfig, error)
```
**Назначение**: Возвращает список всех промо-конфигураций рефералов.

**SQL-запрос**:
```sql
SELECT * FROM referral_configs WHERE type = '1'
```

#### GetReferralActivePromoConfigs
```go
func (r *ReferralConfigDbRepository) GetReferralActivePromoConfigs() ([]*models.ReferralConfig, error)
```
**Назначение**: Возвращает активные промо-конфигурации (не истекшие).

**SQL-запрос**:
```sql
SELECT * FROM referral_configs WHERE type = '1' AND end_date > NOW()
```

### Интерфейс
Реализует `container.ReferralConfigRepository`.

---

# 3. ReferralEventDbRepository

## Файл: [`referral_events_repo.go`](referral_events_repo.go)

### Описание
Репозиторий для работы с событиями реферальной системы. Предоставляет полный набор CRUD операций.

### Структура
```go
type ReferralEventDbRepository struct {
    db *sqlx.DB
}
```

### Конструктор
```go
func NewReferralEventDbRepository() *ReferralEventDbRepository
```
**Назначение**: Создает новый экземпляр репозитория с подключением к базе данных.

### Методы

#### GetReferralEventByID
```go
func (r *ReferralEventDbRepository) GetReferralEventByID(id uint64) (*models.ReferralEvent, error)
```
**Назначение**: Возвращает событие реферала по его ID.

**SQL-запрос**:
```sql
SELECT * FROM referral_events WHERE id = ?
```

#### CreateReferralEvent
```go
func (r *ReferralEventDbRepository) CreateReferralEvent(event *models.ReferralEvent) error
```
**Назначение**: Создает новое событие реферала в базе данных.

**SQL-запрос**:
```sql
INSERT INTO referral_events (ref_id, user_id, type, data, amount, currency, profit, created_at, updated_at, status, config_id, fingerprint) 
VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?)
```

**Особенности**:
- Автоматически устанавливает `created_at` и `updated_at` в текущее время

#### UpdateReferralEvent
```go
func (r *ReferralEventDbRepository) UpdateReferralEvent(event *models.ReferralEvent) error
```
**Назначение**: Обновляет существующее событие реферала в базе данных.

**SQL-запрос**:
```sql
UPDATE referral_events SET user_id = ?, type = ?, data = ?, amount = ?, currency = ?, profit = ?, config_id = ?, fingerprint = ?, updated_at = NOW() 
WHERE id = ?
```

**Особенности**:
- Автоматически обновляет `updated_at`
- Не обновляет `ref_id` и `status`

#### DeleteReferralEvent
```go
func (r *ReferralEventDbRepository) DeleteReferralEvent(id uint64) error
```
**Назначение**: Удаляет событие реферала из базы данных по его ID.

**SQL-запрос**:
```sql
DELETE FROM referral_events WHERE id = ?
```

#### GetReferralEvents
```go
func (r *ReferralEventDbRepository) GetReferralEvents(ref_id uint64, offset uint64, limit uint64) ([]*models.ReferralEvent, error)
```
**Назначение**: Возвращает список событий реферала по его ID с пагинацией.

**SQL-запрос**:
```sql
SELECT * FROM referral_events WHERE ref_id = ? AND type > 1 ORDER BY created_at DESC LIMIT ? OFFSET ?
```

**Особенности**:
- Исключает события с типом `ReferralEventTypeClick` (0) и `ReferralEventTypeRegister` (1)
- Сортирует по дате создания в убывающем порядке
- Поддерживает пагинацию

#### GetReferralEventsCount
```go
func (r *ReferralEventDbRepository) GetReferralEventsCount(ref_id uint64) (uint64, error)
```
**Назначение**: Возвращает количество событий реферала (исключая клики и регистрации).

**SQL-запрос**:
```sql
SELECT COUNT(*) FROM referral_events WHERE ref_id = ? AND type > 1
```

### Интерфейс
Реализует `container.ReferralEventRepository`.

---

# 4. ReferralMUsersDBRepository

## Файл: [`referral_musers_repo.go`](referral_musers_repo.go)

### Описание
Репозиторий для работы с пользователями в контексте реферальной системы. Предоставляет статистику и информацию о пользователях, привлеченных через рефералов.

### Структура
```go
type ReferralMUsersDBRepository struct {
    db *sqlx.DB
}
```

### Конструктор
```go
func NewReferralMUsersRepository() *ReferralMUsersDBRepository
```
**Назначение**: Создает новый экземпляр репозитория с подключением к базе данных.

### Методы

#### GetReferralUsersCount
```go
func (r *ReferralMUsersDBRepository) GetReferralUsersCount(ref_id uint64) (uint64, error)
```
**Назначение**: Возвращает общее количество пользователей, приглашенных рефералом.

**SQL-запрос**:
```sql
SELECT count(id) FROM m_users WHERE ref_id = ?
```

#### GetReferralUsers
```go
func (r *ReferralMUsersDBRepository) GetReferralUsers(ref_id uint64, offset uint64, limit uint64) ([]*container.ReferralMUser, error)
```
**Назначение**: Возвращает пользователей, приглашенных рефералом, с информацией о прибыли и тратах.

**SQL-запрос**:
```sql
SELECT id, login, m_users.currency as currency_id, created_at as reg_date,
    COALESCE(profits.profit, 0) as profit, 
    COALESCE(profits.spent, 0) as money_spent, 
    COALESCE(profits.currency, m_users.currency) as profit_currency
FROM m_users 
LEFT JOIN (
    SELECT user_id, 
        sum(profit) as profit, 
        sum(IF(type = 2, amount, 0)) as spent, 
        currency
    FROM referral_events
    WHERE profit > 0
    GROUP BY user_id, currency
) as profits on (profits.user_id = m_users.id)
WHERE m_users.ref_id = ? 
LIMIT ? OFFSET ?
```

**Особенности**:
- Объединяет данные пользователей с их прибылью и тратами
- Использует `COALESCE` для обработки NULL значений
- Группирует прибыль по пользователю и валюте
- Поддерживает пагинацию

#### GetReferralUsersCountWithProfit
```go
func (r *ReferralMUsersDBRepository) GetReferralUsersCountWithProfit(ref_id uint64) (uint64, error)
```
**Назначение**: Возвращает количество пользователей, соответствующих условиям выборки `GetReferralUsers`.

**SQL-запрос**:
```sql
SELECT COUNT(*)
FROM m_users 
LEFT JOIN (
    SELECT user_id, sum(profit) as profit, currency
    FROM referral_events
    WHERE profit > 0
    GROUP BY user_id, currency
) as profits on (profits.user_id = m_users.id)
WHERE m_users.ref_id = ?
```

#### GetReferralUsersCountwithDeps
```go
func (r *ReferralMUsersDBRepository) GetReferralUsersCountwithDeps(ref_id uint64) (uint64, error)
```
**Назначение**: Возвращает количество пользователей с первыми депозитами.

**SQL-запрос**:
```sql
SELECT count(DISTINCT m_users.id) 
FROM m_users 
inner JOIN payout_transactions on (payout_transactions.user_id = m_users.id)
WHERE ref_id = ?
AND payout_transactions.user_dep_n = 1;
```

**Особенности**:
- Использует `INNER JOIN` с таблицей транзакций
- Фильтрует только первые депозиты (`user_dep_n = 1`)
- Использует `DISTINCT` для исключения дублирования

### Интерфейс
Реализует `container.ReferralMUsersRepository`.

---

# 5. UserReferralsDbRepository

## Файл: [`user_referrals_repo.go`](user_referrals_repo.go)

### Описание
Репозиторий для работы с реферальными кодами пользователей. Предоставляет полный набор CRUD операций и специализированные методы поиска.

### Структура
```go
type UserReferralsDbRepository struct {
    db *sqlx.DB
}
```

### Конструктор
```go
func NewUserReferralsDbRepository() *UserReferralsDbRepository
```
**Назначение**: Создает новый экземпляр репозитория с подключением к базе данных.

### Методы

#### GetUserReferralByID
```go
func (r *UserReferralsDbRepository) GetUserReferralByID(id uint64) (*models.UserReferral, error)
```
**Назначение**: Возвращает реферал по его ID.

**SQL-запрос**:
```sql
SELECT * FROM user_referrals WHERE id = ?
```

#### CreateUserReferral
```go
func (r *UserReferralsDbRepository) CreateUserReferral(userReferral *models.UserReferral) error
```
**Назначение**: Создает новый реферальный код в базе данных.

**SQL-запрос**:
```sql
INSERT INTO user_referrals (user_id, ref_code, created_at, updated_at) VALUES (?, ?, NOW(), NOW())
```

**Особенности**:
- Автоматически устанавливает `created_at` и `updated_at`
- Не сохраняет устаревшие поля (`profit_prc`, `bonus_ref_code`)

#### UpdateUserReferral
```go
func (r *UserReferralsDbRepository) UpdateUserReferral(userReferral *models.UserReferral) error
```
**Назначение**: Обновляет существующий реферальный код.

**SQL-запрос**:
```sql
UPDATE user_referrals SET user_id = ?, ref_code = ?, updated_at = NOW() WHERE id = ?
```

**Особенности**:
- Автоматически обновляет `updated_at`
- Обновляет только активные поля

#### DeleteUserReferral
```go
func (r *UserReferralsDbRepository) DeleteUserReferral(id uint64) error
```
**Назначение**: Удаляет реферальный код по ID.

**SQL-запрос**:
```sql
DELETE FROM user_referrals WHERE id = ?
```

#### GetUserReferralByUserID
```go
func (r *UserReferralsDbRepository) GetUserReferralByUserID(userID uint64) (*models.UserReferral, error)
```
**Назначение**: Возвращает реферальный код по ID пользователя.

**SQL-запрос**:
```sql
SELECT * FROM user_referrals WHERE user_id = ?
```

**Особенности**:
- Обрабатывает случай отсутствия записи (`sql.ErrNoRows`)
- Возвращает `nil` вместо ошибки, если запись не найдена

#### GetUserReferralByRefCode
```go
func (r *UserReferralsDbRepository) GetUserReferralByRefCode(refCode string) (*models.UserReferral, error)
```
**Назначение**: Возвращает реферальный код по строковому коду.

**SQL-запрос**:
```sql
SELECT * FROM user_referrals WHERE ref_code = ?
```

#### GetUserReferrals
```go
func (r *UserReferralsDbRepository) GetUserReferrals(start int64, limit int64) ([]*models.UserReferral, error)
```
**Назначение**: Возвращает список реферальных кодов с пагинацией.

**SQL-запрос**:
```sql
SELECT * FROM user_referrals LIMIT ? OFFSET ?
```

#### UserReferralExists
```go
func (r *UserReferralsDbRepository) UserReferralExists(userID uint64) (bool, error)
```
**Назначение**: Проверяет существование реферального кода для пользователя.

**SQL-запрос**:
```sql
SELECT EXISTS(SELECT 1 FROM user_referrals WHERE user_id = ?)
```

**Особенности**:
- Использует `EXISTS` для эффективной проверки
- Возвращает булево значение

### Интерфейс
Реализует `container.UserReferralRepository`.

---

# Общие особенности

## Подключение к базе данных
Все репозитории используют единое подключение через `db.Instance()`:
```go
func NewRepository() *Repository {
    db := db.Instance()
    return &Repository{db: db}
}
```

## Обработка ошибок
- Все методы возвращают ошибки выполнения SQL-запросов
- Специальная обработка `sql.ErrNoRows` в `GetUserReferralByUserID`
- Ошибки передаются без дополнительной обработки

## SQL-запросы
- Используются параметризованные запросы для предотвращения SQL-инъекций
- Автоматическое управление временными метками (`NOW()`)
- Сложные JOIN-запросы для агрегации данных

## Пагинация
Поддерживается в методах:
- `GetReferralEvents(ref_id, offset, limit)`
- `GetReferralUsers(ref_id, offset, limit)`
- `GetUserReferrals(start, limit)`

## Фильтрация
- Исключение определенных типов событий (`type > 1`)
- Фильтрация по активным промо-конфигурациям (`end_date > NOW()`)
- Фильтрация по первым депозитам (`user_dep_n = 1`)

# Диаграмма архитектуры

```
┌─────────────────────────────────────────────────────────────┐
│                    Repository Layer                         │
├─────────────────────────────────────────────────────────────┤
│  AchievementRewards  │  ReferralConfig  │  ReferralEvent    │
│  DBRepository        │  DbRepository    │  DbRepository     │
├─────────────────────────────────────────────────────────────┤
│  ReferralMUsers      │  UserReferrals   │                   │
│  DBRepository        │  DbRepository    │                   │
└─────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────┐
│                     Database Layer                          │
│                      (sqlx + MySQL)                        │
└─────────────────────────────────────────────────────────────┘
```

## Таблицы базы данных

| Репозиторий | Основная таблица | Дополнительные таблицы |
|-------------|------------------|------------------------|
| `AchievementRewardsDBRepository` | `referral_achievements_rewards` | - |
| `ReferralConfigDbRepository` | `referral_configs` | - |
| `ReferralEventDbRepository` | `referral_events` | - |
| `ReferralMUsersDBRepository` | `m_users` | `referral_events`, `payout_transactions` |
| `UserReferralsDbRepository` | `user_referrals` | - |

# Рекомендации по использованию

## Инициализация
```go
// Создание репозиториев
achievementRepo := repository.NewAchievementRewardsDBRepository()
configRepo := repository.NewReferralConfigDbRepository()
eventRepo := repository.NewReferralEventDbRepository()
musersRepo := repository.NewReferralMUsersRepository()
userRefRepo := repository.NewUserReferralsDbRepository()

// Регистрация в контейнере
container.SetRepositories(configRepo, achievementRepo, eventRepo, userRefRepo, musersRepo)
```

## Обработка ошибок
```go
event, err := eventRepo.GetReferralEventByID(123)
if err != nil {
    if err == sql.ErrNoRows {
        // Запись не найдена
        return nil, ErrEventNotFound
    }
    // Другая ошибка базы данных
    return nil, fmt.Errorf("failed to get event: %w", err)
}
```

## Пагинация
```go
const pageSize = 20
offset := uint64(page * pageSize)
events, err := eventRepo.GetReferralEvents(refID, offset, pageSize)
```

# Потенциальные улучшения

1. **Транзакции**: Добавить поддержку транзакций для атомарных операций
2. **Кэширование**: Реализовать кэширование для часто запрашиваемых данных
3. **Метрики**: Добавить метрики производительности запросов
4. **Логирование**: Логировать выполняемые SQL-запросы
5. **Валидация**: Добавить валидацию входных параметров
6. **Подготовленные запросы**: Использовать prepared statements для повышения производительности