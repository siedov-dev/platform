package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/container"

	"github.com/nats-io/nats.go"
)

func FindBonusParamInRange(msg *nats.Msg) {
	var req FindBonusParamInRangeRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		resp := FindBonusParamInRangeResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	param, err := container.GetBonusParamsDataProvider().FindParamInRange(req.BonusType, req.CurrencyID, req.MinValue)
	if err != nil {
		resp := FindBonusParamInRangeResponse{
			Code:  500,
			Error: "Failed to retrieve bonus parameters",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	resp := FindBonusParamInRangeResponse{
		Param: param,
		Code:  200,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
