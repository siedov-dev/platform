package b2bslots

import (
	"database/sql"
	"encoding/json"
	"log/slog"
	"strconv"
	"time"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/msgbroker"
	"user-manager/internal/service"
	"user-manager/internal/service/container"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"

	"github.com/google/uuid"
)

func Credit(jsonRequest string) string {
	var req CreditRequest
	err := json.Unmarshal([]byte(jsonRequest), &req)
	if err != nil {
		return creditErrorResponse(ErrGeneric, "Invalid request format")
	}

	log := logger.Get()
	// получение ID игрока
	player_id := req.Data.UserID
	// проверка, является ли игра бонусной
	isBonusGame := false
	// валюта
	var currencyName string
	// баланс бонуса
	var bonus_balance float64
	// ID бонуса
	var bonus_id sql.NullInt64
	// баланс
	var balance float64

	currencyName = req.Data.Currency

	user_id, err := strconv.ParseInt(player_id, 10, 64)
	if err != nil {
		log.Error("b2bslots", slog.String("method", "credit"), slog.String("err", "invalid user id"), slog.String("player_id", player_id))
		return creditErrorResponse(ErrGeneric, "Invalid user ID")
	}
	// получение пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("b2bslots", slog.String("method", "credit"), slog.String("err", "user not found"), slog.String("player_id", player_id))
		return creditErrorResponse(ErrUserNotFound, "User not found")
	}
	// получение игровой сессии
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		log.Error("b2bslots", slog.String("method", "credit"), slog.String("err", "session not found"), slog.String("player_id", player_id))
		return creditErrorResponse(ErrGeneric, "Session not found")
	}
	if *gs.Stats.SessionID != req.Data.UserGameToken {
		log.Error("b2bslots", slog.String("method", "credit"), slog.String("err", "invalid session"), slog.String("player_id", player_id), slog.String("session", req.Data.UserGameToken))
		return creditErrorResponse(ErrTokenNotFound, "Invalid session")
	}
	// получение ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("b2bslots", slog.String("method", "credit"), slog.String("err", "currency not found"), slog.String("currency", currencyName))
		return creditErrorResponse(ErrGeneric, "Currency not found")
	}
	// получение кошелька пользователя
	wallet, err := user.GetWallet(curr)
	if err != nil {
		log.Error("b2bslots", slog.String("method", "credit"), slog.String("err", "wallet not found"), slog.String("player_id", player_id), slog.String("currency", currencyName))
		return creditErrorResponse(ErrGeneric, "Wallet not found")
	}

	bonus, err := gs.GetActiveBonus()
	if err == nil && bonus != nil {
		isBonusGame = true
		bonus_balance = bonus.GetBalance()
	}
	game_currency := currencyName

	////
	// получение суммы выигрыша
	// a_p := req.Data.CreditAmount
	// amount, err := strconv.ParseFloat(a_p, 64)
	// if err != nil {
	// 	logger.Get().Error("b2bslots bat amount parse error : " + err.Error())
	// 	return creditErrorResponse(ErrGeneric, "Invalid amount")
	// }
	amount := req.Data.CreditAmount
	if amount < 0 {
		return creditErrorResponse(ErrGeneric, "Invalid amount")
	}
	// проверка, была ли уже обработана транзакция
	ex_trans, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool { return v.TransactionID == req.Data.TransactionID })
	if found {
		return ex_trans.RespString
	}

	if isBonusGame && bonus.GetSpinsAvailable() > 0 && req.Data.CreditType != "freeRounds" {
		log.Error("b2bslots", slog.String("method", "credit"), slog.String("err", "user has free spins"), slog.String("player_id", player_id))
		isBonusGame = false
		gs.DropSessionBonus()
		bonus = nil
		bonus_id = sql.NullInt64{Valid: false}
	}

	// если игра бонусная
	if isBonusGame {
		// если нет активного бонуса - ошибка
		if bonus == nil {
			logger.Get().Error("b2bslots win no active bonus")
			return creditErrorResponse(ErrGeneric, "No active bonus")
		}
		// установка ID бонуса
		bonus_id.Int64 = int64(bonus.GetID())
		bonus_id.Valid = true
		// если бонус не активный - ошибка
		if !bonus.IsActive() {
			logger.Get().Error("bonus is not active")
			return creditErrorResponse(ErrGeneric, "Bonus is not active")
		}
		// если бонусный код указан - проверка, является ли бонус внешним фриспином
		if req.Data.CreditType == "freeRounds" {

			// // получение обработчика внешних фриспинов
			// extfs, err := bonus.GetExtFsHandler("b2bslots")
			// if err != nil {
			// 	logger.Get().Error("b2bslots cant get ext fs halder")
			// 	return creditErrorResponse(ErrGeneric, "Cant get external fs handler")
			// }
			// // вызов метода на внешние фриспины
			// _, err = extfs.ExternFreeSpinWin(amount)
			// if err != nil {
			// 	logger.Get().Error("b2bslots fs error : " + err.Error())
			// 	return creditErrorResponse(ErrGeneric, "INTERNAL_ERROR")
			// }
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
		GameID:        strconv.FormatInt(req.Data.GameCode, 10),
		SessionID:     *gs.Stats.SessionID,
		TransactionID: req.Data.TransactionID,
		Type:          req.Data.CreditType,
		System:        "b2bslots",
		Date:          date.Format("2006-01-02 15:04:05"),
		BonusID:       bonus_id,
		Refunded:      false,
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
				"b2bslots",
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
	if isBonusGame {
		balance = bonus_balance
	}
	// создание ответа
	resp := creditBalanceResponse(user.GetID(), user.GetLogin(), balance, game_currency, *gs.Stats.SessionID, req.Data.TransactionID)
	// установка ответа в транзакцию (для повторной отправки в случае повторного вызова idenpotent)
	transaction.RespString = resp
	// добавление транзакции в базу
	service.Transactions.Add(&transaction)
	// добавление транзакции в список последних транзакций
	user.GetLastTransactions().Push(&transaction)
	// сохранение кошелька
	service.DefferWalletPersistence.Set(wallet.DbWallet.ID, wallet)
	// отправка ответа
	return resp
}

type CreditRequest struct {
	API  string            `json:"api"`
	Data CreditRequestData `json:"data"`
}

type CreditRequestData struct {
	UserID        string  `json:"user_id"`
	UserIP        string  `json:"user_ip"`
	UserGameToken string  `json:"user_game_token"`
	Currency      string  `json:"currency"`
	TurnID        int64   `json:"turn_id"`
	TransactionID string  `json:"transaction_id"`
	GameCode      int64   `json:"game_code"`
	GameName      string  `json:"game_name"`
	CreditAmount  float64 `json:"credit_amount"`
	CreditType    string  `json:"credit_type"`
	RoundID       int64   `json:"round_id"`
}

type CreditResponse struct {
	API      string             `json:"api"`
	Success  bool               `json:"success"`
	Response CreditResponseData `json:"answer"`
}

type CreditResponseData struct {
	OperatorID       int64  `json:"operator_id"`
	TransactionID    string `json:"transaction_id"`
	UserID           string `json:"user_id"`
	UserNickname     string `json:"user_nickname"`
	Balance          string `json:"balance"`
	BonusBalance     string `json:"bonus_balance"`
	BonusAmount      string `json:"bonus_amount"`
	GameToken        string `json:"game_token"`
	ErrorCode        int64  `json:"error_code"`
	ErrorDescription string `json:"error_description"`
	Currency         string `json:"currency"`
	Timestamp        string `json:"timestamp"`
}

func creditErrorResponse(errCode int64, errDesc string) string {
	var now = time.Now().Unix()
	resp := CreditResponse{
		API:     "do-credit-user-ingame",
		Success: true,
		Response: CreditResponseData{
			OperatorID:       operator_id,
			ErrorCode:        errCode,
			ErrorDescription: errDesc,
			Timestamp:        strconv.FormatInt(now, 10),
		},
	}
	respJson, _ := json.Marshal(resp)
	return string(respJson)
}

func creditBalanceResponse(userID stypes.UserID, userNickname string, balance float64, currency string, gameToken string, transaction_id string) string {
	var now = time.Now().Unix()
	resp := CreditResponse{
		API:     "do-credit-user-ingame",
		Success: true,
		Response: CreditResponseData{
			OperatorID:    operator_id,
			ErrorCode:     0,
			Timestamp:     strconv.FormatInt(now, 10),
			UserID:        strconv.FormatInt(int64(userID), 10),
			UserNickname:  userNickname,
			Balance:       strconv.FormatFloat(balance, 'f', 5, 64),
			BonusBalance:  "0.00000",
			BonusAmount:   "0.00000",
			Currency:      currency,
			GameToken:     gameToken,
			TransactionID: transaction_id,
		},
	}
	respJson, _ := json.Marshal(resp)
	return string(respJson)
}
