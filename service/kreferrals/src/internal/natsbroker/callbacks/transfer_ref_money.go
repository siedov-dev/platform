package callbacks

import (
	"encoding/json"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func TransferRefMoney(msg *nats.Msg) {
	// Обработчик клика по реферальной ссылке
	// msg.Data содержит данные события в формате JSON
	var req transferRefMoneyRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		logger.Get().Error("Error unmarshalling click event", "error", err.Error())
		return
	}

	logger.Get().Info("TransferRefMoney", "user_id", req.UserID, "currency", req.Currency)

	transferrred, err := usecases.TransferRefMoney(req.UserID, req.Currency, req.CurrencyID)
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
}

type transferRefMoneyRequest struct {
	UserID     uint64 `json:"user_id"`     // ID пользователя, которому переводятся средства
	Currency   string `json:"currency"`    // Валюта, в которой переводятся средства
	CurrencyID int    `json:"currency_id"` // ID валюты, в которой переводятся средства
}

type transferRefMoneyResponse struct {
	Code     int     `json:"cd"`  // Код статуса HTTP-ответа
	Error    string  `json:"err"` // Сообщение об ошибке, если таковая имеется
	Value    float64 `json:"v"`   // Сумма переведенных средств
	CurrName string  `json:"cn"`  // Название валюты
}
