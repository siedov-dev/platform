# User Manager
Сервис используется для кеширование текущих активных пользователей, работы с их кошелькамми (изменение баланса, управление тоталами, заблокированными для отыгрыша средстваими, реферальными средствами), обмена сообщений (ervents) между интерфейсом и бэкендом, запуском и закрытием игровой сессии.  
Так же является бэкендом для игровых систем `Slotegrator`, `PragmaticPlay` и `QT`.

## Оглавление

- [Основные моменты работы](#основные-моменты-работы)
- [Бонусы](#бонусы)
  - [Бонусные игры](#бонусные-игры)
- [Документация по HTTP-маршрутам](#документация-по-http-маршрутам)
  - [Системные и тестовые маршруты](#системные-и-тестовые-маршруты)
  - [Пользовательские бонусы](#пользовательские-бонусы)
  - [Валюты](#валюты)
  - [Балансы пользователя](#балансы-пользователя)
    - [Заблокированные средства](#заблокированные-средства)
    - [Бонусный баланс](#бонусный-баланс)
    - [Реферальный баланс](#реферальный-баланс)
  - [Управление кошельком](#управление-кошельком)
  - [События пользователя](#события-пользователя)
  - [Пользователи](#пользователи)
  - [Игровые сессии](#игровые-сессии)
  - [Интеграция с игровыми системами](#интеграция-с-игровыми-системами)
    - [Slotegrator](#slotegrator)
    - [PragmaticPlay](#pragmaticplay)
    - [QT](#qt)
  - [Подпись запроса](#подпись-запроса)
- [Описание моделей базы данных](#описание-моделей-базы-данных)
  - [BonusStatistic](#bonusstatistic)
  - [BonusTransaction](#bonustransaction)
  - [GameTransaction](#gametransaction)
  - [M_User](#m_user)
  - [UserBonus](#userbonus)
  - [UserCurrency](#usercurrency)
  - [UserGameStats](#usergamestats)
  - [UserWallet](#userwallet)
- [Описание репозиториев](#описание-репозиториев)
  - [BonusStatisticRepository](#bonusstatisticrepository)
  - [BonustransactionRepository](#bonustransactionrepository)
  - [GameSessionRepository](#gamesessionrepository)
  - [GameTransactionRepository](#gametransactionrepository)
  - [UserBonusRepository](#userbonusrepository)
  - [UserRepository](#userrepository)
  - [WalletsRepository](#walletsrepository)

---

## Основные моменты работы
При каждом обращении о получении данных или изменении баланса, данные о пользователе и его кошельках загружаются в память и сохраняются там в течении 10 минут. При каждом следующем запросе время до очистки пользователя из памяти продлевается.  

Некоторые запросы (операции с реферальными средствами) не загружают пользователя в кеш если его там нету, а выполняют операции напрямую с базой так как являются редкими, используются для начисления средсв либо ручного управления данными пользователя и не имеют смысла загружать память на длительный период.  

Если кошелек для указанного пользователя/валюты не найден в базе то он создаетя.  
Для ускорения работы и уменшения к-ва обращений к БД во времья игры сохранение данных кошелька откладывается на 2 секунды при каждом запросе но не более указанного к-ва раз.  

Все игровые транзакции накапливаются в кеше и сохраняются блоками в отдельной горутине не мешая работе основному потоку игры.

# Бонусы

Игры на бонусы используют средства установленные в поле `money_available` модели `UserBonus`.  
Некоторые бонусы могут использовать так же основные средства кошелька во времья игры (`SharedMoneyBonus`, `SmartFsBonus`...)

Каждый бонус имеет свой тип `Type` которые указывает механику обработки игры данного бонуса (`BonusHandler`) и данные о бонусе отображаемые для пользователя, механику проверки выполнения условий бонуса (`BonusActualizer`).  

Игровые провайдеры предоставляют обработку фриспинов на своей стороне. В таких случаях бонусы, которые используют это систему, обрабатывают данные фриспинов в соответствующих обработчиках внешних фриспинов (`BonusExternalFsHandler`)

## Бонусные игры

Каждый бонус может иметь ограниченный список доступных отыгрыша игр. Список игр в json формати хранится в поле `AllowedGames` модели `UserBonus`.
Если для бонуса доступна только одна игра то заполняются поля `GameProvider` - игровая система игры и `GameID` - ид игры в игровой системе.
При отсутствии установленных игр бонус считается доступным для любой игры.  
Разрешение на запуск бонусной игры проверяется на стороне запуска с использование сервиса `games_manager`.  

Некоторые бонусы после отыгрыша фриспинов "сбрасывают" установленные игры, после чего для бонуса доступными играми становятся все игры настроенные в црм "по умолчанию" для бонусов. (см. документацию `games_manger`, crm)

# Документация по HTTP-маршрутам

## Общие сведения

Маршруты реализованы в функции `SetRoutes` и регистрируются через chi.Mux. Каждый маршрут связан с обработчиком, реализующим бизнес-логику приложения.

---

## Системные и тестовые маршруты

| Метод | Путь | Описание | Обработчик |
|-------|------|----------|------------|
| GET | `/` | Проверка работоспособности сервиса | `handlers.OkHanlder` |
| GET | `/ping` | Ответ "pong" | `handlers.Ping` |

---

## Пользовательские бонусы

| Метод | Путь | Описание | Обработчик |
|-------|------|----------|------------|
| GET | `/user/{user_id}/bonusStatus` | Получение информации о текущем статусе бонуса пользователя | `handlers.BonusStatus` |

---

## Валюты

| Метод | Путь | Описание | Обработчик | Ответ |
|-------|------|----------|------------| ----- |
| GET | `/currency/{curr}/name` | Получение имени валюты по ID | `currencycontroller.GetCurrencyName` | {"cd":200,"err":"","cn":"RUB"} |
| GET | `/currency/{curr}/gameCurrency` | Получение игровой валюты по ID | `currencycontroller.GetGameCurrencyName` | {"cd":200,"err":"","cn":"RUB"} |
| GET | `/currency/{curr}/intCode` | Получение международного кода валюты | `currencycontroller.GetCurrencyIntCode` | {"cd":200,"err":"","ic":643} |
| GET | `/currency/{curr}/hallId` | Получение hallId валюты | `currencycontroller.GetCurrencyHallID` | {"cd":200,"err":"","hid":237} |
| GET | `/currency/{curr}/maxAutopayout` | Получение максимального значения автовыплаты | `currencycontroller.GetCurrencyMaxAutopayout` | {"cd":200,"err":"","max":7000} |

---

## Балансы пользователя

| Метод | Путь | Описание | Обработчик |
|-------|------|----------|------------|
| POST | `/balances` | Получение баланса пользователя по всем кошелькам | `walletcontroller.JsonBalances` |
| POST | `/balance` | Получение баланса по ID валюты | `walletcontroller.JsonBalance` |
| POST | `/balance/add` | Увеличение баланса | `walletcontroller.JsonBalanceAdd` |
| POST | `/balance/rem` | Снятие средств с баланса | `walletcontroller.JsonBalanceRem` |

#### Примеры:
`/balances`
```json
{"user_id":12}
```

```json
{
    "cd":200, // код результата
    "err":"", // ошибка
    "a":1, // активная валюта
    "Values":{ // кошельки
        "1":{ // ид валюты
            "v":66867.3, // баланс float
            "vc":6686730, // баланс в центах
            "cn":"RUB", // название валюты
            "ci":1, // ид валюты
            "a":true, // активен
            "b":0 // ид бонуса для баланса
            },
        "2":{
            "v":500,
            "vc":50000,
            "cn":"UAH",
            "ci":2,
            "a":true,
            "b":0}
            ,
    }
}
```

`/balance`
```json
{"user_id":12,"currency":1}
```

```json
{
    "cd":200,
    "err":"",
    "v":66867.3,
    "vc":6686730,
    "cn":"RUB",
    "ci":1,
    "a":true,
    "b":0
}
```

`/balance/add`
```json
{"user_id":12,"currency":1,"amount":5000.50}
```

```json
{
    "cd":200,
    "err":"",
    "v":66867.3,
    "vc":6686730,
    "cn":"RUB",
    "ci":1,
    "a":true,
    "b":0
}
```

`/balance/rem`
```json
{"user_id":12,"currency":1,"amount":5000.50}
```

```json
{
    "cd":200,
    "err":"",
    "v":66867.3,
    "vc":6686730,
    "cn":"RUB",
    "ci":1,
    "a":true,
    "b":0
}
```

### Заблокированные средства

| Метод | Путь | Описание | Обработчик |
|-------|------|----------|------------|
| POST | `/balance/locked` | Получение суммы заблокированного баланса | `walletcontroller.BalanceLocked` |
| POST | `/balance/locked/add` | Увеличение заблокированной суммы | `walletcontroller.BalanceLockedAdd` |
| POST | `/balance/locked/rem` | Уменьшение заблокированной суммы | `walletcontroller.BalanceLockedRem` |
| POST | `/balance/locked/set` | Установка заблокированной суммы | `walletcontroller.BalanceLockedSet` |

#### Примеры

`/balance/locked`
```json
{"user_id":12,"currency":1}
```

```json
{"cd":200,"err":"","v":1200,"cn":"RUB","ci":1}
```

`/balance/locked/add`
```json
{"user_id":12,"currency":1,"amount":100}
```

```json
{"cd":200,"err":"","v":1300,"cn":"RUB","ci":1}
```

`/balance/locked/rem`
```json
{"user_id":12,"currency":1,"amount":100}
```

```json
{"cd":200,"err":"","v":1200,"cn":"RUB","ci":1}
```

`/balance/locked/set`
```json
{"user_id":12,"currency":1,"amount":100}
```

```json
{"cd":200,"err":"","v":100,"cn":"RUB","ci":1}
```
---

### Бонусный баланс

| Метод | Путь | Описание | Обработчик |
|-------|------|----------|------------|
| POST | `/balance/bonus` | Получение бонусного баланса | `walletcontroller.BalanceBonus` |
| POST | `/balance/bonus/add` | Увеличение бонусного баланса | `walletcontroller.BalanceBonusAdd` |
| POST | `/balance/bonus/rem` | Уменьшение бонусного баланса | `walletcontroller.BalanceBonusRem` |
| POST | `/balance/bonus/set` | Установка бонусного баланса | `walletcontroller.BalanceBonusSet` |

#### Примеры

`/balance/bonus`
```json
{"user_id":12,"currency":1}
```

```json
{"cd":200,"err":"","v":1200,"cn":"RUB","ci":1}
```

`/balance/bonus/add`
```json
{"user_id":12,"currency":1,"amount":100}
```

```json
{"cd":200,"err":"","v":1300,"cn":"RUB","ci":1}
```

`/balance/bonus/rem`
```json
{"user_id":12,"currency":1,"amount":100}
```

```json
{"cd":200,"err":"","v":1200,"cn":"RUB","ci":1}
```

`/balance/bonus/set`
```json
{"user_id":12,"currency":1,"amount":100}
```

```json
{"cd":200,"err":"","v":100,"cn":"RUB","ci":1}
```

### Реферальный баланс

| Метод | Путь | Описание | Обработчик |
|-------|------|----------|------------|
| POST | `/balance/referral` | Получение реферального баланса | `walletcontroller.BalanceReferral` |
| POST | `/balance/referral/add` | Увеличение реферального баланса | `walletcontroller.BalanceReferralAdd` |
| POST | `/balance/referral/rem` | Уменьшение реферального баланса | `walletcontroller.BalanceReferralRem` |
| POST | `/balance/referral/set` | Установка реферального баланса | `walletcontroller.BalanceReferralSet` |


#### Примеры

`/balance/referral`
```json
{"user_id":12,"currency":1}
```

```json
{"cd":200,"err":"","v":1200,"cn":"RUB","ci":1}
```

`/balance/referral/add`
```json
{"user_id":12,"currency":1,"amount":100}
```

```json
{"cd":200,"err":"","v":1300,"cn":"RUB","ci":1}
```

`/balance/referral/rem`
```json
{"user_id":12,"currency":1,"amount":100}
```

```json
{"cd":200,"err":"","v":1200,"cn":"RUB","ci":1}
```

`/balance/referral/set`
```json
{"user_id":12,"currency":1,"amount":100}
```

```json
{"cd":200,"err":"","v":100,"cn":"RUB","ci":1}
```

---

## Управление кошельком

| Метод | Путь | Описание | Обработчик |
|-------|------|----------|------------|
| POST | `/wallet/active` | Активация/деактивация кошелька | `walletcontroller.SetActive` |
| POST | `/wallet/getBonusId` | Получение ID бонуса, влияющего на отображение баланса | `walletcontroller.GetBonusId` |
| POST | `/wallet/setBonusId` | Установка ID бонуса для баланса | `walletcontroller.SetBonusId` |
| POST | `/wallet/setTotalBet` | Установка TotalBet | `walletcontroller.SetTotalBet` |
| POST | `/wallet/getTotalBet` | Получение TotalBet | `walletcontroller.GetTotalBet` |
| POST | `/wallet/setTotalWin` | Установка TotalWin | `walletcontroller.SetTotalWin` |
| POST | `/wallet/getTotalWin` | Получение TotalWin | `walletcontroller.GetTotalWin` |

#### Примеры

`/wallet/active`
```json
{"user_id":12,"currency":1,"active":false}
```

```json
{"cd":200,"err":""}
```

`/wallet/getBonusId`
```json
{"user_id":12,"currency":1}
```

```json
{"cd":200,"err":"","bid":20345}
```

`/wallet/setBonusId`
```json
{"user_id":12,"currency":1,"bonus_id":12345}
```

```json
{"cd":200,"err":""}
```

`/wallet/setTotalBet`
```json
{"user_id":12,"currency":1,"amount":12345}
```

```json
{"cd":200,"err":""}
```

`/wallet/getTotalBet`
```json
{"user_id":12,"currency":1}
```

```json
{"cd":200,"err":"","v":12345,"cn":"RUB","ci":1}
```

`/wallet/setTotalWin`
```json
{"user_id":12,"currency":1,"amount":12345}
```

```json
{"cd":200,"err":""}
```

`/wallet/getTotalWin`
```json
{"user_id":12,"currency":1}
```

```json
{"cd":200,"err":"","v":12345,"cn":"RUB","ci":1}
```
---

## События пользователя

| Метод | Путь | Описание | Обработчик |
|-------|------|----------|------------|
| POST | `/events/add` | Отправка события пользователя | `eventscontroller.EventsAdd` |
| POST | `/events/pull` | Получение событий пользователя | `eventscontroller.EventsPull` |

#### Примеры

`/events/add`
```json
{"user_id":12,"event":"bonus-updated"}
```

```json
{"cd":200,"err":""}
```

`/events/pull`
```json
{"user_id":12}
```

```json
["bonus-updated"]
```
---

## Пользователи

| Метод | Путь | Описание | Обработчик |
|-------|------|----------|------------|
| POST | `/users` | Получение списка онлайн пользователей | `userscontroller.UsersOnline` |

#### Примеры

`/users`
```json
{}
```

```json
{"12":1747991312}
```
---

## Игровые сессии

| Метод | Путь | Описание | Обработчик |
|-------|------|----------|------------|
| POST | `/startGame` | Запуск игровой сессии | `handlers.StartGame` |
| GET | `/getGame/{user_id}` | Получение текущей игровой сессии | `handlers.GetGame` |
| POST | `/closeGame` | Закрытие текущей игровой сессии | `handlers.CloseGame` |

#### Примеры

`/startGame`
```json
{
    "user_id":12,
    "game_id":"f868bdcd4d71ed1b1fc197405c60f48db19f4568",
    "game_provider_id":"sleg",
    "game_group":"Amatic",
    "game_data":{ // GameData DTO
        "system":"sleg",
        "game_id":"f868bdcd4d71ed1b1fc197405c60f48db19f4568",
        "game_name":"", // не используется в запуске
        "provider":"", // не используется в запуске
        "currency":1, // ид валюты
        "bonus_id":0, // активный бонус
        "tnt_game_id":0, // не используется
        "resident_tnt_game_id":0, // не используется
        "cashback_bonus_id":0, // ид кешбэк бонуса
        "non_dep_bonus_id":0, // ид бонуса тип.2 (не используется)
        "filthy_bonus_id":0, // не используется
        "lose_bonus_id":0, // не ипсользуется (бонус на проигрыш)
        "locked_money":1100, // заблокированная сумма отыгрыша
        "wager_prc":100 // процент отыгрыша
        },
    "game_mode":0,
    "session_id":"gZKRzZqoJY9ptDHV"
}
```
`ответ`
```json
{"cd":200,"err":""}
```


`/getGame/12`

`ответ`
```json
{
    "cd":200,
    "err":"",
    "gs":{
        "Stats":{
            "id":565,
            "user_id":12,
            "currency":"RUB",
            "session_id":"gZKRzZqoJY9ptDHV",
            "system":"sleg",
            "group":"Amatic",
            "game_id":"f868bdcd4d71ed1b1fc197405c60f48db19f4568",
            "login_balance":5167.3,
            "logout_balance":0,
            "balance_diff":0,
            "full_total_bet":0,
            "full_total_win":0,
            "real_total_bet":0,
            "real_total_win":0,
            "total_bet":0,
            "total_win":0,
            "login_date":"2025-05-23T12:18:05.482329708+03:00",
            "created_at":"2025-05-23T12:18:05.482329708+03:00",
            "updated_at":"2025-05-23T12:18:05.482334313+03:00",
            "status":0,
            "bonus_id":null
            },
        "GameStartData":{
            "system":"sleg",
            "game_id":"f868bdcd4d71ed1b1fc197405c60f48db19f4568",
            "game_name":"",
            "provider":"",
            "currency":1,
            "bonus_id":0,
            "tnt_game_id":0,
            "resident_tnt_game_id":0,
            "cashback_bonus_id":0,
            "non_dep_bonus_id":0,
            "filthy_bonus_id":0,
            "lose_bonus_id":0,
            "locked_money":1100,
            "wager_prc":100
            },
        "ActiveBonus":null,
        "BetWagerBonus":null
    }
}
```

`/closeGame`

```json
{"user_id":12}
```

```json
{
    "cd":200,
    "err":"",
    "gs":{
        "Stats":{
            "id":565,
            "user_id":12,
            "currency":"RUB",
            "session_id":"gZKRzZqoJY9ptDHV",
            "system":"sleg",
            "group":"Amatic",
            "game_id":"f868bdcd4d71ed1b1fc197405c60f48db19f4568",
            "login_balance":5167.3,
            "logout_balance":5567.3,
            "balance_diff":400,
            "full_total_bet":100,
            "full_total_win":500,
            "real_total_bet":100,
            "real_total_win":500,
            "total_bet":100,
            "total_win":500,
            "login_date":"2025-05-23T12:18:05.482329708+03:00",
            "created_at":"2025-05-23T12:18:05.482329708+03:00",
            "updated_at":"2025-05-23T12:18:05.482334313+03:00",
            "status":0,
            "bonus_id":null
            },
        "GameStartData":{
            "system":"sleg",
            "game_id":"f868bdcd4d71ed1b1fc197405c60f48db19f4568",
            "game_name":"",
            "provider":"",
            "currency":1,
            "bonus_id":0,
            "tnt_game_id":0,
            "resident_tnt_game_id":0,
            "cashback_bonus_id":0,
            "non_dep_bonus_id":0,
            "filthy_bonus_id":0,
            "lose_bonus_id":0,
            "locked_money":1000,
            "wager_prc":100
            },
        "ActiveBonus":null,
        "BetWagerBonus":null
    }
}
```

---

## Интеграция с игровыми системами

### Slotegrator

| Метод | Путь | Описание | Обработчик |
|-------|------|----------|------------|
| GET/POST | `/api/slotegcb` | Входная точка для Slotegrator | `handlers.Sleg` |
| GET/POST | `/slotegcb` | Альтернативная точка для Slotegrator | `handlers.Sleg` |

### PragmaticPlay

| Метод | Путь | Описание | Обработчик |
|-------|------|----------|------------|
| POST | `/api/pplay/token` | Установка токена авторизации | `handlers.PPlaySetAuthToken` |
| POST | `/pplay/token` | Альтернативная установка токена | `handlers.PPlaySetAuthToken` |
| POST | `/api/pragplay/{method}` | Вызов метода PragmaticPlay | `handlers.PPlay` |
| POST | `/pragplay/{method}` | Альтернативный вызов метода | `handlers.PPlay` |
| POST | `/api/pragplay/session/expired` | Завершение сессии | `handlers.PPlaySessionExpired` |
| POST | `/pragplay/session/expired` | Альтернативное завершение сессии | `handlers.PPlaySessionExpired` |

### QT

| Метод | Путь | Описание | Обработчик |
|-------|------|----------|------------|
| GET | `/api/qt/accounts/{player_id}/session` | Проверка игровой сессии | `qt.VerifySession` |
| GET | `/qt/accounts/{player_id}/session` | Альтернативная проверка сессии | `qt.VerifySession` |
| GET | `/api/qt/accounts/{player_id}/balance` | Получение баланса | `qt.Balance` |
| GET | `/qt/accounts/{player_id}/balance` | Альтернативное получение баланса | `qt.Balance` |
| POST | `/api/qt/transactions` | Проведение транзакции | `qt.Transaction` |
| POST | `/qt/transactions` | Альтернативная транзакция | `qt.Transaction` |
| POST | `/api/qt/transactions/rollback` | Откат транзакции | `qt.Rollback` |
| POST | `/qt/transactions/rollback` | Альтернативный откат | `qt.Rollback` |
| POST | `/api/qt/promotion` | Проверка статуса промо | `qt.PromotionStatus` |
| POST | `/qt/promotion` | Альтернативная проверка промо | `qt.PromotionStatus` |
| POST | `/api/qt/bonus/rewards` | Получение бонусных наград | `qt.Rewards` |
| POST | `/qt/bonus/rewards` | Альтернативное получение наград | `qt.Rewards` |

---

## Примечания

- Для некоторых маршрутов доступны альтернативные пути (например, с префиксом `/api/` и без).
- Для корректной работы некоторых маршрутов могут требоваться авторизация или специальные параметры запроса.
- Документация по игровым системам находится в папке \docs

## Подпись запроса
- Подпись всех основных `POST` операций проверяется в `service.CheckRequestSign`. В запросе передаются заголовки `X-sign` - собственно подпись запроса и `X-Time` - текущее времья в формате `unixtime`(timestamp). Подпись формируется вычислением `HMAC-SHA256` для обединенной строки: метода запроса+json строки данных запроса+времени запроса(timestamp) (см. `service.SignRequest`)



# Описание моделей базы данных

## BonusStatistic

**Назначение:**  
Модель `BonusStatistic` используется для хранения истории изменений и действий, связанных с бонусами пользователя. Позволяет отслеживать все события, происходящие с бонусом: начисления, изменения статуса, выигрыши, завершения и т.д.

**Поля:**

| Поле         | Тип         | Описание                                                                                   |
|--------------|-------------|-------------------------------------------------------------------------------------------|
| BonusID      | int64       | Идентификатор бонуса                                                                      |
| BonusType    | int16       | Тип бонуса                                                                                |
| PatternID    | int64       | Идентификатор шаблона бонуса                                                              |
| UserID       | int64       | Идентификатор пользователя                                                                |
| Action       | int8        | Тип действия, связанного с изменением статистики бонуса                                   |
| Amount       | float64     | Количество бонусных средств, задействованных в действии                                   |
| Currency     | int8        | Идентификатор валюты, связанной с бонусом                                                 |
| StatusBefore | int8        | Статус бонуса до изменения                                                                |
| Status       | int8        | Текущий статус бонуса                                                                     |
| CreatedAt    | *time.Time  | Время создания записи                                                                     |
| UpdatedAt    | *time.Time  | Время последнего обновления записи                                                        |
| Data         | *string     | Дополнительная информация в формате JSON                                                  |
| PromocodeID  | *int64      | Идентификатор промокода, связанного с бонусной статистикой (может быть null)              |

---

## BonusTransaction

**Назначение:**  
Модель `BonusTransaction` используется для хранения информации о транзакциях, связанных с бонусами пользователей, например, о выигрышах, начислениях и других операциях по бонусным средствам.

**Поля:**

| Поле      | Тип        | Описание                                                        |
|-----------|------------|-----------------------------------------------------------------|
| UserID    | int64      | Идентификатор пользователя                                      |
| HallID    | int64      | Идентификатор зала (не используется)                            |
| Date      | time.Time  | Дата и время выполнения транзакции                              |
| Sum       | float64    | Сумма транзакции                                                |
| StatusID  | int64      | Идентификатор типа транзакции                                   |
| Currency  | string     | Строковый код валюты, используемой в транзакции                 |
| Operation | string     | Описание операции, выполняемой в рамках транзакции              |

---

## GameTransaction

**Назначение:**  
Модель `GameTransaction` используется для хранения информации о транзакциях, связанных с действиями пользователя в игре: ставки, выигрыши, возвраты и другие операции. Содержит метаданные о системе, пользователе, игре, сессии и деталях транзакции.

**Поля:**

| Поле             | Тип             | Описание                                                                                      |
|------------------|-----------------|-----------------------------------------------------------------------------------------------|
| ID               | int64           | Уникальный идентификатор записи транзакции в базе данных                                      |
| GenID            | sql.NullString  | Сгенерированный ID для идентификации связанных операций или записей (может быть NULL)         |
| System           | string          | Название или идентификатор игровой системы                                                    |
| UserId           | int64           | Идентификатор пользователя, связанного с транзакцией                                          |
| BonusID          | sql.NullInt64   | Идентификатор бонуса, связанного с транзакцией (может быть NULL)                              |
| Action           | string          | Тип действия транзакции (например, ставка, выигрыш, возврат)                                  |
| Amount           | float64         | Сумма транзакции (ставка, выплата или возврат)                                                |
| Currency         | string          | Валюта транзакции (например, USD, EUR, RUB)                                                   |
| GameID           | string          | Идентификатор игры, связанной с транзакцией                                                   |
| SessionID        | string          | Уникальный идентификатор пользовательской сессии                                              |
| TransactionID    | string          | Уникальный идентификатор транзакции                                                           |
| Type             | string          | Тип транзакции (например, "ставка", "выплата", "возврат")                                     |
| Date             | string          | Дата транзакции                                                                               |
| Refunded         | bool            | Признак возврата средств по данной транзакции                                                 |
| BetTransactionId | sql.NullString  | ID связанной ставки (может быть пустым), используется для связи выигрыша со ставкой           |
| LastBet          | float64         | Значение последней ставки пользователя в игре (используется при выигрышной транзакции)        |
| LastBetId        | sql.NullString  | Идентификатор последней ставки (GenID), может быть NULL                                      |
| RespString       | string          | JSON-ответ по данной транзакции для обеспечения идемпотентности (не сохраняется в базу данных)|

---

## M_User

**Назначение:**  
Модель `M_User` используется для хранения основных данных пользователя в базе данных: идентификаторы, контактные данные, валюту, токен доступа и последний IP-адрес.

**Поля:**

| Поле      | Тип             | Описание                                                                 |
|-----------|-----------------|--------------------------------------------------------------------------|
| ID        | int64           | Уникальный идентификатор пользователя                                    |
| Login     | sql.NullString  | Логин пользователя (nullable, но не должен быть пустым)                  |
| Email     | sql.NullString  | Электронная почта пользователя (может быть NULL)                         |
| Phone     | sql.NullString  | Номер телефона пользователя (может быть NULL)                            |
| CurrencyId| int             | Идентификатор текущей валюты пользователя                                |
| Token     | sql.NullString  | Токен доступа пользователя (может быть NULL)                             |
| LastIp    | sql.NullString  | Последний IP-адрес пользователя (может быть NULL)                        |

---

## UserBonus

**Назначение:**  
Модель `UserBonus` описывает бонусы пользователя и их текущее состояние. Используется для хранения информации о типе бонуса, суммах, статусах, ограничениях, связанных играх и других параметрах.

**Поля:**

| Поле                   | Тип               | Описание                                                                                      |
|------------------------|-------------------|-----------------------------------------------------------------------------------------------|
| ID                     | uint              | Уникальный идентификатор бонуса пользователя                                                  |
| UserID                 | uint              | Идентификатор пользователя                                                                    |
| Type                   | int8              | Тип бонуса (1 - Deposit, 2 - Non-deposit, 5 - Plus/Minus и др.)                               |
| MoneyAvailable         | float64           | Сумма бонусных средств, доступных для использования                                           |
| MoneyPaidOut           | float64           | Сумма, выплаченная в рамках бонуса                                                            |
| TotalBet               | float64           | Общая сумма ставок по бонусу                                                                  |
| TotalBetForPayout      | float64           | Сумма ставок, необходимая для получения выплаты                                               |
| TotalWin               | float64           | Общая сумма выигрыша по бонусу                                                                |
| WagerToComplete        | float64           | Сумма ставок, необходимая для выполнения условий бонуса                                       |
| WagerToPayout          | float64           | Сумма ставок для условий выплаты (используется в некоторых бонусах)                           |
| MultiplierToComplete   | float64           | Множитель для выполнения условий бонуса                                                       |
| MultiplierToPayout     | float64           | Множитель для выплаты бонуса                                                                  |
| RealMoneySpent         | float64           | Сумма реальных средств, потраченных в рамках бонуса                                           |
| Status                 | int8              | Текущий статус бонуса                                                                         |
| SpinsTotal             | int               | Общее количество игровых вращений по бонусу                                                   |
| SpinsAvailable         | int               | Количество доступных спинов                                                                   |
| CreatedAt              | *time.Time        | Время создания бонуса                                                                         |
| UpdatedAt              | *time.Time        | Время последнего обновления                                                                   |
| ActiveTo               | *time.Time        | Время завершения бонуса                                                                       |
| TotalBetAfter          | float64           | Используется бонусами для своих нужд                                                          |
| CompletedAt            | *time.Time        | Время завершения бонуса (может быть nil)                                                      |
| MoneyToActivate        | string            | Сумма, необходимая для активации бонуса                                                       |
| Multiplier             | float32           | Размер вейджера                                                                               |
| HoursActive            | int               | Количество часов активности бонуса                                                            |
| MaxWin                 | sql.NullFloat64   | Максимальный возможный выигрыш (может быть null)                                              |
| Tries                  | int               | Количество попыток или текущий день (для Multidays)                                           |
| HallID                 | int               | Идентификатор зала (не используется)                                                          |
| Currency               | int               | Идентификатор валюты бонуса                                                                   |
| PatternID              | sql.NullInt64     | Идентификатор паттерна бонуса (может быть null)                                               |
| PatternStatus          | int               | Не используется                                                                               |
| BonusPrizes            | sql.NullString    | Не используется                                                                               |
| GivenPrizes            | sql.NullString    | Не используется                                                                               |
| EnableAfter            | sql.NullString    | Доступен только после завершения другого бонуса (может быть null)                             |
| GamesLimited           | bool              | Признак ограничения списка доступных игр (не используется)                                    |
| AllowedGames           | sql.NullString    | JSON-список разрешённых игр (может быть пустым)                                               |
| JSONParams             | sql.NullString    | Дополнительные параметры в формате JSON                                                       |
| CreatorType            | int               | Тип создателя бонуса (промокод, доступные бонусы и др.)                                       |
| CreatorID              | sql.NullInt64     | Идентификатор создателя бонуса (может быть null)                                              |
| GameProvider           | sql.NullString    | Игровая система, если бонус лимитирован одной игрой                                           |
| GameID                 | sql.NullString    | Идентификатор игры, если бонус лимитирован одной игрой                                        |
| Title                  | sql.NullString    | Заголовок бонуса (может быть null)                                                            |
| ImgURL                 | sql.NullString    | URL изображения бонуса (может быть null)                                                      |
| Marker                 | int               | Идентификатор или метка бонуса                                                                |

**Дополнительные типы и методы:**

- **BonusGames**  
  Тип: `map[string][]string`  
  Используется для хранения списка разрешённых игр по игровым системам.

- **GetGames() (BonusGames, error)**  
  Возвращает список разрешённых игр для бонуса или ошибку, если данные недоступны или некорректны.

- **GetMaxXWin() float64**  
  Возвращает максимальное значение выигрыша (`max_x_win`) из поля `JSONParams`. Если данных нет или они некорректны — возвращает 0.

---

## UserCurrency

**Назначение:**  
Модель `UserCurrency` описывает валюту, используемую пользователем в системе. Содержит информацию о названии, символе, кодах, игровых валютах и дополнительных свойствах.

**Поля:**

| Поле           | Тип             | Описание                                                                                  |
|----------------|-----------------|-------------------------------------------------------------------------------------------|
| ID             | int64           | Уникальный идентификатор валюты                                                           |
| Name           | string          | Название валюты                                                                           |
| GameCurrency   | sql.NullString  | Игровая валюта (может быть Null), например, для USDT игровой валютой может быть USD       |
| HallId         | int64           | Идентификатор хола (не используется)                                                      |
| Symbol         | string          | Символ валюты (например, $, €, ₽)                                                         |
| Code           | int64           | Числовой код валюты по международному стандарту                                           |
| IsCrypto       | bool            | Флаг, указывающий, является ли валюта криптовалютой                                       |
| MaxAutoPayout  | float64         | Максимальная сумма автоматической выплаты                                                 |

---

## UserGameStats

**Назначение:**  
Модель `UserGameStats` используется для хранения статистики игрового сеанса пользователя. Содержит информацию о балансе, ставках, выигрышах, временных отметках и связанных сущностях (игра, система, бонус).

**Поля:**

| Поле            | Тип         | Описание                                                                                      |
|-----------------|-------------|-----------------------------------------------------------------------------------------------|
| ID              | int64       | Уникальный идентификатор записи в таблице user_game_stats                                     |
| UserID          | int64       | Идентификатор пользователя, связанный с игровой сессией                                       |
| Currency        | string      | Валюта, используемая в игровом сеансе                                                        |
| SessionID       | *string     | Идентификатор сессии пользователя                                                             |
| System          | *string     | Идентификатор игровой системы (например, sleg, qt, pragplay)                                  |
| Group           | *string     | Игровая группа (провайдер)                                                                    |
| GameID          | *string     | Идентификатор игры                                                                            |
| LoginBalance    | float64     | Баланс пользователя на момент запуска игровой сессии                                          |
| LogoutBalance   | float64     | Баланс пользователя на момент завершения игровой сессии                                       |
| BalanceDiff     | float64     | Разница балансов на момент завершения игровой сессии                                          |
| FullTotalBet    | float64     | Общая сумма ставок за сессию (включая бонусные и реальные средства)                           |
| FullTotalWin    | float64     | Общая сумма выигрышей за сессию (включая бонусные и реальные средства)                        |
| RealTotalBet    | float64     | Сумма всех ставок за сессию (без бонусных ставок)                                            |
| RealTotalWin    | float64     | Сумма всех выигрышей за сессию (без бонусных ставок)                                         |
| TotalBet        | float64     | Общая сумма ставок пользователя за сессию (без бонусных ставок)                              |
| TotalWin        | float64     | Суммарный выигрыш пользователя за сессию (без бонусных ставок)                               |
| LoginDate       | *time.Time  | Дата и время входа пользователя в игровую сессию                                             |
| LogoutDate      | *time.Time  | Время выхода пользователя из игровой сессии или окончания сеанса                             |
| LastActiveDate  | *time.Time  | Дата и время последней активности пользователя в игровой сессии                              |
| CreatedAt       | *time.Time  | Время создания записи в базе данных                                                          |
| UpdatedAt       | *time.Time  | Время последнего обновления записи в базе данных                                             |
| OuterTable      | *string     | Не используется                                                                              |
| OuterID         | *int64      | Не используется                                                                              |
| Status          | int         | Не используется                                                                              |
| BonusID         | *int64      | Идентификатор бонуса, связанного с игровой сессией пользователя (может быть пустым)          |

---

## UserWallet

**Назначение:**  
Модель `UserWallet` используется для хранения информации о кошельке пользователя: суммы, валюты, состояния, связанных бонусов и статистики по ставкам и выигрышам.

**Поля:**

| Поле          | Тип           | Описание                                                                                         |
|---------------|---------------|--------------------------------------------------------------------------------------------------|
| ID            | int64         | Уникальный идентификатор кошелька пользователя                                                   |
| UserID        | int64         | Идентификатор пользователя, связанного с данным кошельком                                        |
| CurrencyID    | int8          | Идентификатор валюты, используемой в кошельке                                                    |
| Active        | bool          | Признак активности кошелька                                                                      |
| Amount        | float64       | Сумма денежных средств, доступных в кошельке                                                     |
| LockedMoney   | float64       | Сумма средств, заблокированных для отыгрыша                                                      |
| ReferralMoney | float64       | Сумма реферальных вознаграждений                                                                 |
| BonusMoney    | float64       | Сумма бонусных средств (не используется)                                                         |
| TotalBet      | float64       | Общая сумма ставок, сделанных пользователем                                                      |
| TotalWin      | float64       | Общее количество выигрышей пользователя                                                          |
| BonusID       | sql.NullInt64 | Идентификатор бонуса, связанного с кошельком (может быть NULL; используется для SharedMoneyBonus)|

---

# Описание репозиториев

## BonusStatisticRepository

**Назначение:**  
`BonusStatisticRepository` — интерфейс и реализация для работы с таблицей статистики бонусов (`bonus_statistics`). Позволяет добавлять новые записи о событиях, связанных с бонусами пользователей (например, начисления, изменения статуса, выигрыши и т.д.).

**Основные методы:**

- **Insert(bs *models.BonusStatistic) (int64, error)**  
  Добавляет новую запись в таблицу статистики бонусов и возвращает идентификатор вставленной записи или ошибку.

**Использование:**  
Для получения экземпляра репозитория используйте функцию `GetBonusStatisticRepository()`.  
Реализация использует базу данных через пакет `db` и логирование ошибок через пакет `logger`.

---

## BonustransactionRepository

**Назначение:**  
`BonustransactionRepository` — интерфейс и реализация для работы с транзакциями бонусов пользователей (таблица `payout_transactions`). Используется для записи информации о транзакциях, связанных с бонусами, например, о выигрыше или выплате бонусных средств.

**Основные методы:**

- **Insert(tr *models.BonusTransaction) (int64, error)**  
  Добавляет новую запись о бонусной транзакции в базу данных и возвращает её идентификатор или ошибку.

**Использование:**  
Для получения экземпляра репозитория используйте функцию `GetBonusTransactionRepository()`.  
Реализация использует базу данных через пакет `db` и логирование ошибок через пакет `logger`.

---

## GameSessionRepository

**Назначение:**  
`GameSessionRepository` — интерфейс и реализация для работы с игровой статистикой пользователя (`user_game_stats`). Позволяет получать, добавлять и обновлять записи о сессиях пользователя, включая информацию о балансе, ставках, выигрышах и связанных сущностях.

**Основные методы:**

- **GetByID(id int64) (*models.UserGameStats, error)**  
  Возвращает объект `UserGameStats` по идентификатору или ошибку, если запись не найдена.

- **Insert(sess *models.UserGameStats) (int64, error)**  
  Добавляет новую запись о сессии пользователя и возвращает идентификатор вставленной записи или ошибку.

- **Update(sess *models.UserGameStats) error**  
  Обновляет существующую запись о сессии пользователя в базе данных.

**Использование:**  
Для получения экземпляра репозитория используйте функцию `GetGameSessionRepository()`.  
Реализация использует базу данных через пакет `db` и логирование ошибок через пакет `logger`.

---

## GameTransactionRepository

**Назначение:**  
`GameTransactionRepository` — интерфейс и реализация для работы с игровыми транзакциями (`game_transactions`). Позволяет получать транзакции по ID или по идентификатору транзакции, а также вставлять одну или несколько новых транзакций в базу данных.

**Основные методы:**

- **GetByID(id int64) (*models.GameTransaction, error)**  
  Возвращает игровую транзакцию по её уникальному идентификатору.

- **GetByTrID(transaction_id int64) (*models.GameTransaction, error)**  
  Возвращает игровую транзакцию по идентификатору транзакции (transaction_id).

- **Insert(trs ...*models.GameTransaction) error**  
  Вставляет одну или несколько транзакций в базу данных.

**Использование:**  
Для получения экземпляра репозитория используйте функцию `GetTransactionRepository()`.  
Реализация использует базу данных через пакет `db` и логирование ошибок через пакет `logger`.

---

## UserBonusRepository

**Назначение:**  
`UserBonusRepository` — интерфейс и реализация для работы с бонусами пользователей (`users_bonuses`). Позволяет получать, обновлять и добавлять записи о бонусах пользователя, включая всю связанную информацию о состоянии, суммах, ограничениях и параметрах бонуса.

**Основные методы:**

- **GetByID(id int64) (*models.UserBonus, error)**  
  Извлекает информацию о бонусе пользователя по идентификатору.

- **Update(bonus *models.UserBonus) error**  
  Обновляет информацию о бонусе пользователя в базе данных.

- **Insert(bonus *models.UserBonus) (int64, error)**  
  Добавляет новую запись о бонусе пользователя и возвращает идентификатор новой записи.

**Использование:**  
Для получения экземпляра репозитория используйте функцию `GetUserBonusRepository()`.  
Реализация использует базу данных через пакет `db`.

---

## UserRepository

**Назначение:**  
`UserRepository` — интерфейс и реализация для работы с данными пользователей (`m_users`). Позволяет получать, обновлять данные пользователя и время его последней активности.

**Основные методы:**

- **GetByID(id int64) (*models.M_User, error)**  
  Получает пользователя по его уникальному идентификатору.

- **Update(user *models.M_User) error**  
  Обновляет данные пользователя в базе данных.

- **UpdateLastActive(user_id int, dt time.Time) error**  
  Обновляет время последней активности пользователя.

**Использование:**  
Для получения экземпляра репозитория используйте функцию `GetUserRepository()`.  
Реализация использует базу данных через пакет `db` и логирование ошибок через пакет `logger`.

---

## WalletsRepository

**Назначение:**  
`WalletsRepository` — интерфейс и реализация для работы с кошельками пользователей (`user_wallets`) и их валютами. Позволяет получать, обновлять, создавать кошельки, а также загружать список валют пользователя.

**Основные методы:**

- **GetByID(id int64) (*models.UserWallet, error)**  
  Возвращает кошелек пользователя по его уникальному идентификатору.

- **GetByUserID(user_id int64) ([]models.UserWallet, error)**  
  Возвращает список кошельков пользователя по его идентификатору.

- **GetByUserIDCurrID(user_id int64, curr int8) (*models.UserWallet, error)**  
  Возвращает кошелек пользователя по его идентификатору и идентификатору валюты.

- **Update(wallet *models.UserWallet) error**  
  Обновляет данные указанного кошелька пользователя.

- **Create(user_id int64, currency int8) error**  
  Создает новый кошелек для пользователя с указанной валютой.

- **LoadUserCurrencies() ([]models.UserCurrency, error)**  
  Загружает список валют пользователей из базы данных.

**Использование:**  
Для получения экземпляра репозитория используйте функцию `GetWalletsRepository()`.  
Реализация использует базу данных через пакет `db` и логирование ошибок через пакет `logger`.

---