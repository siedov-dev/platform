package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func BonusActualize(msg *nats.Msg) {
	var req GetBonusGameRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		resp := BonusActualizeResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	win, err := usecases.BonusGameActualize(req.BonusID, req.WagerPrc)
	if err != nil {
		resp := BonusActualizeResponse{
			Code:  500,
			Error: "Failed to retrieve bonus win",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	resp := BonusActualizeResponse{
		Win:  win,
		Code: 200,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
