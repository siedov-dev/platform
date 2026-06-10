package b2bslots

import (
	"encoding/json"
	"fmt"
	"log/slog"
	"strconv"
	"time"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/service"
	"user-manager/internal/stypes"
)

func Auth(jsonRequest string) string {
	var req AuthRequest

	err := json.Unmarshal([]byte(jsonRequest), &req)
	if err != nil {
		return authErrorResponse(ErrGeneric, "Invalid request format")
	}

	// получение ID пользователя по токену
	user_id, ok := service.PPlayAuthTokens.Get(req.Request.UserAuthToken)
	if !ok {
		logger.Get().Error("b2bslots", slog.String("token not found", jsonRequest))
		return authErrorResponse(ErrTokenNotFound, "Token not found")
	}
	if fmt.Sprintf("%d", user_id) != req.Request.UserID {
		logger.Get().Error("b2bslots", slog.String("token does not match user", jsonRequest))
		return authErrorResponse(ErrTokenNotFound, "Token does not match user")
	}
	// получение пользователя по ID
	user, err := service.GetUser(user_id)
	if err != nil {
		logger.Get().Error("b2bslots", slog.String("user not found", fmt.Sprintf("%v", user_id)))
		return authErrorResponse(ErrUserNotFound, "User not found")
	}

	// получение игровой сессии
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		logger.Get().Error("b2bslots", slog.String("session not found", fmt.Sprintf("%v", user_id)))
		return authErrorResponse(ErrGeneric, "Session not found")
	}
	// получение валюты игры
	var game_currency string
	curr_info, ok := service.Currencies.Find(func(v models.UserCurrency) bool { return v.ID == int64(gs.GameStartData.CurrencyID) })
	if !ok {
		logger.Get().Error("b2bslots", slog.String("currency not found", fmt.Sprintf("%d", gs.GameStartData.CurrencyID)))
		return authErrorResponse(ErrGeneric, "Currency not found")
	}
	if curr_info.GameCurrency.Valid {
		game_currency = curr_info.GameCurrency.String
	} else {
		game_currency = curr_info.Name
	}
	var game_token string
	if gs.Stats.SessionID == nil {
		logger.Get().Error("b2bslots", slog.String("session id is nil", fmt.Sprintf("%v", user_id)))
		return authErrorResponse(ErrGeneric, "Session token not found")
	}
	game_token = *gs.Stats.SessionID
	nickname := user.GetDbUser().Login.String

	if b, err := gs.GetActiveBonus(); err == nil && b != nil {
		logger.Get().Info("b2bslots bonus auth:", "bonus:", b.GetID())
		bonus_balance := b.GetBalance()
		return authResponse(user.GetID(), nickname, bonus_balance, game_currency, req.Request.UserAuthToken, game_token)
	}

	// получение кошелька пользователя
	wallet, err := user.GetWallet(gs.GameStartData.CurrencyID)
	if err != nil {
		logger.Get().Error("b2bslots", slog.String("wallet not found", fmt.Sprintf("%d", gs.GameStartData.CurrencyID)))
		return authErrorResponse(ErrGeneric, "Wallet not found")
	}
	balance := wallet.GetBalance()

	return authResponse(user.GetID(), nickname, balance, game_currency, req.Request.UserAuthToken, game_token)
}

type AuthRequest struct {
	API     string          `json:"api"`
	Request AuthRequestData `json:"data"`
}

type AuthRequestData struct {
	UserID        string `json:"user_id"`
	UserIP        string `json:"user_ip"`
	UserAuthToken string `json:"user_auth_token"`
	Currency      string `json:"currency"`
	GameCode      int64  `json:"game_code"`
	GameName      string `json:"game_name"`
}

type AuthResponse struct {
	API      string           `json:"api"`
	Success  bool             `json:"success"`
	Response AuthResponseData `json:"answer"`
}

type AuthResponseData struct {
	OperatorID       int64  `json:"operator_id"`
	UserID           string `json:"user_id"`
	UserNickname     string `json:"user_nickname"`
	Balance          string `json:"balance"`
	BonusBalance     string `json:"bonus_balance"`
	AuthToken        string `json:"auth_token"`
	GameToken        string `json:"game_token"`
	ErrorCode        int64  `json:"error_code"`
	ErrorDescription string `json:"error_description"`
	Currency         string `json:"currency"`
	Timestamp        string `json:"timestamp"`
}

func authErrorResponse(errCode int64, errDesc string) string {
	var now = time.Now().Unix()
	resp := AuthResponse{
		API:     "do-auth-user-ingame",
		Success: true,
		Response: AuthResponseData{
			OperatorID:       operator_id,
			ErrorCode:        errCode,
			ErrorDescription: errDesc,
			Timestamp:        strconv.FormatInt(now, 10),
		},
	}
	respJson, _ := json.Marshal(resp)
	return string(respJson)
}

func authResponse(userID stypes.UserID, userNickname string, balance float64, currency string, authToken string, gameToken string) string {
	var now = time.Now().Unix()
	resp := AuthResponse{
		API:     "do-auth-user-ingame",
		Success: true,
		Response: AuthResponseData{
			OperatorID:   operator_id,
			ErrorCode:    0,
			Timestamp:    strconv.FormatInt(now, 10),
			UserID:       strconv.FormatInt(int64(userID), 10),
			UserNickname: userNickname,
			Balance:      strconv.FormatFloat(balance, 'f', 5, 64),
			AuthToken:    authToken,
			GameToken:    gameToken,
			Currency:     currency,
			BonusBalance: "0.00000",
		},
	}
	respJson, _ := json.Marshal(resp)
	return string(respJson)
}
