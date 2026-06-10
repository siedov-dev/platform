package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/container"

	"github.com/nats-io/nats.go"
)

func GetBonusParamsByTypeAndCurrency(msg *nats.Msg) {
	var req BonusTypeAndCurrencyRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		resp := BonusParamsResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	params, err := container.GetBonusParamsDataProvider().GetParamsByBonusTypeAndCurrency(req.BonusType, req.CurrencyID)
	if err != nil {
		resp := BonusParamsResponse{
			Code:  500,
			Error: "Failed to retrieve bonus parameters",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	resp := BonusParamsResponse{
		Params: params,
		Code:   200,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
