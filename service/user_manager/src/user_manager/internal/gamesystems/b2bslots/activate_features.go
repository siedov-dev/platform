package b2bslots

import (
	"encoding/json"
	"log/slog"
	"strconv"
	"time"
	"user-manager/internal/logger"
	"user-manager/internal/service"
	"user-manager/internal/stypes"
)

func ActivateFeatures(jsonRequest string) string {
	var req ActivateFeaturesRequest

	err := json.Unmarshal([]byte(jsonRequest), &req)
	if err != nil {
		return activateFeaturesErrorResponse(ErrGeneric, "Invalid request format")
	}
	log := logger.Get()
	// получение ID игрока
	player_id := req.Data.UserID

	user_id, err := strconv.ParseInt(player_id, 10, 64)
	if err != nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "invalid user id"), slog.String("player_id", player_id))
		return activateFeaturesErrorResponse(ErrGeneric, "Invalid user ID")
	}
	// получение пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "user not found"), slog.String("player_id", player_id))
		return activateFeaturesErrorResponse(ErrUserNotFound, "User not found")
	}
	// получение игровой сессии
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "session not found"), slog.String("player_id", player_id))
		return activateFeaturesErrorResponse(ErrGeneric, "Session not found")
	}

	bonus, err := gs.GetActiveBonus()
	if err != nil || bonus == nil {
		log.Error("b2bslots", slog.String("method", "ActivateFeatures"), slog.String("err", "active bonus not found"), slog.String("player_id", player_id))
		return activateFeaturesErrorResponse(ErrGeneric, "Active bonus not found")
	}
	if bonus.GetID() != req.Data.FreeRounds.ID {
		log.Error("b2bslots", slog.String("method", "ActivateFeatures"), slog.String("err", "bonus ID mismatch"), slog.String("player_id", player_id), slog.Int64("request_bonus_id", req.Data.FreeRounds.ID), slog.Int64("user_bonus_id", bonus.GetID()))
		return activateFeaturesErrorResponse(ErrGeneric, "Bonus ID mismatch")
	}
	return activateFeaturesResponse(user.GetID(), user.GetLogin(), bonus.GetBalance(), req.Data.Currency, *gs.Stats.SessionID)

}

type ActivateFeaturesRequest struct {
	API  string                      `json:"api"`
	Data ActivateFeaturesRequestData `json:"data"`
}

type ActivateFeaturesRequestData struct {
	UserID        string `json:"user_id"`
	UserIP        string `json:"user_ip"`
	UserGameToken string `json:"user_game_token"`
	Currency      string `json:"currency"`
	GameCode      int64  `json:"game_code"`
	FreeRounds    struct {
		ID int64 `json:"id"`
	} `json:"free_rounds"`
}

func activateFeaturesErrorResponse(errCode int64, errDesc string) string {
	var now = time.Now().Unix()
	resp := FeaturesResponse{
		API:     "do-activate-features-user-ingame",
		Success: true,
		Data: FeaturesResponseData{
			OperatorID:       operator_id,
			ErrorCode:        errCode,
			ErrorDescription: errDesc,
			Timestamp:        strconv.FormatInt(now, 10),
		},
	}
	respJson, _ := json.Marshal(resp)
	return string(respJson)
}

func activateFeaturesResponse(userID stypes.UserID, userNickname string, balance float64, currency string, gameToken string) string {
	var now = time.Now().Unix()
	resp := FeaturesResponse{
		API:     "do-activate-features-user-ingame",
		Success: true,
		Data: FeaturesResponseData{
			OperatorID:   operator_id,
			ErrorCode:    0,
			Timestamp:    strconv.FormatInt(now, 10),
			UserID:       strconv.FormatInt(int64(userID), 10),
			UserNickname: userNickname,
			Balance:      strconv.FormatFloat(balance, 'f', 5, 64),
			BonusBalance: "0.00000",
			Currency:     currency,
			GameToken:    gameToken,
		},
	}
	respJson, _ := json.Marshal(resp)
	return string(respJson)
}

type FeaturesResponse struct {
	API     string               `json:"api"`
	Success bool                 `json:"success"`
	Data    FeaturesResponseData `json:"answer"`
}
type FeaturesResponseData struct {
	OperatorID       int64  `json:"operator_id"`
	UserID           string `json:"user_id"`
	UserNickname     string `json:"user_nickname"`
	Balance          string `json:"balance"`
	BonusBalance     string `json:"bonus_balance"`
	GameToken        string `json:"game_token"`
	ErrorCode        int64  `json:"error_code"`
	ErrorDescription string `json:"error_description"`
	Currency         string `json:"currency"`
	Timestamp        string `json:"timestamp"`
}
