package pragplay

import (
	"crypto/md5"
	"database/sql"
	"encoding/hex"
	"fmt"
	"log/slog"
	"net/http"
	"regexp"
	"sort"
	"strconv"
	"strings"
	"time"
	"user-manager/internal/config"
	"user-manager/internal/db/models"
	pplayresponses "user-manager/internal/gamesystems/pragplay/pplay_responses"
	"user-manager/internal/logger"
	"user-manager/internal/msgbroker"
	"user-manager/internal/service"
	"user-manager/internal/service/container"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"

	"github.com/go-chi/render"
	"github.com/google/uuid"
)

type PPlayEndpoint struct {
	Login string
	Key   string
}

// Make - создаёт новый эндпоинт Pragplay
func Make() PPlayEndpoint {
	return PPlayEndpoint{
		Login: config.Get().PragPlay.Login,
		Key:   config.Get().PragPlay.Key,
	}
}

// Authenticate - аутентифицирует пользователя
func (pp *PPlayEndpoint) Authenticate(w http.ResponseWriter, r *http.Request) {
	log := logger.Get()
	// поиск токена аутентификации
	token := r.PostFormValue("token")
	// получение ID пользователя по токену
	user_id, ok := service.PPlayAuthTokens.Get(token)
	// если токен не найден
	if !ok {
		log.Error("pplay", slog.String("token not found", token))
		// отправка ошибки
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(4, "Token not found"))
		return
	}
	// получение пользователя по ID
	user, err := service.GetUser(user_id)
	// если пользователь не найден
	if err != nil {
		log.Error("sleg", slog.String("user not found", fmt.Sprintf("%v", user_id)))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(4, "User not found"))
		return
	}
	// получение игровой сессии
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		log.Error("sleg", slog.String("user not found", fmt.Sprintf("%v", user_id)))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(4, "Game session not found"))
		return
	}
	var bonus_balance float64 = 0.00

	// получение валюты игры (USDT = USD)
	var game_currency string
	curr_info, ok := service.Currencies.Find(func(v models.UserCurrency) bool { return v.ID == int64(gs.GameStartData.CurrencyID) })
	if !ok {
		logger.Get().Error("currency not found", slog.Int("currency", int(gs.GameStartData.CurrencyID)))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "currency not found"))
		return
	}
	if curr_info.GameCurrency.Valid {
		game_currency = curr_info.GameCurrency.String
	} else {
		game_currency = curr_info.Name
	}

	// создание ID игрока (['bonus']user_id[currency])
	var player_id string
	if b, err := gs.GetActiveBonus(); err == nil && b != nil {
		logger.Get().Info("pplay bonus auth:", "bonus:", b.GetID())
		player_id = fmt.Sprintf("%s%d%s", "bonus", user_id, gs.Stats.Currency)
		bonus_balance = b.GetBalance()
		render.JSON(w, r, pplayresponses.MakeAuthResponse(player_id, game_currency, 0, bonus_balance, *gs.Stats.SessionID))
		return
	}

	player_id = fmt.Sprintf("%d%s", user_id, gs.Stats.Currency)

	// получение кошелька пользователя
	wallet, err := user.GetWallet(gs.GameStartData.CurrencyID)
	if err != nil {
		logger.Get().Error("wallet not found", slog.Int("currency", int(gs.GameStartData.CurrencyID)))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wallet not found"))
		return
	}

	logger.Get().Info("pplay auth", "user", player_id)

	render.JSON(w, r, pplayresponses.MakeAuthResponse(player_id, game_currency, wallet.GetBalance(), 0, *gs.Stats.SessionID))
}

// Balance - получение баланса
func (pp *PPlayEndpoint) Balance(w http.ResponseWriter, r *http.Request) {
	log := logger.Get()
	// получение ID игрока
	player_id := r.PostFormValue("userId")
	// проверка, является ли игра бонусной
	isBonusGame := false
	// ID пользователя
	var UserId string
	// валюта
	var currencyName string
	// баланс бонуса
	var bonus_balance float64 = 0.00

	// регулярное выражение для поиска ID игрока
	re := regexp.MustCompile(`^(bonus)?([0-9]+)([a-zA-Z]{3,5})?$`)
	// определение ID игрока
	match := re.FindStringSubmatch(player_id)
	if match != nil {
		isBonusGame = match[1] != ""
		UserId = match[2]
		if match[3] != "" {
			currencyName = match[3]
		}
	}

	// если валюта USD, то преобразуем в USDT
	if currencyName == "USD" {
		currencyName = "USDT"
	}

	// преобразование ID пользователя в число
	user_id, err := strconv.ParseInt(UserId, 10, 64)
	if err != nil {
		log.Error("sleg", slog.String("cannot convert user id", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "cannot convert user id"))
		return
	}

	// получение пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("sleg", slog.String("user not found", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(2, "user not found"))
		return
	}

	// получение игровой сессии
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		log.Error("sleg", slog.String("no game session", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no game session"))
		return
	}

	// если валюта не указана, то используем валюту игры
	if currencyName == "" {
		currencyName = gs.Stats.Currency
	}

	// получение ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("sleg", slog.String("wrong currency name", currencyName))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrong currency name"))
		return
	}

	// получение кошелька пользователя
	wallet, err := user.GetWallet(curr)
	if err != nil {
		logger.Get().Error("wallet not found", slog.Int("currency", int(curr)))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wallet not found"))
		return
	}

	// если игра бонусная
	if isBonusGame {
		// если активный бонус не найден - ошибка
		b, err := gs.GetActiveBonus()
		if err != nil || b == nil {
			logger.Get().Error("pplay balance no active bonus")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no active bonus"))
			return
		}
		// получение баланса бонуса
		bonus_balance = b.GetBalance()
	}

	// получение валюты игры (USDT = USD)
	var game_currency string
	curr_info, ok := service.Currencies.Find(func(v models.UserCurrency) bool { return v.ID == int64(curr) })
	if !ok {
		logger.Get().Error("currency not found", slog.Int("currency", int(gs.GameStartData.CurrencyID)))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "currency not found"))
		return
	}
	if curr_info.GameCurrency.Valid {
		game_currency = curr_info.GameCurrency.String
	} else {
		game_currency = curr_info.Name
	}

	// отправка ответа
	if bonus_balance > 0 {
		// если баланс бонуса больше 0, то отправляем только баланс бонуса
		// иначе прагматик их обьединяет
		render.JSON(w, r, pplayresponses.MakePPlayResponse(game_currency, 0, bonus_balance))
	} else {
		render.JSON(w, r, pplayresponses.MakePPlayResponse(game_currency, wallet.GetBalance(), bonus_balance))
	}
}

// Bet - ставка
func (pp *PPlayEndpoint) Bet(w http.ResponseWriter, r *http.Request) {
	log := logger.Get()
	// получение ID игрока
	player_id := r.PostFormValue("userId")
	// проверка, является ли игра бонусной
	isBonusGame := false
	// ID пользователя
	var UserId string
	// валюта
	var currencyName string
	// баланс бонуса
	var bonus_balance, bonus_bet float64
	// ID бонуса
	var bonus_id sql.NullInt64
	// баланс
	var balance float64
	// регулярное выражение для поиска ID игрока
	re := regexp.MustCompile(`^(bonus)?([0-9]+)([a-zA-Z]{3,5})?$`)
	// определение ID игрока
	match := re.FindStringSubmatch(player_id)
	if match != nil {
		isBonusGame = match[1] != ""
		UserId = match[2]
		if match[3] != "" {
			currencyName = match[3]
		}
	}
	// если валюта USD, то преобразуем в USDT
	if currencyName == "USD" {
		currencyName = "USDT"
	}
	// преобразование ID пользователя в число
	user_id, err := strconv.ParseInt(UserId, 10, 64)
	if err != nil {
		log.Error("sleg", slog.String("cannot convert user id", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "cannot convert user id"))
		return
	}
	// получение пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("sleg", slog.String("user not found", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(2, "user not found"))
		return
	}
	// получение игровой сессии
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		log.Error("sleg", slog.String("no game session", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no game session"))
		return
	}
	// если валюта не указана, то используем валюту игры
	if currencyName == "" {
		currencyName = gs.Stats.Currency
	}
	// получение ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("sleg", slog.String("wrogn currency name", currencyName))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrogn currency name"))
		return
	}
	// получение кошелька пользователя
	wallet, err := user.GetWallet(curr)
	if err != nil {
		logger.Get().Error("wallet not found", slog.Int("currency", int(curr)))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wallet not found"))
		return
	}
	// если игра бонусная
	if isBonusGame {
		// если активный бонус не найден - ошибка
		b, err := gs.GetActiveBonus()
		if err != nil || b == nil {
			logger.Get().Error("pplay balance no active bonus")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no active bonus"))
			return
		}
		// получение баланса бонуса
		bonus_balance = b.GetBalance()
	}

	// получение валюты игры (USDT = USD)
	var game_currency string
	curr_info, ok := service.Currencies.Find(func(v models.UserCurrency) bool { return v.ID == int64(curr) })
	if !ok {
		logger.Get().Error("currency not found", slog.Int("currency", int(gs.GameStartData.CurrencyID)))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "currency not found"))
		return
	}
	if curr_info.GameCurrency.Valid {
		game_currency = curr_info.GameCurrency.String
	} else {
		game_currency = curr_info.Name
	}
	////
	// получение суммы ставки
	a_p := r.PostFormValue("amount")
	amount, err := strconv.ParseFloat(a_p, 64)
	if err != nil {
		logger.Get().Error("pplay bat amount parse error : " + err.Error())
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, err.Error()))
		return
	}
	if amount < 0 {
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrong amount value"))
		return
	}
	// проверка, была ли уже обработана транзакция
	ex_trans, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool { return v.TransactionID == r.PostFormValue("reference") })
	if found {
		render.PlainText(w, r, ex_trans.RespString)
		return
	}
	// если бонусный код указан
	if r.PostFormValue("bonusCode") != "" {
		// если игра не бонусная - ошибка
		if !isBonusGame {
			logger.Get().Error("pplay bet no active bonus")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no active bonus"))
			return
		}
	}
	// если игра бонусная
	if isBonusGame {
		// получение активного бонуса
		bonus, err := gs.GetActiveBonus()
		// если бонус не найден - ошибка
		if err != nil || bonus == nil {
			logger.Get().Error("pplay bet no active bonus")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no active bonus"))
			return
		}
		// установка ID бонуса
		bonus_id.Int64 = int64(bonus.GetID())
		bonus_id.Valid = true
		// если бонус не активен - ошибка
		if !bonus.IsActive() {
			logger.Get().Error("bonus is not active")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "bonus is not active"))
			return
		}
		// проверка, была ли уже обработана транзакция
		_, found := user.GetLastRefundsTransactions().Find(func(v *models.GameTransaction) bool {
			return v.BetTransactionId.String == r.PostFormValue("reference")
		})
		if found {
			gen_id := sql.NullString{}
			gen_id.String = uuid.New().String()
			gen_id.Valid = true

			date := time.Now()

			transaction := models.GameTransaction{
				GenID:         gen_id,
				UserId:        user.GetID(),
				Action:        "bet",
				Amount:        amount,
				Currency:      wallet.GetCurrencyName(),
				GameID:        r.PostFormValue("gameId"),
				SessionID:     r.PostFormValue("reference"),
				TransactionID: r.PostFormValue("reference"),
				Type:          r.PostFormValue("roundId"),
				System:        "pragplay",
				Date:          date.Format("2006-01-02 15:04:05"),
				BonusID:       bonus_id,
				Refunded:      true,
			}

			resp := pplayresponses.MakeBetResponse(gen_id.String, game_currency, wallet.GetBalance(), bonus.GetBalance(), 0)
			transaction.RespString = resp.String()

			service.Transactions.Add(&transaction)
			render.JSON(w, r, resp)
			return
		}

		// установка ID транзакции
		bonus.SetBetTransactionID(r.PostFormValue("reference"))
		bonus.SetBackupState(bonus.GetUserBonus())

		// если бонусный код указан - обработка внешних фриспинов
		if r.PostFormValue("bonusCode") != "" {
			// проверка, соответствует ли бонусный код бонусу
			re := regexp.MustCompile(`(d)?` + fmt.Sprintf("%d", bonus.GetID()))
			match := re.FindStringSubmatch(r.PostFormValue("bonusCode"))
			if len(match) == 0 {
				logger.Get().Error("pplay wrong freespin id")
				render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrong freespin id"))
				return

			}
			// получение обработчика внешних фриспинов
			extfs, err := bonus.GetExtFsHandler("pplay")
			if err != nil {
				logger.Get().Error("pplay cant get ext fs halder")
				render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "INTERNAL_ERROR"))
				return
			}
			// обработка внешних фриспинов
			_, err = extfs.ExternFreeSpinBet(amount)
			if err != nil {
				logger.Get().Error("pplay fs error : " + err.Error())
				render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "INTERNAL_ERROR"))
			}
		} else {

			// Снятие средств с бонуса
			_, ra, ba, err := bonus.RemMoney(amount) // снимаем деньги с бонуса
			if err != nil {
				logger.Get().Error("pplay INSUFFICIENT_FUNDS")
				render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(1, "INSUFFICIENT_FUNDS"))
				return
			}
			// добавление ставки в бонус
			gs.AddBonusBet(ba) // добавляем бонусную ставку в тоталы по бонусам
			if ra > 0 {
				gs.AddTotalBet(ra)     // добавляем реальную ставку в тоталы по игре
				wallet.AddTotalBet(ra) // добавляем реальную ставку в тоталы по кошельку
			}
		}
		// обновление бонуса
		win := bonus.Actualize()
		//bonus.Save()
		// service.DefferBonusPersistence.Set(bonus.GetID(), bonus)
		gs.SetRealWin(win) // установка реального выигрыша при выигрыше бонуса (win - выигрыш)
		// получение баланса бонуса
		bonus_balance = bonus.GetBalance()
		bonus_bet = amount

	} else {
		// Обычная игра
		// проверка, была ли уже обработана транзакция
		_, found := user.GetLastRefundsTransactions().Find(func(v *models.GameTransaction) bool {
			return v.BetTransactionId.String == r.PostFormValue("transaction_id")
		})
		if found {
			gen_id := sql.NullString{}
			gen_id.String = uuid.New().String()
			gen_id.Valid = true

			date := time.Now()

			transaction := models.GameTransaction{
				GenID:         gen_id,
				UserId:        user.GetID(),
				Action:        "bet",
				Amount:        amount,
				Currency:      wallet.GetCurrencyName(),
				GameID:        r.PostFormValue("gameId"),
				SessionID:     r.PostFormValue("reference"),
				TransactionID: r.PostFormValue("reference"),
				Type:          r.PostFormValue("roundId"),
				System:        "pragplay",
				Date:          date.Format("2006-01-02 15:04:05"),
				BonusID:       bonus_id,
				Refunded:      true,
			}

			resp := pplayresponses.MakeBetResponse(gen_id.String, game_currency, wallet.GetBalance(), 0, 0)
			transaction.RespString = resp.String()

			service.Transactions.Add(&transaction)
			render.JSON(w, r, resp)
			return
		}
		// Снятие средств с кошелька
		_, err = wallet.RemMoney(amount)
		if err != nil {
			logger.Get().Error("pplay INSUFFICIENT_FUNDS")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(1, "INSUFFICIENT_FUNDS"))
			return
		}
		// добавление ставки в кошелек
		wallet.AddTotalBet(amount)
		// если заблокированные средства больше 0, то снимаем заблокированные средства
		if wallet.GetLockedMoney() > 0 {
			wallet.RemLockedMoney(amount * (float64(gs.GameStartData.WagerPrc) / 100))
		}
		// добавление ставки в игровую сессию
		gs.AddTotalBet(amount)

		// если есть бонус на реальные ставки
		if wb, err := gs.GetBetWagerBonus(); err == nil && wb != nil {
			// вызов метода на реальные ставки
			wb.OnUserBet(amount)
			// сохранение бонуса
			// service.DefferBonusPersistence.Set(wb.GetID(), wb)
		}

		balance = wallet.GetBalance()
	}

	// генерация ID транзакции
	gen_id := sql.NullString{}
	gen_id.String = uuid.New().String()
	gen_id.Valid = true

	date := time.Now()

	// создание транзакции
	transaction := models.GameTransaction{
		GenID:         gen_id,
		UserId:        user.GetID(),
		Action:        "bet",
		Amount:        amount,
		Currency:      wallet.GetCurrencyName(),
		GameID:        r.PostFormValue("gameId"),
		SessionID:     r.PostFormValue("reference"),
		TransactionID: r.PostFormValue("reference"),
		Type:          r.PostFormValue("roundId"),
		System:        "pragplay",
		Date:          date.Format("2006-01-02 15:04:05"),
		BonusID:       bonus_id,
	}

	// не показывать реальный баланс на бонусных играх (чтобы не сумировать баланс бонуса с балансом кошелька)
	if bonus_balance > 0 {
		balance = 0
	}
	// создание ответа
	resp := pplayresponses.MakeBetResponse(gen_id.String, game_currency, balance, bonus_balance, bonus_bet)
	transaction.RespString = resp.String()
	// добавление транзакции в базу
	service.Transactions.Add(&transaction)
	// установка последней транзакции ставки (для привязки к выигрышу)
	user.SetLastBetTransaction(&transaction)
	// добавление транзакции в список последних транзакций
	user.GetLastTransactions().Push(&transaction)
	// сохранение кошелька
	//wallet.Save()
	service.DefferWalletPersistence.Set(wallet.DbWallet.ID, wallet)
	// отправка ответа
	render.JSON(w, r, resp)
}

// win - обрабатывает выигрыш
func (pp *PPlayEndpoint) Win(w http.ResponseWriter, r *http.Request) {
	log := logger.Get()
	player_id := r.PostFormValue("userId")
	isBonusGame := false
	var UserId string
	var currencyName string
	var bonus_balance float64
	var bonus_id sql.NullInt64
	var balance float64

	// регулярное выражение для проверки, является ли игрок бонусным
	re := regexp.MustCompile(`^(bonus)?([0-9]+)([a-zA-Z]{3,5})?$`)

	match := re.FindStringSubmatch(player_id)
	if match != nil {
		isBonusGame = match[1] != ""
		UserId = match[2]
		if match[3] != "" {
			currencyName = match[3]
		}
	}
	// если валюта указана как USD, то преобразуем в USDT
	if currencyName == "USD" {
		currencyName = "USDT"
	}
	// преобразование ID игрока в число
	user_id, err := strconv.ParseInt(UserId, 10, 64)
	if err != nil {
		log.Error("sleg", slog.String("cannot convert user id", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "cannot convert user id"))
		return
	}
	// получение пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("sleg", slog.String("user not found", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(2, "user not found"))
		return
	}
	// получение игровой сессии
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		log.Error("sleg", slog.String("no game session", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no game session"))
		return
	}
	// если валюта не указана, то используем валюту игровой сессии
	if currencyName == "" {
		currencyName = gs.Stats.Currency
	}
	// получение ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("sleg", slog.String("wrogn currency name", currencyName))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrogn currency name"))
		return
	}
	// получение кошелька
	wallet, err := user.GetWallet(curr)
	if err != nil {
		logger.Get().Error("wallet not found", slog.Int("currency", int(curr)))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wallet not found"))
		return
	}
	// если игра бонусная
	if isBonusGame {
		// если нет активного бонуса - ошибка
		b, err := gs.GetActiveBonus()
		if err != nil || b == nil {
			logger.Get().Error("pplay balance no active bonus")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no active bonus"))
			return
		}
		// получение баланса бонуса
		bonus_balance = b.GetBalance()
	}
	// получение валюты игры (USDT = USD)
	var game_currency string
	curr_info, ok := service.Currencies.Find(func(v models.UserCurrency) bool { return v.ID == int64(curr) })
	if !ok {
		logger.Get().Error("currency not found", slog.Int("currency", int(gs.GameStartData.CurrencyID)))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "currency not found"))
		return
	}
	if curr_info.GameCurrency.Valid {
		game_currency = curr_info.GameCurrency.String
	} else {
		game_currency = curr_info.Name
	}
	////
	// получение суммы выигрыша
	a_p := r.PostFormValue("amount")
	amount, err := strconv.ParseFloat(a_p, 64)
	if err != nil {
		logger.Get().Error("pplay bat amount parse error : " + err.Error())
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, err.Error()))
		return
	}
	if amount < 0 {
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrong amount value"))
		return
	}
	// проверка, была ли уже обработана транзакция
	ex_trans, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool { return v.TransactionID == r.PostFormValue("reference") })
	if found {
		render.PlainText(w, r, ex_trans.RespString)
		return
	}
	// если бонусный код указан - проверка, является ли игра бонусной
	if r.PostFormValue("bonusCode") != "" {
		if !isBonusGame {
			logger.Get().Error("pplay win no active bonus")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no active bonus"))
			return
		}
	}
	// если игра бонусная
	if isBonusGame {
		bonus, err := gs.GetActiveBonus()
		// если нет активного бонуса - ошибка
		if err != nil || bonus == nil {
			logger.Get().Error("pplay win no active bonus")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no active bonus"))
			return
		}
		// установка ID бонуса
		bonus_id.Int64 = int64(bonus.GetID())
		bonus_id.Valid = true
		// если бонус не активный - ошибка
		if !bonus.IsActive() {
			logger.Get().Error("bonus is not active")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "bonus is not active"))
			return
		}
		// // установка ID транзакции
		// bonus.SetBetTransactionID(r.PostFormValue("reference"))
		// // сохранение состояния бонуса
		// bonus.SetBackupState(bonus.GetUserBonus())
		// если бонусный код указан - проверка, является ли бонус внешним фриспином
		if r.PostFormValue("bonusCode") != "" {
			// проверка, соответствует ли бонусный код бонусу
			re := regexp.MustCompile(`(d)?` + fmt.Sprintf("%d", bonus.GetID()))
			match := re.FindStringSubmatch(r.PostFormValue("bonusCode"))
			if len(match) == 0 {
				logger.Get().Error("pplay wrong freespin id")
				render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrong freespin id"))
				return

			}
			// получение обработчика внешних фриспинов
			extfs, err := bonus.GetExtFsHandler("pplay")
			if err != nil {
				logger.Get().Error("pplay cant get ext fs halder")
				render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "INTERNAL_ERROR"))
				return
			}
			// вызов метода на внешние фриспины
			_, err = extfs.ExternFreeSpinWin(amount)
			if err != nil {
				logger.Get().Error("pplay fs error : " + err.Error())
				render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "INTERNAL_ERROR"))
			}
		} else {
			// добавляем деньги в бонус
			_, ra, ba := bonus.AddMoney(amount) // добавляем выигрыш на бонус
			gs.AddBonusWin(ba)                  // добавляем выигрыш на бонус в тоталы по бонусам
			if ra > 0 {
				gs.AddTotalWin(ra)     // добавляем реальный выигрыш в тоталы по игре
				wallet.AddTotalWin(ra) // добавляем реальный выигрыш в тоталы по кошельку
			}
		}
		// обновление бонуса
		win := bonus.Actualize()
		// сохранение бонуса
		//bonus.Save()
		// service.DefferBonusPersistence.Set(bonus.GetID(), bonus)
		// установка реального выигрыша при выигрыше бонуса (win - выигрыш)
		gs.SetRealWin(win)
		// получение баланса бонуса
		bonus_balance = bonus.GetBalance()
	} else {
		// обычная игра
		// добавление выигрыша в кошелек
		wallet.AddMoney(amount)
		// добавление выигрыша в игровую сессию
		wallet.AddTotalWin(amount)
		// добавление выигрыша в игровую сессию тоталы
		gs.AddTotalWin(amount)
		// если есть бонус на реальные ставки
		if bw, err := gs.GetBetWagerBonus(); err == nil && bw != nil {
			// вызов метода на реальные ставки
			bw.OnUserWin(amount)
			// сохранение бонуса
			// service.DefferBonusPersistence.Set(bw.GetID(), bw)
		}
		// получение баланса кошелька
		balance = wallet.GetBalance()
	}
	// генерация ID транзакции
	gen_id := sql.NullString{}
	gen_id.String = uuid.New().String()
	gen_id.Valid = true
	// получение даты
	date := time.Now()
	// создание транзакции
	transaction := models.GameTransaction{
		GenID:         gen_id,
		UserId:        user.GetID(),
		Action:        "win",
		Amount:        amount,
		Currency:      wallet.GetCurrencyName(),
		GameID:        r.PostFormValue("gameId"),
		SessionID:     r.PostFormValue("reference"),
		TransactionID: r.PostFormValue("reference"),
		Type:          r.PostFormValue("roundId"),
		System:        "pragplay",
		Date:          date.Format("2006-01-02 15:04:05"),
		BonusID:       bonus_id,
	}
	// получение последней транзакции ставки
	last_bet := user.GetLastBetTransaction()
	if last_bet != nil {
		// установка суммы последней ставки
		transaction.LastBet = last_bet.Amount
		transaction.LastBetId = last_bet.GenID
		// если игра не бонусная, то отправка сообщения с брокером
		if transaction.BonusID.Int64 == 0 {
			container.GetMsgBroker().Send(msgbroker.NewSpinMsg(
				int(transaction.UserId),
				"pragplay",
				transaction.Currency,
				transaction.GameID,
				transaction.LastBet,
				transaction.Amount,
				time.Now(),
			))
		}
		last_bet.Amount = 0 // обнуляем последнюю ставку, чтобы не было повторных ставок при призовых
	}
	// если баланс бонуса больше 0, то скрываем реальный баланс
	if bonus_balance > 0 {
		balance = 0
	}
	// создание ответа
	resp := pplayresponses.MakeWinResponse(gen_id.String, game_currency, balance, bonus_balance)
	// установка ответа в транзакцию (для повторной отправки в случае повторного вызова idenpotent)
	transaction.RespString = resp.String()
	// добавление транзакции в базу
	service.Transactions.Add(&transaction)
	// добавление транзакции в список последних транзакций
	user.GetLastTransactions().Push(&transaction)
	// сохранение кошелька
	//wallet.Save()
	service.DefferWalletPersistence.Set(wallet.DbWallet.ID, wallet)
	// отправка ответа
	render.JSON(w, r, resp)
}

// refund - обрабатывает возврат средств
func (pp *PPlayEndpoint) Refund(w http.ResponseWriter, r *http.Request) {
	log := logger.Get()
	player_id := r.PostFormValue("userId")
	isBonusGame := false
	var UserId string
	var currencyName string
	var bonus_id sql.NullInt64
	// регулярное выражение для проверки, является ли игрок бонусным
	re := regexp.MustCompile(`^(bonus)?([0-9]+)([a-zA-Z]{3,5})?$`)
	// поиск соответствия регулярному выражению
	match := re.FindStringSubmatch(player_id)
	if match != nil {
		isBonusGame = match[1] != ""
		UserId = match[2]
		if match[3] != "" {
			currencyName = match[3]
		}
	}
	// если валюта указана как USD, то преобразуем в USDT
	if currencyName == "USD" {
		currencyName = "USDT"
	}
	// преобразование ID игрока в число
	user_id, err := strconv.ParseInt(UserId, 10, 64)
	if err != nil {
		log.Error("sleg", slog.String("cannot convert user id", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "cannot convert user id"))
		return
	}
	// получение пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("sleg", slog.String("user not found", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(2, "user not found"))
		return
	}
	// получение игровой сессии
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		log.Error("sleg", slog.String("no game session", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no game session"))
		return
	}
	// если валюта не указана, то используем валюту игровой сессии
	if currencyName == "" {
		currencyName = gs.Stats.Currency
	}
	// получение ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("sleg", slog.String("wrogn currency name", currencyName))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrogn currency name"))
		return
	}
	// получение кошелька
	wallet, err := user.GetWallet(curr)
	if err != nil {
		logger.Get().Error("wallet not found", slog.Int("currency", int(curr)))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wallet not found"))
		return
	}
	// если игра бонусная
	if isBonusGame {
		// если нет активного бонуса - ошибка
		if _, err := gs.GetActiveBonus(); err != nil {
			logger.Get().Error("pplay refund no active bonus")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no active bonus"))
			return
		}
	}

	////
	// получение суммы возврата
	a_p := r.PostFormValue("amount")
	amount, err := strconv.ParseFloat(a_p, 64)
	if err != nil {
		logger.Get().Error("pplay refund amount parse error : " + err.Error())
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, err.Error()))
		return
	}
	// если сумма возврата меньше 0, то ошибка
	if amount < 0 {
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrong refund amount value"))
		return
	}
	// проверка, была ли уже обработана транзакция
	ex_trans, found := user.GetLastRefundsTransactions().Find(func(v *models.GameTransaction) bool { return v.BetTransactionId.String == r.PostFormValue("reference") })
	if found {
		render.JSON(w, r, &pplayresponses.PPlayRefundResponse{TransactionID: ex_trans.GenID.String})
		return
	}
	// если игра бонусная
	if isBonusGame {
		bonus, err := gs.GetActiveBonus()
		// если нет активного бонуса - ошибка
		if err != nil || bonus == nil {
			logger.Get().Error("pplay refund no active bonus")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no active bonus"))
			return
		}
		// если бонус активный и транзакция соответствует транзакции бонуса
		if bonus.IsActive() && bonus.GetBetTransactionID() == r.PostFormValue("reference") {
			// восстановление состояния бонуса
			bonus.RestoreState()
			// очистка ID транзакции бонуса
			bonus.SetBetTransactionID("")
			// обновление бонуса
			bonus.Actualize()
			// сохранение бонуса
			// service.DefferBonusPersistence.Set(bonus.GetID(), bonus)
			//bonus.Save()
			bonus_id.Int64 = bonus.GetID()
			bonus_id.Valid = true
		}
	} else {
		// обычная игра
		// поиск транзакции ставки
		tr_to_refund, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool {
			return v.TransactionID == r.PostFormValue("reference") && !v.Refunded
		})
		if found {
			// установка флага возврата
			tr_to_refund.Refunded = true
			// добавление средств на кошелек
			wallet.AddMoney(amount)
		}
	}
	// генерация ID транзакции
	gen_id := sql.NullString{}
	gen_id.String = uuid.New().String()
	gen_id.Valid = true
	// получение даты
	date := time.Now()
	// получение ID транзакции ставки
	var bet_tr_id sql.NullString
	bet_tr_id.String = r.PostFormValue("reference")
	bet_tr_id.Valid = true

	transaction := models.GameTransaction{
		GenID:            gen_id,
		UserId:           user.GetID(),
		Action:           "refund",
		Amount:           amount,
		Currency:         wallet.GetCurrencyName(),
		GameID:           r.PostFormValue("gameId"),
		SessionID:        r.PostFormValue("reference"),
		TransactionID:    r.PostFormValue("reference"),
		BetTransactionId: bet_tr_id,
		Type:             r.PostFormValue("type"),
		System:           "pragplay",
		Date:             date.Format("2006-01-02 15:04:05"),
		BonusID:          bonus_id,
	}
	// добавление транзакции в базу
	service.Transactions.Add(&transaction)
	// добавление транзакции в список последних транзакций
	user.GetLastRefundsTransactions().Push(&transaction)
	// сохранение кошелька
	//wallet.Save()
	service.DefferWalletPersistence.Set(wallet.DbWallet.ID, wallet)
	// отправка ответа
	render.JSON(w, r, &pplayresponses.PPlayRefundResponse{TransactionID: transaction.GenID.String})
}

// BonusWin - обрабатывает выигрыш бонуса (завершение внешних фриспинов)
// данный метод вызывается при завершении внешних фриспинов
func (pp *PPlayEndpoint) BonusWin(w http.ResponseWriter, r *http.Request) {
	log := logger.Get()
	player_id := r.PostFormValue("userId")
	isBonusGame := false
	var UserId string
	var currencyName string
	var bonus_balance float64
	var bonus_id sql.NullInt64
	var balance float64

	// регулярное выражение для проверки, является ли игрок бонусным
	re := regexp.MustCompile(`^(bonus)?([0-9]+)([a-zA-Z]{3,5})?$`)
	// поиск соответствия регулярному выражению
	match := re.FindStringSubmatch(player_id)
	if match != nil {
		isBonusGame = match[1] != ""
		UserId = match[2]
		if match[3] != "" {
			currencyName = match[3]
		}
	}
	// если валюта указана как USD, то преобразуем в USDT
	if currencyName == "USD" {
		currencyName = "USDT"
	}
	// преобразование ID игрока в число
	user_id, err := strconv.ParseInt(UserId, 10, 64)
	if err != nil {
		log.Error("sleg", slog.String("cannot convert user id", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "cannot convert user id"))
		return
	}
	// получение пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("sleg", slog.String("user not found", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(2, "user not found"))
		return
	}
	// получение игровой сессии
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		log.Error("sleg", slog.String("no game session", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no game session"))
		return
	}
	// если валюта не указана, то используем валюту игровой сессии
	if currencyName == "" {
		currencyName = gs.Stats.Currency
	}
	// получение ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("sleg", slog.String("wrogn currency name", currencyName))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrogn currency name"))
		return
	}
	// получение кошелька
	wallet, err := user.GetWallet(curr)
	if err != nil {
		logger.Get().Error("wallet not found", slog.Int("currency", int(curr)))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wallet not found"))
		return
	}

	// получение валюты игры
	var game_currency string
	curr_info, ok := service.Currencies.Find(func(v models.UserCurrency) bool { return v.ID == int64(curr) })
	if !ok {
		logger.Get().Error("currency not found", slog.Int("currency", int(gs.GameStartData.CurrencyID)))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "currency not found"))
		return
	}
	if curr_info.GameCurrency.Valid {
		game_currency = curr_info.GameCurrency.String
	} else {
		game_currency = curr_info.Name
	}
	/////
	// получение суммы выигрыша
	a_p := r.PostFormValue("amount")
	amount, err := strconv.ParseFloat(a_p, 64)
	if err != nil {
		logger.Get().Error("pplay bat amount parse error : " + err.Error())
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, err.Error()))
		return
	}
	if amount < 0 {
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrong amount value"))
		return
	}
	// проверка, была ли уже обработана транзакция
	ex_trans, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool { return v.TransactionID == r.PostFormValue("reference") })
	if found {
		render.PlainText(w, r, ex_trans.RespString)
		return
	}
	// если игра не бонусная, то ошибка
	if r.PostFormValue("bonusCode") != "" {
		if !isBonusGame {
			logger.Get().Error("pplay bet no active bonus")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no active bonus"))
			return
		}
	}
	// если игра бонусная
	if isBonusGame {
		// если нет активного бонуса - ошибка
		bonus, err := gs.GetActiveBonus()
		if err != nil {
			logger.Get().Error("pplay balance no active bonus")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no active bonus"))
			return
		}
		// если бонус не активный, то ошибка
		if bonus == nil {
			logger.Get().Error("pplay bet no active bonus")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no active bonus"))
			return
		}
		bonus_id.Int64 = bonus.GetID()
		bonus_id.Valid = true
		// если бонус не активный, то ошибка
		if !bonus.IsActive() {
			logger.Get().Error("bonus is not active")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "bonus is not active"))
			return
		}
		// установка ID транзакции бонуса
		bonus.SetBetTransactionID(r.PostFormValue("reference"))
		// сохранение состояния бонуса
		bonus.SetBackupState(bonus.GetUserBonus())
		// если бонусный код не пустой
		if r.PostFormValue("bonusCode") != "" {
			// проверка, соответствует ли бонусный код ID бонуса
			re := regexp.MustCompile(`(d)?` + fmt.Sprintf("%d", bonus.GetID()))
			match := re.FindStringSubmatch(r.PostFormValue("bonusCode"))
			if len(match) == 0 {
				logger.Get().Error("pplay wrong freespin id")
				render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrong freespin id"))
				return
			}
			// получение обработчика внешних фриспинов
			extfs, err := bonus.GetExtFsHandler("pplay")
			if err != nil {
				logger.Get().Error("pplay cant get ext fs halder")
				render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "INTERNAL_ERROR"))
				return
			}
			// вызов метода завершения внешних фриспинов (завершение внешних фриспинов)
			_, err = extfs.ExternFreeSpinsCompleted(amount)
			if err != nil {
				logger.Get().Error("pplay fs error : " + err.Error())
				render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "INTERNAL_ERROR"))
			}
		} else {
			// добавление средств на кошелек
			bonus.AddMoney(amount)
			// добавление суммы ставки на бонус
			gs.AddBonusBet(amount)
		}
		// обновление бонуса
		win := bonus.Actualize()
		//bonus.Save()
		// service.DefferBonusPersistence.Set(bonus.GetID(), bonus)
		gs.SetRealWin(win)

		bonus_balance = bonus.GetBalance()
	} else {
		// обычная игра (для данного метода этого проиходить не должно)
		// добавление средств на кошелек
		wallet.AddMoney(amount)
		// добавление суммы выигрыша в тоталы кошелька
		wallet.AddTotalWin(amount)
		// добавление суммы выигрыша в тоталы игры
		gs.AddTotalWin(amount)
		// если есть бонус на реальные ставки
		if bw, err := gs.GetBetWagerBonus(); err == nil && bw != nil {
			// вызов метода начисления бонуса
			bw.OnUserWin(amount)
			// сохранение бонуса
			// service.DefferBonusPersistence.Set(bw.GetID(), bw)
		}
		// получение баланса кошелька
		balance = wallet.GetBalance()
	}
	// генерация ID транзакции
	gen_id := sql.NullString{}
	gen_id.String = uuid.New().String()
	gen_id.Valid = true
	// получение даты
	date := time.Now()
	// создание транзакции
	transaction := models.GameTransaction{
		GenID:         gen_id,
		UserId:        user.GetID(),
		Action:        "bonusWin",
		Amount:        amount,
		Currency:      wallet.GetCurrencyName(),
		GameID:        r.PostFormValue("gameId"),
		SessionID:     r.PostFormValue("reference"),
		TransactionID: r.PostFormValue("reference"),
		Type:          r.PostFormValue("roundId"),
		System:        "pragplay",
		Date:          date.Format("2006-01-02 15:04:05"),
		BonusID:       bonus_id,
	}
	// получение последней ставки
	last_bet := user.GetLastBetTransaction()
	if last_bet != nil {
		transaction.LastBet = last_bet.Amount
		transaction.LastBetId = last_bet.GenID
	}
	// если бонусный баланс больше 0, то устанавливаем баланс в 0
	if bonus_balance > 0 {
		balance = 0
	}
	// создание ответа
	resp := pplayresponses.MakeWinResponse(gen_id.String, game_currency, balance, bonus_balance)
	// установка ответа в транзакцию
	transaction.RespString = resp.String()
	// добавление транзакции в базу
	service.Transactions.Add(&transaction)
	user.GetLastTransactions().Push(&transaction)
	//wallet.Save()
	service.DefferWalletPersistence.Set(wallet.DbWallet.ID, wallet)
	// отправка ответа
	render.JSON(w, r, resp)
}

// JackpotWin - обрабатывает выигрыш джекпота
func (pp *PPlayEndpoint) JackpotWin(w http.ResponseWriter, r *http.Request) {
	log := logger.Get()
	player_id := r.PostFormValue("userId")
	var UserId string
	var currencyName string
	var bonus_balance float64
	var balance float64

	// регулярное выражение для проверки, является ли игрок бонусным
	re := regexp.MustCompile(`^(bonus)?([0-9]+)([a-zA-Z]{3,5})?$`)
	// поиск соответствия регулярному выражению
	match := re.FindStringSubmatch(player_id)
	if match != nil {
		UserId = match[2]
		if match[3] != "" {
			currencyName = match[3]
		}
	}
	// если валюта указана как USD, то преобразуем в USDT
	if currencyName == "USD" {
		currencyName = "USDT"
	}
	// если валюта не указана, то используем валюту игрока
	if currencyName == "" {
		currencyName = r.PostFormValue("currency")
	}
	// преобразование ID игрока в число
	user_id, err := strconv.ParseInt(UserId, 10, 64)
	if err != nil {
		log.Error("sleg", slog.String("cannot convert user id", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "cannot convert user id"))
		return
	}
	// получение пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("sleg", slog.String("user not found", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(2, "user not found"))
		return
	}
	// получение ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("sleg", slog.String("wrogn currency name", currencyName))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrogn currency name"))
		return
	}
	// получение кошелька
	wallet, err := user.GetWallet(curr)
	if err != nil {
		logger.Get().Error("wallet not found", slog.Int("currency", int(curr)))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wallet not found"))
		return
	}
	/////
	// получение суммы выигрыша
	a_p := r.PostFormValue("amount")
	amount, err := strconv.ParseFloat(a_p, 64)
	if err != nil {
		logger.Get().Error("pplay bat amount parse error : " + err.Error())
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, err.Error()))
		return
	}
	if amount < 0 {
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrong amount value"))
		return
	}
	// проверка, была ли уже обработана транзакция
	ex_trans, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool { return v.TransactionID == r.PostFormValue("reference") })
	if found {
		render.PlainText(w, r, ex_trans.RespString)
		return
	}
	// добавление средств на кошелек
	wallet.AddMoney(amount)
	// добавление суммы выигрыша в тоталы кошелька
	wallet.AddTotalWin(amount)
	// получение баланса кошелька
	balance = wallet.GetBalance()
	// генерация ID транзакции
	gen_id := sql.NullString{}
	gen_id.String = uuid.New().String()
	gen_id.Valid = true
	// получение даты
	date := time.Now()
	// создание транзакции
	transaction := models.GameTransaction{
		GenID:         gen_id,
		UserId:        user.GetID(),
		Action:        "jackpotWin",
		Amount:        amount,
		Currency:      wallet.GetCurrencyName(),
		GameID:        r.PostFormValue("gameId"),
		SessionID:     r.PostFormValue("reference"),
		TransactionID: r.PostFormValue("reference"),
		Type:          r.PostFormValue("jackpotId"),
		System:        "pragplay",
		Date:          date.Format("2006-01-02 15:04:05"),
	}
	// получение последней ставки
	last_bet := user.GetLastBetTransaction()
	if last_bet != nil {
		transaction.LastBet = last_bet.Amount
		transaction.LastBetId = last_bet.GenID
	}
	// создание ответа
	resp := pplayresponses.MakeWinResponse(gen_id.String, r.PostFormValue("currency"), balance, bonus_balance)
	// установка ответа в транзакцию
	transaction.RespString = resp.String()
	// добавление транзакции в базу
	service.Transactions.Add(&transaction)
	user.GetLastTransactions().Push(&transaction)
	//wallet.Save()
	service.DefferWalletPersistence.Set(wallet.DbWallet.ID, wallet)
	// отправка ответа
	render.JSON(w, r, resp)
}

// EndRound - обрабатывает завершение раунда
// данный метод может не вызыватся и не является нужным для работы системы
// по сути просто возвращает баланс
func (pp *PPlayEndpoint) EndRound(w http.ResponseWriter, r *http.Request) {
	log := logger.Get()
	player_id := r.PostFormValue("userId")
	isBonusGame := false
	var UserId string
	var currencyName string
	var bonus_balance float64 = 0.00

	// регулярное выражение для проверки, является ли игрок бонусным
	re := regexp.MustCompile(`^(bonus)?([0-9]+)([a-zA-Z]{3,5})?$`)
	// поиск соответствия регулярному выражению
	match := re.FindStringSubmatch(player_id)
	if match != nil {
		isBonusGame = match[1] != ""
		UserId = match[2]
		if match[3] != "" {
			currencyName = match[3]
		}
	}
	// если валюта указана как USD, то преобразуем в USDT
	if currencyName == "USD" {
		currencyName = "USDT"
	}
	// преобразование ID игрока в число
	user_id, err := strconv.ParseInt(UserId, 10, 64)
	if err != nil {
		log.Error("sleg", slog.String("cannot convert user id", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "cannot convert user id"))
		return
	}
	// получение пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("sleg", slog.String("user not found", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(2, "user not found"))
		return
	}
	// получение игровой сессии
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		log.Error("sleg", slog.String("no game session", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no game session"))
		return
	}
	// если валюта не указана, то используем валюту игрока
	if currencyName == "" {
		currencyName = gs.Stats.Currency
	}
	// получение ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("sleg", slog.String("wrogn currency name", currencyName))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrogn currency name"))
		return
	}
	// получение кошелька
	wallet, err := user.GetWallet(curr)
	if err != nil {
		logger.Get().Error("wallet not found", slog.Int("currency", int(curr)))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wallet not found"))
		return
	}
	// если игра бонусная
	if isBonusGame {
		// если бонус не активный, то ошибка
		bonus, err := gs.GetActiveBonus()
		if err != nil {
			logger.Get().Error("pplay balance no active bonus")
			render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "no active bonus"))
			return
		}
		bonus_balance = bonus.GetBalance()
	}
	// если бонусный баланс больше 0, то отправляем ответ с бонусным балансом
	if bonus_balance > 0 {
		render.JSON(w, r, pplayresponses.MakeEndRoundResponse(0, bonus_balance))
	} else {
		render.JSON(w, r, pplayresponses.MakeEndRoundResponse(wallet.GetBalance(), bonus_balance))
	}

}

// PromoWin - обрабатывает выигрыш промо
func (pp *PPlayEndpoint) PromoWin(w http.ResponseWriter, r *http.Request) {
	log := logger.Get()
	player_id := r.PostFormValue("userId")
	var UserId string
	var currencyName string
	var balance float64

	// регулярное выражение для проверки, является ли игрок бонусным
	re := regexp.MustCompile(`^(bonus)?([0-9]+)([a-zA-Z]{3,5})?$`)
	// поиск соответствия регулярному выражению
	match := re.FindStringSubmatch(player_id)
	if match != nil {
		UserId = match[2]
		if match[3] != "" {
			currencyName = match[3]
		}
	}
	// если валюта указана как USD, то преобразуем в USDT
	if currencyName == "USD" {
		currencyName = "USDT"
	}
	// если валюта не указана, то используем валюту игрока
	if currencyName == "" {
		currencyName = r.PostFormValue("currency")
	}
	// преобразование ID игрока в число
	user_id, err := strconv.ParseInt(UserId, 10, 64)
	if err != nil {
		log.Error("sleg", slog.String("cannot convert user id", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "cannot convert user id"))
		return
	}
	// получение пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("sleg", slog.String("user not found", UserId))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(2, "user not found"))
		return
	}
	// получение ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("sleg", slog.String("wrogn currency name", currencyName))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrogn currency name"))
		return
	}
	// получение кошелька
	wallet, err := user.GetWallet(curr)
	if err != nil {
		logger.Get().Error("wallet not found", slog.Int("currency", int(curr)))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wallet not found"))
		return
	}
	/////
	// получение суммы выигрыша
	a_p := r.PostFormValue("amount")
	amount, err := strconv.ParseFloat(a_p, 64)
	if err != nil {
		logger.Get().Error("pplay bat amount parse error : " + err.Error())
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, err.Error()))
		return
	}
	if amount < 0 {
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "wrong amount value"))
		return
	}
	// проверка, была ли уже обработана транзакция
	ex_trans, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool { return v.TransactionID == r.PostFormValue("reference") })
	if found {
		render.PlainText(w, r, ex_trans.RespString)
		return
	}
	// добавление средств на кошелек
	wallet.AddMoney(amount)
	// добавление суммы выигрыша в тоталы кошелька
	wallet.AddTotalWin(amount)
	// получение баланса кошелька
	balance = wallet.GetBalance()
	// генерация ID транзакции
	gen_id := sql.NullString{}
	gen_id.String = uuid.New().String()
	gen_id.Valid = true
	// получение даты
	date := time.Now()
	// создание транзакции
	transaction := models.GameTransaction{
		GenID:         gen_id,
		UserId:        user.GetID(),
		Action:        "promoWin",
		Amount:        amount,
		Currency:      wallet.GetCurrencyName(),
		GameID:        r.PostFormValue("gameId"),
		SessionID:     r.PostFormValue("reference"),
		TransactionID: r.PostFormValue("reference"),
		Type:          r.PostFormValue("campaignType"),
		System:        "pragplay",
		Date:          date.Format("2006-01-02 15:04:05"),
	}
	// получение последней ставки
	last_bet := user.GetLastBetTransaction()
	if last_bet != nil {
		transaction.LastBet = last_bet.Amount
		transaction.LastBetId = last_bet.GenID
	}
	// создание ответа
	resp := pplayresponses.MakeWinResponse(gen_id.String, r.PostFormValue("currency"), balance, 0)
	// установка ответа в транзакцию
	transaction.RespString = resp.String()
	// добавление транзакции в базу
	service.Transactions.Add(&transaction)
	user.GetLastTransactions().Push(&transaction)
	//wallet.Save()
	service.DefferWalletPersistence.Set(wallet.DbWallet.ID, wallet)
	// отправка ответа
	render.JSON(w, r, resp)
}

// SessionExpired - обрабатывает истечение срока сессии
// данный метод может не вызыватся и не является нужным для работы системы
// по сути просто возвращает ok
func (pp *PPlayEndpoint) SessionExpired(w http.ResponseWriter, r *http.Request) {
	// сессия должна быть закрыта из kraken
	// поэтому просто возвращаем ok
	render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(0, "Success"))
}

// CalcHash - вычисляет хеш для проверки подлинности запроса
func (pp *PPlayEndpoint) CalcHash(data map[string]string) string {
	// удаление хеша из данных
	delete(data, "hash")
	// создание слайса для хранения ключей
	keys := make([]string, 0, len(data))
	for k := range data {
		keys = append(keys, k)
	}
	sort.Strings(keys)
	// создание слайса для хранения строк
	var dstr []string
	// добавление ключей и значений в слайс
	for _, k := range keys {
		dstr = append(dstr, k+"="+data[k])
	}
	// вычисление хеша
	hash := md5.Sum([]byte(strings.Join(dstr, "&") + pp.Key))
	// возвращение хеша в виде строки
	return hex.EncodeToString(hash[:])
}
