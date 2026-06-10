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

func UpdateFeatures(jsonRequest string) string {
	var req UpdateFeaturesRequest

	err := json.Unmarshal([]byte(jsonRequest), &req)
	if err != nil {
		return getFeaturesErrorResponse(ErrGeneric, "Invalid request format")
	}
	log := logger.Get()
	// получение ID игрока
	player_id := req.Data.UserID

	user_id, err := strconv.ParseInt(player_id, 10, 64)
	if err != nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "invalid user id"), slog.String("player_id", player_id))
		return updateFeaturesErrorResponse(ErrGeneric, "Invalid user ID")
	}
	// получение пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "user not found"), slog.String("player_id", player_id))
		return updateFeaturesErrorResponse(ErrUserNotFound, "User not found")
	}
	// получение игровой сессии
	gs := user.GetGameSession()
	if gs == nil || gs.Stats.SessionID == nil {
		log.Error("b2bslots", slog.String("method", "debit"), slog.String("err", "session not found"), slog.String("player_id", player_id))
		return updateFeaturesErrorResponse(ErrGeneric, "Session not found")
	}

	bonus, err := gs.GetActiveBonus()
	if err != nil || bonus == nil {
		log.Error("b2bslots", slog.String("method", "UpdateFeatures"), slog.String("err", "active bonus not found"), slog.String("player_id", player_id))
		return updateFeaturesErrorResponse(ErrGeneric, "Active bonus not found")
	}

	// получение обработчика внешних фриспинов
	extfs, err := bonus.GetExtFsHandler("b2bslots")
	if err != nil {
		logger.Get().Error("b2bslots cant get ext fs halder")
		return debitErrorResponse(ErrGeneric, "Cant get external fs handler")
	}
	// обработка внешних фриспинов
	_, err = extfs.ExternFreeSpinBet(0)
	if err != nil {
		logger.Get().Error("b2bslots fs error : " + err.Error())
		return debitErrorResponse(ErrGeneric, "INTERNAL_ERROR")
	}
	_, err = extfs.ExternFreeSpinWin(req.Data.FreeRounds.RoundWin)
	if err != nil {
		logger.Get().Error("b2bslots fs error : " + err.Error())
		return debitErrorResponse(ErrGeneric, "INTERNAL_ERROR")
	}
	bonus.Actualize()

	return updateFeaturesBalanceResponse(user.GetID(), user.GetLogin(), bonus.GetBalance(), req.Data.Currency, *gs.Stats.SessionID)
}

type UpdateFeaturesRequest struct {
	API  string             `json:"api"`
	Data UpdateFeaturesData `json:"data"`
}

type UpdateFeaturesData struct {
	UserID     string           `json:"user_id"`
	UserIP     string           `json:"user_ip"`
	Currency   string           `json:"currency"`
	GameCode   int              `json:"game_code"`
	FreeRounds FreeRoundsUpdate `json:"free_rounds"`
}

type FreeRoundsUpdate struct {
	ID       int64   `json:"id"`
	Win      float64 `json:"win"`
	Count    int     `json:"count"`
	Played   int     `json:"played"`
	RoundWin float64 `json:"round_win"`
	RoundID  int64   `json:"round_id"`
}

func updateFeaturesErrorResponse(errCode int64, errDesc string) string {
	var now = time.Now().Unix()
	resp := FeaturesResponse{
		API:     "do-update-features-user-ingame",
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

func updateFeaturesBalanceResponse(userID stypes.UserID, userNickname string, balance float64, currency string, gameToken string) string {
	var now = time.Now().Unix()
	resp := FeaturesResponse{
		API:     "do-update-features-user-ingame",
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
