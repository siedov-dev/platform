package slotegrator

import (
	"database/sql"
	"errors"
	"fmt"
	"log/slog"
	"net/http"
	"regexp"
	"strconv"
	"time"
	"user-manager/internal/config"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/msgbroker"
	"user-manager/internal/server/requests"
	"user-manager/internal/service"
	"user-manager/internal/service/container"
	slegresponses "user-manager/internal/slotegrator/sleg_responses"
	"user-manager/internal/slotegrator/slegapi"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"

	"github.com/google/uuid"
)

type Endpoint struct {
	MerchantID string
	Key        string
}

// Make создает новый экземпляр Endpoint для работы с Slotegrator
// документация: в папке docs
func Make() Endpoint {
	return Endpoint{
		MerchantID: config.Get().Slotegrator.MerchantID,
		Key:        config.Get().Slotegrator.Key,
	}
}

// CheckSign проверяет подпись запроса
func (ep *Endpoint) CheckSign(r *http.Request) bool {
	var headers map[string]string = make(map[string]string, 3)

	headers["X-Merchant-Id"] = ep.MerchantID //r.Header.Get("X-Merchant-Id")
	headers["X-Timestamp"] = r.Header.Get("X-Timestamp")
	headers["X-Nonce"] = r.Header.Get("X-Nonce")

	postData := make(map[string]string)
	for key, values := range r.PostForm {
		// берем только первое значение (может быть список)
		postData[key] = values[0]
	}

	sign := slegapi.Sign(postData, headers)
	logger.Get().Info("sleg", slog.String("sign", sign))

	return r.Header.Get("X-Sign") == sign
}

// Balance возвращает баланс пользователя
func (ep *Endpoint) Balance(user *service.User, isBonusGame bool, currency currency.CurrencyID) (slegresponses.BalanceResponse, error) {
	// Получаем игровую сессию
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		logger.Get().Error("sleg", slog.Int64("no game session", user.GetID()))
		return slegresponses.BalanceResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("no game session found"))
	}
	if isBonusGame {
		bonus, err := gs.GetActiveBonus()
		if err != nil {
			return slegresponses.BalanceResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err)
		}
		if bonus == nil {
			return slegresponses.BalanceResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("no active bonus"))
		}

		return slegresponses.MakeBalanceResponse(bonus.GetBalance()), nil
	}

	wallet, err := user.GetWallet(currency)
	if err != nil {
		return slegresponses.BalanceResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err)
	}
	return slegresponses.MakeBalanceResponse(wallet.GetBalance()), nil
}

// Bet обрабатывает ставку пользователя
func (ep *Endpoint) Bet(r *http.Request, user *service.User, isBonusGame bool, currency currency.CurrencyID) (slegresponses.BetResponse, error) {
	// Получаем игровую сессию
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		logger.Get().Error("sleg", slog.Int64("no game session", user.GetID()))
		return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("no game session found"))
	}

	var bonus_id sql.NullInt64 // ID бонуса
	var balance float64        // баланс

	// получаем кошелек пользователя
	wallet, err := user.GetWallet(currency)
	if err != nil {
		return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err)
	}

	// получаем сумму ставки
	a_p := r.PostFormValue("amount")
	amount, err := strconv.ParseFloat(a_p, 64)
	if err != nil {
		return slegresponses.BetResponse{}, err
	}
	if amount < 0 {
		return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("wrong amount value"))
	}

	// проверяем, была ли уже обработана транзакция
	ex_trans, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool { return v.TransactionID == r.PostFormValue("transaction_id") })
	if found {
		return slegresponses.MakeBetResponse(wallet.GetBalance(), ex_trans.GenID.String), nil
	}

	// если ставка на фриспины, проверяем, что это бонусная игра
	if r.PostFormValue("type") == "freespin" && r.PostFormValue("freespin_id") != "" {
		if !isBonusGame {
			return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("not a bonus game"))
		}
	}

	// если ставка на бонус, обрабатываем бонусную ставку
	if isBonusGame {
		bonus, err := gs.GetActiveBonus() // получаем активный бонус
		if err != nil {
			return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err)
		}

		if bonus == nil {
			return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("no active bonus"))
		}
		bonus_id.Int64 = bonus.GetID()
		bonus_id.Valid = true

		// если бонус не активен, возвращаем ошибку
		if !bonus.IsActive() {
			return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("bonus is not active"))
		}

		// проверяем, была ли ставка отменена
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
				GameID:        r.PostFormValue("game_uuid"),
				SessionID:     r.PostFormValue("session_id"),
				TransactionID: r.PostFormValue("transaction_id"),
				Type:          r.PostFormValue("type"),
				System:        "sleg",
				Date:          date.Format("2006-01-02 15:04:05"),
				BonusID:       bonus_id,
				Refunded:      true,
			}

			service.Transactions.Add(&transaction)
			return slegresponses.MakeBetResponse(bonus.GetBalance(), transaction.GenID.String), nil
		}

		bonus.SetBetTransactionID(r.PostFormValue("transaction_id")) // сохраняем ID транзакции
		bonus.SetBackupState(bonus.GetUserBonus())                   // сохраняем состояние бонуса

		// если внешние фриспины, обрабатываем ставку на фриспины
		if r.PostFormValue("type") == "freespin" && r.PostFormValue("freespin_id") != "" {
			// проверяем, что ID фриспина совпадает с ID бонуса
			re := regexp.MustCompile(`(d)?` + fmt.Sprintf("%d", bonus.GetID()))
			match := re.FindStringSubmatch(r.PostFormValue("freespin_id"))
			if len(match) == 0 {
				return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("wrong freespin id"))
			}

			extfs, err := bonus.GetExtFsHandler("sleg") // получаем обработчик внешнего фриспина
			if err != nil {
				return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err)
			}
			_, err = extfs.ExternFreeSpinBet(amount) // обрабатываем ставку на фриспины
			if err != nil {
				return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err)
			}
		} else {

			_, ra, ba, err := bonus.RemMoney(amount) // снимаем деньги с бонуса
			if err != nil {
				return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INSUFFICIENT_FUNDS", errors.New("INSUFFICIENT FUNDS"))
			}
			gs.AddBonusBet(ba) // добавляем бонусную ставку в тоталы по бонусам
			if ra > 0 {
				gs.AddTotalBet(ra)     // добавляем реальную ставку в тоталы по игре
				wallet.AddTotalBet(ra) // добавляем реальную ставку в тоталы по кошельку
			}
		}

		// обновляем бонус
		win := bonus.Actualize()
		//bonus.Save()
		// сохраняем бонус
		//service.DefferBonusPersistence.Set(bonus.GetID(), bonus)
		// если бонус выиграл, то устанавливаем выигрыш
		gs.SetRealWin(win)

		balance = bonus.GetBalance()
	} else {
		// если не бонусная игра, обрабатываем ставку на обычную игру
		// проверяем, была ли ставка отменена
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
				GameID:        r.PostFormValue("game_uuid"),
				SessionID:     r.PostFormValue("session_id"),
				TransactionID: r.PostFormValue("transaction_id"),
				Type:          r.PostFormValue("type"),
				System:        "sleg",
				Date:          date.Format("2006-01-02 15:04:05"),
				BonusID:       bonus_id,
				Refunded:      true,
			}

			service.Transactions.Add(&transaction)
			return slegresponses.MakeBetResponse(wallet.GetBalance(), transaction.GenID.String), nil
		}

		_, err = wallet.RemMoney(amount) // снимаем деньги с кошелька
		if err != nil {
			return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INSUFFICIENT_FUNDS", errors.New("INSUFFICIENT FUNDS"))
		}

		wallet.AddTotalBet(amount) // добавляем ставку в тоталы по кошельку
		if wallet.GetLockedMoney() > 0 {
			wallet.RemLockedMoney(amount * (float64(gs.GameStartData.WagerPrc) / 100))
		}
		gs.AddTotalBet(amount) // добавляем ставку в тоталы по игре

		// если есть бонус на ставку, обрабатываем его
		if bw, err := gs.GetBetWagerBonus(); err == nil {
			bw.OnUserBet(amount) // обрабатываем ставку на бонус
			//bw.Save()
			//service.DefferBonusPersistence.Set(bw.GetID(), bw) // сохраняем бонус
		}

		balance = wallet.GetBalance() // получаем баланс кошелька
	}

	// генерируем ID транзакции
	gen_id := sql.NullString{}
	gen_id.String = uuid.New().String()
	gen_id.Valid = true

	date := time.Now()

	// создаем транзакцию
	transaction := models.GameTransaction{
		GenID:         gen_id,
		UserId:        user.GetID(),
		Action:        "bet",
		Amount:        amount,
		Currency:      wallet.GetCurrencyName(),
		GameID:        r.PostFormValue("game_uuid"),
		SessionID:     r.PostFormValue("session_id"),
		TransactionID: r.PostFormValue("transaction_id"),
		Type:          r.PostFormValue("type"),
		System:        "sleg",
		Date:          date.Format("2006-01-02 15:04:05"),
		BonusID:       bonus_id,
	}

	service.Transactions.Add(&transaction)        // добавляем транзакцию в базу
	user.SetLastBetTransaction(&transaction)      // сохраняем транзакцию последней ставки
	user.GetLastTransactions().Push(&transaction) // добавляем транзакцию в список транзакций
	//wallet.Save()
	service.DefferWalletPersistence.Set(wallet.DbWallet.ID, wallet) // сохраняем кошелек

	// возвращаем ответ
	return slegresponses.MakeBetResponse(balance, transaction.GenID.String), nil
}

// Win обрабатывает выигрыш пользователя
func (ep *Endpoint) Win(r *http.Request, user *service.User, isBonusGame bool, currency currency.CurrencyID) (slegresponses.BetResponse, error) {
	// Получаем игровую сессию
	// Выигрышы могут быть вне игровой сессии, поэтому проверка на сессию не обязательна
	gs := user.GetGameSession()

	var bonus_id sql.NullInt64 // ID бонуса
	var balance float64        // баланс

	wallet, err := user.GetWallet(currency) // получаем кошелек пользователя
	if err != nil {
		return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err)
	}

	// получаем сумму выигрыша
	a_p := r.PostFormValue("amount")
	amount, err := strconv.ParseFloat(a_p, 64)
	if err != nil {
		return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err)
	}
	if amount < 0 {
		return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("wrong amount value"))
	}

	// тип выигрыша
	win_type := r.PostFormValue("type")

	// проверяем, была ли транзакция уже обработана
	ex_trans, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool { return v.TransactionID == r.PostFormValue("transaction_id") })
	if found {
		return slegresponses.MakeBetResponse(wallet.GetBalance(), ex_trans.GenID.String), nil
	}

	// если ставка на фриспины, проверяем, что это бонусная игра
	if win_type == "freespin" && r.PostFormValue("freespin_id") != "" {
		if !isBonusGame {
			return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("not a bonus game"))
		}
	}

	// если ставка на бонус, обрабатываем бонусную ставку
	if isBonusGame {
		if gs == nil {
			logger.Get().Error("sleg", slog.Int64("no game session for bonus win", user.GetID()))
			balance = 0 // если нет игровой сессии, то баланс будет 0
		} else {

			bonus, err := gs.GetActiveBonus()
			if err != nil {
				return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err)
			}

			if bonus == nil {
				return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("no active bonus"))
			}
			bonus_id.Int64 = bonus.GetID()
			bonus_id.Valid = true

			// если бонус не активен, возвращаем ошибку
			if !bonus.IsActive() {
				return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("bonus is not active"))
			}

			// если внешние фриспины, обрабатываем выигрыш на фриспинах
			if win_type == "freespin" && r.PostFormValue("freespin_id") != "" {
				// проверяем, что ID фриспина совпадает с ID бонуса
				re := regexp.MustCompile(`(d)?` + fmt.Sprintf("%d", bonus.GetID()))
				match := re.FindStringSubmatch(r.PostFormValue("freespin_id"))
				if len(match) == 0 {
					return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("wrong freespin id"))
				}

				extfs, err := bonus.GetExtFsHandler("sleg") // получаем обработчик внешнего фриспина
				if err != nil {
					return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err)
				}
				_, err = extfs.ExternFreeSpinWin(amount) // обрабатываем выигрыш на фриспинах
				if err != nil {
					return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err)
				}
				if r.PostFormValue("quantity") == "0" {
					extfs.ExternFreeSpinsCompleted(amount)
				}
			} else {
				// если это обычный выигрыш на бонусе, добавляем выигрыш на бонус
				// исключаем выигрыши на фриспинах, которые обрабатываются отдельно
				// и выигрыши турниров, промо и т.п.
				if win_type == "win" {
					_, ra, ba := bonus.AddMoney(amount) // добавляем выигрыш на бонус
					gs.AddBonusWin(ba)                  // добавляем выигрыш на бонус в тоталы по бонусам
					if ra > 0 {
						gs.AddTotalWin(ra)     // добавляем реальный выигрыш в тоталы по игре
						wallet.AddTotalWin(ra) // добавляем реальный выигрыш в тоталы по кошельку
					}
				}
			}

			// win - реальный выигрыш на бонусе
			win := bonus.Actualize() // обновляем бонус
			//err := bonus.Save()
			// service.DefferBonusPersistence.Set(bonus.GetID(), bonus) // сохраняем бонус
			gs.SetRealWin(win) // устанавливаем выигрыш

			balance = bonus.GetBalance() // получаем баланс бонуса
		}
	} else {
		wallet.AddMoney(amount)    // добавляем выигрыш на кошелек
		wallet.AddTotalWin(amount) // добавляем выигрыш в тоталы по кошельку
		if gs != nil {
			gs.AddTotalWin(amount) // добавляем выигрыш в тоталы по игре

			// если есть бонус на реальную игру, обрабатываем его
			if bw, err := gs.GetBetWagerBonus(); err == nil {
				bw.OnUserWin(amount) // обрабатываем выигрыш на бонусе
				//bw.Save()
				//service.DefferBonusPersistence.Set(bw.GetID(), bw) // сохраняем бонус
			}
		} else {
			gd := stypes.GameData{
				System:     "sleg",
				GameID:     r.PostFormValue("game_uuid"),
				GameName:   "",
				Provider:   "",
				CurrencyID: wallet.Currency,
			}
			srq := requests.StartGameRequest{
				UserID:     user.GetID(),
				GameId:     r.PostFormValue("game_uuid"),
				ProviderId: "sleg",
				GameGroup:  "",
				GameData:   gd,
				GameMode:   0,
				SessionId:  "winoutofsession", // уникальный ID сессии для выигрыша вне сессии
			}

			gs, err = gs.CreateGameSession(user.GetID(), wallet.GetCurrencyName(), srq, gd) // создаем игровую сессию вне сессии
			if err != nil {
				logger.Get().Error("sleg", slog.Int64("error creating game session for win", user.GetID()), slog.Any("error", err))
			} else {
				now := time.Now()
				gs.AddTotalWin(amount) // добавляем выигрыш в тоталы по игре
				gs.Stats.LogoutBalance = amount
				gs.Stats.BalanceDiff = amount
				gs.Stats.LogoutDate = &now // обновляем время выхода из сессии
				gs.Save()
			}
		}

		balance = wallet.GetBalance() // получаем баланс кошелька
	}

	// генерируем ID транзакции
	gen_id := sql.NullString{}
	gen_id.String = uuid.New().String()
	gen_id.Valid = true

	date := time.Now()

	// создаем транзакцию
	transaction := models.GameTransaction{
		GenID:         gen_id,
		UserId:        user.GetID(),
		Action:        "win",
		Amount:        amount,
		Currency:      wallet.GetCurrencyName(),
		GameID:        r.PostFormValue("game_uuid"),
		SessionID:     r.PostFormValue("session_id"),
		TransactionID: r.PostFormValue("transaction_id"),
		Type:          win_type,
		System:        "sleg",
		Date:          date.Format("2006-01-02 15:04:05"),
		BonusID:       bonus_id,
	}

	last_bet := user.GetLastBetTransaction() // получаем последнюю ставку
	if last_bet != nil {
		transaction.LastBet = last_bet.Amount
		transaction.LastBetId = last_bet.GenID
		// если не бонусная игра и обычный выигрыш на ставке, отправляем сообщение с брокером (турниры)
		if transaction.BonusID.Int64 == 0 && win_type == "win" {
			container.GetMsgBroker().Send(msgbroker.NewSpingMsg(
				int(transaction.UserId),
				"sleg",
				transaction.Currency,
				transaction.GameID,
				transaction.LastBet,
				transaction.Amount,
				time.Now(),
			))
		}
		last_bet.Amount = 0 // обнуляем последнюю ставку, чтобы не было повторных ставок при призовых
	}

	service.Transactions.Add(&transaction)        // добавляем транзакцию в базу
	user.GetLastTransactions().Push(&transaction) // добавляем транзакцию в список транзакций
	//wallet.Save()
	service.DefferWalletPersistence.Set(wallet.DbWallet.ID, wallet) // сохраняем кошелек

	return slegresponses.MakeBetResponse(balance, transaction.GenID.String), nil
}

// Refund обрабатывает возврат ставки пользователя
func (ep *Endpoint) Refund(r *http.Request, user *service.User, isBonusGame bool, currency currency.CurrencyID) (slegresponses.BetResponse, error) {
	// Получаем игровую сессию
	gs := user.GetGameSession()

	var bonus_id sql.NullInt64 // ID бонуса
	var balance float64        // баланс

	wallet, err := user.GetWallet(currency) // получаем кошелек пользователя
	if err != nil {
		return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err)
	}

	a_p := r.PostFormValue("amount") // получаем сумму возврата
	amount, err := strconv.ParseFloat(a_p, 64)
	if err != nil {
		return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err)
	}
	if amount < 0 {
		return slegresponses.BetResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("wrong amount value"))
	}

	// проверяем, была ли транзакция уже обработана
	ex_trans, found := user.GetLastRefundsTransactions().Find(func(v *models.GameTransaction) bool { return v.TransactionID == r.PostFormValue("transaction_id") })
	if found {
		return slegresponses.MakeBetResponse(wallet.GetBalance(), ex_trans.GenID.String), nil
	}

	if isBonusGame {
		if gs != nil {
			bonus, err := gs.GetActiveBonus() // получаем активный бонус
			if err == nil && bonus != nil && bonus.IsActive() && bonus.GetBetTransactionID() == r.PostFormValue("bet_transaction_id") {
				bonus.RestoreState()          // восстанавливаем состояние бонуса
				bonus.SetBetTransactionID("") // сбрасываем ID транзакции
				bonus.Actualize()             // обновляем бонус
				// service.DefferBonusPersistence.Set(bonus.GetID(), bonus) // сохраняем бонус
				//bonus.Save()
				balance = bonus.GetBalance() // получаем баланс бонуса
				bonus_id.Int64 = bonus.GetID()
				bonus_id.Valid = true
			}
		}
	} else {
		// получаем транзакцию для возврата
		tr_to_refund, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool {
			return v.TransactionID == r.PostFormValue("bet_transaction_id") && !v.Refunded
		})
		if found && tr_to_refund.Action == "bet" {
			tr_to_refund.Refunded = true // отмечаем транзакцию как возвращенную
			wallet.AddMoney(amount)      // добавляем сумму возврата на кошелек
		}
		balance = wallet.GetBalance() // получаем баланс кошелька
	}

	// генерируем ID транзакции
	gen_id := sql.NullString{}
	gen_id.String = uuid.New().String()
	gen_id.Valid = true

	date := time.Now()

	var bet_tr_id sql.NullString // ID транзакции
	bet_tr_id.String = r.PostFormValue("bet_transaction_id")
	bet_tr_id.Valid = true

	transaction := models.GameTransaction{
		GenID:            gen_id,
		UserId:           user.GetID(),
		Action:           "refund",
		Amount:           amount,
		Currency:         wallet.GetCurrencyName(),
		GameID:           r.PostFormValue("game_uuid"),
		SessionID:        r.PostFormValue("session_id"),
		TransactionID:    r.PostFormValue("transaction_id"),
		BetTransactionId: bet_tr_id,
		Type:             r.PostFormValue("type"),
		System:           "sleg",
		Date:             date.Format("2006-01-02 15:04:05"),
		BonusID:          bonus_id,
	}

	service.Transactions.Add(&transaction)               // добавляем транзакцию в базу
	user.GetLastRefundsTransactions().Push(&transaction) // добавляем транзакцию в список транзакций
	//wallet.Save()
	service.DefferWalletPersistence.Set(wallet.DbWallet.ID, wallet) // сохраняем кошелек

	return slegresponses.MakeBetResponse(balance, transaction.GenID.String), nil
}

func (ep *Endpoint) Rollback(r *http.Request, user *service.User, isBonusGame bool, currency currency.CurrencyID) (slegresponses.RollbackResponse, error) {

	var bonus_id sql.NullInt64              // ID бонуса
	wallet, err := user.GetWallet(currency) // получаем кошелек пользователя
	if err != nil {
		return slegresponses.RollbackResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err)
	}
	req, err := ParseRollbackRequest(r) // парсим запрос на откат
	if err != nil {
		return slegresponses.RollbackResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err)
	}
	rolled_back_transactions := make([]string, 0)

	// проверяем, была ли транзакция уже обработана
	ex_trans, found := user.GetLastRefundsTransactions().Find(func(v *models.GameTransaction) bool { return v.TransactionID == r.PostFormValue("transaction_id") })
	if found {
		logger.Get().Info("sleg", slog.Int64("transaction already rolled back", user.GetID()))
		for _, rt := range req.RollbackTransactions {
			// получаем транзакцию для возврата
			tr_to_refund, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool {
				return v.TransactionID == rt.TransactionID
			})
			logger.Get().Info("sleg", slog.String("transaction to refund", rt.TransactionID))
			if found {
				logger.Get().Info("sleg", slog.String("transaction found", tr_to_refund.TransactionID))
				if tr_to_refund.Refunded {
					rolled_back_transactions = append(rolled_back_transactions, rt.TransactionID)
				}
			}
		}
		return slegresponses.MakeRollbackResponse(wallet.GetBalance(), ex_trans.GenID.String, rolled_back_transactions), nil
	}

	if !isBonusGame {
		for _, rt := range req.RollbackTransactions {
			// получаем транзакцию для возврата
			tr_to_refund, found := user.GetLastTransactions().Find(func(v *models.GameTransaction) bool {
				return v.TransactionID == rt.TransactionID
			})
			logger.Get().Info("sleg", slog.String("transaction to refund", rt.TransactionID))
			if found {
				logger.Get().Info("sleg", slog.String("transaction found", tr_to_refund.TransactionID))
				if tr_to_refund.Refunded {
					rolled_back_transactions = append(rolled_back_transactions, rt.TransactionID)
				} else if tr_to_refund.Action == "bet" {
					tr_to_refund.Refunded = true // отмечаем транзакцию как возвращенную
					wallet.AddMoney(rt.Amount)   // добавляем сумму возврата на кошелек
					rolled_back_transactions = append(rolled_back_transactions, rt.TransactionID)
				} else if tr_to_refund.Action == "win" {
					tr_to_refund.Refunded = true         // отмечаем транзакцию как возвращенную
					_, err := wallet.RemMoney(rt.Amount) // снимаем сумму возврата с кошелька
					if err != nil {
						return slegresponses.RollbackResponse{}, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err)
					}
					rolled_back_transactions = append(rolled_back_transactions, rt.TransactionID)
				}
			}
		}
	}

	// генерируем ID транзакции
	gen_id := sql.NullString{}
	gen_id.String = uuid.New().String()
	gen_id.Valid = true

	date := time.Now()

	var bet_tr_id sql.NullString // ID транзакции
	bet_tr_id.String = r.PostFormValue("bet_transaction_id")
	bet_tr_id.Valid = true

	transaction := models.GameTransaction{
		GenID:            gen_id,
		UserId:           user.GetID(),
		Action:           "rollback",
		Amount:           0,
		Currency:         wallet.GetCurrencyName(),
		GameID:           r.PostFormValue("game_uuid"),
		SessionID:        r.PostFormValue("session_id"),
		TransactionID:    r.PostFormValue("transaction_id"),
		BetTransactionId: bet_tr_id,
		Type:             r.PostFormValue("type"),
		System:           "sleg",
		Date:             date.Format("2006-01-02 15:04:05"),
		BonusID:          bonus_id,
	}

	service.Transactions.Add(&transaction)                          // добавляем транзакцию в базу
	user.GetLastRefundsTransactions().Push(&transaction)            // добавляем транзакцию в список транзакций
	service.DefferWalletPersistence.Set(wallet.DbWallet.ID, wallet) // сохраняем кошелек

	return slegresponses.MakeRollbackResponse(wallet.GetBalance(), transaction.GenID.String, rolled_back_transactions), nil
}

// RollbackTransaction описывает одну транзакцию для отката
type RollbackTransaction struct {
	Action        string  // действие (bet/win)
	Amount        float64 // сумма
	TransactionID string  // идентификатор транзакции
	Type          string  // тип (bet/win)
}

// RollbackRequest описывает структуру всего запроса
type RollbackRequest struct {
	Action               string
	Currency             string
	GameUUID             string
	PlayerID             string
	TransactionID        string
	SessionID            string
	Type                 string
	RollbackTransactions []RollbackTransaction
}

// ParseRollbackRequest декодирует POST-запрос в структуру RollbackRequest
func ParseRollbackRequest(r *http.Request) (*RollbackRequest, error) {
	err := r.ParseForm()
	if err != nil {
		return nil, err
	}

	req := &RollbackRequest{
		Action:        r.PostFormValue("action"),
		Currency:      r.PostFormValue("currency"),
		GameUUID:      r.PostFormValue("game_uuid"),
		PlayerID:      r.PostFormValue("player_id"),
		TransactionID: r.PostFormValue("transaction_id"),
		SessionID:     r.PostFormValue("session_id"),
		Type:          r.PostFormValue("type"),
	}

	// Собираем rollback_transactions
	var rollbackTransactions []RollbackTransaction
	for i := 0; ; i++ {
		prefix := "rollback_transactions[" + strconv.Itoa(i) + "]"
		action := r.PostFormValue(prefix + "[action]")
		if action == "" {
			break // нет следующей транзакции
		}
		amountStr := r.PostFormValue(prefix + "[amount]")
		amount, _ := strconv.ParseFloat(amountStr, 64)
		rollbackTransactions = append(rollbackTransactions, RollbackTransaction{
			Action:        action,
			Amount:        amount,
			TransactionID: r.PostFormValue(prefix + "[transaction_id]"),
			Type:          r.PostFormValue(prefix + "[type]"),
		})
	}
	req.RollbackTransactions = rollbackTransactions

	return req, nil
}
