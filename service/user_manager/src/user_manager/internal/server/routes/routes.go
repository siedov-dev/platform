package routes

import (
	"user-manager/internal/gamesystems/qt"
	"user-manager/internal/server/handlers"
	currencycontroller "user-manager/internal/server/handlers/currency_controller"
	eventscontroller "user-manager/internal/server/handlers/events_controller"
	userscontroller "user-manager/internal/server/handlers/users_controller"
	walletcontroller "user-manager/internal/server/handlers/wallet_controller"

	"github.com/go-chi/chi/v5"
)

// SetRoutes регистрирует все маршруты HTTP API для заданного экземпляра маршрутизатора `chi.Mux`.
// Маршруты включают обработку запросов для проверки состояния сервиса, управления пользователями, балансами,
// валютами, бонусами, игровыми сессиями, событиями и другими API, связанными с функциональностью сервера.
// Каждому маршруту назначается соответствующий обработчик для управления конкретной логикой приложения.
func SetRoutes(r *chi.Mux) {
	// Проверка работоспособности сервиса
	r.Get("/", handlers.OkHanlder)
	// Ответ "pong"
	r.Get("/ping", handlers.Ping)

	// Только для тестов (нет проверки подписи)
	// r.Get("/user/{user_id}/balances", walletcontroller.Balances)
	// r.Get("/user/{user_id}/balance/{curr}", walletcontroller.Balance)
	// r.Get("/user/{user_id}/add/{curr}/{val}", walletcontroller.AddMoney)
	// r.Get("/user/{user_id}/rem/{curr}/{val}", walletcontroller.RemMoney)
	////

	// Получение информации о текущем статусе бонуса
	r.Get("/user/{user_id}/bonusStatus", handlers.BonusStatus)

	// Получение имени валюты по ид валюты
	r.Get("/currency/{curr}/name", currencycontroller.GetCurrencyName)
	// Получение игровой валюты для ид валюты
	r.Get("/currency/{curr}/gameCurrency", currencycontroller.GetGameCurrencyName)
	// Получение международного кода валюты для ид валюты
	r.Get("/currency/{curr}/intCode", currencycontroller.GetCurrencyIntCode)
	// Получение хола валюты для ид валюты
	r.Get("/currency/{curr}/hallId", currencycontroller.GetCurrencyHallID)
	// Получение максивального значние автовыплаты для ид валюты
	r.Get("/currency/{curr}/maxAutopayout", currencycontroller.GetCurrencyMaxAutopayout)

	// Состояние баланса пользователя по всем кошелькам
	r.Post("/balances", walletcontroller.JsonBalances)
	// Баланс пользователя по ид валюты
	r.Post("/balance", walletcontroller.JsonBalance)
	// Увеличение баланса кошелька
	r.Post("/balance/add", walletcontroller.JsonBalanceAdd)
	// Снятие средств с баланса кошелька
	r.Post("/balance/rem", walletcontroller.JsonBalanceRem)

	// Получение суммы заблокированного для отыгрыша баланса кошелька
	r.Post("/balance/locked", walletcontroller.BalanceLocked)
	// Увеличение суммы заблокированного для отыгрыша баланса кошелька
	r.Post("/balance/locked/add", walletcontroller.BalanceLockedAdd)
	// Уменьшение суммы заблокированного для отыгрыша баланса кошелька
	r.Post("/balance/locked/rem", walletcontroller.BalanceLockedRem)
	// Установка суммы заблокированного для отыгрыша баланса кошелька
	r.Post("/balance/locked/set", walletcontroller.BalanceLockedSet)

	// Получение накопительного баланса кошелька
	r.Post("/balance/bonus", walletcontroller.BalanceBonus)
	// Увеличение накопительного баланса кошелька
	r.Post("/balance/bonus/add", walletcontroller.BalanceBonusAdd)
	// Уменьшение накопительного баланса кошелька
	r.Post("/balance/bonus/rem", walletcontroller.BalanceBonusRem)
	// Установка накопительного баланса кошелька
	r.Post("/balance/bonus/set", walletcontroller.BalanceBonusSet)

	// Получение реферального баланса кошелька
	r.Post("/balance/referral", walletcontroller.BalanceReferral)
	// Увеличение реферального баланса кошелька
	r.Post("/balance/referral/add", walletcontroller.BalanceReferralAdd)
	// Уменьшение реферального баланса кошелька
	r.Post("/balance/referral/rem", walletcontroller.BalanceReferralRem)
	// Установка реферального баланса кошелька
	r.Post("/balance/referral/set", walletcontroller.BalanceReferralSet)
	// Перевод реферальных средств на основной баланс кошелька
	r.Post("/balance/referral/transfer", walletcontroller.BalanceReferralTransfer)

	// Активация/деактивация кошелька
	r.Post("/wallet/active", walletcontroller.SetActive)
	// Получение ид бонуса, модифицирующего отображение баланса кошелька
	r.Post("/wallet/getBonusId", walletcontroller.GetBonusId)
	// Установка ид бонуса, модифицирующего отображение баланса кошелька
	r.Post("/wallet/setBonusId", walletcontroller.SetBonusId)

	// Установка TotalBet
	r.Post("/wallet/setTotalBet", walletcontroller.SetTotalBet)
	// Получение TotalBet
	r.Post("/wallet/getTotalBet", walletcontroller.GetTotalBet)
	// Установка TotalWin
	r.Post("/wallet/setTotalWin", walletcontroller.SetTotalWin)
	// Получение TotalWin
	r.Post("/wallet/getTotalWin", walletcontroller.GetTotalWin)

	// Отправка события юзера
	r.Post("/events/add", eventscontroller.EventsAdd)
	// Получение событий юзера
	r.Post("/events/pull", eventscontroller.EventsPull)

	// Список онлайн юзеров
	r.Post("/users", userscontroller.UsersOnline)

	// Запуск игровой сесии
	r.Post("/startGame", handlers.StartGame)
	// Получение текущей игровой сессии
	r.Get("/getGame/{user_id}", handlers.GetGame)
	// Закрытие текущей игровой сессии
	r.Post("/closeGame", handlers.CloseGame)

	// Маршруты для ендпойнтов системы Slotegrator
	r.Get("/api/slotegcb", handlers.Sleg)
	r.Post("/api/slotegcb", handlers.Sleg)
	r.Get("/slotegcb", handlers.Sleg)
	r.Post("/slotegcb", handlers.Sleg)

	// Маршруты для ендпойнтов системы PragmaticPlay
	r.Post("/api/pplay/token", handlers.PPlaySetAuthToken)
	r.Post("/pplay/token", handlers.PPlaySetAuthToken)
	r.Post("/api/pragplay/{method}", handlers.PPlay)
	r.Post("/pragplay/{method}", handlers.PPlay)
	r.Post("/api/pragplay/session/expired", handlers.PPlaySessionExpired)
	r.Post("/pragplay/session/expired", handlers.PPlaySessionExpired)

	// Маршруты для ендпойнотов системы QT
	r.Get("/api/qt/accounts/{player_id}/session", qt.VerifySession)
	r.Get("/qt/accounts/{player_id}/session", qt.VerifySession)
	r.Get("/api/qt/accounts/{player_id}/balance", qt.Balance)
	r.Get("/qt/accounts/{player_id}/balance", qt.Balance)
	r.Post("/api/qt/transactions", qt.Transaction)
	r.Post("/qt/transactions", qt.Transaction)
	r.Post("/api/qt/transactions/rollback", qt.Rollback)
	r.Post("/qt/transactions/rollback", qt.Rollback)
	r.Post("/api/qt/promotion", qt.PromotionStatus)
	r.Post("/qt/promotion", qt.PromotionStatus)
	r.Post("/api/qt/bonus/rewards", qt.Rewards)
	r.Post("/qt/bonus/rewards", qt.Rewards)

	// Маршруты для ендпойнтов системы B2BSlots
	r.Post("/b2bslots", handlers.B2BSlotsHandler)
}
