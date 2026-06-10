# Документация сервиса K-Tournaments

## Описание
K-Tournaments - сервис для управления "своими" турнирами и их призами.

## Структура документации

 - [Принцип работы](#принцип-работы)
 - [API](#api)
 - [База данных](#база-данных)

# Принцип работы
Сервис загружает ожидающие запуска, активные и 10 последних завершенных турниров из базы. Каждую минуту сервис проверяет даты турниров и меняет статусы и перемещает в соответствующий контейнер.  
Сервис подписывается на сообщения типа spins.* брокера сообщений NATS.  
Формат сообщений spin:

```go
// SpinMessage - сообщение о спине турнира.
type SpinMessage struct {
	UserID     int       `json:"user_id"`     // ID пользователя
	GameSystem string    `json:"game_system"` // Система игры
	Currency   string    `json:"currency"`    // Валюта
	GameID     string    `json:"game_id"`     // ID игры
	Bet        float64   `json:"bet"`         // Ставка
	Win        float64   `json:"win"`         // Выигрыш
	SpinTime   time.Time `json:"spin_time"`   // Время спина
}
```
Каждое сообщение представляет собой завершенную комбинацию ставки-выигрыша в игре.  
После получения spin сообщения сервис проходит по всем активным турнирам и вызывает метод `ProcessSpin` в котором турниры обрабатывают spin. Турнир проверяет подходит ли данный spin под его параметры: валюта, минимальная ставка, пользователь принял участие (для турниров где это необходимо) и т.д. Если spin подходит вызывается обработчик соответствующей игры турнира (`TournamentGame`), которые возвращает заработанные в результате спина очки, которые добавляются к общему к-ву очков в турнирной таблице для данного пользователя и возвращает `true` - указывая что данный spin обработан.

## Завершение турнира

После завершения турнира он перпеходит в статус `STATUS_DONE`(2) которые указывает что турнир завершен но призы не выданы. Призы выдаются на стороне сайта, после чего сайт вызывает запрос на установку турнира в статус `STATUS_COMPLETED`(3).

### Статусы турнира
- 0 (STATUS_NOT_ACTIVE) - не активен
- 1 (STATUS_ACTIVE) - активен
- 2 (STATUS_DONE) - завершен, но не выданы награды
- 3 (STATUS_COMPLETED) - завершен и выданы награды

## Валюта турнира

Турнир можно запустить как для всех доступных валют так и для одной определенной. Если установлена конкретная валюта - турнир будет доступен только для играков с указанной валютой.   
При этом если турнир выбирает параметры для начисление очков по указаной в spin валюте.

## Призы турнира

Турнир содержит призы двух типов - реальное денежное вознаграждение и призовой итем (SlcItem). Кадое призовое место может получить один из так и оба типа приза.
Приз турнира хранится в SlcItem которые уже и выдает соответсвующую награду ( деньги и/или SlcItem). Награды обрабатываются на стороне сайта.

# API

## Общая информация

Сервис K-Tournaments использует NATS для обмена сообщениями. Все методы API доступны через NATS subjects с префиксом `tournaments.*`.

## Методы API

### Управление турнирами

#### Получение активных турниров
- **Subject**: `tournaments.active-tournaments`
- **Запрос**:
```json
{
    "user_id": 12, // ID пользователя для которого выбирается турнир
    "currency_id": 0,  // ID валюты (0 - все валюты)
    "test": false      // Включать тестовые турниры
}
```
- **Ответ**: 
в ответе возвращает список активных турнриров каждые елемент которого хранит информацию о самой моделе турнира и дополнительные данные (минимальные ставки, призовые места, и условия турнира)
```json
"{
    "2":{
        "model":{
            "id":2,
            "type":1,
            "name":"first test",
            "status":2,
            "start_time":"2025-04-09T09:26:00+03:00",
            "end_time":"2025-04-16T09:26:00+03:00",
            "game_type":4,
            "game_list":"{\"sleg\":[\"6e514731d448e9ce4bbec08ee14d054fa5570d54\",\"598a4244039d75fef7a03fb3e4b4cf3ba93c01e2\",\"805e294072873e2afdcc2f500913568bac1a6ee7\",\"43084f01ef7f58e309829de90cb8302332955227\",\"01b288ab410801ad4b76fd8b3c842df055f06bd0\",\"7106ba327950a241fca4e9b146e1d4f1ee740bf2\"]}",
            "params":"{\"min_bets\":{\"RUB\":\"20\",\"UAH\":\"10\",\"USD\":\"1\",\"EUR\":\"1\",\"INR\":\"20\",\"USDT\":\"1\",\"KZT\":\"50\"}}",
            "tournament_bonus_group_id":3,
            "created_at":"2025-04-05T09:29:38+03:00",
            "updated_at":"2025-04-24T09:50:03+03:00",
            "currency":1,
            "is_test":true,
            "tile_img":"/storage/user_img/tournaments/mobile.webp",
            "mobile_img":"/storage/user_img/tournaments/mobile.webp",
            "desktop_img":"/storage/user_img/tournaments/info-banner_desktop.png",
            "cabinet_d_img":"/storage/user_img/tournaments/info-banner_desktop.png",
            "cabinet_m_img":"/storage/user_img/tournaments/wide.webp",
            "req_confirm":false,
            "conditions":"{\"en\":{\"descr\":\"\u003cp\u003esome condition text\u003c\\/p\u003e\"},\"ru\":{\"descr\":\"Новые супер турниры\"},\"ua\":{\"descr\":null},\"hi\":{\"descr\":null},\"kz\":{\"descr\":null},\"de\":{\"descr\":null},\"fr\":{\"descr\":null},\"it\":{\"descr\":null},\"pt\":{\"descr\":null},\"es\":{\"descr\":null},\"sk\":{\"descr\":null}}",
            "aggr_req":false,
            "prize_fund":"{\"en\":{\"descr\":\"Prizes\"},\"ru\":{\"descr\":\"Призы\"},\"ua\":{\"descr\":null},\"hi\":{\"descr\":null},\"kz\":{\"descr\":null},\"de\":{\"descr\":null},\"fr\":{\"descr\":null},\"it\":{\"descr\":null},\"pt\":{\"descr\":null},\"es\":{\"descr\":null},\"sk\":{\"descr\":null}}",
            "prize_page":"{\"en\":{\"descr\":\"prize page\"},\"ru\":{\"descr\":\"Страница призов\"},\"ua\":{\"descr\":null},\"hi\":{\"descr\":null},\"kz\":{\"descr\":null},\"de\":{\"descr\":null},\"fr\":{\"descr\":null},\"it\":{\"descr\":null},\"pt\":{\"descr\":null},\"es\":{\"descr\":null},\"sk\":{\"descr\":null}}"
        },
        "games_limited":true,
        "min_bets":{"EUR":1,"INR":20,"KZT":50,"RUB":20,"UAH":10,"USD":1,"USDT":1},
        "prize_places":9,
        "prize_bank":{"EUR":140,"INR":310,"KZT":11000,"RUB":12577,"UAH":4246,"USD":20,"USDT":30},
        "conditions":{"de":"","en":"\u003cp\u003esome condition text\u003c/p\u003e","es":"","fr":"","hi":"","it":"","kz":"","pt":"","ru":"Новые супер турниры","sk":"","ua":""}
    }
}
```

#### Получение ожидающих турниров
- **Subject**: `tournaments.pending-tournaments`
- **Запрос**:
```json
{
    "user_id": 12, // ID пользователя для которого выбирается турнир
    "currency_id": 0,  // ID валюты (0 - все валюты)
    "test": false      // Включать тестовые турниры
}
```
- **Ответ**: 
в ответе возвращает список ожидающий запуска турнриров каждые елемент которого хранит информацию о самой моделе турнира и дополнительные данные (минимальные ставки, призовые места, и условия турнира)
```json
"{
    "2":{
        "model":{
            "id":2,
            "type":1,
            "name":"first test",
            "status":0,
            "start_time":"2025-04-09T09:26:00+03:00",
            "end_time":"2025-04-16T09:26:00+03:00",
            "game_type":4,
            "game_list":"{\"sleg\":[\"6e514731d448e9ce4bbec08ee14d054fa5570d54\",\"598a4244039d75fef7a03fb3e4b4cf3ba93c01e2\",\"805e294072873e2afdcc2f500913568bac1a6ee7\",\"43084f01ef7f58e309829de90cb8302332955227\",\"01b288ab410801ad4b76fd8b3c842df055f06bd0\",\"7106ba327950a241fca4e9b146e1d4f1ee740bf2\"]}",
            "params":"{\"min_bets\":{\"RUB\":\"20\",\"UAH\":\"10\",\"USD\":\"1\",\"EUR\":\"1\",\"INR\":\"20\",\"USDT\":\"1\",\"KZT\":\"50\"}}",
            "tournament_bonus_group_id":3,
            "created_at":"2025-04-05T09:29:38+03:00",
            "updated_at":"2025-04-24T09:50:03+03:00",
            "currency":1,
            "is_test":true,
            "tile_img":"/storage/user_img/tournaments/mobile.webp",
            "mobile_img":"/storage/user_img/tournaments/mobile.webp",
            "desktop_img":"/storage/user_img/tournaments/info-banner_desktop.png",
            "cabinet_d_img":"/storage/user_img/tournaments/info-banner_desktop.png",
            "cabinet_m_img":"/storage/user_img/tournaments/wide.webp",
            "req_confirm":false,
            "conditions":"{\"en\":{\"descr\":\"\u003cp\u003esome condition text\u003c\\/p\u003e\"},\"ru\":{\"descr\":\"Новые супер турниры\"},\"ua\":{\"descr\":null},\"hi\":{\"descr\":null},\"kz\":{\"descr\":null},\"de\":{\"descr\":null},\"fr\":{\"descr\":null},\"it\":{\"descr\":null},\"pt\":{\"descr\":null},\"es\":{\"descr\":null},\"sk\":{\"descr\":null}}",
            "aggr_req":false,
            "prize_fund":"{\"en\":{\"descr\":\"Prizes\"},\"ru\":{\"descr\":\"Призы\"},\"ua\":{\"descr\":null},\"hi\":{\"descr\":null},\"kz\":{\"descr\":null},\"de\":{\"descr\":null},\"fr\":{\"descr\":null},\"it\":{\"descr\":null},\"pt\":{\"descr\":null},\"es\":{\"descr\":null},\"sk\":{\"descr\":null}}",
            "prize_page":"{\"en\":{\"descr\":\"prize page\"},\"ru\":{\"descr\":\"Страница призов\"},\"ua\":{\"descr\":null},\"hi\":{\"descr\":null},\"kz\":{\"descr\":null},\"de\":{\"descr\":null},\"fr\":{\"descr\":null},\"it\":{\"descr\":null},\"pt\":{\"descr\":null},\"es\":{\"descr\":null},\"sk\":{\"descr\":null}}"
        },
        "games_limited":true,
        "min_bets":{"EUR":1,"INR":20,"KZT":50,"RUB":20,"UAH":10,"USD":1,"USDT":1},
        "prize_places":9,
        "prize_bank":{"EUR":140,"INR":310,"KZT":11000,"RUB":12577,"UAH":4246,"USD":20,"USDT":30},
        "conditions":{"de":"","en":"\u003cp\u003esome condition text\u003c/p\u003e","es":"","fr":"","hi":"","it":"","kz":"","pt":"","ru":"Новые супер турниры","sk":"","ua":""}
    }
}
```

#### Получение завершенных турниров
- **Subject**: `tournaments.completed-tournaments`
- **Запрос**:
```json
{
    "user_id": 12, // ID пользователя для которого выбирается турнир
    "currency_id": 0,  // ID валюты (0 - все валюты)
    "test": false      // Включать тестовые турниры
}
```
- **Ответ**: 
в ответе возвращает список завершенных турнриров (включая не выданные награды) каждые елемент которого хранит информацию о самой моделе турнира и дополнительные данные (минимальные ставки, призовые места, и условия турнира)
```json
"{
    "2":{
        "model":{
            "id":2,
            "type":1,
            "name":"first test",
            "status":3,
            "start_time":"2025-04-09T09:26:00+03:00",
            "end_time":"2025-04-16T09:26:00+03:00",
            "game_type":4,
            "game_list":"{\"sleg\":[\"6e514731d448e9ce4bbec08ee14d054fa5570d54\",\"598a4244039d75fef7a03fb3e4b4cf3ba93c01e2\",\"805e294072873e2afdcc2f500913568bac1a6ee7\",\"43084f01ef7f58e309829de90cb8302332955227\",\"01b288ab410801ad4b76fd8b3c842df055f06bd0\",\"7106ba327950a241fca4e9b146e1d4f1ee740bf2\"]}",
            "params":"{\"min_bets\":{\"RUB\":\"20\",\"UAH\":\"10\",\"USD\":\"1\",\"EUR\":\"1\",\"INR\":\"20\",\"USDT\":\"1\",\"KZT\":\"50\"}}",
            "tournament_bonus_group_id":3,
            "created_at":"2025-04-05T09:29:38+03:00",
            "updated_at":"2025-04-24T09:50:03+03:00",
            "currency":1,
            "is_test":true,
            "tile_img":"/storage/user_img/tournaments/mobile.webp",
            "mobile_img":"/storage/user_img/tournaments/mobile.webp",
            "desktop_img":"/storage/user_img/tournaments/info-banner_desktop.png",
            "cabinet_d_img":"/storage/user_img/tournaments/info-banner_desktop.png",
            "cabinet_m_img":"/storage/user_img/tournaments/wide.webp",
            "req_confirm":false,
            "conditions":"{\"en\":{\"descr\":\"\u003cp\u003esome condition text\u003c\\/p\u003e\"},\"ru\":{\"descr\":\"Новые супер турниры\"},\"ua\":{\"descr\":null},\"hi\":{\"descr\":null},\"kz\":{\"descr\":null},\"de\":{\"descr\":null},\"fr\":{\"descr\":null},\"it\":{\"descr\":null},\"pt\":{\"descr\":null},\"es\":{\"descr\":null},\"sk\":{\"descr\":null}}",
            "aggr_req":false,
            "prize_fund":"{\"en\":{\"descr\":\"Prizes\"},\"ru\":{\"descr\":\"Призы\"},\"ua\":{\"descr\":null},\"hi\":{\"descr\":null},\"kz\":{\"descr\":null},\"de\":{\"descr\":null},\"fr\":{\"descr\":null},\"it\":{\"descr\":null},\"pt\":{\"descr\":null},\"es\":{\"descr\":null},\"sk\":{\"descr\":null}}",
            "prize_page":"{\"en\":{\"descr\":\"prize page\"},\"ru\":{\"descr\":\"Страница призов\"},\"ua\":{\"descr\":null},\"hi\":{\"descr\":null},\"kz\":{\"descr\":null},\"de\":{\"descr\":null},\"fr\":{\"descr\":null},\"it\":{\"descr\":null},\"pt\":{\"descr\":null},\"es\":{\"descr\":null},\"sk\":{\"descr\":null}}"
        },
        "games_limited":true,
        "min_bets":{"EUR":1,"INR":20,"KZT":50,"RUB":20,"UAH":10,"USD":1,"USDT":1},
        "prize_places":9,
        "prize_bank":{"EUR":140,"INR":310,"KZT":11000,"RUB":12577,"UAH":4246,"USD":20,"USDT":30},
        "conditions":{"de":"","en":"\u003cp\u003esome condition text\u003c/p\u003e","es":"","fr":"","hi":"","it":"","kz":"","pt":"","ru":"Новые супер турниры","sk":"","ua":""}
    }
}
```

#### Получение информации о турнире
- **Subject**: `tournaments.get-tournament`
- **Запрос**:
```json
{
    "id": 0  // ID турнира
}
```
- **Ответ**: 
```json
{
    "model":{
        "id":2,
        "type":1,
        "name":"first test",
        "status":3,
        "start_time":"2025-04-09T09:26:00+03:00",
        "end_time":"2025-04-16T09:26:00+03:00",
        "game_type":4,
        "game_list":"{\"sleg\":[\"6e514731d448e9ce4bbec08ee14d054fa5570d54\",\"598a4244039d75fef7a03fb3e4b4cf3ba93c01e2\",\"805e294072873e2afdcc2f500913568bac1a6ee7\",\"43084f01ef7f58e309829de90cb8302332955227\",\"01b288ab410801ad4b76fd8b3c842df055f06bd0\",\"7106ba327950a241fca4e9b146e1d4f1ee740bf2\"]}",
        "params":"{\"min_bets\":{\"RUB\":\"20\",\"UAH\":\"10\",\"USD\":\"1\",\"EUR\":\"1\",\"INR\":\"20\",\"USDT\":\"1\",\"KZT\":\"50\"}}",
        "tournament_bonus_group_id":3,
        "created_at":"2025-04-05T09:29:38+03:00",
        "updated_at":"2025-04-24T09:50:03+03:00",
        "currency":1,
        "is_test":true,
        "tile_img":"/storage/user_img/tournaments/mobile.webp",
        "mobile_img":"/storage/user_img/tournaments/mobile.webp",
        "desktop_img":"/storage/user_img/tournaments/info-banner_desktop.png",
        "cabinet_d_img":"/storage/user_img/tournaments/info-banner_desktop.png",
        "cabinet_m_img":"/storage/user_img/tournaments/wide.webp",
        "req_confirm":false,
        "conditions":"{\"en\":{\"descr\":\"\u003cp\u003esome condition text\u003c\\/p\u003e\"},\"ru\":{\"descr\":\"Новые супер турниры\"},\"ua\":{\"descr\":null},\"hi\":{\"descr\":null},\"kz\":{\"descr\":null},\"de\":{\"descr\":null},\"fr\":{\"descr\":null},\"it\":{\"descr\":null},\"pt\":{\"descr\":null},\"es\":{\"descr\":null},\"sk\":{\"descr\":null}}",
        "aggr_req":false,
        "prize_fund":"{\"en\":{\"descr\":\"Prizes\"},\"ru\":{\"descr\":\"Призы\"},\"ua\":{\"descr\":null},\"hi\":{\"descr\":null},\"kz\":{\"descr\":null},\"de\":{\"descr\":null},\"fr\":{\"descr\":null},\"it\":{\"descr\":null},\"pt\":{\"descr\":null},\"es\":{\"descr\":null},\"sk\":{\"descr\":null}}",
        "prize_page":"{\"en\":{\"descr\":\"prize page\"},\"ru\":{\"descr\":\"Страница призов\"},\"ua\":{\"descr\":null},\"hi\":{\"descr\":null},\"kz\":{\"descr\":null},\"de\":{\"descr\":null},\"fr\":{\"descr\":null},\"it\":{\"descr\":null},\"pt\":{\"descr\":null},\"es\":{\"descr\":null},\"sk\":{\"descr\":null}}"
        },
    "games_limited":true,
    "min_bets":{"EUR":1,"INR":20,"KZT":50,"RUB":20,"UAH":10,"USD":1,"USDT":1},
    "prize_places":9,
    "prize_bank":{"EUR":140,"INR":310,"KZT":11000,"RUB":12577,"UAH":4246,"USD":20,"USDT":30},
    "conditions":{"de":"","en":"\u003cp\u003esome condition text\u003c/p\u003e","es":"","fr":"","hi":"","it":"","kz":"","pt":"","ru":"Новые супер турниры","sk":"","ua":""}
}
```

#### Перезагрузка турнира
- **Subject**: `tournaments.reload-tournament`
- **Запрос**: аналогичен get-tournament
- **Ответ**: "ok" или сообщение об ошибке

#### Выгрузка турнира
- **Subject**: `tournaments.unload-tournament`
- **Запрос**: аналогичен get-tournament
- **Ответ**: "ok" или сообщение об ошибке

#### Установка состояния турнира
- **Subject**: `tournaments.set-state`
- **Запрос**:
```json
{
    "id": 0,  // ID турнира
    "state": 0          // Новое состояние
}
```
- **Ответ**: "ok" или сообщение об ошибке

### Управление участниками

#### Проверка участия пользователя
- **Subject**: `tournaments.user-participates`
- **Запрос**:
```json
{
    "id": 0,  // ID турнира
    "user_id": 0        // ID пользователя
}
```
- **Ответ**: 
```json
{
    "resp": true/false  // Участвует/не участвует
}
```

#### Добавление участника
- **Subject**: `tournaments.add-participant`
- **Запрос**: 
```json
{
    "id": 0,  // ID турнира
    "user_id": 0        // ID пользователя
}
```
- **Ответ**: "ok" или сообщение об ошибке

#### Получение позиции пользователя
- **Subject**: `tournaments.user-position`
- **Запрос**:
```json
{
    "id": 0,  // ID турнира
    "user_id": 0        // ID пользователя
}
```
- **Ответ**:
```json
{
    "position":1, // Позиция в таблице лидеров (начиная с 1)
    "player":{
        "user_id":12, // ID пользователя
        "points":42, // К-во очков
        "login":"lexx" // Логин пользователя
    }
}
```

### Информация о турнире

#### Получение таблицы лидеров
- **Subject**: `tournaments.get-values`
- **Запрос**:
```json
{
    "id": 0,  // ID турнира
    "limit": 10         // Количество записей
}
```
- **Ответ**: 
```json
{
    "1":{
        "user_id":12,
        "points":42,
        "login":"lexx"
        },
    "2":{
        "user_id":153870,
        "points":4,
        "login":"asdasd"
    }
}
```

#### Получение призов турнира
- **Subject**: `tournaments.get-prizes`
- **Запрос**:
```json
{
    "id": 0,  // ID турнира
    "currency": "RUB"    // Валюта для денежных призов
}
```
- **Ответ**:
```json
{
    "1":{
        "money_amount":3000,
        "item":{
            "item_type":25,
            "amount":3,
            "bonus_type":0,
            "img":"",
            "texts":""
            },
        "img":"",
        "texts":""
    },
    "2":{
        "money_amount":0,
        "item":{
            "item_type":25,
            "amount":2,
            "bonus_type":0,
            "img":"",
            "texts":""
        },
        "img":"",
        "texts":""
    },
    "3":{
        "money_amount":1000,
        "img":"",
        "texts":""
    },
    "4":{
        "money_amount":123,
        "img":"",
        "texts":""
    },
    "5":{
        "money_amount":123,
        "img":"",
        "texts":""
    },
    "6":{
        "money_amount":0,
        "item":{
            "item_type":0,
            "amount":1,
            "bonus_type":49,
            "bonus_params":"{\"freespins\":\"10\",\"wager\":\"10\",\"min_sum\":\"100\",\"max_sum\":\"1000\",\"max_win\":\"1000\",\"max_x_win\":\"2\",\"min_wager\":\"0\",\"max_bet\":\"0\"}",
            "bonus_games":"{\"sleg\":[\"f868bdcd4d71ed1b1fc197405c60f48db19f4568\"]}",
            "img":"/storage/user_img/slc/LittleChest.png",
            "texts":"{\"en\":{\"name\":\"bonus\",\"descr\":\"bonus from tournament\"},\"ru\":{\"name\":null,\"descr\":null},\"ua\":{\"name\":null,\"descr\":null},\"hi\":{\"name\":null,\"descr\":null},\"kz\":{\"name\":null,\"descr\":null},\"de\":{\"name\":null,\"descr\":null},\"fr\":{\"name\":null,\"descr\":null},\"it\":{\"name\":null,\"descr\":null},\"pt\":{\"name\":null,\"descr\":null},\"es\":{\"name\":null,\"descr\":null},\"sk\":{\"name\":null,\"descr\":null}}"
        },
        "img":"",
        "texts":""
    //...
    },
}
```

#### Получение описания турнира
- **Subject**: `tournaments.get-description`
- **Запрос**:
```json
{
    "id": 2,  // ID турнира
    "lang": "en"        // Код языка
}
```
- **Ответ**: 
```html
<p>some condition text</p>
```

### Игровые методы

#### Получение турнира для игры
- **Subject**: `tournaments.game-tournament`
- **Запрос**:
```json
{
    "user_id": 12,       // ID пользователя
    "currency": "USD",  // Валюта
    "game_system": "sleg",  // Система игры
    "game_id": "6e514731d448e9ce4bbec08ee14d054fa5570d54",      // ID игры
    "test": false       // Тестовый режим
}
```
- **Ответ**: 
```json
{
    "error":"",
    "tournament":{
        "model":{
            "id":2,
            "type":1,
            "name":"first test",
            "status":1,
            "start_time":"2025-05-26T09:26:00+03:00",
            "end_time":"2025-05-27T09:26:00+03:00",
            "game_type":4,
            "game_list":"{\"sleg\":[\"6e514731d448e9ce4bbec08ee14d054fa5570d54\",\"598a4244039d75fef7a03fb3e4b4cf3ba93c01e2\",\"805e294072873e2afdcc2f500913568bac1a6ee7\",\"43084f01ef7f58e309829de90cb8302332955227\",\"01b288ab410801ad4b76fd8b3c842df055f06bd0\",\"7106ba327950a241fca4e9b146e1d4f1ee740bf2\",\"cfda61e54e80bd75542de0aa7f0461db39fd3dc9\",\"daed5908c39534676a4f1a4cb17c66b035349111\",\"c830f60f45624b83b354d801e8e941ef7ead032e\",\"70266002a4b54d2617b9641891de5e0b8bba8bdd\",\"b29a82c2fa16a4faf3cdbc2c7707432baa8962c0\",\"f2e15c795d5ff692a960ba0878a5152224b1445e\",\"ad9307a14ab0757b1c6522dff9529815e6c369a9\",\"5ca40cfa52a6861e437735986361ef42593d7184\",\"8b1d2cc86662fd420e88095c9ebbe0813ee2b412\",\"7f15d4b6f8e1cc827e170534c286516d3ed22123\",\"b373e6e56b0a23fd8858d16c58bb8cf829cf6320\",\"f05046c9eac85c7165e499e93bea6c111f53b1e6\",\"75ad3fb3cac4ac9efbcc6838569876d66c8ea91f\",\"fbf6e313ca78809664c9891e6a1fb598ea138aa4\",\"bf216a68667333386b4429033cc2c45fdb633aa0\"]}",
            "params":"{\"min_bets\":{\"RUB\":\"20\",\"UAH\":\"10\",\"USD\":\"1\",\"EUR\":\"1\",\"INR\":\"20\",\"USDT\":\"1\",\"KZT\":\"50\"}}",
            "tournament_bonus_group_id":3,
            "created_at":"2025-04-05T09:29:38+03:00",
            "updated_at":"2025-05-26T10:45:36+03:00",
            "currency":1,
            "is_test":true,
            "tile_img":"/storage/user_img/tournaments/mobile.webp",
            "mobile_img":"/storage/user_img/tournaments/mobile.webp",
            "desktop_img":"/storage/user_img/tournaments/info-banner_desktop.png",
            "cabinet_d_img":"/storage/user_img/tournaments/info-banner_desktop.png",
            "cabinet_m_img":"/storage/user_img/tournaments/wide.webp",
            "req_confirm":false,
            "conditions":"{\"en\":{\"descr\":\"\u003cp\u003esome condition text\u003c\\/p\u003e\"},\"ru\":{\"descr\":\"Новые супер турниры\"},\"ua\":{\"descr\":null},\"hi\":{\"descr\":null},\"kz\":{\"descr\":null},\"de\":{\"descr\":null},\"fr\":{\"descr\":null},\"it\":{\"descr\":null},\"pt\":{\"descr\":null},\"es\":{\"descr\":null},\"sk\":{\"descr\":null}}",
            "aggr_req":false,
            "prize_fund":"{\"en\":{\"descr\":\"Prizes\"},\"ru\":{\"descr\":\"Призы\"},\"ua\":{\"descr\":null},\"hi\":{\"descr\":null},\"kz\":{\"descr\":null},\"de\":{\"descr\":null},\"fr\":{\"descr\":null},\"it\":{\"descr\":null},\"pt\":{\"descr\":null},\"es\":{\"descr\":null},\"sk\":{\"descr\":null}}",
            "prize_page":"{\"en\":{\"descr\":\"prize page\"},\"ru\":{\"descr\":\"Страница призов\"},\"ua\":{\"descr\":null},\"hi\":{\"descr\":null},\"kz\":{\"descr\":null},\"de\":{\"descr\":null},\"fr\":{\"descr\":null},\"it\":{\"descr\":null},\"pt\":{\"descr\":null},\"es\":{\"descr\":null},\"sk\":{\"descr\":null}}"
        },
        "games_limited":true,
        "min_bets":{"EUR":1,"INR":20,"KZT":50,"RUB":20,"UAH":10,"USD":1,"USDT":1},
        "prize_places":9,
        "prize_bank":{"EUR":140,"INR":310,"KZT":11000,"RUB":12577,"UAH":4246,"USD":20,"USDT":30},
        "conditions":{"de":"","en":"\u003cp\u003esome condition text\u003c/p\u003e","es":"","fr":"","hi":"","it":"","kz":"","pt":"","ru":"Новые супер турниры","sk":"","ua":""}
    }
}
```

### Системные методы

#### Проверка соединения
- **Subject**: `tournaments.ping`
- **Запрос**: пустой
- **Ответ**: "pong"

#### Перезагрузка всех турниров
- **Subject**: `tournaments.reload-tournaments`
- **Запрос**: пустой
- **Ответ**: "ok"

## Обработка ошибок

В случае ошибки сервис возвращает JSON-объект с описанием ошибки:
```json
{
    "error": "Error description"
}
```

---

# База данных
## Документация моделей пакета models

## Оглавление
1. [Модель турнира (TournamentModel)](#модель-турнира-tournamentmodel)
2. [Модель призов турнира (TournamentPrizeModel)](#модель-призов-турнира-tournamentprizemodel)
3. [Модель значений турнира (TournamentValueModel)](#модель-значений-турнира-tournamentvaluemodel)

## Модель турнира (TournamentModel)

### Описание
Модель `TournamentModel` представляет собой основную структуру данных для хранения информации о турнирах. Она включает в себя все основные параметры турнира, такие как статус, временные рамки, игровые параметры и визуальные элементы.

### Статусы турнира
- 0 (STATUS_NOT_ACTIVE) - не активен
- 1 (STATUS_ACTIVE) - активен
- 2 (STATUS_DONE) - завершен, но не выданы награды
- 3 (STATUS_COMPLETED) - завершен и выданы награды

### Структура модели

| Поле | Тип | JSON поле | Описание |
|------|-----|-----------|----------|
| ID | uint64 | id | Уникальный идентификатор турнира |
| Type | int | type | Тип турнира (не используется) |
| Name | string | name | Название турнира |
| Status | int | status | Статус турнира |
| StartTime | time.Time | start_time | Время начала турнира |
| EndTime | time.Time | end_time | Время окончания турнира |
| GameType | int | game_type | Тип игры (1 - мультипликатор, 2 - ставка * x, 3 - выигрыш * x, 4 - выигрыш / k) |
| GameList | *string | game_list | Список игр (опционально) |
| Params | string | params | Параметры турнира |
| CurrencyID | int | currency | Валюта |
| IsTest | bool | is_test | Тестовый режим |
| TileImg | string | tile_img | Изображение для плитки |
| MobileImg | string | mobile_img | Изображение для мобильного |
| DesktopImg | string | desktop_img | Изображение для десктопа |
| CabinetDImg | string | cabinet_d_img | Изображение для кабинета десктоп |
| CabinetMImg | string | cabinet_m_img | Изображение для кабинета мобильное |
| Conditions | string | conditions | Условия турнира |
| AgreementReq | bool | aggr_req | Требуется согласие на условия |
| PrizeFund | string | prize_fund | Призовой фонд |
| PrizePage | string | prize_page | Страница призы |

### Примечания
- Многие поля помечены как "не используется" в текущей версии
- Модель содержит несколько типов изображений для разных платформ
- Поля CreatedAt и UpdatedAt автоматически обновляются при создании/изменении записи

## Модель призов турнира (TournamentPrizeModel)

### Описание
Модель `TournamentPrizeModel` представляет собой структуру данных для хранения информации о призах в турнирах. Она включает в себя как денежные призы в различных валютах, так и внутриигровые предметы (items).

### Структура модели

| Поле | Тип | JSON поле | Описание |
|------|-----|-----------|----------|
| ID | uint64 | id | Уникальный идентификатор приза |
| GroupID | uint64 | group_id | Идентификатор группы призов |
| Place | int | place | Позиция в призовом фонде |
| Name | string | name | Название приза |
| Img | string | img | URL или путь к изображению приза |
| Texts | string | texts | Текстовое описание приза |
| CurrencyAmounts | *string | currency_amounts | Денежные вознаграждения в различных валютах (опционально) |
| ItemType | *int | item_type | Тип игрового предмета (опционально) |
| Amount | int | amount | Количество призовых предметов |
| BonusType | int | bonus_type | Тип бонуса для призов-бонусов |
| BonusParams | *string | bonus_params | Параметры бонуса (опционально) |
| BonusGames | *string | bonus_games | Список игр, где действует бонус (опционально) |
| ContainerImg | string | container_img | URL или путь к изображению контейнера приза |
| ContainerTexts | string | container_texts | Текстовое описание контейнера приза |

## Модель значений турнира (TournamentValueModel)

### Описание
Модель `TournamentValueModel` используется для хранения значений (очков) участников турнира. Она связывает пользователей с их достижениями в конкретном турнире.

### Структура модели

| Поле | Тип | JSON поле | Описание |
|------|-----|-----------|----------|
| ID | uint64 | id | Уникальный идентификатор значения |
| TournamentID | uint64 | tournament_id | ID турнира |
| UserID | int | user_id | ID пользователя |
| Value | float32 | value | Значение (очки) |
| CreatedAt | *time.Time | created_at | Время создания (опционально) |
| UpdatedAt | *time.Time | updated_at | Время обновления (опционально) |

### Примечания
- Модель используется для отслеживания прогресса участников в турнире
- Поля CreatedAt и UpdatedAt автоматически обновляются при создании/изменении записи