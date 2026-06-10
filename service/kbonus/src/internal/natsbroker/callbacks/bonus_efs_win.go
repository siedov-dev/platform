package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func BonusExtFsWin(msg *nats.Msg) {
	var req BonusGameExtFsMoneyRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		resp := BonusBalanceResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	balance, err := usecases.BonusExtFsWin(req.BonusID, req.WagerPrc, req.Amount, req.Provider)
	if err != nil {
		resp := BonusBalanceResponse{
			Code:  500,
			Error: "Failed to retrieve bonus balance",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	resp := BonusBalanceResponse{
		Balance: balance,
		Code:    200,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
