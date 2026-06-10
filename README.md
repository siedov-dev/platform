# Krakencassino Документация

# Развертывание

## Для запуска системы:
 - развернуть БД
 - установить и настроить подключение к Redis. Для Redis соединения `kraken` (`config/database`) установлен конкретный `slug` так как такое же соединение потребуется в crm.
 - подключить `cron`
 - запустить очереди сообщений `по умолчанию` и `game` (`queue_work.sh` и `queue_game.sh`).  
   В линукс системах с `systemd` можно запустить в виде сервисов (примеры в файлах `kraken-qgame.service` и `kraken-queue.serevice` - скопировать в `/etc/systemd/system` и исправить пути к скриптам) 
```shell
  php artisan queue:work
  php artisan queue:work --queue=game
```
 - настроить ключи и параметры в .env файле, настроить ключи в параметрах (`/config/config.yaml`) всех сервисов (`/service`).
 - запуск сервисов отдельно либо с помощью `Docker`. Для каждого сервиса есть `Dockerfile` и скрипт сборки (`docker-build.sh`) и запуска (`docker-build.sh`) либо общий компоуз файл (`/service/docker-compose.yml`) для `Docker compose`.   
 - стартовые операции `Laravel`:
```shell
  composer dumpautoload
  php artisan key:generate
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
```
---

# Регистрация

Регистрация на сайте доступна как из формы регистрации самого сайта, так и из внешних "прелендинг" сайтов (колесо, партнерские переходы и т.п.)
Регистрация обрабатывается в `AuthController`.

## Основная регистрация
Регистрация из формы на сайте обрабатывается в методе `register`. Учитывается возможность регистрации как по номеру телефона, так и по email.  
Регистрационная форма для валюты `EUR` является более расширенной и содержит дополнительные данные пользователя. 

 - для валидации данных используется `RegisterRequest`
 - обрабатываются и заполняются основные регистрационные данные пользователя
 - проверяется реферальная пользовательская ссылка (куки `uref` устанавливаемый в миддлваре `UserReferralsMiddleware`)
 - установка источника перехода пользователя (куки `origRef` устанавливаемый в миддлваре `UsersOrigin`)
 - для валюты `EUR` устанавливаются верификационные данные
 - устанавливается токен пользователя для запрета двойного входа
 - проверка наличия `tg_client_hash` для привязки пользователя к менеджеру crm
 - проверка `clickId` для привязки пользователя к партнеру (миддлвар `CheckPartnerLink`)
 - создаются регистрационные бонусы в методе `createBonus`
 - при наличии в запросе поля `promocode` активируется соответствующий промокод
 - при установленном куки `fortune_bonus` выдается соответствующий бонус (устанавливается при переходе с прелендинг сайтов колеса фортуны в методе `regWithBonusByParams`)
 - запускается фоновая задача завершения регистрации `RegistrationDoneJob`

## Регистрация из внешних сайтов
Методы `regPreland`, `joinWithBonusByToken`, `regWithBonusByParams`, `joinWithBonusByParams` используются для автоматической регистрации из прелендинг сайтов (колеса фортуны и т.п.)  
Метод `joinHashClient` Используется в ссылках менеджеров, и устанавливает куки `tg_client_hash` для привязки пользователя к менеджеру выдавшему линк на сайт в задаче `BindManagerJob` 

# Авторизация
Авторизация пользователя выполняется с помощью `LoginForm` которая верифицирует входящие в `POST` данные, возвращает ошибки методом `getError` 
и авторизует пользователя в методе `login`. После авторизации создает событие `visit` в сервисе `user_manager` необходимое для передачи в систему `pixel-api`.

После авторизации в слушателе `LoginListener` сохраняется информации об ip пользователя в `UserLoginLog`, синхронизируется информация с `CustomerIo` выводятся необходимые попапы. 

# Платежные системы

Платежные методы представлены в моделе `PaymentSystems`. Все методы разделены по типу `TYPE_PAYIN`  - депозиты, `TYPE_PAYOUT` - выплаты, `TYPE_CRYPTO_BUY` - пополнение криптовалюты.
А также могут быть принадлежать определенной группе (поле `system_group` хранящее перечисление `PaymentSystemGroup`). 
Поле `system_id` указывает какую систему депозита/выплаты (`Payin`/`Payout`) использует данный метод.

Все платежные АПИ находятся в `App\PaymentsAPI\`

## Депозит

Каждая платежная система для депозита должна реализовать свой класс `Payin` (`App\Payin`). Класс `Payin` представляет собой систему депозитов.
Предоставляет информацию о реквизитах платежного метода системы депозита, является ли метод скрытым, проверяет реквизиты, 
делает запросы к апи платежной системы и возвращает url, форму для перехода на страницу оплаты или модальное окно с данными для оплаты.

### Флоу депозита:

 - пользователь активирует модальное окно депозита (`window.wallet.showDeposit` или другими методами)
 - `WalletController::deposit` возвращает `view` `common\blocks\deposit` или `common\blocks\deposit-crypto` я криптовалют.
 - `view` из `\App\Models\PaymentSystems::getDepositGroups` получает группы платежных методов
 - `view` из `\App\Models\PaymentSystems::getPayinSystemsForCurrency` получает платежные методы для выбранной валюты
 - при выборе платежного метода `view` получает реквизиты из `WalletController::getPayinRequisites` который получает их из соответствующей `Payin::getFormRequisites` 
и проверяет блокировку мультидепа
 - пользователь заполняет необходимые данные и выполняет запрос на депозит (`PayController::deposit`)
 - `PayController` создает транзакцию `PayoutTransaction`
 - получает соответствующую систему депозита `Payin`
 - проверяет блокировку, необходимость изменения суммы, возможность продолжения операции депозита с помощью `Payin`
 - выполняет `process` и в случае успеха возвращает полученные данные назад в ui
 - `view` на основе результата перебрасывает пользователя на страницу платежа или открывает его в `iframe`
 - пользователь совершает депозит
 - платежная система присылает `callback` с результатом по `api` маршруту соответствующий АПИ метод
 - АПИ платежа обрабатывает `callback` и передает `PayoutTransaction` на завершение в `Payin::completeTransaction`
 - `Payin::completeTransaction` устанавливает транзакцию в завершенную, проверяет наличие бонусов ожидающих платежа, устанавливает номер депозита... 

# Выплата
Каждая платежная система для выплат должна реализовать свой класс `Payout` (`App\Payout`). Класс `Payout` представляет собой систему выплат.
Предоставляет информацию о реквизитах платежного метода системы депозита, является ли метод скрытым, проверяет реквизиты,
делает запросы к апи платежной системы и возвращает реквизиты необходимые для совершения выплаты.

### Флоу выплаты:

 - пользователь активирует модальное окно депозита (`window.wallet.showPayout` или другими методами)
 - `WalletController::payout` возвращает `view` `common\blocks\payout`
 - `view` из `\App\Models\PaymentSystems::getPayoutGroups` получает группы платежных методов
 - `view` из `\App\Models\PaymentSystems::getPayoutSystemsForCurrency` получает платежные методы для выбранной валюты
 - при выборе платежного метода `view` получает реквизиты из `WalletController::getPayoutRequisites` который получает их из соответствующей `Payout::getFormRequisites`
 - пользователь заполняет необходимые данные и выполняет запрос на депозит (`PayController::payout`)
 - `PayController` проверяет возможность выплаты и создает заявку на выплату `ApplicationForPayment`
 - отменяются бонусы, для которых запрещен вывод до отыгрыша
 - администратор подтверждает заявку на выплату, либо, если доступна автовыплата, заявка переходит в статус `ApplicationForPayment::STATUS_AUTO_PAY_PENDING`
 - каждую минуту запускается обработчик `\App\Http\Controllers\PayController::processAutoPayments()` в `App\Console\Kernel` 
 - `processAutoPayments` запускает для каждой `ApplicationForPayment` с незавершенным статусом `processAutoPayment` соответствующий `Payout::process`,
который выполняет запрос на выплату, и проверяет статус и завершает транзакцию создавая `PayoutTransaction`.

# Создание платежной системы

Для создания платежной системы:
 - АПИ платежной системы в директории App\PaymentsAPI
 - добавить ID статусов в `PayoutTransaction` и поместить в соответствующие массивы
 - создать в `App\Payin` клас системы депозитов унаследованный от `Payin`, создать соответствующий `Type` системы
 - в созданном `Payin` обязательно реализовать свои методы `process` и `getStatusCodes`, в конструкторе прописать название и тип
```php
    public function __construct(MUser $user)
    {
        parent::__construct($user);
        $this->name = "CooCooPay";
        $this->type = self::TYPE_COOCOOPAY;
    }
```
 - при необходимости переопределить другие методы, пример для отображения нестандартных реквизитов
 - создать в `App\Payout` клас системы депозитов унаследованный от `Payout` и реализующий интерфейс `AutoPayoutSystem` (если нужна система выплат), создать соответствующий `Type` системы
 - в созданном `Payout` обязательно реализовать свои методы `processAutoPayment` и `checkParams`
 - при необходимости переопределить другие методы, пример для отображения нестандартных реквизитов
 - прописать callback маршруты
 - создать `db:seeder` и заполнить данные необходимых методов в `PaymentSystems` и таблицы `payout_transactions_statusTransaction`
пример:
```php

class PaymePaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $system_name = 'Payme';
        $image_dir = '/img/paysystem/';

        $items = [
            [
                'type' => PaymentSystems::TYPE_PAYIN,
                'currency_id' => Wallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'RUB',
                'min' => 600,
                'max' => 300000,
                'name' => 'Payme',
            ],

            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => Wallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'RUB_card',
                'min' => 600,
                'max' => 300000,
                'name' => 'Payme card',
            ],

            [
                'type' => PaymentSystems::TYPE_PAYOUT,
                'currency_id' => Wallet::CURRENCY_RUB,
                'method_alias' => $system_name . '.' . 'RUB_phone',
                'min' => 600,
                'max' => 300000,
                'name' => 'Payme phone',
            ],

        ];

        foreach ($items as $item) {

            PaymentSystems::updateOrCreate(
                [
                    'type' => $item['type'],
                    'currency_id' => $item['currency_id'],
                    'method_alias' => $item['method_alias'],
                    'system_id' => Payin::TYPE_PAYME,
                    'system_name' => $system_name,
                    'name' => $item['name'],
                ],
                [
                    'image_url' => $image_dir . 'visa.png',
                    'min_sum' => $item['min'],
                    'max_sum' => $item['max'],
                    'test_mode' => true,
                ]
            );
        }

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_PAYME_PENDING, 'value' => 'STATUS_PAYME_PENDING'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_PAYME_SUCCESS, 'value' => 'STATUS_PAYME_SUCCESS'],
                []
            );

        DB::table('payout_transactions_statusTransaction')
            ->updateOrInsert(
                ['id' => PayoutTransaction::STATUS_PAYME_FAIL, 'value' => 'STATUS_PAYME_FAIL'],
                []
            );
    }
}
```
### Примечание:
Все файлы каталогов `Payin`, `PaymentAPI`, `Payout` должны содержать свои копии в тех же каталогах `crm`. 
Так же в `crm` необходимо добавить информацию о новой системе в файле `App\Helpers\PayFuncs`.

# Кошельки

Кошельки пользователя представлены классом `App\Wallet`. 
Все операции с кошельком выполняются в сервисе `user_manager` к которому `Wallet` обращается через `App\Services\Users\UserManager`.
В базе кошельки сохранены в таблице `user_wallets` и представлены моделью `UserWallet`. `UserWallet` не должен использоваться напрямую!

Управление активными кошельками выполняется в `WalletController`. При отсутствии кошелька для определенной валюты в базе он создается автоматически сервисом `user_manager`.

## Создание валюты

Для создания новой валюты:
- добавить холы slotmashine.halls (отменено)
- создать запись в таблице `user_currencies`
- создать запись в таблице `bonus_currency_configs`
- добавить в `currency-icon.css` цвет валюты и знак (копия в црм)
- добавить в `currency` сервиса user_manager
- добавить в `App\Helpers\Currency`
- добавить константы в `UserWallet` (необходим рефакторинг и убрать использование данного класса), `Wallet`
- прописать значения рангов
- продублировать данные классы и css в `crm`
- `crm` добавить валюту в права в группе `Разрешенные валюты` в таблице `crm_rights` или через `Настройка существующих прав` 

---

# Игры

Классы, обрабатывающие игры, находятся в каталоге `app\GamesProviders`.  
Информацию о всех играх системы обрабатывает `GamesManager`. `GamesManager` обеспечивает связь с сервисом `games_manager` 
для быстрого доступа к данным о всех играх, поиску игр, информацию о группах игр и бонусных играх.  

Для каждого варианта типа устройства и языка предусмотрена настройка своего набора доступных игр. Настройки проводятся в crm и загружаются сервисом `games_manager`.

Игры представлены классом `Game`. Каждая игра принадлежит определенному провайдеру `GameProvider` и группе игр этого провайдера `GameGroup`.

Каждая игра относится к одной из категорий `slots`, `live`, `table` или `sport`. 
Так же у каждой игры может быть несколько пометок (тэгов): `new`, `top`, `crash`, `live` т.п.

Класс `GamesCollection` представляет коллекцию игр, с возможностью итерации, пагинации, сортировки, фильтрации, сериализациии и десиарилизации как и с json к и с данных в массиве (dto из сервиса)

Клас `SearchResult` представляет результат поиска игр в сервисе `games_manager`.

## Запуск игры

Запуск игры происходит в `EmulatorController::index`. Игра может быть запущена в режиме на реальные деньги или демо, если игровой провайдер поддерживает данный режим.
`balance_mode` пользователя устанавливает режим игры — реальный счет или игра на бонус. 

Флоу:
 - Закрывается предыдущая игровая сессия (если была запущена)
```php 
EmulatorController::transferMoneyBack();
 ```
 - Проверяется наличие игры и возможность ее запуска
 - Проверка режима баланса игры, проверка доступности бонуса и возможности запуска игры на этом бонусе
 - В зависимости от провайдера получаем его апи и запрашиваем `url` запуска игры
 - `Api` провайдера получает данные для игровой сессии из `GameData`
 - `Api` проверяет необходимость запуска новой игровой сессии на стороне провайдера 
(например если пользователь играл на бонус и запускает игру на реальные деньги, то необходим запуск новой игровой сессии)
 - `Api` создает игровую сессию на сервисе `user_manager` (данные об игровой сессии хранятся в моделе/таблице `UserGameStat`)
 - `Api` делает запрос к провайдеру для получения `url` игры и возвращает его в `EmulatorController`
 - `EmulatorController` открывает `view` `goplay`
 - `goplay` отображает `url` игры в `ifame` и ежесекундно проверяет информацию о бонусе для отображение информации о нем
```js
let res = await network.get('/getBonusPad');
```
 - если для игры доступен турнир то `goplay` также отображает и обновляет турнирную таблицу
```php
@include('desktop.tournaments.tournament-table', ['tournament' => $tournament,])
 ```

##  Закрытие игры

При переходе на маршрут с `returnMoney` `middleware` происходит закрытие игровой сессии в `EmulatorController::transferMoneyBack`
```php
Route::group(['middleware' => 'returnMoney'], function() {
```
 - `EmulatorController` проверяет наличие игровой сессии
Пример ответа:
```php
array:3 [ 
  "cd" => 200
  "err" => ""
  "gs" => array:4 [
    "Stats" => array:21 [
      "id" => 570
      "user_id" => 12
      "currency" => "RUB"
      "session_id" => "rBZRApA8bsMHkkmU"
      "system" => "sleg"
      "group" => "Amatic"
      "game_id" => "f868bdcd4d71ed1b1fc197405c60f48db19f4568"
      "login_balance" => 5567.3
      "logout_balance" => 0
      "balance_diff" => 0
      "full_total_bet" => 0
      "full_total_win" => 0
      "real_total_bet" => 0
      "real_total_win" => 0
      "total_bet" => 0
      "total_win" => 0
      "login_date" => "2025-05-30T10:06:26.495939627+03:00"
      "created_at" => "2025-05-30T10:06:26.495939627+03:00"
      "updated_at" => "2025-05-30T10:06:26.495943686+03:00"
      "status" => 0
      "bonus_id" => null
    ]
    "GameStartData" => array:14 [
      "system" => "sleg"
      "game_id" => "f868bdcd4d71ed1b1fc197405c60f48db19f4568"
      "game_name" => ""
      "provider" => ""
      "currency" => 1
      "bonus_id" => 0
      "tnt_game_id" => 0
      "resident_tnt_game_id" => 0
      "cashback_bonus_id" => 0
      "non_dep_bonus_id" => 0
      "filthy_bonus_id" => 0
      "lose_bonus_id" => 0
      "locked_money" => 900
      "wager_prc" => 100
    ]
    "ActiveBonus" => null
    "BetWagerBonus" => null
  ]
]
```
 - если сессия активна, получает соответствующего игрового провайдера и вызывает закрытие сессии
```php
    $system_name = $gs['gs']['GameStartData']['system'];
    $provider = GamesManager::getProvider($system_name);
    $closed_sess = $provider->getApi()->closeSession($user);
```
 - `провайдер` вызывает закрытие игровой сессии через сервис `user_manager`
 - проверяет необходимость начисления кешбэк бонусов
 - и запускает задачу фоновой обработки завершения игры `ExitGoGameJob`
 - `ExitGoGameJob` обновляет статистики `UserStatistics` (для достижений, заданий и т.п.)
 - проверяет повышение ранга в результате игры
 - отправляет необходимые `events` в `CustomerIo`
 - отправляет `laravel event` `MoneySpent` (для отправки в партнерскую систему или других операций)
 - обновляет достижения пользователя
 - сохраняет результат `TopWin` (который сохраняет значения для зал славы, и последний лучших выигрышей - не используется)
---

# Бонусы

Система позволяет создавать бонусы с различными механиками работы. Бонусная система находится в `App\Models\Bonuses`.
Базовым классом бонуса является `UserBonus` который реализует интерфейс `BonusInterface` и содержит основные необходимые операции.
Так же реализует интерфейсы:
 - `VegasBonusInterface` - интерфейс нужен для подключения бонуса к играм. Больше не используется, вся обработка бонусов в играх перенесена в сервис `user_manager`.
 - `DepositBonusInterface` - интерфейс используется для бонусов, которые обрабатывают депозиты (активация или любая другая реакция на депозит).
Используется в `Payin::completeTransaction`.
 - `BonusSegmentAttachable` - интерфейс для подключения к бонусным сегментам `BonusSegment`.

Тип каждого бонуса определяется по полю `type`, бонус приводится к нужному классу/модели методом `UserBonus::convertByType(UserBonus $bonus)`, 
который возвращает соответствующую типу модель бонуса.

Бонус находится в одном из состояний:
```php
    const STATUS_NOT_ACTIVATED = 0; // Ожидание активации
    const STATUS_ACTIVE = 1; // Активен
    const STATUS_DONE = 2; // Завершён
    const STATUS_CANCELED = 3;  // Отменен
    const STATUS_PENDING = 4; // Ожидает
    const STATUS_EXPIRED = 5; // Истёк
    const STATUS_WIN = 6; // Выигран
    const STATUS_WAITING_FOR_PAYMENT = 7; // Ожидает оплаты
    const STATUS_WAITING_FOR_AGREEMENT = 8; // Ожидает активации
    const STATUS_FILTHY = 9; // Отмывочный (не используется)
    const STATUS_WAITING_FOR_LOSE = 10; // Ожидает проигрыша
    const STATUS_LOCKED = 11; // Заблокирован
    const STATUS_BUY_REQUIRED = 12; // Требует покупки (не используется)
    const STATUS_ERROR = 13; // Ошибка
    const STATUS_DEPOSIT_LISTEN = 14; // Требует уведомления об оплате
```

При каждом отображении бонуса вызывается метод `actualize` для обновления текущего состояния.
При создании и смене состояния бонусы записывают логи в `BonusStatistic`.

## Выдача бонусов

Для бонусов в `crm` создаются параметры в `json` формате. Для каждой валюты можно (и желательно) определить свои параметры.
Бонусы создаются как из конкретного класса бонуса вызовом метода `createFromParams` так и методом `UserBonus::fromParams`. 

Бонусы создаются из:
 - `BonusSchedule` - запланированная выдача бонусов. Используется для запланированной выдачи бонусов пользователям в небольших к-вах.
 - `BonusAvailable` - модель "доступных бонусов". Используется для массовой выдачи бонусов. 
Возможна выдача как большой, преднастроенной, группе людей, так и всем пользователям с конкретной валютой или вообще всем пользователям. 
При этом физически создает бонус только при активации, что экономит к-во записей в базе данных.
 - `BonusPromocode` - модель промокодов. Бонусы создаются при вводе пользователем соответствующего промокода. Обрабатывается в `BonusPromocodesController`.
 - `BonusPatterns` - модель заготовленных бонусов. Используется для создания шаблонных бонусов при регистрации, подтверждении почты и т.д.
 - `SlcItem` - итем геймификации может содержать в себе бонус для выдачи пользователю из инвентаря.

### Дополнительные модели:

- `BonusRegBType` - модель определяет, какие регистрационные бонусы должны выдаваться в зависимости от валюты.
- `BonusMultiDaysParam` - модель с параметрами бонусов по дням для `MultiDaysBonus` 

## Активация бонуса

Доступные бонусы отображаются в кабинете пользователя. Активным для игры может быть только один бонус. 
Пользователь должен "активировать" бонус перед тем, как играть. Активацию бонуса обрабатывает `GetBonusController`.
Он проверяет возможность активации, существование активного бонуса, отменяет бонусы, выдает подтверждение отмены активного бонуса при активации нового.

Кроме `UserBonus` в кабинете пользователя так же отображаются "доступные бонусы" `BonusAvailable`. 
Для отображения и активации он создает "временный бонус" и устанавливает в нем "sub_id" благодаря которому `GetBonusController` определяет 
его принадлежность к `BonusAvailable` использует его для создания `UserBonus` при активации по кнопке "забрать".

## Отображение бонусов

Для отображения бонусов в кабинете пользователя используются классы рендеринга в `App\Models\Bonuses\Render`. 
Которые реализуют интерфейс `App\Models\Bonuses\Contracts\BonusRenderer`:
```php
interface BonusRenderer
{
    public function renderCabinetBonus(string $device = ''): string;
    public function renderPlayButton(string $device = ''): string;
}
```

Каждый бонус возвращает нужный рендерер в методе `getRenderer`.  
`BonusRenderer` отвечает за создание `view` плашек бонусов и кнопок запуска игры бонуса для определенного типа устройства (декстоп или телефон). 

## Документация класса UserBonus

## Описание
`UserBonus` - базовый класс для работы с бонусами пользователей. Класс предоставляет основной функционал для управления бонусами, их активации, проверки статусов и работы с игровыми провайдерами.

## Свойства

### Основные свойства
- `id` - Идентификатор бонуса
- `user_id` - Идентификатор пользователя
- `type` - Тип бонуса
- `money_available` - Доступные бонусные средства
- `money_paid_out` - Выплаченные средства
- `total_win` - Общий выигрыш
- `total_bet` - Общая сумма ставок
- `status` - Статус бонуса
- `spins_total` - Общее количество фриспинов
- `spins_available` - Доступное количество фриспинов
- `active_to` - Дата истечения срока действия
- `currency` - Валюта бонуса
- `pattern_id` - Идентификатор шаблона бонуса
- `game_provider` - Провайдер игры
- `game_id` - Идентификатор игры

### Константы статусов
- `STATUS_NOT_ACTIVATED` (0) - Ожидание активации
- `STATUS_ACTIVE` (1) - Активен
- `STATUS_DONE` (2) - Завершён
- `STATUS_CANCELED` (3) - Отменен
- `STATUS_PENDING` (4) - Ожидает
- `STATUS_EXPIRED` (5) - Истёк
- `STATUS_WIN` (6) - Выигран
- `STATUS_WAITING_FOR_PAYMENT` (7) - Ожидает оплаты
- `STATUS_WAITING_FOR_AGREEMENT` (8) - Ожидает активации
- `STATUS_FILTHY` (9) - Отмывочный (не используется)
- `STATUS_WAITING_FOR_LOSE` (10) - Ожидает проигрыша
- `STATUS_LOCKED` (11) - Заблокирован
- `STATUS_BUY_REQUIRED` (12) - Требует покупки
- `STATUS_ERROR` (13) - Ошибка
- `STATUS_DEPOSIT_LISTEN` (14) - Требует уведомления об оплате

## Методы

### Основные методы работы с бонусом

#### `getId(): ?int`
Возвращает идентификатор бонуса.

#### `activate(): bool`
Активирует бонус. Возвращает результат активации.

#### `actualize()`
Актуализирует состояние бонуса.

#### `isExpired(): bool`
Проверяет, истек ли срок действия бонуса.

#### `cancelBonus()`
Отменяет бонус.

### Методы получения информации

#### `getName(): string`
Возвращает название бонуса.

#### `getDescription(): string`
Возвращает описание бонуса.

#### `getShortDescription(): string`
Возвращает короткое описание бонуса.

#### `getStatusName(): string`
Возвращает название текущего статуса бонуса.

#### `getBalanceString(): string|float`
Возвращает строку с балансом бонуса.

#### `getBalanceName(): string`
Возвращает название типа баланса (спины или денежный баланс).

### Методы проверки состояния

#### `isActive(): bool`
Проверяет, активен ли бонус.

#### `isDone(): bool`
Проверяет, завершен ли бонус.

#### `isCanceled(): bool`
Проверяет, отменен ли бонус.

#### `isPending(): bool`
Проверяет, находится ли бонус в состоянии ожидания.

#### `isCompleted(): bool`
Проверяет, завершен ли бонус (истек, выполнен или отменен).

#### `isLocked(): bool`
Проверяет, заблокирован ли бонус.

### Методы работы с играми

#### `isGameAllowed(Game $game): bool`
Проверяет, разрешена ли игра для бонуса.

#### `getAllowedGames(): GamesCollection`
Возвращает коллекцию доступных для бонуса игр.

#### `getPlayUrl(): string`
Возвращает URL для запуска игры на бонус.

#### `isDirectPlayUrl(): bool`
Проверяет, должен ли бонус запускать игру напрямую.

### Методы работы с депозитами

#### `onDeposit(float $amount, int $currency): bool`
Обработчик совершения депозита.

#### `confirmPayment(MUser $user, float $amount, int $currency): float`
Подтверждает оплату для бонусов, ожидающих оплаты.

### Статические методы

#### `static convertByType(UserBonus $bonus)`
Конвертирует бонус в соответствующий класс по типу.

#### `static fromParams(int $bonus_type, string $params, int $user_id, bool $tmp = true): UserBonus`
Создает бонус из JSON-строки с параметрами.

#### `static findFirstActiveBonus(int $userId, ?int $currency = null): ?UserBonus`
Возвращает первый доступный активный бонус пользователя.

## Отношения с другими моделями

### `user(): HasOne`
Отношение к модели пользователя.

### `pattern(): HasOne`
Отношение к шаблону паттерна бонуса.

### `bonusSegments(): BelongsToMany`
Отношение к сегментам бонуса.

## События

### `afterCreated()`
Выполняется после создания бонуса:
- Привязывает бонус к сегментам
- Устанавливает флаг наличия нового бонуса
- Сохраняет статистику создания бонуса

## Примечания
- Класс поддерживает различные типы бонусов через систему наследования
- Реализует интерфейсы `BonusInterface`, `VegasBonusInterface`, `DepositBonusInterface`, `BonusSegmentAttachable`
- Использует систему событий Laravel для обработки создания бонуса

---

# Геймификация

Геймификация (внутриигровые активности) обеспечивается классами в директории `App\Models\Slc` и контроллером `SlcController`.

## Slc валюта

`Slc` (Slotcoin) валюта — игровая валюта предназначена для покупки SlcItem-ов, игры в Slotmachine, обмена на реальные стредства и др.

`SlcWallet` - кошелек для сохранения Slc валюты пользователя. Предоставляет операции по добавлению/снятию валюты. 
Для каждой реальной валюты существует отдельный `SlcWallet`. Для получения кошелька используется метод `SlcWallet::getForUser`. 
При отсутствии кошелька данный метод создает его.

## SlcItem

`SlcItem` главный элемент геймификации. Представляет интерфейс игрового итема который может выдавать бонус, кешбэк, изменять вейджер и т.д.
Представлен интерфейсом `App\Models\Slc\SlcItem`.

Реализуется:
 - `BonusPromocode` - промокод с `promo_type` `SlcItem` выдает `SlcItem`.
 - `TournamentAwardSlcItem` - награда турнира, выдается за призовое место в турнире, может содержать как сумму реальной валюты так и `SlcItem`.
 - `TournamentSlcItem` - итем содержащийся в `TournamentAwardSlcItem`
 - `SlcAchievementReward` - награда за достижение.
 - `UserRankReward` - награда за ранг.
 - `ShipBarrelItem` - награда бочки `cashback+`.
 - `SlcDailyReward` - награда за ежедневный вход.
 - `UserTaskReward` - награды за задания.
 - `UserTodaysReward` - награда за сгенерированные задания на день.
 - `SlcShopItem` - итем магазина.
 - `SlcChestItem` - итем находящийся в сундуке `LostTreasures`.
 - `SlcUserShopItem` - итем инвентаря пользователя.

`SlcItem` может быть как уникальным, так и содержать определенное к-во елементов. 
При этом поле `amount` может использоватся для указания не только к-ва итемов но и других данных, например размер процента понижения вейджера.
Итемы могут иметь срок действия.

Каждый `SlcItem` предоставляет свой класс `ShopItemInfo`, который предоставляет информацию о итеме и используется для генерации соответствующих блоков отображения.
При этом для итема могут быть прописаны свои значение описания, названия и иконки (методы `getImage`, `getDescription`, `getName`), 
которые являются приоритетными по сравнению с методами `ShopItemInfo`.

Так же каждый `SlcItem` предоставляет свой `SlcItemActivator` - класс используемый для активации итема. Интерфейс содержит методы необходимые для активации итема:
 - `activateItem` собственно активаци итема (выдача бонуса, отображение окна стрельбы, понижение вейджера бонуса и т.д.)
 - `requiresDialog` говорит о том, что для активации итема необходим дополнительный диалог. 
Так же может использоваться для того, чтобы отобразить информацию о дополнительных требованиях для активации итема.
 - `getDialog` возвращает `view` диалогового окна для активации итема (выбор бонуса для понижения вейджера, окно с информацией о требованиях и т.п.)

### SlcDataProvider
`App\Providers\SlcDataProvider` регистрирует некоторые фабрики для геймификации:
 - `SlcItemFactory` - фабрика для создания соответствующих `ShopItemInfo` и `SlcItemActivator` для каждого типа `SlcItem`.
 - `AchievementDataFactory` - фабрика для создания `SlcAchievementData` предоставляющих данные для достижений.

## Инвентарь
Отображает инвентарь пользователя метод `SlcController::userItems`.

Класс `SlcUserInventory` предоставляет методы для работы с инвентарем пользователя. Инвентарь пользователя сохраняется в моделе `SlcUserShopItem`.
При добавлении нового итема в инвентарь пользователя сохраняется лог в модели `SlcInventoryLog`. 
При каждом получении инвентаря на каждом итеме вызывается метод `checkStatus` проверяющий срок истечения итема.

Для получения к-ва новых итемов в инвентаре используется метод статический метод `SlcUserShopItem::getNewItemsCount`.

## Документация класса SlcUserInventory

`SlcUserInventory` - класс для управления инвентарем пользователя в системе SLC.

## Конструктор

### `__construct(MUser $user)`
Приватный конструктор класса, принимает объект пользователя.

## Статические методы

### `forUser(MUser $user): SlcUserInventory`
Создает и возвращает экземпляр инвентаря для указанного пользователя.

**Параметры:**
- `$user` - объект пользователя (MUser)

**Возвращает:** Экземпляр SlcUserInventory

## Публичные методы

### `addItem(SlcItem $item, string $source = "shop"): ?SlcUserShopItem`
Добавляет предмет в инвентарь пользователя.

**Параметры:**
- `$item` - объект предмета (SlcItem)
- `$source` - источник получения предмета (по умолчанию "shop")

**Возвращает:** SlcUserShopItem или null

**Особенности:**
- Создает лог в инвентаре
- Если тип предмета SLC, добавляет валюту в кошелек
- Поддерживает стакающиеся предметы
- Устанавливает время жизни предмета

### `getItems(bool $byGroups = false): Collection`
Возвращает коллекцию всех активных предметов в инвентаре пользователя.

**Параметры:**
- `$byGroups` - флаг группировки предметов (по умолчанию false)

**Возвращает:** Collection предметов SlcUserShopItem

### `getFirstItem(SlcItemType $type): ?SlcUserShopItem`
Возвращает первый доступный предмет указанного типа.

**Параметры:**
- `$type` - тип предмета (SlcItemType)

**Возвращает:** SlcUserShopItem или null

### `getItemsCount(SlcItemType $type): int`
Возвращает количество предметов указанного типа.

**Параметры:**
- `$type` - тип предмета (SlcItemType)

**Возвращает:** Количество предметов (int)

### `hasItem(SlcItemType $type, int $amount = 1): bool`
Проверяет наличие указанного количества предметов определенного типа.

**Параметры:**
- `$type` - тип предмета (SlcItemType)
- `$amount` - необходимое количество (по умолчанию 1)

**Возвращает:** true/false

### `takeItem(SlcItemType $type, int $amount = 1): ?SlcItem`
Забирает указанное количество предметов из инвентаря.

**Параметры:**
- `$type` - тип предмета (SlcItemType)
- `$amount` - количество предметов для изъятия (по умолчанию 1)

**Возвращает:** SlcItem или null

**Особенности:**
- Использует транзакции для обеспечения целостности данных
- Блокирует записи при обновлении

## Ранги

Текущий ранг игрока определяется по `TotalBet` его кошелька. Классы для работы с рангами пользователя находятся в `App\Models\Rank\`.

Модель `UserRank` представляет функции для работы с информацией о ранге.  
Модель `UserRankReward` содержит `SlcItem` награды за каждый полученный ранг.  
Модель `UserRankRewarded` содержит информацию о получении награды за уровень ранга по валюте.  
Модель `UserRankLog` содержит логи получения рангов пользователями с информацией о предыдущем уровне ранга (для случаев, когда пользователь получит несколько рангов за игру).


Методы `MUser` для получения информации о ранге:
```php

    /**
     * Возвращает текущий уровень ранга
     * @param int|null $currency
     * @return int
     */
    public function getLevel(?int $currency = null): int
    {
        return $this->getRank($currency)->getLevel();
    }

    /**
     * Возвращает информацию о ранге
     * @param int|null $currency
     * @return UserRank
     */
    public function getRank(?int $currency = null): UserRank
    {
        if (!$currency) $currency = $this->currency;
        $rank = UserRank::getForLvl(UserRank::getAmountLevel($this->getRankValue($currency), $currency), $currency);
        return $rank ?? new UserRank();
    }

    /**
     * Возвращает текущее значение ранга (сумму ставок)
     * @param int|null $currency
     * @return float
     */
    public function getRankValue(?int $currency = null): float
    {
        return $this->getWallet($currency)->getTotalBet();
    }

    /**
     * Возвращает процент прогресса текущего ранга
     * @param int|null $currency
     * @return int
     */
    public function getNextRankProgress(?int $currency = null): int
    {
        if (!$currency) $currency = $this->currency;
        return $this->getRank($currency)->getNextRankProgress($this->getRankValue($currency), $currency);
    }

    /**
     * Возвращает название ранга
     * @return string
     */
    public function getRankName(): string
    {
        return $this->getRank()->getRankName();
    }
```

## Достижения

Классы для работы с достижениями находятся в директории `App\Slc\Achievements`.

За достижение отвечает класс `SlcAchievement`. Тип достижения определяется полем `type` которое является перечислением `SlcAchievementType`.
Достижение может быть ограничено только для определенной валюты, для этого устанавливается поле `currency` с ид валюты. 
Используется когда для разных валют необходимы разные параметры. 
Поле `target_value` указывает на значение цели достижения.

`SlcAchievementData` интерфейс классов, предоставляющих достижениям данные о текущем значении достижения.  
Данные классы создаются фабрикой `AchievementDataFactory` на основе типа `SlcAchievement`.  
Привязка `AchievementDataFactory` к конкретной реализации `AchievementDataConcreteFactory` выполнена в `App\Providers\SlcDataProvider`.

Методы `SlcAchievementData`:
```php

interface SlcAchievementData
{
    /**
     * Возвращает дополнительные параметры необходимые достижению
     * ['param name' => 'field_name'] где field_name один из float_param/int_param/string_param
     * Используется в Crm
     * @return array
     */
    public function getRequiredParams(): array;

    /**
     * Возвращает текущее значение достижения
     * @param MUser $user
     * @return int
     */
    public function getCurrentValue(MUser $user): int;

    /**
     * Возвращает строку цели достижения
     * @param MUser $user
     * @return string
     */
    public function getGoalString(MUser $user): string;

    /**
     * Возвращает строку значение достижения
     * @param MUser $user
     * @return string
     */
    public function getValueHint(MUser $user): string;

    /**
     * Возвращает процент выполнения достижения
     * @param MUser $user
     * @return int
     */
    public function getCurrentPercent(MUser $user): int;
}
```

#### Награды за достижения

Награды за достижение определяются классом `SlcAchievementReward`, который реализует `SlcItem`. 
Реализация классом `SlcAchievement` интерфейса `SlcItem` является устаревшей и больше не требуется. 

Класс `SlcUserAchievement` отвечает за достижения, полученные пользователем. Поле `rewarded` указывает на то что вознаграждение за достижение получено.

### Обновление всех достижений

Для обновления всех достижений используется статически метод `SlcAchievement`:
```php
public static function updateAchievements(MUser $user): void
    {
        foreach (SlcAchievement::all() as $achievement) {
            $achievement->checkCompleted($user);
        }
    }
```
Проходя по всем достижениям, вызывает метод `checkCompleted`:
```php
    public function checkCompleted(MUser $m_user): bool
    {
        if ($this->isCompleted($m_user->getId())) return true;
        if ($this->getData()->getCurrentValue($m_user) >= $this->getTargetValue()) {
            $this->setCompleted($m_user);
            return true;
        }
        return false;
    }
```
Который если достижение не завершено, получает необходимый `SlcAchievementData`, сравнивает его значение с текущей целью 
и при успехе создает завершенное достижение методом `setCompleted` (который создает `SlcUserAchievement`). И возвращает завершено ли достижение.  
`SlcController` при отображении достижений в методе `achievements` вызывает этот метод перед отображением каждого достижения. 
А метод `achievementReward` в данном контроллере используется для выдачи наград и пометки `SlcUserAchievement` как получившего награду.

## Магазин

Магазин позволяет приобретать Slc итемы за Slc валюту, представлен классом `SlcShopItem`, реализует `SlcItem`.

Дополнительные поля:
 - `price` - цена.
 - `priority` - приоритет отображения в магазине.
 - `disabled` - товар отключен от продаж.

Отображает магазин метод `SlcController::shop`.  
При покупке вызывается метод `SlcController::buyItemModal` который отображает диалог покупки проверяя ранга для возможности совершения покупки.  
Обработка попупки происходит в методе `SlcController::buyItem`

### Обмен

Вкладка `обмен` магазина позволяет обменивать Slc ту на реальные деньги.

Итемы для обмена представленны классом `SlcCurrencyExchange`. Диалог обмена и обмен обрабатывается методом `SlcController::exchange`.  

`SlcController::exchange` проверяет доступный ли обмен по рангу пользователя, создает `PayoutTransaction::STATUS_SLC_EXCHANGE`
и сохраняет логи обмена в `SlcExchangeLog`.

## Slotmashine

`Slotmashine` - внутренняя простая игра в слоты на Slc валюту. Обрабатывается `SlcSlotMachineController` (`smModal` - окно игры, `spin` - обработка спина).
`View` игры находится в `views/common/blocks/slotmashine`.

Классы используемые `Slotmashine`:
 - `SlcSlotMachineCombination` - выигрышные комбинации. Метод `checkCombination` проверяет комбинацию и возвращает сумму выигрыша.
 - `SmSymbolType` - перечисление символов барабанов.
 - `SlcSlotMachineSymbol` - символы барабанов.  Метод `getForDrum` возвращает набор символов для барабана.
 - `SlcSlotMachineLog` - логи прокрутов.
 - `SlcConfig` - класс с настройками геймификации. Используется для получения/установки джекпота, получения типа рандом генератора и цены ставки в `Slc`.

### Spin

`SlcSlotMachineController::spin` получает необходимые параметры спина, проверяет доступный баланс, снимает необходимое значение `Slc` кошелька,
`SlcConfig::addJackpotBet` увеличивает значение джекпота от прокрута, генерирует установленным генератором значения барабанов,
с помощью `SlcSlotMachineCombination::checkCombination` получает сумму выигрыша или -1 при джекпоте, сохраняет лог и возвращает результат в формате:

```php
[
    "is_jackpot":false, // признак джекпота
    "0":5, // символ первого барабана
    "1":6, // символ второго барабана
    "2":7, // символ третьего барабана
    "win":0, // сумма выигрыша
    "balance":198, // баланс игрока
    "jackpot":"125.60", // сумма джекпота
    "bet":1 // сумма ставки
]
```

Если выигран джекпот устанавливается сумма выигрыша в размере джекпота и джекпот сбрасывается к его стартовому значению настраиваемому в `crm`:
```php
//jackpot
        if ($win === -1) {
            $win = floor($params->getJackpot());
            $params->setJackpot($params->getStartJackpot());
            $params->save();
            $result['is_jackpot'] = true;
            $wallet->add($win, 'SlotMachine win');
        }
```

### Random генератор

По умолчанию система использует для генерации стандартный `random_int` (`RANDOM_GEN_DEFAULT`).  
Так же система позволяет использовать встроенные в процессоры поколения `Ivy bridge` (инструкция `rd_rand`) генераторы случайных чисел.
Настраивается в `crm`, устанавливает значение `SlcConfig::random_gen` в `RANDOM_GEN_RDRAND`.

Для использования процессорного генератора случайных чисел подключается C-библиотека `librdrandget.so` (`C_libs/rdrandget/`) которая подключается через `php-ffi`. 

## Luckyspin

`Luckyspin` - колесо фортуны позволяющее получить один раз в день один из бонусов в результате вращения колеса.
Один раз в неделю бесплатный спин, дальше раз в день за установленную цену.

`Luckyspin` обрабатывается в `WheelController`.

Метод `WheelController::wheelModal` отображает окно колеса (`устройство/blocks/wheel-of-fortune`). Передает спин совершенный за сегодня и все бонусы (призы) доступные колесу.
Скрипты обработки колеса находятся в `js/common/wheel.js`.

Метод `WheelController::spin` обрабатывает прокрут колеса. Использует классы:
 - `SlcConfig` - класс с настройками геймификации. Используется получения цен прокрутов, и респинов.
 - `WheelSpinLog` - сохраняет результат спина колеса. Ид выигрыша сохраняется в `whee_prize_id`. Заполненное поле `bonus_id` указывает что бонус был получен.
 - `UserDailyStat` - клас сохраняет данные на одну сутку, очищается раз в сутку. Используется для сохранения информации о к-ве респинов сделанных за день (`wheel_respin`).
 - `UserWeeklyData` - класс сохраняет данные пользователя в течении недели. Ипользуется для определения был ли сделан спин на неделе (`wheel_spin`).
 - `FortuneWheelBonuses` - модель содержит бонусы доступные для колеса фортуны и определяет к-во секций колеса (настраивается в `crm`).

***Возвращает:***
```php
[
    'win_id' => $win_id, // ид приза
    'angle' => -1 * ($section_size * $win_id), // угол вращения
    'title' => __($prize->getBonusTitle()), // название приза (бонуса)
    'user_spins' => $user_free_spins, // бесплатные спины (не используются)
    'respin_cost' => $cost, //цена респина
    'can_respin' => ($cost <= $wallet->getBalance() && $respin < 2), // можно ли сделать респин
    'respins' => $respin, // к-во совершенных респинов
]
```

Метод `WheelController::takeBonus` выдает пользователю выигранный бонус устанавливая при этом поле `bonus_id` в `WheelSpinLog`.

## LostTreasures

`Lost treasures` - предоставляет возможность пользователю получать призы из сундуков с помощью ключей.

Доступны 6 типов ключей (перечисление `ChestKeyType`):
```php
    case KeyLittle = "key_little";
    case KeyMiddle = "key_middle";
    case KeyBig = "key_big";
    case KeyLost = "key_lost";
    case KeyBlack = "key_black";
    case KeyBermuda = "key_bermuda";
```

Каждому ключу соответсвует `SlcItem`:
```php
public function slcItemType(): SlcItemType
    {
        return match ($this) {
            self::KeyLittle => SlcItemType::KeyLittle,
            self::KeyMiddle => SlcItemType::KeyMiddle,
            self::KeyBig => SlcItemType::KeyBig,
            self::KeyLost => SlcItemType::KeyLost,
            self::KeyBermuda => SlcItemType::KeyBermuda,
            self::KeyBlack => SlcItemType::KeyBlack,
        };
    }
```

И тип сундука (`ChestType`):
```php
    public function getChestType(): ChestType
    {
        return match ($this) {
            self::KeyLittle => ChestType::LittleChest,
            self::KeyMiddle => ChestType::MiddleChest,
            self::KeyBig => ChestType::BigChest,
            self::KeyLost => ChestType::LostContainer,
            self::KeyBermuda => ChestType::BermudaChest,
            self::KeyBlack => ChestType::BlackBox,
        };
    }
```
Сундуки делятся на обычные и легендарные, деление сделано только для визуального разделения, разницы в механике работы между ними нету:
```php
/**
     * get common key types
     * @return ChestKeyType[]
     */
    public static function getCommon(): array
    {
        return [
            ChestKeyType::KeyLittle,
            ChestKeyType::KeyMiddle,
            ChestKeyType::KeyBig,
        ];
    }

    /**
     * get legendary key types
     * @return ChestKeyType[]
     */
    public static function getLegendary(): array
    {
        return [
            ChestKeyType::KeyBermuda,
            ChestKeyType::KeyLost,
            ChestKeyType::KeyBlack,
        ];
    }
```
Страница сундуков отображается из метода `SlcController::treasures`.

### Открытие сундука

Открытие сундука обрабатывается методом `SlcController::openChest`.

Итемы сундука описываются моделью `SlcChestItem`.  
Каждый `SlcChestItem` принадлежит одному типу сундука (`chest_type`) и одной группе призов (`group`). 
Имеет шанс выпадение в группе `chance` минимальное к-во итемов `amount` и может быть установлено максимальное значение если нужно случайное выпадение к-ва итемов.
`multiplier` устанавливает множитель к-ва итемов.

При открытии сундука итемы создаются следующим образом:
 - из каждой группы выбирается случайно один `SlcChestItem` на основе процента шанса выпадения итемов в группе. Шанс выпадения всех итемов группы не долен превышать 100.
 - если у итема установлено максимальное к-во, то случайным образом выбирается к-во от `amount` до `max_amount`
 - если у итема множитель `multiplier` > 1 то к-во умножается на множитель. 

Таким образом, например, если мы хотим установить выпадение 10, 20 или 30 ключей из одной группы с шансом 30 процентов, устанавливаем поля итема:
 - `group` = 1 (первая группа)
 - `amount` = 1
 - `max_amount` = 3
 - `multiplier` = 10
 - `chance` = 30

## Cashback+

`Cashback+` - игра, позволяющая получить увеличение еженедельного кэшбэка с помощью стрельбы ядрами по бочке.

За обработку игры отвечает `SlcCashbackController`. Для стрельбы используются два типа `SlcItem` - `Cannonball` черное ядро с щансом повреждения бочки 20% 
и `GoldCannonball` золотое ядро с шансом повреждения 100%.

Для отображения анимации используется LottiePlayer:
```html
    <script async src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
```
Json файлы анимаций находятся в `/img/cashback/anims/`.

Бочка находится в одном из трех состояний:
```php
    const BARREL_NEW = 0; // целая
    const BARREL_HALF_BROKEN = 1; // частично повреждена
    const BARREL_ALMOST_BROKEN = 2; // сильно повреждена
```

Для сохранения состояния бочки используются методы `MUser`:
```php
 public function setValue(string $key, mixed $value): bool
    {
        return UserValue::setValue($this->getId(), $key, $value);
    }

    public function getValue(string $key, mixed $default = null): mixed
    {
        return UserValue::getValue($this->getId(), $key, $default);
    }
```
Которые используют класс `UserValue` для сохранения разнообразных значение пользователя. 

При стрельбе в методе `shoot` в зависимости от ядра определяется успешно ли повреждена бочка (переменная `$success`), 
и если бочка находилась в состоянии `BARREL_ALMOST_BROKEN` и пользователю случайным образом выдается `ShipBarrelItem`.

`ShipBarrelItem` реализует `Slcitem`является наградой за стрельбу, при активации увеличивает прибавку пользователя к еженедельному кэшбэку.
В поле `amount` содержит процент который, добавляется к увеличению кэшбэка, а в поле `chance` шанс выпадения конкретного итема.
Сумма `chance` всех `ShipBarrelItem` в базе должна быть 100. Настраивается в `crm`.

В ответе стрельбы `shoot` возвращает следующие данные:
```php
$resp = [
            'success' => $success, // успешное повреждение
            'barrel1' => $states[$barrel_state], // состояние бочки после анимации
            'barrel2' => $animation, // анимация бочки
            'won_cashback' => false, // не используется
            'cashback' => 0, // не используется
            'won' => $won, // бочка разбита
            'win_text' => $win_text, // текст отображаемый при разбитии
            'win_image' => $win_image, // иконка соответствующего кешбэк+ при разбитии
            'black_balls_left' => $black_balls_left, // к-во оставшихся черных ядер
            'gold_balls_left' => $gold_balls_left, //  к-во оставшихся золотых ядер
            'state' => $barrel_state, // состояние бочки
        ];
```

## Задания

В системе доступны три типа заданий: ежедневный вход (фактически просто награды, а не задания), стартовый набор заданий и ежедневные задачи, 
которые генерируются случайным образом из настроек.

`SlcController::tasks` отображает страничку всех задач.

### Ежедневный вход

За ежедневный вход пользователь может получить награду. Каждый следующий день за авторизацию пользователь имеет возможность получить следующую награду, до 7 дней подряд.
После этого цикл начинается с первого дня. Если пользователь пропустил день, то активный день сбрасывается. 

Активный день сохраняется в значениях пользователя (`UserValue`) `active_day` и всегда указывает на день, за который будет выдана награда,
 дата последней полученной награды в `login_reward_date`.

Награды за вход хранятся в `SlcDailyReward`. Он реализует `SlcItem`, и для выдачи наград просто передается инвентарю.  
В `SlcLoginRewardsLog` сохраняются логи выдачи наград. При выдаче наград устанавливаются соответствующие `UserValue`:
```php
// получение наград
    if ($request->isMethod('POST')) {

        SlcLoginRewardsLog::add($user, $active_day);

        $user->setValue('login_reward_date', now()->format('Y-m-d'));
        if ($active_day > 6) {
            $active_day = 1;
        } else {
            $active_day++;
        }
        $user->setValue('active_day', $active_day);
        $inventory = SlcUserInventory::forUser($user);
        foreach ($rewards as $reward) {
            $inventory->addItem($reward, 'login-reward-'.$active_day);
        }
        return redirect()->route('slc.tasks');
    }
```

### Стартовые задания

После регистрации пользователю доступны стартовые задачи, после выполнения которых ему станут доступны ежедневные задачи.
За стартовые задачи отвечает модель `SlcTask`.

Типы стартовых задач определены в `SlcTaskType`. Функция 'SlcTaskType::getTaskChecker' возвращает `TaskChecker` для определенного типа задачи.
`TaskChecker` предоставляет функции для проверки выполнения стартовой задачи и информации о статусе выполнения и награде за задачу:

```php
interface TaskChecker
{
    /**
     * Проверяет завершена ли задача
     * @param MUser $user
     * @param SlcTask $task
     * @return bool task completed
     */
    public function checkComplete(MUser $user, SlcTask $task): bool;

    /**
     * Награда за задачу
     * @param SlcTask $task
     * @return SlcItem
     */
    public function getReward(SlcTask $task): SlcItem;

    /**
     * Текущее значение выполнения задачи
     * @param MUser $user
     * @param SlcTask $task
     * @return int
     */
    public function getCurrentValue(MUser $user, SlcTask $task): int;

    /**
     * Целевое значение задачи
     * @param SlcTask $task
     * @return int
     */
    public function getGoalValue(SlcTask $task): int;
}

```

`SlcCompletedTask` модель отвечающая за сохранение статуса стартовой задачи. 

***Статусы задачи (`SlcTaskStatus`) могут быть:***
```php
    case NotCompleted = -1; // не завершена
    case Completed = 0; // завершена
    case Rewarded = 1; // награда получена
```

Статический метод `Slctask::checkAllTasks` проверяет все задачи на завершение. 
При этом вызывая `TaskChecker::checkComplete`, который, если задача выполнена, устанавливает `SlcCompletedTask` в выполненную. 
```php
SlcCompletedTask::setTaskStatus($user->getId(), $task->getType(), SlcTaskStatus::Completed);
```
После чего, пользователь вызывает диалоговое окно награды и может ее получить в `taskRewardModal::taskRewardModal`. 

### Ежедневные задания

После завершения стартовых задач пользователь получает доступ к ежедневным задачам. 
Проверить завершение стартовых задач можно проверим завершена ли задача `SlcTaskType::FirstSteps` - которая является заданием завершить все стартовые задания.
```php
// разрешены ли ежедневные задачи
$is_daily_allowed = SlcCompletedTask::getTaskStatus($user->getId(), SlcTaskType::FirstSteps) === SlcTaskStatus::Rewarded;
```

Каждый день при первом обращении к ежедневным задачам, пользователю генерируются задачи на день из настроенных в `crm`.

Получиться задачи можно из статического метода `UserDailyTask::getTodaysTasks`. 
Данный метод делает выборку сегодняшних задач в `UserDailyTask`, а если задач нет то генерирует их и затем возвращет.

При генерации случайно выбирается одна из групп параметров задач из которой выбираются задачи на сегодня:
```php
$group_id = UserTodaysTask::getTodaysGroup($user)->getId();
$params = UserTaskParams::with('games')->where('group_id', $group_id)
    ->orderBy('priority', 'asc')->get();
```
`UserTaskParams` класс содержащий настройки для генерации ежедневной задачи. 
С помощью данного класса создается ежедневная задача `UserDailyTask` где все изменяемые параметры генерируются из `gen*` функций `UserTaskParams`:
```php
    $task = new UserDailyTask();
    $task->setGroupId($group_id);
    $task->setUserId($user->getId());
    $task->setType($task_params->getType());
    $task->setCurrency($task_params->getCurrency());
    $task->setTargetValue($task_params->genTargetValue());
    $task->setIntParam($task_params->genIntParam());
    $task->setFloatParam($task_params->genFloatParam());
    $task->setStringParam($task_params->getStringParam());
    if ($task_params->games->count() > 0) {
        $task->setStringParam($task_params->games->shuffle()->first()->getCid());
    }
    $task->save();
```
Например `genTargetValue` генерирует случайно значение цели задачи в диапазоне от `target_value` до `target_value_to` с шагом `target_value_step`.

`UserTaskParams` содержит награды за выполнение задачи `UserTaskReward`, на основе которых создаются награды за сегодняшние задачи `UserTodaysReward`.
 К-во наград генерируется по тому же принципу.

В результате генерации получаем список `UserDailyTask` - задачи на сегодня с наградами в `UserTodaysReward`.

`UserDailyTask::checkStatus` выполняет проверку задачи на завершение, используется в `Slctask::checkAllTasks` при проверке выполнения всех задач.

Для работы с интерфейсом `UserDailyTask::getTask` возвращает реализацию `DailyTask` для каждого типа задачи.

```php
interface DailyTask
{
    /**
     * Возвращает строку задачи задания
     * @return string
     */
    public function getGoal(): string;

    /**
     * Проверяет задачу на выполнения
     * @return bool возвращает завершена ли задача.
     */
    public function checkTask(): bool;

    /**
     * Возвращает значение цели задачи
     * @return int
     */
    public function getGoalValue(): int;

    /**
     * Возвращает текущее значение выполнения задачи
     * @return int
     */
    public function getCurrentValue(): int;

    /**
     * Рендерит елемент задачи для интерфейса
     * @return string
     */
    public function renderTaskItem(): string;
```

---

# Турниры

Система предоставляет возможность создавать рекламные промо турниров игровых провайдеров, а так же свои турниры с разнообразными призами.

Для работы со всеми типами турниров используется `TournamentController`.
Классы для работы с турнирами находятся в `App\Models\Tournaments`.

`TournamentController::index` выводит страничку с турнирами. Информацию о доступных промо-турнирах `view` `index` берет прямо во времья генерации (`CustomTournament::getAvailableTournaments()`)

`TournamentController::info` выводит страницу с информацией о турнире (как кастом (не используются), так и своих).
## Свои турниры

Обработкой своих турниров занимается сервис `ktournaments`. 
Связь с сервисом обрабатывается через `App\Services\Tournaments\TournamentsService`, который использует `App\Services\NatsService\NatsService` для обмена данными.

Для включения работы с сервисом необходимо прописать настройки в `.env` файле:
```dotenv
NATS_TOKEN="токен сервиса"
TOURNAMENTS_ENABLED=true
```

Данные турниры представлены классом `GoTournament`. `GoTournament` создается на основе массива данных полученных из сервиса.
Все методы `TournamentsService` по работе с турнирами возвращают `GoTournament`.
***Пример данных:***
```php
array:6 [
  "model" => array:24 [
    "id" => 3
    "type" => 1
    "name" => "Pending tournament test"
    "status" => 2
    "start_time" => "2025-04-14T17:05:00+03:00"
    "end_time" => "2025-04-17T11:29:00+03:00"
    "game_type" => 2
    "game_list" => "{"sleg":["ce54k534bfd819daesw675c82ef3d3cf05y10i74","96f67ad0de49d50ab726098edcc43b4029ffb79f","d964k83d960a24755swa9378c1c555e796yb0i19","22405fe5986e497d9454b573711e15562d1d73d6","65f9k73a73d8264a0swab568b77c0a4e02ydbi0e","cc8333e92fa43c2cef6c64a20f847dbb864220db","ed02kf35acb96310esw88028ce095f2b39y76i0b","8d4c6a5f148d0fdd8e63f5a15ff544922bc830c3","3263k13b479f1d4fesw97c88daff9b3602ybfi41","70a5469fcf572ee45fdaa16e48d4e4e4b940298d","b45cka36f359657c9sw09338c35b2c92a2y60i15","9719b57a51756c918dc1820951a4b28069886c3a"]}"
    "params" => "{"min_bets":{"RUB":"10","UAH":"1","USD":"1","EUR":"1","INR":"10","USDT":"1","KZT":"100"}}"
    "tournament_bonus_group_id" => 10
    "created_at" => "2025-04-14T16:34:07+03:00"
    "updated_at" => "2025-04-17T11:29:02+03:00"
    "currency" => 0
    "is_test" => true
    "tile_img" => "/storage/user_img/tournaments/mobile.webp"
    "mobile_img" => "/storage/user_img/tournaments/title.webp"
    "desktop_img" => "/storage/user_img/tournaments/wide.webp"
    "cabinet_d_img" => "/storage/user_img/tournaments/info-banner_desktop.png"
    "cabinet_m_img" => "/storage/user_img/tournaments/info-banner_desktop.png"
    "req_confirm" => false
    "conditions" => "{"en":{"descr":"<h2>And now more text<\/h2><p>&nbsp;<\/p><p><span style=\"color:hsl(0, 75%, 60%);\">Bla bla bla<\/span><\/p><p>This tournament should be next<\/p>"},"ru":{"descr":null},"ua":{"descr":null},"hi":{"descr":null},"kz":{"descr":null},"de":{"descr":null},"fr":{"descr":null},"it":{"descr":null},"pt":{"descr":null},"es":{"descr":null},"sk":{"descr":null}}"
    "aggr_req" => false
    "prize_fund" => ""
    "prize_page" => ""
  ]
  "games_limited" => true
  "min_bets" => array:7 [
    "EUR" => 1
    "INR" => 10
    "KZT" => 100
    "RUB" => 10
    "UAH" => 1
    "USD" => 1
    "USDT" => 1
  ]
  "prize_places" => 3
  "prize_bank" => array:7 [
    "EUR" => 200
    "INR" => 15000
    "KZT" => 215000
    "RUB" => 16000
    "UAH" => 7500
    "USD" => 200
    "USDT" => 1150
  ]
  "conditions" => array:11 [
    "de" => ""
    "en" => "<h2>And now more text</h2><p>&nbsp;</p><p><span style="color:hsl(0, 75%, 60%);">Bla bla bla</span></p><p>This tournament should be next</p>"
    "es" => ""
    "fr" => ""
    "hi" => ""
    "it" => ""
    "kz" => ""
    "pt" => ""
    "ru" => ""
    "sk" => ""
    "ua" => ""
  ]
]
```

Турниры могут быть созданы как для конкретной валюты, так и для всех валют. 
При этом информация о призах, и описание турниров создаются и отображаются отдельно для каждой валюты и локали.
Для отображения `SlcItem` наград используется `TournamentSlcItem`.

Турниры имеют 4 возможные игровые механики, которые настраиваются `crm`:
```php
    case Multiplier = 1;
    case BetX = 2;
    case WinX = 3;
    case WinK = 4;
```
- `Multiplier` начисляет очки по соотношению выигрыша к ставке.
- `BetX` начисляет очки за каждые n суммы ставки (ставка 100, в параметрах для валюты шаг установлен 10 = 10 очков)
- `WinX` начисляет очки за каждые n суммы выигрыша (выигрыш 100, в параметрах для валюты шаг установлен 10 = 10 очков)
- `WinK` начисляет очки за каждый выигрыш в зависимости от настройки градации очков.

Турниры могут требовать от пользователя согласие на участие в турнире. 
В таких турнирах очки будут начисляться только после того как пользователь принял согласие на участие (`TournamentController::participate`).

Для игр в десктопном режиме во время игры в играх турнире отображается таблица лидеров турнира и позиции пользователя в нем (`desktop/tournaments/tournament-table.blade`).
Таблица обновляется в реальном времени, использует `TournamentController::gameTableData` для получения данных.

### Завершение турнира

После завершения турнир переходит в статус `STATUS_COMPLETED`. Призы за турнир выдаются на стороне сайта.

Обработка наград завершенного турнира происходит в команде `AwardTournamentPrizes`.
Она получает список завершенных турниров:
```php
    $s = new TournamentsService();
    if (!$s->tournamentsEnabled()) return Command::SUCCESS;

    // pending award tournaments
    $pa_tournament = $s->getPendingAwardTournaments();
```
Далее устанавливает турнир в статус `STATUS_DONE` и выдает награды.

Награды турнира выдаются в виде `SlcItem` `AwardTournamentPrizes`. Которая может содержать как сумму реальных денежный средств полученных за призовое место,
так и `SlcItem`.

## Промо турниры

Для создания рекламных акций турниров игровых провайдеров используются `TournamentPromo` и `CustomTorunament` (больше не используются).
Они реализуют интерфейс `TournamentInterface` для отображения информации о турнире.

`TournamentPromo` настраивается в `crm`, и содержит всю необходимую информацию для отображения промоакции турнира.
Он сохраняет необходимые картинки и так же `html` сраницы описания турнира и страницы призов.

`TournamentPromo` в `infoUrl` указывает маршрут к `TournamentController::promoInfo` которые отображает страницу с информацией о промоакции турнира.

`TournamentPromo` может находиться в тестовом режиме, тогда он будет отображаться только для тестеров.

Промо турниры добавляются к списку всех `CustomTournament`:
```php
    self::$active_tournaments = TournamentPromo::getAvailableTournaments()->all();
```

# Контроллеры

## Controller
Базовый клас для контроллеров. Устанавливает метатэги и генерирует необходимый `view`  зависимости от используемого устройства.

## Live\IndexController
`TV-Bet` контроллер. Обеспечивает запуск лайв игр, обмен данными с `TV-Bet`.

## AuthController
Обеспечивает авторизацию, регистрацию, регистрацию из прелендинг сайтов (колесо и т.п.) 

## BetProductionController
`Sport` контроллер. Обеспечивает запуск и обмен данными для игр BetProduction.

## BonusesController
Отображает информацию о бонусах, бонусную строку в меню пользователя, и некоторые бонусные попапы.

## BonusPromocodesController
Активация промокодов, активация промокодов из crm.

## BonusStatusController
Передает информацию о бонусе во время игры для бонусной плашки.

## CabinetController
Отвечает за отображение информации в кабинете пользователя, а также некоторые операции в нем (смена логина, пароля, отмена выплаты, самоблокировка)

## ContactsVerificationController
Отвечает за верификацию данных пользователя (email, phone) отправляет коды для подтверждения, а так же обрабатывает и передает для верификации персональные данные пользователя из раздела `Настройки:Личные данные` персонального кабинета (паспорт, фото...).

## EmulatorController
Отвечает за запуск игр и закрытие игровой сессии.

## FooterController
Отображает футер страницы, созданные в crm

## GamesController
Отвечает за поиск игр (игры по тэгам, игры для лендинг групп, бонусные игры).

## GetBonusController
Активация, сброс, отмена бонусов. Выдача бонусов (`BonusSchedule`), бонусы дня рождения, модалки велкам бонуса.

## HallOfFameController
Отвечает за отображение зала славы

## IndexController
Главная страница. Формы логина и регистрации. Авторизация соц сетей (не используется). Помечает необходимость отображение попапов для только-что зарегестрированных или залогиневшихся пользователей.  
Так же обеспечивает обработку маршрута для отображение окна депозита при редиректе (`/deposit`)

## NotificationsController
Контроллер сообщений нотификации (не используется).

## PayController
Контроллер платежей. Депозит, выплата, обработка автовыплаты, некоторые старые методы колбэков платежных систем.

## PopupsController
Котроллер попапов. Отвечает за отображение попапов из модели `UserPopup`. оверяет наличие новых попапов для пользователя, а также отображает попапы после регистрации.

## PromoController
Контроллер промо-акций (меню `Promo`).

## RanksController
Обрабатывает получение награды за ранг пользователя.

## SimpleCaptchaController
Генерация простой картинки капчи с сохранением кода в сессию.

## SlcCashbackController
Обработка игры стрельбы ядрами для получения `cashback+`. Отображает модалку с игрой. Обрабатывает выстрел из пушки.

## SlcController
Котроллер slc итемов (геймификации). Отвечает за обработку и отображение заданий пользователя, магазина итемов, инвентаря пользователя, достижений, сундуков (Lost treasured), информации о итемах, активации итемов, описания соответствующих страниц.

## SlcSlotMachineController
Колнтроллер Slotmashine. Обрабатывает игру слотмашины.

## SubscribeController
Обрабатывает подписку/отписку от email рассылок

## TournamentController
Контроллер турниров. Отвечает за отображение информации о внутренних турнирах (через сервис ktournaments) а также промо турниров игровых операторов. Обрабатывает принятие участия в турнирах (которые этого требуют) а так же обновление таблицы лидеров турнира в играх.

## TVBetController
Контроллер запуска TV-bet игр.

## WalletController
Контроллер управления кошельками пользователя. Обрабатывает переключение кошельков (валют), Добавление/удаление кошелька (активация/деактивация кошельки физически не удаляются).  
Так же отображение окна депозита/выплаты, получение реквизитов депозита/выплаты и получение балансов пользователя.

## WheelController
Обрабатывает механики Колеса Фортуны (`Lucky Spin`). Отображение, вращение, получение приза.

---
# Middleware

## BlockWebHostsMiddlware
Отключает работу сайта на хостах предназначенных для обработки платежей.

## CheckBlock
Проверяет не заблокирован ли пользователь, уведомляет о причине блокировки. (Блокируется в crm) 

## CheckIpBlock
Проверяет блокировку ip адреса (сервис kipinfo)

## CheckPartnerLink
Отправляет в партнерскую систему уведомление о первом посещении сайта пользователем (`visitOnline`). Если в адресе присутствует промокод - сохраняет в сессию для последующей обработки.

## CheckToken
Проверка токена пользователя. Для блокировки двойного входа в систему.

## ForceJsonResponse
Установка json заголовков для ответа (сомнительная необходимость, возможно нужно удалить)

## ForceNoCache
Устанавливает заголовки запрета кеширования.

## ResolveLocale
Определение локали пользователя. Если локаль в маршруте не установлена для `GET` запросов делает редирект на тот же маршрут с определенной локалью.
Если локаль в маршруте не установлена, локаль определяется по заголовкам браузера, если их нет то по данным из ip адреса. 
Обновляет локаль у пользователя. 

## ReturnMoney
Закрывает игровую сессию.

## UserReferralsMiddleware
Определяет реферальную ссылку для пользователя (не путать с партнерскими ссылками). Для реферальной системы пользователей.

## UsersOrigin
Сохраняет информацию об исходном сайте с которого пришёл пользователь.

