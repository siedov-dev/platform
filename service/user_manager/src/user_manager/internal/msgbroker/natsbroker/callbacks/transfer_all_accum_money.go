package callbacks

import (
	"encoding/json"
	"fmt"
	"log/slog"
	"net/http"
	"user-manager/internal/logger"
	"user-manager/internal/service"
	"user-manager/internal/stypes"

	"github.com/nats-io/nats.go"
)

type TransferAllAccumMoneyRequest struct {
	UserID int64 `json:"user_id"` // ID пользователя, которому переводятся все накопленные средства
}

type TransferAllAccumMoneyResponse struct {
	Error     string          `json:"err"`     // Ошибка, если есть
	ErrorCode int             `json:"cd"`      // Код ошибки, если есть
	UserID    int64           `json:"user_id"` // ID пользователя, которому переводятся все накопленные средства
	Amounts   map[int]float64 `json:"amounts"` // Сумма переведенных средств по валютам
}

func TransferAllAccumMoney(msg *nats.Msg) {
	var req TransferAllAccumMoneyRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		logger.Get().Error("Error unmarshalling transfer all accum money request", slog.String("error", err.Error()))
		sendErrorResponse(msg, fmt.Errorf("error: unable to parse request"))
		return
	}

	user, err := service.GetUserTemp(stypes.UserID(req.UserID))
	if err != nil {
		logger.Get().Error("Error getting user", slog.String("error", err.Error()))
		sendErrorResponse(msg, fmt.Errorf("error: unable to get user"))
		return
	}

	response := TransferAllAccumMoneyResponse{
		Error:     "",
		ErrorCode: http.StatusOK,
		UserID:    req.UserID,
		Amounts:   make(map[int]float64),
	}

	wallets := user.GetWallets()
	for _, wallet := range wallets {
		if wallet.GetBonusMoney() != 0 {
			transferred := wallet.TransferBonusMoney()
			err = wallet.Save()
			if err != nil {
				logger.Get().Error("Error saving wallet", slog.String("error", err.Error()))
				sendErrorResponse(msg, fmt.Errorf("error: unable to save wallet"))
				return
			}
			response.Amounts[int(wallet.Currency)] = transferred
		}
	}

	respJSON, err := json.Marshal(response)
	if err != nil {
		logger.Get().Error("Error marshalling transfer all accum money response", slog.String("error", err.Error()))
		sendErrorResponse(msg, fmt.Errorf("error: unable to create response"))
		return
	}
	if err := msg.Respond(respJSON); err != nil {
		logger.Get().Error("Error responding to transfer all accum money", slog.String("error", err.Error()))
		return
	}
}
