package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func GetFirstNotActiveBonus(msg *nats.Msg) {
	// Извлекаем userID и currency из запроса
	var request UserIDCurrencyIDRequest
	if err := json.Unmarshal(msg.Data, &request); err != nil {
		msg.Respond([]byte("invalid request format"))
		return
	}

	// Получаем первый неактивированный бонус пользователя
	bonus, err := usecases.GetFirstNotActiveBonus(request.UserID, request.CurrencyID)
	if err != nil {
		resp := GetBonusResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	if bonus == nil {
		resp := GetBonusResponse{
			Code:  404,
			Error: "Bonus not found",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	resp := GetBonusResponse{
		Attributes: *bonus.UserBonus,
		Meta:       bonus.GetBonusInfo(),
		Code:       200,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
