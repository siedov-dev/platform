package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/container"

	"github.com/nats-io/nats.go"
)

func ParamsGetByKeyValue(msg *nats.Msg) {
	var req GetParamByKeyRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		resp := GetParamByKeyResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	param, err := container.GetBonusParamsDataProvider().GetParamByKey(req.BonusType, req.CurrencyID, req.Key)
	if err != nil {
		resp := GetParamByKeyResponse{
			Code:  500,
			Error: "Failed to retrieve bonus parameters",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	resp := GetParamByKeyResponse{
		Param: param,
		Code:  200,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
