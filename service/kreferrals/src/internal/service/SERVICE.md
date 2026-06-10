# Документация сервисного слоя

## Обзор

Сервисный слой содержит бизнес-логику для управления конфигурациями реферальной системы и наградами за достижения. Он обеспечивает централизованное управление настройками, автоматическое обновление промо-акций и кэширование данных в памяти для быстрого доступа.

## Архитектура

Сервисный слой состоит из двух основных компонентов:
- **Service** ([`service.go`](service.go)) - основной сервис управления конфигурациями
- **RefConfig** ([`ref_config.go`](ref_config.go)) - структура конфигурации реферальной системы

---

# Основной сервис

## Файл: [`service.go`](service.go)

### Глобальные переменные

```go
var (
    main_config         *RefConfig               // Основная конфигурация рефералов
    active_promo_config *RefConfig               // Активная промо-конфигурация рефералов
    active_promo        *models.ReferralConfig   // Активная промо-конфигурация для проверки дат
    promo_configs       []*models.ReferralConfig // Список ожидающих промо-конфигураций
    user_configs        map[uint64]*RefConfig    // Конфигурации для пользователей (ключ - ID пользователя)
    mx                  sync.RWMutex             // Мьютекс для потокобезопасности
    ctx                 context.Context          // Контекст для управления жизненным циклом
    ticker              *time.Ticker             // Тикер для периодических обновлений
    ach_rewards         map[uint64]map[string]float64 // Награды за достижения
)
```

### Функции жизненного цикла

#### MustInit
```go
func MustInit(context context.Context)
```

**Назначение**: Инициализация сервиса с загрузкой конфигураций и запуском фонового обновления.

**Алгоритм**:
1. Сохранение контекста
2. Загрузка всех конфигураций через `LoadConfigs()`
3. Запуск тикера с интервалом 1 минута
4. Запуск горутины для периодического обновления:
   ```go
   ticker = time.NewTicker(1 * time.Minute)
   go func() {
       for {
           select {
           case <-ticker.C:
               err := updateConfigs()
               if err != nil {
                   logger.Get().Error("Failed to update referral configs", "error", err.Error())
               }
               logger.DailyLog.Flush()
           case <-ctx.Done():
               ticker.Stop()
               return
           }
       }
   }()
   ```

**Особенности**:
- Паникует при ошибке загрузки конфигураций
- Запускает фоновую горутину для автоматических обновлений
- Поддерживает graceful shutdown через контекст

#### Stop
```go
func Stop()
```

**Назначение**: Корректная остановка сервиса с очисткой ресурсов.

**Алгоритм**:
1. Блокировка мьютекса
2. Остановка тикера
3. Очистка всех глобальных переменных
4. Освобождение памяти

### Функции управления конфигурациями

#### LoadConfigs
```go
func LoadConfigs() error
```

**Назначение**: Загрузка всех конфигураций из базы данных в память.

**Алгоритм загрузки**:

1. **Основная конфигурация**:
   ```go
   m_config, err := container.GetReferralConfigRepository().GetReferralMainConfig()
   main_config, err = NewRefConfig(m_config)
   ```

2. **Пользовательские конфигурации**:
   ```go
   user_confs, err := container.GetReferralConfigRepository().GetReferralUsersConfigs()
   user_configs = make(map[uint64]*RefConfig, len(user_confs))
   for _, conf := range user_confs {
       uconf, err := NewRefConfig(conf)
       user_configs[conf.UserID] = uconf
   }
   ```

3. **Промо-конфигурации**:
   ```go
   promo_configs, err = container.GetReferralConfigRepository().GetReferralActivePromoConfigs()
   
   // Поиск активной промо-конфигурации
   for i, conf := range promo_configs {
       if conf.StartDate.Before(time.Now()) && conf.EndDate.After(time.Now()) {
           active_promo_config, err = NewRefConfig(conf)
           active_promo = conf
           // Удаление из списка ожидающих
           promo_configs = append(promo_configs[:i], promo_configs[i+1:]...)
           break
       }
   }
   ```

4. **Награды за достижения**:
   ```go
   err = loadAchievementRewards()
   ```

**Особенности**:
- Потокобезопасная загрузка с блокировкой мьютекса
- Автоматическое определение активных промо-акций
- Обработка различных типов данных в JSON

#### updateConfigs
```go
func updateConfigs() error
```

**Назначение**: Периодическое обновление конфигураций для обработки промо-акций.

**Логика обновления**:

1. **Проверка окончания активной промо-акции**:
   ```go
   if active_promo != nil && active_promo.EndDate.Before(time.Now()) {
       active_promo_config = nil
       active_promo = nil
   }
   ```

2. **Поиск новых активных промо-акций**:
   ```go
   for i, conf := range promo_configs {
       if conf.StartDate.Before(time.Now()) && conf.EndDate.After(time.Now()) {
           active_promo_config, err = NewRefConfig(conf)
           active_promo = conf
           promo_configs = append(promo_configs[:i], promo_configs[i+1:]...)
           break
       }
   }
   ```

**Особенности**:
- Автоматическая активация промо-акций по времени
- Автоматическая деактивация истекших промо-акций
- Потокобезопасность с блокировкой мьютекса

#### loadAchievementRewards
```go
func loadAchievementRewards() error
```

**Назначение**: Загрузка наград за достижения из базы данных.

**Алгоритм**:
1. Получение всех наград из репозитория
2. Создание карты `ach_rewards[achievement_id][currency] = amount`
3. Парсинг JSON данных с поддержкой различных типов:
   ```go
   decodedSums := make(map[string]any)
   json.Unmarshal([]byte(reward.RewardSums), &decodedSums)
   
   for currency, sum := range decodedSums {
       switch value := sum.(type) {
       case float64:
           ach_rewards[reward.AchievementID][currency] = value
       case int:
           ach_rewards[reward.AchievementID][currency] = float64(value)
       case string:
           floatValue, err := strconv.ParseFloat(value, 64)
           ach_rewards[reward.AchievementID][currency] = floatValue
       }
   }
   ```

**Особенности**:
- Поддержка множественных типов данных в JSON
- Гибкое преобразование типов (int, float64, string)
- Детальная обработка ошибок парсинга

### Публичные функции доступа

#### GetUserIDByRefID
```go
func GetUserIDByRefID(ref_id uint64) (uint64, error)
```

**Назначение**: Получение ID пользователя по ID реферального кода.

**Алгоритм**:
1. Получение реферального кода из репозитория
2. Проверка существования кода и пользователя
3. Возврат ID пользователя или 0, если не найден

**Особенности**:
- TODO: Добавить кэширование для повышения производительности
- Обработка случаев с NULL значениями

#### GetUserConfig
```go
func GetUserConfig(userID uint64) *RefConfig
```

**Назначение**: Получение конфигурации для конкретного пользователя с приоритетами.

**Приоритеты конфигураций**:
1. **Активная промо-конфигурация** (наивысший приоритет)
2. **Индивидуальная конфигурация пользователя**
3. **Основная конфигурация** (по умолчанию)

```go
func GetUserConfig(userID uint64) *RefConfig {
    mx.RLock()
    defer mx.RUnlock()

    if active_promo_config != nil {
        return active_promo_config  // Промо-акция имеет приоритет
    }

    if user_config, ok := user_configs[userID]; ok {
        return user_config  // Индивидуальная конфигурация
    }

    return main_config  // Основная конфигурация по умолчанию
}
```

**Особенности**:
- Потокобезопасное чтение с RLock
- Четкая иерархия приоритетов
- Всегда возвращает валидную конфигурацию

#### GetAchievementReward
```go
func GetAchievementReward(ach_id uint64, currency string) float64
```

**Назначение**: Получение размера награды за конкретное достижение в указанной валюте.

**Алгоритм**:
```go
if rewards, ok := ach_rewards[ach_id]; ok {
    if amount, ok := rewards[currency]; ok {
        return amount
    }
}
return 0.0  // Если награда не найдена
```

**Особенности**:
- Потокобезопасное чтение
- Возврат 0.0 для несуществующих наград
- Поддержка множественных валют

#### GetAchievementRewards
```go
func GetAchievementRewards() map[uint64]map[string]float64
```

**Назначение**: Получение всех наград за достижения.

**Возвращает**: Полную карту наград `[achievement_id][currency] = amount`

---

# Конфигурация рефералов

## Файл: [`ref_config.go`](ref_config.go)

### Структура RefConfig

```go
type RefConfig struct {
    ID     uint64             `json:"id"`      // Идентификатор конфигурации
    GGRPrc int                `json:"ggr_prc"` // Процент GGR (Gross Gaming Revenue)
    FdSums map[string]float64 `json:"fd_sums"` // Суммы FD (First Deposit) по валютам
}
```

**Назначение**: Представление конфигурации реферальной системы в удобном для использования формате.

### Конструктор

#### NewRefConfig
```go
func NewRefConfig(dbConfig *models.ReferralConfig) (*RefConfig, error)
```

**Назначение**: Создание экземпляра RefConfig из модели базы данных.

**Алгоритм создания**:

1. **Инициализация структуры**:
   ```go
   c := &RefConfig{}
   c.GGRPrc = dbConfig.GGRPrc
   c.FdSums = make(map[string]float64)
   c.ID = dbConfig.ID
   ```

2. **Парсинг JSON данных FdSums**:
   ```go
   if dbConfig.FdSums != "" {
       decodedSums := make(map[string]any)
       json.Unmarshal([]byte(dbConfig.FdSums), &decodedSums)
       
       for currency, sum := range decodedSums {
           // Обработка различных типов данных
       }
   }
   ```

3. **Преобразование типов данных**:
   ```go
   switch value := sum.(type) {
   case float64:
       c.FdSums[currency] = value
   case int:
       c.FdSums[currency] = float64(value)
   case string:
       if floatValue, err := strconv.ParseFloat(value, 64); err == nil {
           c.FdSums[currency] = floatValue
       } else {
           return nil, fmt.Errorf("invalid FD sum for currency %s: %s", currency, value)
       }
   default:
       return nil, errors.New("invalid type for FD sum: " + fmt.Sprintf("%T", sum))
   }
   ```

**Особенности**:
- Гибкое преобразование типов из JSON
- Детальная обработка ошибок
- Поддержка пустых FdSums

### Методы доступа

#### GetGGRPrc
```go
func (c *RefConfig) GetGGRPrc() int
```

**Назначение**: Получение процента GGR (Gross Gaming Revenue).

**Возвращает**: Процент от валового игрового дохода для начисления рефералу.

#### GetFdSum
```go
func (c *RefConfig) GetFdSum(currency string) float64
```

**Назначение**: Получение суммы бонуса за первый депозит в указанной валюте.

**Алгоритм**:
```go
if sum, ok := c.FdSums[currency]; ok {
    return sum
}
return 0.0  // Если валюта не найдена
```

**Особенности**:
- Безопасный доступ к карте валют
- Возврат 0.0 для неподдерживаемых валют

---

# Диаграмма архитектуры

```
┌─────────────────────────────────────────────────────────────────┐
│                        Service Layer                           │
├─────────────────────────────────────────────────────────────────┤
│                    Управление конфигурациями                    │
│  ┌─────────────────┐    ┌─────────────────┐    ┌──────────────┐ │
│  │   Main Config   │    │  Promo Config   │    │ User Configs │ │
│  │   • GGR %       │    │   • Start Date  │    │  • Per User  │ │
│  │   • FD Sums     │    │   • End Date    │    │  • Override  │ │
│  │   • Default     │    │   • Priority    │    │  • Custom    │ │
│  └─────────────────┘    └─────────────────┘    └──────────────┘ │
├─────────────────────────────────────────────────────────────────┤
│                    Управление наградами                         │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │              Achievement Rewards                            │ │
│  │   ach_rewards[achievement_id][currency] = amount            │ │
│  │   • Multi-currency support                                 │ │
│  │   • JSON parsing with type conversion                      │ │
│  │   • Thread-safe access                                     │ │
│  └─────────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────────┤
│                    Автоматическое обновление                    │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │                    Background Ticker                       │ │
│  │   • 1-minute interval                                      │ │
│  │   • Promo activation/deactivation                          │ │
│  │   • Graceful shutdown support                              │ │
│  │   • Error logging and recovery                             │ │
│  └─────────────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                      Repository Layer                          │
│  • ReferralConfigRepository  • AchievementRewardsRepository    │
│  • UserReferralRepository                                      │
└─────────────────────────────────────────────────────────────────┘
```

# Приоритеты конфигураций

## Иерархия применения

1. **Активная промо-конфигурация** (временная, наивысший приоритет)
   - Действует в указанный период времени
   - Применяется ко всем пользователям
   - Автоматически активируется и деактивируется

2. **Индивидуальная конфигурация пользователя**
   - Персональные настройки для VIP-клиентов
   - Постоянно действующая
   - Переопределяет основную конфигурацию

3. **Основная конфигурация** (по умолчанию)
   - Базовые настройки системы
   - Применяется при отсутствии других конфигураций
   - Всегда доступна

## Пример применения приоритетов

```go
// Пользователь 123 имеет индивидуальную конфигурацию
// Но активна промо-акция "Новый год"
config := GetUserConfig(123)

// Результат: промо-конфигурация (приоритет 1)
// Индивидуальная конфигурация временно не действует
```

# Управление промо-акциями

## Жизненный цикл промо-акции

1. **Создание**: Промо-конфигурация создается в базе данных с датами начала и окончания
2. **Ожидание**: Конфигурация находится в списке `promo_configs`
3. **Активация**: При наступлении `StartDate` конфигурация становится активной
4. **Действие**: Промо-конфигурация применяется ко всем пользователям
5. **Деактивация**: При наступлении `EndDate` конфигурация удаляется

## Автоматическое управление

```go
// Каждую минуту проверяется:
if active_promo != nil && active_promo.EndDate.Before(time.Now()) {
    // Деактивация истекшей промо-акции
    active_promo_config = nil
    active_promo = nil
}

// Поиск новых активных промо-акций
for _, conf := range promo_configs {
    if conf.StartDate.Before(time.Now()) && conf.EndDate.After(time.Now()) {
        // Активация новой промо-акции
        active_promo_config, _ = NewRefConfig(conf)
        active_promo = conf
        break
    }
}
```

# Потокобезопасность

## Стратегия блокировок

- **RWMutex**: Используется для разделения операций чтения и записи
- **Read Lock**: Для функций получения данных (`GetUserConfig`, `GetAchievementReward`)
- **Write Lock**: Для функций изменения данных (`LoadConfigs`, `updateConfigs`)

## Примеры использования

```go
// Чтение (множественные горутины могут читать одновременно)
func GetUserConfig(userID uint64) *RefConfig {
    mx.RLock()
    defer mx.RUnlock()
    // ... чтение данных
}

// Запись (эксклюзивный доступ)
func LoadConfigs() error {
    mx.Lock()
    defer mx.Unlock()
    // ... изменение данных
}
```

# Обработка ошибок

## Стратегии обработки

1. **Критические ошибки**: Паника при инициализации
   ```go
   if err != nil {
       panic("Failed to load referral configs: " + err.Error())
   }
   ```

2. **Периодические ошибки**: Логирование с продолжением работы
   ```go
   if err != nil {
       logger.Get().Error("Failed to update referral configs", "error", err.Error())
   }
   ```

3. **Ошибки парсинга**: Детальные сообщения об ошибках
   ```go
   return fmt.Errorf("invalid FD sum for currency %s: %s", currency, value)
   ```

# Производительность и оптимизация

## Кэширование

- **В памяти**: Все конфигурации загружаются в память при старте
- **Быстрый доступ**: O(1) доступ к конфигурациям через карты
- **Минимальные запросы к БД**: Данные обновляются только периодически

## Потенциальные улучшения

1. **Кэширование GetUserIDByRefID**: Добавить кэш для часто запрашиваемых ID
2. **Lazy Loading**: Загружать пользовательские конфигурации по требованию
3. **Метрики**: Добавить метрики производительности
4. **Валидация**: Добавить валидацию конфигураций при загрузке

# Примеры использования

## Инициализация сервиса

```go
ctx := context.Background()
service.MustInit(ctx)

// Корректная остановка
defer service.Stop()
```

## Получение конфигурации пользователя

```go
config := service.GetUserConfig(userID)
ggrPercent := config.GetGGRPrc()
fdBonus := config.GetFdSum("USD")
```

## Получение наград за достижения

```go
reward := service.GetAchievementReward(achievementID, "USD")
if reward > 0 {
    // Начислить награду
}
```

## Получение ID пользователя по рефералу

```go
userID, err := service.GetUserIDByRefID(refID)
if err != nil {
    return err
}
if userID == 0 {
    return errors.New("referral not found")
}
```

Сервисный слой обеспечивает эффективное управление конфигурациями реферальной системы с автоматическим обновлением промо-акций, потокобезопасным доступом к данным и гибкой системой приоритетов конфигураций.