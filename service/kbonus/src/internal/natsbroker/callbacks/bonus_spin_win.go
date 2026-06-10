package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func BonusSpinWin(msg *nats.Msg) {
	var req BonusGameMoneyRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		resp := GenericResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	err := usecases.BonusOnWinSpin(req.BonusID, req.WagerPrc, req.Amount)
	if err != nil {
		resp := GenericResponse{
			Code:  500,
			Error: "Failed to retrieve bonus balance",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	resp := GenericResponse{
		Code: 200,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
