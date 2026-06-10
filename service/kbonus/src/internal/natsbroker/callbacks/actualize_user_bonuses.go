package callbacks

import (
	"encoding/json"
	"fmt"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func ActualizeUserBonuses(msg *nats.Msg) {
	// Извлекаем userID из запроса
	var req UserIDRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		resp := StatusResponse{
			Code:  400,
			Error: fmt.Sprintf("invalid request format: %s", err.Error()),
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	// Актуализируем бонусы пользователя
	if err := usecases.ActualizeUserBonuses(req.UserID); err != nil {
		resp := StatusResponse{
			Code:  500,
			Error: fmt.Sprintf("failed to actualize user bonuses: %s", err.Error()),
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	// Отправляем успешный ответ
	resp := StatusResponse{
		Code:  200,
		Error: "",
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
