package b2bslots

import (
	"database/sql"
	"encoding/json"
	"log/slog"
	"strconv"
	"time"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/service"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"

	"github.com/google/uuid"
)

// Betting from user balance
func Debit(jsonRequest string) string {
	var req DebitRequest
	err := json.Unmarshal([]byte(jsonRequest), &req)
	if err != nil {
		return debitErrorResponse(ErrGeneric, "Invalid request format")
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
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "invalid user id"), slog.String("player_id", player_id))
		return debitErrorResponse(ErrGeneric, "Invalid user ID")
	}
	// получение пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "user not found"), slog.String("player_id", player_id))
		return debitErrorResponse(ErrUserNotFound, "User not found")
	}
	// получение игровой сессии
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "session not found"), slog.String("player_id", player_id))
		return debitErrorResponse(ErrGeneric, "Session not found")
	}
	if *gs.Stats.SessionID != req.Data.UserGameToken {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "invalid session"), slog.String("player_id", player_id), slog.String("session", req.Data.UserGameToken))
		return debitErrorResponse(ErrTokenNotFound, "Invalid session")
	}
	// получение ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "currency not found"), slog.String("currency", currencyName))
		return debitErrorResponse(ErrGeneric, "Currency not found")
	}
	// получение кошелька пользователя
	wallet, err := user.GetWallet(curr)
	if err != nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "wallet not found"), slog.String("player_id", player_id), slog.String("currency", currencyName))
		return debitErrorResponse(ErrGeneric, "Wallet not found")
	}

	bonus, err := gs.GetActiveBonus()
	if err == nil && bonus != nil {
		isBonusGame = true
		bonus_balance = bonus.GetBalance()
	}
	game_currency := currencyName
	////
	// получение суммы ставки
	// a_p := req.Data.DebitAmount
	// amount, err := strconv.ParseFloat(a_p, 64)
	// if err != nil || amount < 0 {
	// 	log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "invalid amount"), slog.String("amount", a_p))
	// 	return debitErrorResponse(ErrGeneric, "Invalid amount")
	// }
	amount := req.Data.DebitAmount
	// проверка, была ли уже обработана транзакция
	ex_trans, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool { return v.TransactionID == req.Data.TransactionID })
	if found {
		return ex_trans.RespString
	}

	// если запрос баланса
	if req.Data.DebitType == "getBalance" {
		if isBonusGame {
			balance = bonus_balance
		} else {
			balance = wallet.GetBalance()
		}
		return debitBalanceResponse(user.GetID(), user.GetLogin(), balance, game_currency, *gs.Stats.SessionID, req.Data.TransactionID)
	}

	if isBonusGame && bonus.GetSpinsAvailable() > 0 && req.Data.DebitType != "freeRounds" {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "user has free spins"), slog.String("player_id", player_id))
		isBonusGame = false
		gs.DropSessionBonus()
		bonus = nil
		bonus_id = sql.NullInt64{Valid: false}
	}

	// если игра бонусная
	if isBonusGame {
		// если бонус не найден - ошибка
		if bonus == nil {
			logger.Get().Error("b2bslots bet no active bonus")
			return debitErrorResponse(ErrGeneric, "No active bonus")
		}
		// установка ID бонуса
		bonus_id.Int64 = int64(bonus.GetID())
		bonus_id.Valid = true
		// если бонус не активен - ошибка
		if !bonus.IsActive() {
			logger.Get().Error("b2bslots bonus is not active")
			return debitErrorResponse(ErrGeneric, "Bonus is not active")
		}
		// проверка, была ли уже отменена транзакция
		_, found := user.GetLastRefundsTransactions().Find(func(v *models.GameTransaction) bool {
			return v.BetTransactionId.String == req.Data.TransactionID
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
				GameID:        strconv.FormatInt(req.Data.GameCode, 10),
				SessionID:     *gs.Stats.SessionID,
				TransactionID: req.Data.TransactionID,
				Type:          req.Data.DebitType,
				System:        "b2bslots",
				Date:          date.Format("2006-01-02 15:04:05"),
				BonusID:       bonus_id,
				Refunded:      true,
			}

			resp := debitBalanceResponse(user.GetID(), user.GetLogin(), 0, game_currency, *gs.Stats.SessionID, req.Data.TransactionID)
			transaction.RespString = resp

			service.Transactions.Add(&transaction)
			return resp
		}

		// установка ID транзакции
		bonus.SetBetTransactionID(req.Data.TransactionID)
		bonus.SetBackupState(bonus.GetUserBonus())

		// если бонусный код указан - обработка внешних фриспинов
		if req.Data.DebitType == "freeRounds" {

			// // получение обработчика внешних фриспинов
			// extfs, err := bonus.GetExtFsHandler("b2bslots")
			// if err != nil {
			// 	logger.Get().Error("b2bslots cant get ext fs halder")
			// 	return debitErrorResponse(ErrGeneric, "Cant get external fs handler")
			// }
			// // обработка внешних фриспинов
			// _, err = extfs.ExternFreeSpinBet(amount)
			// if err != nil {
			// 	logger.Get().Error("b2bslots fs error : " + err.Error())
			// 	return debitErrorResponse(ErrGeneric, "INTERNAL_ERROR")
			// }
		} else {

			// Снятие средств с бонуса
			_, ra, ba, err := bonus.RemMoney(amount) // снимаем деньги с бонуса
			if err != nil {
				logger.Get().Error("b2bslots INSUFFICIENT_FUNDS")
				return debitErrorResponse(ErrInsufficient, "INSUFFICIENT_FUNDS")
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
		gs.SetRealWin(win) // установка реального выигрыша при выигрыше бонуса (win - выигрыш)
		// получение баланса бонуса
		bonus_balance = bonus.GetBalance()
	} else {
		// Обычная игра
		// проверка, была ли уже отменена транзакция
		_, found := user.GetLastRefundsTransactions().Find(func(v *models.GameTransaction) bool {
			return v.BetTransactionId.String == req.Data.TransactionID
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
				GameID:        strconv.FormatInt(req.Data.GameCode, 10),
				SessionID:     *gs.Stats.SessionID,
				TransactionID: req.Data.TransactionID,
				Type:          req.Data.DebitType,
				System:        "b2bslots",
				Date:          date.Format("2006-01-02 15:04:05"),
				BonusID:       bonus_id,
				Refunded:      true,
			}

			resp := debitBalanceResponse(user.GetID(), user.GetLogin(), 0, game_currency, *gs.Stats.SessionID, req.Data.TransactionID)
			transaction.RespString = resp

			service.Transactions.Add(&transaction)
			return resp
		}
		// Снятие средств с кошелька
		_, err = wallet.RemMoney(amount)
		if err != nil {
			logger.Get().Error("b2bslots INSUFFICIENT_FUNDS")
			return debitErrorResponse(ErrInsufficient, "INSUFFICIENT_FUNDS")
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
		GameID:        strconv.FormatInt(req.Data.GameCode, 10),
		SessionID:     *gs.Stats.SessionID,
		TransactionID: req.Data.TransactionID,
		Type:          req.Data.DebitType,
		System:        "b2bslots",
		Date:          date.Format("2006-01-02 15:04:05"),
		BonusID:       bonus_id,
		Refunded:      false,
	}

	if isBonusGame {
		balance = bonus_balance
	}
	// создание ответа
	resp := debitBalanceResponse(user.GetID(), user.GetLogin(), balance, game_currency, *gs.Stats.SessionID, req.Data.TransactionID)
	transaction.RespString = resp
	// добавление транзакции в базу
	service.Transactions.Add(&transaction)
	// установка последней транзакции ставки (для привязки к выигрышу)
	user.SetLastBetTransaction(&transaction)
	// добавление транзакции в список последних транзакций
	user.GetLastTransactions().Push(&transaction)
	// сохранение кошелька
	service.DefferWalletPersistence.Set(wallet.DbWallet.ID, wallet)
	// отправка ответа
	return resp
}

type DebitRequest struct {
	API  string           `json:"api"`
	Data DebitRequestData `json:"data"`
}

type DebitRequestData struct {
	UserID        string  `json:"user_id"`
	UserIP        string  `json:"user_ip"`
	UserGameToken string  `json:"user_game_token"`
	Currency      string  `json:"currency"`
	TurnID        int64   `json:"turn_id"`
	TransactionID string  `json:"transaction_id"`
	GameCode      int64   `json:"game_code"`
	GameName      string  `json:"game_name"`
	DebitAmount   float64 `json:"debit_amount"`
	DebitType     string  `json:"debit_type"`
	RoundID       int64   `json:"round_id"`
}

type DebitResponse struct {
	API      string            `json:"api"`
	Success  bool              `json:"success"`
	Response DebitResponseData `json:"answer"`
}

type DebitResponseData struct {
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

func debitErrorResponse(errCode int64, errDesc string) string {
	var now = time.Now().Unix()
	resp := DebitResponse{
		API:     "do-debit-user-ingame",
		Success: true,
		Response: DebitResponseData{
			OperatorID:       operator_id,
			ErrorCode:        errCode,
			ErrorDescription: errDesc,
			Timestamp:        strconv.FormatInt(now, 10),
		},
	}
	respJson, _ := json.Marshal(resp)
	return string(respJson)
}

func debitBalanceResponse(userID stypes.UserID, userNickname string, balance float64, currency string, gameToken string, transaction_id string) string {
	var now = time.Now().Unix()
	resp := DebitResponse{
		API:     "do-debit-user-ingame",
		Success: true,
		Response: DebitResponseData{
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
