package qt

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"io"
	"log/slog"
	"net/http"
	"regexp"
	"strconv"
	"time"
	"user-manager/internal/config"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/msgbroker"
	"user-manager/internal/service"
	"user-manager/internal/service/container"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
	"github.com/google/uuid"
)

var KEY string = ""

// init - инициализирует ключ для работы с QT
func init() {
	KEY = config.Get().QT.Key
}

// VerifySession - проверяет сессию пользователя
func VerifySession(w http.ResponseWriter, r *http.Request) {
	log := logger.Get()
	log.Info("qt", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	log.Info("qt", slog.String("headers", fmt.Sprintf("%v\n", r.Header)))

	if r.Header.Get("Pass-Key") != KEY {
		log.Error("qt", slog.String("LOGIN_FAILED", "The given pass-key is incorrect"))
		w.WriteHeader(http.StatusUnauthorized)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "The given pass-key is incorrect"})
		return
	}

	isBonusGame := false
	var UserId string
	var currencyName string
	var balance float64

	player_id := chi.URLParam(r, "player_id")

	// регулярное выражение для получения ID пользователя и валюты и бонуса
	re := regexp.MustCompile(`^(bonus)?([0-9]+)([a-zA-Z]{3,5})?$`)

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

	// преобразуем ID пользователя в число
	user_id, err := strconv.ParseInt(UserId, 10, 64)
	if err != nil {
		log.Error("qt", slog.String("qt cannot convert user id", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "cannot convert user id"})
		return
	}

	// получаем пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("qt", slog.String("qt user not found", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "user not found"})
		return
	}

	// получаем игровую сессию пользователя
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		log.Error("qt", slog.String("qt no game session", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "no game session"})
		return
	}

	// проверяем, что сессия пользователя совпадает с сессией в запросе
	if gs.Stats.SessionID == nil || *gs.Stats.SessionID != r.Header.Get("Wallet-Session") {
		log.Error("qt", slog.String("wrong game session", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "wrong game session"})
		return
	}

	// если валюта не указана, то используем валюту из игровой сессии
	if currencyName == "" {
		currencyName = gs.Stats.Currency
	}

	// получаем ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("qt", slog.String("qt wrong currency name", currencyName))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "wrogn currency name"})
		return
	}

	// получаем кошелек пользователя
	wallet, err := user.GetWallet(curr)
	if err != nil {
		logger.Get().Error("qt wallet not found", slog.Int("currency", int(curr)))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "wallet not found"})
		return
	}

	// если игра с бонусом, то получаем баланс бонуса
	if isBonusGame {
		bonus, err := gs.GetActiveBonus()
		if err != nil {
			logger.Get().Error("pplay balance no active bonus")
			w.WriteHeader(http.StatusBadRequest)
			render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "no active bonus"})
			return
		}
		balance = bonus.GetBalance()
	} else {
		// если игра без бонуса, то получаем баланс кошелька
		balance = wallet.GetBalance()
	}

	// получаем валюту игры (USDT = USD)
	var game_currency string
	curr_info, ok := service.Currencies.Find(func(v models.UserCurrency) bool { return v.ID == int64(curr) })
	if !ok {
		logger.Get().Error("qt currency not found", slog.Int("currency", int(gs.GameStartData.CurrencyID)))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "currency not found"})
		return
	}
	if curr_info.GameCurrency.Valid {
		game_currency = curr_info.GameCurrency.String
	} else {
		game_currency = curr_info.Name
	}

	// отправляем ответ баланса
	render.PlainText(w, r, MakeBalanceResponse(balance, game_currency))
	w.Header().Set("Content-Type", "application/json")
}

// Balance - возвращает баланс пользователя
func Balance(w http.ResponseWriter, r *http.Request) {
	log := logger.Get()
	log.Info("qt", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	log.Info("qt", slog.String("headers", fmt.Sprintf("%v\n", r.Header)))

	// проверяем, что ключ корректен
	if r.Header.Get("Pass-Key") != KEY {
		log.Error("qt", slog.String("LOGIN_FAILED", "The given pass-key is incorrect"))
		w.WriteHeader(http.StatusUnauthorized)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "The given pass-key is incorrect"})
		return
	}

	isBonusGame := false
	var UserId string
	var currencyName string
	var balance float64

	player_id := chi.URLParam(r, "player_id")

	// регулярное выражение для получения ID пользователя и валюты и бонуса
	re := regexp.MustCompile(`^(bonus)?([0-9]+)([a-zA-Z]{3,5})?$`)

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

	// преобразуем ID пользователя в число
	user_id, err := strconv.ParseInt(UserId, 10, 64)
	if err != nil {
		log.Error("qt", slog.String("qt cannot convert user id", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "cannot convert user id"})
		return
	}

	// получаем пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("qt", slog.String("qt user not found", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "user not found"})
		return
	}

	// получаем игровую сессию пользователя
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		log.Error("qt", slog.String("qt no game session", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "no game session"})
		return
	}

	// проверяем, что сессия пользователя совпадает с сессией в запросе
	if gs.Stats.SessionID == nil || *gs.Stats.SessionID != r.Header.Get("Wallet-Session") {
		log.Error("qt", slog.String("wrong game session", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "INVALID_TOKEN", Message: "Missing, invalid or expired player (wallet) session token."})
		return
	}

	// если валюта не указана, то используем валюту из игровой сессии
	if currencyName == "" {
		currencyName = gs.Stats.Currency
	}

	// получаем ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("qt", slog.String("qt wrong currency name", currencyName))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "wrogn currency name"})
		return
	}

	// получаем кошелек пользователя
	wallet, err := user.GetWallet(curr)
	if err != nil {
		logger.Get().Error("qt wallet not found", slog.Int("currency", int(curr)))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "wallet not found"})
		return
	}

	// если игра с бонусом, то получаем баланс бонуса
	if isBonusGame {
		bonus, err := gs.GetActiveBonus()
		if err != nil {
			logger.Get().Error("pplay balance no active bonus")
			w.WriteHeader(http.StatusBadRequest)
			render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "no active bonus"})
			return
		}
		balance = bonus.GetBalance()
	} else {
		// если игра без бонуса, то получаем баланс кошелька
		balance = wallet.GetBalance()
	}

	// получаем валюту игры (USDT = USD)
	var game_currency string
	curr_info, ok := service.Currencies.Find(func(v models.UserCurrency) bool { return v.ID == int64(curr) })
	if !ok {
		logger.Get().Error("qt currency not found", slog.Int("currency", int(gs.GameStartData.CurrencyID)))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "currency not found"})
		return
	}
	if curr_info.GameCurrency.Valid {
		game_currency = curr_info.GameCurrency.String
	} else {
		game_currency = curr_info.Name
	}

	// отправляем ответ баланса
	w.Header().Set("Content-Type", "application/json")
	render.PlainText(w, r, MakeBalanceResponse(balance, game_currency))
}

// Transaction - обрабатывает транзакции
func Transaction(w http.ResponseWriter, r *http.Request) {
	log := logger.Get()
	log.Info("qt", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	log.Info("qt", slog.String("headers", fmt.Sprintf("%v\n", r.Header)))

	// проверяем, что ключ корректен
	if r.Header.Get("Pass-Key") != KEY {
		log.Error("qt", slog.String("LOGIN_FAILED", "The given pass-key is incorrect"))
		w.WriteHeader(http.StatusUnauthorized)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "The given pass-key is incorrect"})
		return
	}

	// читаем тело запроса
	body, err := io.ReadAll(r.Body)
	if err != nil {
		logger.Get().Error("qt", slog.String("err", err.Error()))
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: err.Error()})
		return
	}

	log.Info("qt", slog.String("qt request", string(body)))

	var req transaction_request
	err = json.Unmarshal(body, &req)
	if err != nil {
		logger.Get().Error("qt", slog.String("err", err.Error()))
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: err.Error()})
		return
	}

	// получаем метод транзакции
	method := req.TxnType

	log.Info("qt", slog.String("PostForm", fmt.Sprintf("%v", r.PostForm)))

	// обрабатываем транзакцию
	switch method {
	case "DEBIT":
		bet(w, r, req)
		return
	case "CREDIT":
		win(w, r, req)
		return
	}

	// отправляем ответ на неизвестный метод транзакции
	w.WriteHeader(http.StatusUnauthorized)
	render.JSON(w, r, ErrorResponse{Code: "UNKNOWN_ERROR", Message: "transaction method unknown " + method})
}

func bet(w http.ResponseWriter, r *http.Request, req transaction_request) {
	log := logger.Get()

	isBonusGame := false
	var UserId string
	var currencyName string
	var balance float64
	var bonus_id sql.NullInt64

	// получаем ID пользователя
	player_id := req.PlayerId

	// регулярное выражение для получения ID пользователя и валюты и бонуса
	re := regexp.MustCompile(`^(bonus)?([0-9]+)([a-zA-Z]{3,5})?$`)

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

	// преобразуем ID пользователя в число
	user_id, err := strconv.ParseInt(UserId, 10, 64)
	if err != nil {
		log.Error("qt", slog.String("qt cannot convert user id", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "cannot convert user id"})
		return
	}

	// получаем пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("qt", slog.String("qt user not found", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "user not found"})
		return
	}

	// получаем игровую сессию пользователя
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		log.Error("qt", slog.String("qt no game session", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "no game session"})
		return
	}

	// проверяем, что сессия пользователя совпадает с сессией в запросе
	if gs.Stats.SessionID == nil || *gs.Stats.SessionID != r.Header.Get("Wallet-Session") {
		log.Error("qt", slog.String("wrong game session", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "wrong game session"})
		return
	}

	// если валюта не указана, то используем валюту из игровой сессии
	if currencyName == "" {
		currencyName = gs.Stats.Currency
	}

	// получаем ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("sleg", slog.String("qt wrong currency name", currencyName))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "wrogn currency name"})
		return
	}

	// получаем кошелек пользователя
	wallet, err := user.GetWallet(curr)
	if err != nil {
		logger.Get().Error("qt wallet not found", slog.Int("currency", int(curr)))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "wallet not found"})
		return
	}

	/////
	// проверяем, что сумма транзакции корректна
	amount := req.Amount
	// amount, err := strconv.ParseFloat(a_p, 64)
	// if err != nil {
	// 	logger.Get().Error("qt bet amount parse error : " + err.Error())
	// 	w.WriteHeader(http.StatusBadRequest)
	// 	render.JSON(w, r, ErrorResponse{Code: "REQUEST_DECLINED", Message: "bet amount parse error"})
	// 	return
	// }
	if amount < 0 {
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "REQUEST_DECLINED", Message: "amount < 0"})
		return
	}

	// проверяем, что транзакция уже обработана
	ex_trans, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool { return v.TransactionID == r.PostFormValue("txnId") })
	if found {
		w.Header().Set("Content-Type", "application/json")
		render.PlainText(w, r, ex_trans.RespString)
		return
	}

	// если транзакция с бонусом, то проверяем, что игра с бонусом
	if req.BonusType != "" {
		if !isBonusGame {
			logger.Get().Error("qt bet no active bonus")
			w.WriteHeader(http.StatusBadRequest)
			render.JSON(w, r, ErrorResponse{Code: "REQUEST_DECLINED", Message: "bet no active bonus"})
			return
		}
	}

	// если транзакция с бонусом, обрабатываем её соответствующим образом
	if isBonusGame {
		// получаем активный бонус
		bonus, err := gs.GetActiveBonus()
		if err != nil {
			logger.Get().Error("pplay bet no active bonus")
			w.WriteHeader(http.StatusBadRequest)
			render.JSON(w, r, ErrorResponse{Code: "REQUEST_DECLINED", Message: "bet no active bonus"})
			return
		}

		// устанавливаем ID бонуса
		bonus_id.Int64 = bonus.GetID()
		bonus_id.Valid = true

		// проверяем, что бонус активен
		if !bonus.IsActive() {
			logger.Get().Error("bonus is not active")
			w.WriteHeader(http.StatusBadRequest)
			render.JSON(w, r, ErrorResponse{Code: "REQUEST_DECLINED", Message: "bet no active bonus"})
			return
		}

		// проверяем, что транзакция не была отменена
		_, found := user.GetLastRefundsTransactions().Find(func(v *models.GameTransaction) bool {
			return v.BetTransactionId.String == req.TxnId
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
				GameID:        req.GameId,
				SessionID:     r.Header.Get("Wallet-Session"),
				TransactionID: req.TxnId,
				Type:          req.RoundId,
				System:        "qt",
				Date:          date.Format("2006-01-02 15:04:05"),
				BonusID:       bonus_id,
				Refunded:      true,
			}

			resp := MakeTransactionResponse(bonus.GetBalance(), gen_id.String)
			transaction.RespString = resp

			service.Transactions.Add(&transaction)
			render.JSON(w, r, resp)
			return
		}

		// устанавливаем ID транзакции бонуса
		bonus.SetBetTransactionID(req.TxnId)
		bonus.SetBackupState(bonus.GetUserBonus())

		// если указан BonusCode то обрабатываем его внешний фриспин
		if req.BonusCode != "" {
			// проверяем, что ID бонуса совпадает с ID фриспина
			re := regexp.MustCompile(`(d)?` + fmt.Sprintf("%d", bonus.GetID()))
			match := re.FindStringSubmatch(req.BonusCode)
			if len(match) == 0 {
				logger.Get().Error("qt wrong freespin id")
				w.WriteHeader(http.StatusBadRequest)
				render.JSON(w, r, ErrorResponse{Code: "REQUEST_DECLINED", Message: "qt wrong freespin id"})
				return

			}

			// получаем обработчик внешнего фриспина
			extfs, err := bonus.GetExtFsHandler("qt")
			if err != nil {
				logger.Get().Error("qt cant get ext fs halder")
				w.WriteHeader(http.StatusInternalServerError)
				render.JSON(w, r, ErrorResponse{Code: "UNKNOWN_ERROR", Message: "qt cant get ext fs halder"})
				return
			}
			// обрабатываем внешний фриспин
			_, err = extfs.ExternFreeSpinBet(amount)
			if err != nil {
				logger.Get().Error("qt fs error : " + err.Error())
				w.WriteHeader(http.StatusInternalServerError)
				render.JSON(w, r, ErrorResponse{Code: "UNKNOWN_ERROR", Message: "qt fs error"})
			}
		} else {
			// снимаем деньги из бонуса
			_, ra, ba, err := bonus.RemMoney(amount) // снимаем деньги с бонуса
			if err != nil {
				logger.Get().Error("qt INSUFFICIENT_FUNDS")
				w.WriteHeader(http.StatusBadRequest)
				render.JSON(w, r, ErrorResponse{Code: "INSUFFICIENT_FUNDS", Message: "INSUFFICIENT FUNDS"})
				return
			}
			// добавляем ставку в бонус
			gs.AddBonusBet(ba) // добавляем бонусную ставку в тоталы по бонусам
			if ra > 0 {
				gs.AddTotalBet(ra)     // добавляем реальную ставку в тоталы по игре
				wallet.AddTotalBet(ra) // добавляем реальную ставку в тоталы по кошельку
			}
		}

		// обновляем бонус получаем сумму выиграша если бонус завершен
		win := bonus.Actualize()
		//bonus.Save()
		// service.DefferBonusPersistence.Set(bonus.GetID(), bonus)
		// устанавливаем реальный выигрыш
		gs.SetRealWin(win)
		// получаем баланс бонуса
		balance = bonus.GetBalance()
	} else {

		// проверяем, что транзакция не была отменена
		_, found := user.GetLastRefundsTransactions().Find(func(v *models.GameTransaction) bool {
			return v.BetTransactionId.String == req.TxnId
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
				GameID:        req.GameId,
				SessionID:     r.Header.Get("Wallet-Session"),
				TransactionID: req.TxnId,
				Type:          req.RoundId,
				System:        "qt",
				Date:          date.Format("2006-01-02 15:04:05"),
				BonusID:       bonus_id,
				Refunded:      true,
			}

			resp := MakeTransactionResponse(wallet.GetBalance(), gen_id.String)
			transaction.RespString = resp

			service.Transactions.Add(&transaction)
			render.JSON(w, r, resp)
			return
		}

		// снимаем деньги из кошелька
		_, err = wallet.RemMoney(amount)
		if err != nil {
			logger.Get().Error("qt INSUFFICIENT_FUNDS")
			w.WriteHeader(http.StatusBadRequest)
			render.JSON(w, r, ErrorResponse{Code: "INSUFFICIENT_FUNDS", Message: "INSUFFICIENT FUNDS"})
			return
		}

		// добавляем ставку в тоталы кошелька
		wallet.AddTotalBet(amount)
		// если есть заблокированные деньги, то снимаем их
		if wallet.GetLockedMoney() > 0 {
			wallet.RemLockedMoney(amount * (float64(gs.GameStartData.WagerPrc) / 100))
		}

		// добавляем ставку в тоталы сессии
		gs.AddTotalBet(amount)

		// если есть бонус на реальные деньги, то обрабатываем его
		if bw, err := gs.GetBetWagerBonus(); err == nil {
			// обрабатываем ставку в бонусе
			bw.OnUserBet(amount)
			//gs.BetWagerBonus.Save()
			// добавляем бонус в очередь на сохранение
			// service.DefferBonusPersistence.Set(bw.GetID(), bw)
		}

		// получаем баланс кошелька
		balance = wallet.GetBalance()
	}

	// генерируем ID транзакции
	gen_id := sql.NullString{}
	gen_id.String = uuid.New().String()
	gen_id.Valid = true

	// получаем текущую дату
	date := time.Now()

	// создаём транзакцию
	transaction := models.GameTransaction{
		GenID:         gen_id,
		UserId:        user.GetID(),
		Action:        "bet",
		Amount:        amount,
		Currency:      wallet.GetCurrencyName(),
		GameID:        req.GameId,
		SessionID:     r.Header.Get("Wallet-Session"),
		TransactionID: req.TxnId,
		Type:          req.RoundId,
		System:        "qt",
		Date:          date.Format("2006-01-02 15:04:05"),
		BonusID:       bonus_id,
	}

	// создаём ответ на транзакцию
	resp := MakeTransactionResponse(balance, gen_id.String)
	transaction.RespString = resp

	// добавляем транзакцию в базу
	service.Transactions.Add(&transaction)
	// устанавливаем последнюю транзакцию ставки (для привязки к выигрышу)
	user.SetLastBetTransaction(&transaction)
	// добавляем транзакцию в список последних транзакций
	user.GetLastTransactions().Push(&transaction)
	//wallet.Save()
	// добавляем кошелек в очередь на сохранение
	service.DefferWalletPersistence.Set(wallet.DbWallet.ID, wallet)
	// отправляем ответ
	render.PlainText(w, r, resp)
	w.Header().Set("Content-Type", "application/json")
}

// win - обрабатывает выигрыш
func win(w http.ResponseWriter, r *http.Request, req transaction_request) {
	log := logger.Get()

	isBonusGame := false
	var UserId string
	var currencyName string
	var balance float64
	var bonus_id sql.NullInt64

	player_id := req.PlayerId

	// регулярное выражение для получения ID пользователя и валюты и бонуса
	re := regexp.MustCompile(`^(bonus)?([0-9]+)([a-zA-Z]{3,5})?$`)

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

	// преобразуем ID пользователя в число
	user_id, err := strconv.ParseInt(UserId, 10, 64)
	if err != nil {
		log.Error("qt", slog.String("qt cannot convert user id", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "cannot convert user id"})
		return
	}

	// получаем пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("qt", slog.String("qt user not found", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "user not found"})
		return
	}

	// получаем игровую сессию пользователя
	gs := user.GetGameSession()
	// if gs == nil || gs.Stats.SessionID == nil {
	// 	log.Error("qt", slog.String("qt no game session", UserId))
	// 	w.WriteHeader(http.StatusBadRequest)
	// 	render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "no game session"})
	// 	return
	// }

	// если валюта не указана, то используем валюту из игровой сессии
	if currencyName == "" && gs != nil {
		currencyName = gs.Stats.Currency
	}

	// получаем ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("sleg", slog.String("qt wrong currency name", currencyName))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "wrogn currency name"})
		return
	}

	// получаем кошелек пользователя
	wallet, err := user.GetWallet(curr)
	if err != nil {
		logger.Get().Error("qt wallet not found", slog.Int("currency", int(curr)))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "wallet not found"})
		return
	}

	// получаем сумму выигрыша
	amount := req.Amount
	// amount, err := strconv.ParseFloat(a_p, 64)
	// if err != nil {
	// 	logger.Get().Error("qt bet amount parse error : " + err.Error())
	// 	w.WriteHeader(http.StatusBadRequest)
	// 	render.JSON(w, r, ErrorResponse{Code: "REQUEST_DECLINED", Message: "win amount parse error"})
	// 	return
	// }
	if amount < 0 {
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "REQUEST_DECLINED", Message: "amount < 0"})
		return
	}

	// проверяем, что транзакция не была отменена
	ex_trans, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool { return v.TransactionID == r.PostFormValue("txnId") })
	if found {
		w.Header().Set("Content-Type", "application/json")
		render.PlainText(w, r, ex_trans.RespString)
		return
	}

	// если указан BonusType то проверяем, что игра с бонусом
	if req.BonusType != "" {
		if !isBonusGame {
			logger.Get().Error("qt bet no active bonus")
			w.WriteHeader(http.StatusBadRequest)
			render.JSON(w, r, ErrorResponse{Code: "REQUEST_DECLINED", Message: "bet no active bonus"})
			return
		}
	}

	// если игра с бонусом, то обрабатываем его
	if isBonusGame {
		// если игровая сессия не nil, то проверяем, что сессия активна
		if gs == nil || gs.Stats.SessionID == nil {
			log.Error("qt", slog.String("qt no game session for bonus", UserId))
			w.Header().Set("Content-Type", "application/json")
			render.PlainText(w, r, MakeTransactionResponse(0, uuid.New().String()))
			return
		}

		// получаем активный бонус
		bonus, err := gs.GetActiveBonus()
		// если бонус не найден, то отправляем ошибку
		if err != nil {
			logger.Get().Error("qt bet no active bonus")
			w.WriteHeader(http.StatusBadRequest)
			render.JSON(w, r, ErrorResponse{Code: "REQUEST_DECLINED", Message: "bet no active bonus"})
			return
		}

		// устанавливаем ID бонуса
		bonus_id.Int64 = int64(bonus.GetID())
		bonus_id.Valid = true

		// проверяем, что бонус активен
		if !bonus.IsActive() {
			logger.Get().Error("bonus is not active")
			w.WriteHeader(http.StatusBadRequest)
			render.JSON(w, r, ErrorResponse{Code: "REQUEST_DECLINED", Message: "bet no active bonus"})
			return
		}

		// устанавливаем ID транзакции бонуса
		bonus.SetBetTransactionID(req.TxnId)
		// сохраняем резервную копию состояния бонуса
		bonus.SetBackupState(bonus.GetUserBonus())

		// если указан BonusCode то обрабатываем его внешний фриспин
		if req.BonusCode != "" {
			// проверяем, что ID бонуса совпадает с ID фриспина
			re := regexp.MustCompile(`(d)?` + fmt.Sprintf("%d", bonus.GetID()))
			match := re.FindStringSubmatch(req.BonusCode)
			if len(match) == 0 {
				logger.Get().Error("qt wrong freespin id")
				w.WriteHeader(http.StatusBadRequest)
				render.JSON(w, r, ErrorResponse{Code: "REQUEST_DECLINED", Message: "qt wrong freespin id"})
				return

			}

			// получаем обработчик внешнего фриспина
			extfs, err := bonus.GetExtFsHandler("qt")
			if err != nil {
				logger.Get().Error("qt cant get ext fs halder")
				w.WriteHeader(http.StatusInternalServerError)
				render.JSON(w, r, ErrorResponse{Code: "UNKNOWN_ERROR", Message: "qt cant get ext fs halder"})
				return
			}
			// обрабатываем внешний фриспин
			_, err = extfs.ExternFreeSpinWin(amount)
			if err != nil {
				logger.Get().Error("qt fs error : " + err.Error())
				w.WriteHeader(http.StatusInternalServerError)
				render.JSON(w, r, ErrorResponse{Code: "UNKNOWN_ERROR", Message: "qt fs error"})
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
		// обновляем бонус получаем сумму выигрыша если бонус завершен
		win := bonus.Actualize()
		//bonus.Save()
		// service.DefferBonusPersistence.Set(bonus.GetID(), bonus)
		// устанавливаем реальный выигрыш
		gs.SetRealWin(win)
		// получаем баланс бонуса
		balance = bonus.GetBalance()
	} else {
		// добавляем деньги в кошелек
		wallet.AddMoney(amount)
		// добавляем тотал вин в кошелек
		wallet.AddTotalWin(amount)
		// если игровая сессия активна
		if gs != nil {
			// добавляем тотал вин в сессию
			gs.AddTotalWin(amount)
			// если есть бонус на реальные деньги, то обрабатываем его
			if bw, err := gs.GetBetWagerBonus(); err == nil {
				bw.OnUserWin(amount)
				//gs.BetWagerBonus.Save()
				// service.DefferBonusPersistence.Set(bw.GetID(), bw)
			}
		}

		// получаем баланс кошелька
		balance = wallet.GetBalance()
	}

	// генерируем ID транзакции
	gen_id := sql.NullString{}
	gen_id.String = uuid.New().String()
	gen_id.Valid = true

	// получаем текущую дату
	date := time.Now()

	// создаём транзакцию
	transaction := models.GameTransaction{
		GenID:         gen_id,
		UserId:        user.GetID(),
		Action:        "win",
		Amount:        amount,
		Currency:      wallet.GetCurrencyName(),
		GameID:        req.GameId,
		SessionID:     r.Header.Get("Wallet-Session"),
		TransactionID: req.TxnId,
		Type:          req.RoundId,
		System:        "qt",
		Date:          date.Format("2006-01-02 15:04:05"),
		BonusID:       bonus_id,
	}

	// получаем последнюю ставку
	last_bet := user.GetLastBetTransaction()
	// если последняя ставка не nil, то добавляем её в транзакцию
	if last_bet != nil {
		transaction.LastBet = last_bet.Amount
		transaction.LastBetId = last_bet.GenID
		// если нет бонуса, то отправляем сообщение с брокером (для турниров на данный момент)
		if transaction.BonusID.Int64 == 0 {
			container.GetMsgBroker().Send(msgbroker.NewSpinMsg(
				int(transaction.UserId),
				"qt",
				transaction.Currency,
				transaction.GameID,
				transaction.LastBet,
				transaction.Amount,
				time.Now(),
			))
		}
		last_bet.Amount = 0 // обнуляем последнюю ставку, чтобы не было повторных ставок при призовых
	}

	// создаём ответ на транзакцию
	resp := MakeTransactionResponse(balance, gen_id.String)
	transaction.RespString = resp

	// добавляем транзакцию в базу
	service.Transactions.Add(&transaction)
	// добавляем транзакцию в список последних транзакций
	user.GetLastTransactions().Push(&transaction)
	// сохраняем кошелек
	service.DefferWalletPersistence.Set(wallet.DbWallet.ID, wallet)

	// отправляем ответ
	render.PlainText(w, r, resp)
	w.Header().Set("Content-Type", "application/json")
}

// Rollback - обрабатывает отмену транзакции
func Rollback(w http.ResponseWriter, r *http.Request) {
	log := logger.Get()

	isBonusGame := false
	var UserId string
	var currencyName string
	var balance float64
	var bonus_id sql.NullInt64

	log.Info("qt", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	log.Info("qt", slog.String("headers", fmt.Sprintf("%v\n", r.Header)))

	// проверяем, что ключ корректен
	if r.Header.Get("Pass-Key") != KEY {
		log.Error("qt", slog.String("LOGIN_FAILED", "The given pass-key is incorrect"))
		w.WriteHeader(http.StatusUnauthorized)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "The given pass-key is incorrect"})
		return
	}

	// читаем тело запроса
	body, err := io.ReadAll(r.Body)
	if err != nil {
		logger.Get().Error("qt", slog.String("err", err.Error()))
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: err.Error()})
		return
	}

	log.Info("qt", slog.String("qt request", string(body)))

	// парсим тело запроса
	var req rollback_request
	err = json.Unmarshal(body, &req)
	if err != nil {
		logger.Get().Error("qt", slog.String("err", err.Error()))
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: err.Error()})
		return
	}

	// получаем ID пользователя
	player_id := req.PlayerId
	// регулярное выражение для получения ID пользователя и валюты и бонуса
	re := regexp.MustCompile(`^(bonus)?([0-9]+)([a-zA-Z]{3,5})?$`)

	// получаем ID пользователя, валюту и бонус
	match := re.FindStringSubmatch(player_id)
	if match != nil {
		// устанавливаем флаг, что игра с бонусом
		isBonusGame = match[1] != ""
		// устанавливаем ID пользователя
		UserId = match[2]
		// если валюта указана, то устанавливаем её
		if match[3] != "" {
			currencyName = match[3]
		}
	}

	// если валюта USD, то преобразуем в USDT
	if currencyName == "USD" {
		currencyName = "USDT"
	}

	// преобразуем ID пользователя в число
	user_id, err := strconv.ParseInt(UserId, 10, 64)
	if err != nil {
		log.Error("qt", slog.String("qt cannot convert user id", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "cannot convert user id"})
		return
	}

	// получаем пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("qt", slog.String("qt user not found", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "user not found"})
		return
	}

	// получаем ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("sleg", slog.String("qt wrong currency name", currencyName))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "wrogn currency name"})
		return
	}

	// получаем кошелек пользователя
	wallet, err := user.GetWallet(curr)
	if err != nil {
		logger.Get().Error("qt wallet not found", slog.Int("currency", int(curr)))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "wallet not found"})
		return
	}

	// получаем сумму транзакции
	/////
	amount := req.Amount
	// amount, err := strconv.ParseFloat(a_p, 64)
	// if err != nil {
	// 	logger.Get().Error("qt bet amount parse error : " + err.Error())
	// 	w.WriteHeader(http.StatusBadRequest)
	// 	render.JSON(w, r, ErrorResponse{Code: "REQUEST_DECLINED", Message: "win amount parse error"})
	// 	return
	// }
	if amount < 0 {
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "REQUEST_DECLINED", Message: "amount < 0"})
		return
	}

	// проверяем, что транзакция уже обработана
	ex_trans, found := user.GetLastRefundsTransactions().Find(func(v *models.GameTransaction) bool { return v.BetTransactionId.String == req.TxnId })
	if found {
		w.Header().Set("Content-Type", "application/json")
		render.PlainText(w, r, ex_trans.RespString)
		return
	}

	// получаем игровую сессию
	gs := user.GetGameSession()

	// если игра с бонусом
	if isBonusGame {
		// если игровая сессия не nil
		if gs == nil {
			w.Header().Set("Content-Type", "application/json")
			render.PlainText(w, r, MakeTransactionResponse(0, ""))
			return
		}
		// получаем активный бонус
		bonus, err := gs.GetActiveBonus()
		// если бонус не nil и активен и ID транзакции бонуса совпадает с ID транзакции отмены
		if err == nil && bonus.IsActive() && bonus.GetBetTransactionID() == req.TxnId {
			// восстанавливаем резервную копию состояния бонуса
			bonus.RestoreState()
			// обнуляем ID транзакции бонуса
			bonus.SetBetTransactionID("")
			bonus.Actualize()
			// service.DefferBonusPersistence.Set(bonus.GetID(), bonus)
			//bonus.Save()
			bonus_id.Int64 = int64(bonus.GetUserBonus().ID)
			bonus_id.Valid = true
		}
		// если бонус не nil
		if bonus != nil {
			// получаем баланс бонуса
			balance = bonus.GetBalance()
		}
	} else {
		// ищем транзакцию для отмены
		tr_to_refund, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool {
			return v.TransactionID == req.BetId && !v.Refunded
		})
		// если транзакция найдена
		if found {
			// устанавливаем флаг, что транзакция отменена
			tr_to_refund.Refunded = true
			// добавляем деньги в кошелек
			wallet.AddMoney(amount)
		}
		// получаем баланс кошелька
		balance = wallet.GetBalance()
	}

	// генерируем ID транзакции
	gen_id := sql.NullString{}
	gen_id.String = uuid.New().String()
	gen_id.Valid = true

	// получаем текущую дату
	date := time.Now()

	// устанавливаем ID транзакции ставки
	var bet_tr_id sql.NullString
	bet_tr_id.String = req.BetId
	bet_tr_id.Valid = true

	// создаём транзакцию
	transaction := models.GameTransaction{
		GenID:            gen_id,
		UserId:           user.GetID(),
		Action:           "refund",
		Amount:           amount,
		Currency:         wallet.GetCurrencyName(),
		GameID:           req.BetId,
		SessionID:        r.Header.Get("Wallet-Session"),
		TransactionID:    req.TxnId,
		BetTransactionId: bet_tr_id,
		Type:             req.RoundId,
		System:           "qt",
		Date:             date.Format("2006-01-02 15:04:05"),
		BonusID:          bonus_id,
	}

	// добавляем транзакцию в базу
	service.Transactions.Add(&transaction)
	// добавляем транзакцию в список последних транзакций отмены
	user.GetLastRefundsTransactions().Push(&transaction)
	// сохраняем кошелек
	service.DefferWalletPersistence.Set(wallet.DbWallet.ID, wallet)
	// отправляем ответ
	w.Header().Set("Content-Type", "application/json")
	render.PlainText(w, r, MakeTransactionResponse(balance, transaction.GenID.String))
}

// PromotionStatus - обрабатывает статус промо (завершение внешних спинов)
func PromotionStatus(w http.ResponseWriter, r *http.Request) {
	log := logger.Get()

	// флаг, что игра с бонусом
	isBonusGame := false
	// ID пользователя
	var UserId string

	// логируем запрос
	log.Info("qt", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	log.Info("qt", slog.String("headers", fmt.Sprintf("%v\n", r.Header)))

	// проверяем, что ключ корректен
	if r.Header.Get("Pass-Key") != KEY {
		log.Error("qt", slog.String("LOGIN_FAILED", "The given pass-key is incorrect"))
		w.WriteHeader(http.StatusUnauthorized)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "The given pass-key is incorrect"})
		return
	}

	// читаем тело запроса
	body, err := io.ReadAll(r.Body)
	if err != nil {
		logger.Get().Error("qt", slog.String("err", err.Error()))
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: err.Error()})
		return
	}

	log.Info("qt", slog.String("qt request", string(body)))

	// парсим тело запроса
	var req promotion_status_request
	err = json.Unmarshal(body, &req)
	if err != nil {
		logger.Get().Error("qt", slog.String("err", err.Error()))
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: err.Error()})
		return
	}

	// получаем ID пользователя
	player_id := req.PlayerId
	// регулярное выражение для получения ID пользователя и валюты и бонуса
	re := regexp.MustCompile(`^(bonus)?([0-9]+)([a-zA-Z]{3,5})?$`)

	// получаем ID пользователя, валюту и бонус
	match := re.FindStringSubmatch(player_id)
	if match != nil {
		// устанавливаем флаг, что игра с бонусом
		isBonusGame = match[1] != ""
		// устанавливаем ID пользователя
		UserId = match[2]
	}

	// преобразуем ID пользователя в число
	user_id, err := strconv.ParseInt(UserId, 10, 64)
	if err != nil {
		log.Error("qt", slog.String("qt cannot convert user id", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "cannot convert user id"})
		return
	}

	// получаем пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("qt", slog.String("qt user not found", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "user not found"})
		return
	}

	// получаем ID игры
	gameId := r.PostFormValue("claimedGameId")
	if gameId == "" {
		gameId = "bonus"
	}

	// получаем игровую сессию
	gs := user.GetGameSession()

	// если статус промо завершен и игра с бонусом и игровая сессия не nil и активный бонус не nil
	if req.Status == "COMPLETED" && isBonusGame && gs != nil {
		bonus, err := gs.GetActiveBonus()
		// если бонус активен
		if err == nil && bonus.IsActive() {
			// получаем обработчик внешних спинов
			fsh, err := bonus.GetExtFsHandler("qt")
			if err == nil {
				// получаем сумму выплаты
				var amount float64
				if req.TotalPayout != 0 {
					amount = req.TotalPayout
				}
				// вызываем метод завершения внешних спинов
				fsh.ExternFreeSpinsCompleted(amount)
			}
		}
	}

	// отправляем ответ
	w.WriteHeader(http.StatusNoContent)
}

// Rewards - обрабатывает награду
func Rewards(w http.ResponseWriter, r *http.Request) {
	log := logger.Get()

	// ID пользователя
	var UserId string
	// валюта
	var currencyName string

	// логируем запрос
	log.Info("qt", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	log.Info("qt", slog.String("headers", fmt.Sprintf("%v\n", r.Header)))

	// проверяем, что ключ корректен
	if r.Header.Get("Pass-Key") != KEY {
		log.Error("qt", slog.String("LOGIN_FAILED", "The given pass-key is incorrect"))
		w.WriteHeader(http.StatusUnauthorized)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "The given pass-key is incorrect"})
		return
	}

	// читаем тело запроса
	body, err := io.ReadAll(r.Body)
	if err != nil {
		logger.Get().Error("qt", slog.String("err", err.Error()))
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: err.Error()})
		return
	}

	log.Info("qt", slog.String("qt request", string(body)))

	// парсим тело запроса
	var req rewards_request
	err = json.Unmarshal(body, &req)
	if err != nil {
		logger.Get().Error("qt", slog.String("err", err.Error()))
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: err.Error()})
		return
	}

	// получаем ID пользователя
	player_id := req.PlayerId
	// регулярное выражение для получения ID пользователя и валюты и бонуса
	re := regexp.MustCompile(`^(bonus)?([0-9]+)([a-zA-Z]{3,5})?$`)

	// получаем ID пользователя, валюту и бонус
	match := re.FindStringSubmatch(player_id)
	if match != nil {
		// устанавливаем ID пользователя
		UserId = match[2]
		// если валюта указана, то устанавливаем её
		if match[3] != "" {
			currencyName = match[3]
		}
	}

	// преобразуем ID пользователя в число
	user_id, err := strconv.ParseInt(UserId, 10, 64)
	if err != nil {
		log.Error("qt", slog.String("qt cannot convert user id", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "cannot convert user id"})
		return
	}

	// получаем пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("qt", slog.String("qt user not found", UserId))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "user not found"})
		return
	}

	// проверяем, что транзакция уже обработана
	ex_trans, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool { return v.TransactionID == r.PostFormValue("txnId") })
	if found {
		w.Header().Set("Content-Type", "application/json")
		render.PlainText(w, r, ex_trans.RespString)
		return
	}

	// получаем ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("sleg", slog.String("qt wrong currency name", currencyName))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "wrogn currency name"})
		return
	}

	// получаем кошелек пользователя
	wallet, err := user.GetWallet(curr)
	if err != nil {
		logger.Get().Error("qt wallet not found", slog.Int("currency", int(curr)))
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "LOGIN_FAILED", Message: "wallet not found"})
		return
	}

	// получаем сумму награды
	amount := req.Amount
	// amount, err := strconv.ParseFloat(a_p, 64)
	// if err != nil {
	// 	logger.Get().Error("qt bet amount parse error : " + err.Error())
	// 	w.WriteHeader(http.StatusBadRequest)
	// 	render.JSON(w, r, ErrorResponse{Code: "REQUEST_DECLINED", Message: "win amount parse error"})
	// 	return
	// }
	if amount < 0 {
		w.WriteHeader(http.StatusBadRequest)
		render.JSON(w, r, ErrorResponse{Code: "REQUEST_DECLINED", Message: "amount < 0"})
		return
	}

	// добавляем деньги в кошелек
	wallet.AddMoney(amount)
	// добавляем тотал вин в кошелек
	wallet.AddTotalWin(amount)

	// генерируем ID транзакции
	gen_id := sql.NullString{}
	gen_id.String = uuid.New().String()
	gen_id.Valid = true
	// получаем текущую дату
	date := time.Now()

	// создаём транзакцию
	transaction := models.GameTransaction{
		GenID:         gen_id,
		UserId:        user.GetID(),
		Action:        "win",
		Amount:        amount,
		Currency:      wallet.GetCurrencyName(),
		GameID:        req.RewardTitle,
		SessionID:     r.Header.Get("Wallet-Session"),
		TransactionID: req.TxnId,
		Type:          req.RewardType,
		System:        "qt",
		Date:          date.Format("2006-01-02 15:04:05"),
	}

	resp := MakeTransactionResponse(wallet.GetBalance(), gen_id.String)
	transaction.RespString = resp

	// добавляем транзакцию в базу
	service.Transactions.Add(&transaction)
	// добавляем транзакцию в список последних транзакций
	user.GetLastTransactions().Push(&transaction)
	//wallet.Save()
	service.DefferWalletPersistence.Set(wallet.DbWallet.ID, wallet)

	render.PlainText(w, r, resp)
	w.Header().Set("Content-Type", "application/json")
}
