package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func CancelBonusFromAvailable(msg *nats.Msg) {
	var req CancelFromBonusAvailable
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		resp := BoolResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	err := usecases.CancelBonusAvailable(req.BonusID, req.UserID)
	if err != nil {
		resp := BoolResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	resp := BoolResponse{
		Code: 200,
		Data: true,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
