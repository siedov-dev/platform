package callbacks

import (
	"encoding/json"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

// TransferAccumulatedMoney переводит накопленные деньги на реферальный счет пользователя.
func TransferAccumulatedMoney(msg *nats.Msg) {
	// msg.Data содержит данные события в формате JSON
	var req transferRefMoneyRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		logger.Get().Error("Error unmarshalling click event", "error", err.Error())
		return
	}

	logger.Get().Info("TransferAccumulatedMoney", "user_id", req.UserID, "currency", req.Currency)

	transferrred, err := usecases.TransferAccumulatedMoney(req.UserID, req.Currency, req.CurrencyID)
	if err != nil {
		logger.Get().Error("Error transferring referral money", "user_id", req.UserID, "currency", req.Currency, "error", err.Error())
		resp := transferRefMoneyResponse{
			Code:  500,
			Error: err.Error(),
		}
		data, _ := json.Marshal(resp)
		msg.Respond(data)
		return
	}
	resp := transferRefMoneyResponse{
		Code:     200,
		Value:    transferrred,
		CurrName: req.Currency,
	}
	data, _ := json.Marshal(resp)
	if err := msg.Respond(data); err != nil {
		logger.Get().Error("Error responding to transfer request", "user_id", req.UserID, "currency", req.Currency, "error", err.Error())
		return
	}
	msg.Respond(data) // Отправляем ответ обратно в NATS
}
