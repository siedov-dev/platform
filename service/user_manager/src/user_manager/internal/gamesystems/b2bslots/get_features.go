package b2bslots

import (
	"encoding/json"
	"log/slog"
	"strconv"
	"time"
	"user-manager/internal/logger"
	"user-manager/internal/service"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"
)

func GetFeatures(jsonRequest string) string {
	var req GetFeaturesRequest

	err := json.Unmarshal([]byte(jsonRequest), &req)
	if err != nil {
		return getFeaturesErrorResponse(ErrGeneric, "Invalid request format")
	}
	log := logger.Get()
	// получение ID игрока
	player_id := req.Data.UserID
	// валюта
	var currencyName string
	// баланс бонуса
	var bonus_balance float64
	// баланс
	var balance float64

	currencyName = req.Data.Currency

	user_id, err := strconv.ParseInt(player_id, 10, 64)
	if err != nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "invalid user id"), slog.String("player_id", player_id))
		return getFeaturesErrorResponse(ErrGeneric, "Invalid user ID")
	}
	// получение пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "user not found"), slog.String("player_id", player_id))
		return getFeaturesErrorResponse(ErrUserNotFound, "User not found")
	}
	// получение игровой сессии
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "session not found"), slog.String("player_id", player_id))
		return getFeaturesErrorResponse(ErrGeneric, "Session not found")
	}
	// получение ID валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "currency not found"), slog.String("currency", currencyName))
		return getFeaturesErrorResponse(ErrGeneric, "Currency not found")
	}
	// получение кошелька пользователя
	wallet, err := user.GetWallet(curr)
	if err != nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "wallet not found"), slog.String("player_id", player_id), slog.String("currency", currencyName))
		return getFeaturesErrorResponse(ErrGeneric, "Wallet not found")
	}

	bonus, err := gs.GetActiveBonus()
	if err == nil && bonus != nil && bonus.GetSpinsAvailable() > 0 {
		bonus_balance = bonus.GetBalance()
		max_bet, err := bonus.GetFsMaxBet()
		logger.Get().Debug("b2bslots", slog.String("method", "GetFeatures"), slog.Float64("max_bet", max_bet))
		if err != nil {
			log.Error("b2bslots", slog.String("method", "GetFeatures"), slog.String("err", "invalid max_bet in bonus params"), slog.Int64("bonus_id", bonus.GetID()))
			return getFeaturesErrorResponse(ErrGeneric, "Invalid bonus parameters")
		}
		game_info, err := GetGameInfo(req.Data.GameCode, currencyName)
		if err != nil {
			log.Error("b2bslots", slog.String("method", "GetFeatures"), slog.String("err", "cannot get game info"), slog.Int64("game_code", req.Data.GameCode), slog.String("currency", currencyName), slog.String("error", err.Error()))
			return getFeaturesErrorResponse(ErrGeneric, "Cannot get game info")
		}
		// определение подходящей ставки для фриспинов
		// ставка должна быть не больше max_bet
		// и максимально возможной для данной игры
		dnms := 1.0
		bet := 0
		lines := 0
		curr_bet := 0.0
		for _, b := range game_info.Data.Bets {
			for _, l := range game_info.Data.Lines {
				if float64(b*l) <= max_bet && float64(b*l) > curr_bet {
					bet = b
					lines = l
					curr_bet = float64(b * l)
				}
			}
		}
		fr := &FreeRounds{
			ID:      bonus.GetID(),
			Count:   int64(bonus.GetSpinsAvailable()),
			Bet:     int64(bet),
			Lines:   int64(lines),
			MPL:     1,
			CP:      dnms,
			Version: 2,
		}
		return getFeaturesResponse(user.GetID(), user.GetLogin(), bonus_balance, currencyName, *gs.Stats.SessionID, fr)

	}
	game_currency := currencyName

	balance = wallet.GetBalance()
	return getFeaturesResponse(user.GetID(), user.GetLogin(), balance, game_currency, *gs.Stats.SessionID, nil)
}

type GetFeaturesRequest struct {
	API  string                 `json:"api"`
	Data GetFeaturesRequestData `json:"data"`
}

type GetFeaturesRequestData struct {
	UserID        string `json:"user_id"`
	UserIP        string `json:"user_ip"`
	UserGameToken string `json:"user_game_token"`
	Currency      string `json:"currency"`
	GameCode      int64  `json:"game_code"`
}

type GetFeaturesResponse struct {
	API     string                  `json:"api"`
	Success bool                    `json:"success"`
	Data    GetFeaturesResponseData `json:"answer"`
}
type GetFeaturesResponseData struct {
	OperatorID       int64       `json:"operator_id"`
	TransactionID    string      `json:"transaction_id"`
	UserID           string      `json:"user_id"`
	UserNickname     string      `json:"user_nickname"`
	Balance          string      `json:"balance"`
	BonusBalance     string      `json:"bonus_balance"`
	BonusAmount      string      `json:"bonus_amount"`
	GameToken        string      `json:"game_token"`
	ErrorCode        int64       `json:"error_code"`
	ErrorDescription string      `json:"error_description"`
	Currency         string      `json:"currency"`
	Timestamp        string      `json:"timestamp"`
	FreeRounds       *FreeRounds `json:"free_rounds,omitempty"`
}

type FreeRounds struct {
	ID      int64   `json:"id"`
	Count   int64   `json:"count"`
	Bet     int64   `json:"bet"`
	Lines   int64   `json:"lines"`
	MPL     int64   `json:"mpl"`
	CP      float64 `json:"cp"`
	Version int64   `json:"version"`
}

func getFeaturesErrorResponse(errCode int64, errDesc string) string {
	var now = time.Now().Unix()
	resp := GetFeaturesResponse{
		API:     "do-get-features-user-ingame",
		Success: true,
		Data: GetFeaturesResponseData{
			OperatorID:       operator_id,
			ErrorCode:        errCode,
			ErrorDescription: errDesc,
			Timestamp:        strconv.FormatInt(now, 10),
		},
	}
	respJson, _ := json.Marshal(resp)
	return string(respJson)
}

func getFeaturesResponse(userID stypes.UserID, userNickname string, balance float64, currency string, gameToken string, fr *FreeRounds) string {
	var now = time.Now().Unix()
	resp := GetFeaturesResponse{
		API:     "do-get-features-user-ingame",
		Success: true,
		Data: GetFeaturesResponseData{
			OperatorID:   operator_id,
			ErrorCode:    0,
			Timestamp:    strconv.FormatInt(now, 10),
			UserID:       strconv.FormatInt(int64(userID), 10),
			UserNickname: userNickname,
			Balance:      strconv.FormatFloat(balance, 'f', 5, 64),
			BonusBalance: "0.00000",
			BonusAmount:  "0.00000",
			Currency:     currency,
			GameToken:    gameToken,
			FreeRounds:   fr,
		},
	}
	respJson, _ := json.Marshal(resp)
	return string(respJson)
}
