package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/container"

	"github.com/nats-io/nats.go"
)

func ParamsLowestMinValue(msg *nats.Msg) {
	var req ParamsLowestMinValueRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		resp := ParamsLowestMinValueResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	value, err := container.GetBonusParamsDataProvider().GetLowestMinValue(req.BonusType, req.CurrencyID)
	if err != nil {
		resp := ParamsLowestMinValueResponse{
			Code:  500,
			Error: "Failed to retrieve bonus parameters",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	resp := ParamsLowestMinValueResponse{
		Value: value,
		Code:  200,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
