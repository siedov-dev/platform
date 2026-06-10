package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func CancelBonusesOnPayout(msg *nats.Msg) {
	var req UserIDRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		resp := GenericResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	usecases.CancelBonusesOnPayout(req.UserID)
	resp := GenericResponse{
		Code: 200,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
