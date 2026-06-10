package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func HasCancelableOnPayout(msg *nats.Msg) {
	// Извлекаем userID из запроса
	var request UserIDRequest
	if err := json.Unmarshal(msg.Data, &request); err != nil {
		msg.Respond([]byte("invalid request format"))
		return
	}

	// Проверяем, есть ли у пользователя бонусы с возможностью отмены
	hasCancelable, err := usecases.HasCancelableOnPayout(request.UserID)
	if err != nil {
		resp := GetBonusResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	resp := BoolResponse{
		Code: 200,
		Data: hasCancelable,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
