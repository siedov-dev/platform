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

func EndFeatures(jsonRequest string) string {
	var req EndFeaturesRequest

	err := json.Unmarshal([]byte(jsonRequest), &req)
	if err != nil {
		return endFeaturesErrorResponse(ErrGeneric, "Invalid request format")
	}
	log := logger.Get()
	// получение ID игрока
	player_id := req.Data.UserID

	user_id, err := strconv.ParseInt(player_id, 10, 64)
	if err != nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "invalid user id"), slog.String("player_id", player_id))
		return endFeaturesErrorResponse(ErrGeneric, "Invalid user ID")
	}
	// получение пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "user not found"), slog.String("player_id", player_id))
		return endFeaturesErrorResponse(ErrUserNotFound, "User not found")
	}
	// получение игровой сессии
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "session not found"), slog.String("player_id", player_id))
		return endFeaturesErrorResponse(ErrGeneric, "Session not found")
	}

	bonus, err := gs.GetActiveBonus()
	if err != nil || bonus == nil {
		log.Error("b2bslots", slog.String("method", "UpdateFeatures"), slog.String("err", "active bonus not found"), slog.String("player_id", player_id))
		return endFeaturesErrorResponse(ErrGeneric, "Active bonus not found")
	}
	h, err := bonus.GetExtFsHandler("b2bslots")
	if err != nil {
		log.Error("b2bslots", slog.String("method", "EndFeatures"), slog.String("err", "ext fs handler not found"), slog.String("player_id", player_id))
		return endFeaturesErrorResponse(ErrGeneric, "Ext fs handler not found")
	}
	balance, err := h.ExternFreeSpinsCompleted(req.Data.FreeRounds.Win)
	if err != nil {
		log.Error("b2bslots", slog.String("method", "EndFeatures"), slog.String("err", "failed to end free spins"), slog.String("player_id", player_id), slog.String("error", err.Error()))
		return endFeaturesErrorResponse(ErrGeneric, "Failed to end free spins")
	}

	return endFeaturesResponse(user.GetID(), user.GetLogin(), balance, req.Data.Currency, *gs.Stats.SessionID)
}

type EndFeaturesRequest struct {
	API  string                 `json:"api"`
	Data EndFeaturesRequestData `json:"data"`
}

type EndFeaturesRequestData struct {
	UserID        string `json:"user_id"`
	UserIP        string `json:"user_ip"`
	UserGameToken string `json:"user_game_token"`
	Currency      string `json:"currency"`
	GameCode      int64  `json:"game_code"`
	FreeRounds    struct {
		ID  int64   `json:"id"`
		Win float64 `json:"win"`
	} `json:"free_rounds"`
}

func endFeaturesErrorResponse(errCode int64, errDesc string) string {
	var now = time.Now().Unix()
	resp := FeaturesResponse{
		API:     "do-end-features-user-ingame",
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

func endFeaturesResponse(userID stypes.UserID, userNickname string, balance float64, currency string, gameToken string) string {
	var now = time.Now().Unix()
	resp := FeaturesResponse{
		API:     "do-end-features-user-ingame",
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
