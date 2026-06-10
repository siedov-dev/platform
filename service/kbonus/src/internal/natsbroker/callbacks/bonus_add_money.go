package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func BonusAddMoney(msg *nats.Msg) {
	var req BonusGameMoneyRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		resp := BonusMoneyChangeResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	balance, real_amount, bonus_amount, err := usecases.BonusAddMoney(req.BonusID, req.WagerPrc, req.Amount)
	if err != nil {
		resp := BonusMoneyChangeResponse{
			Code:  500,
			Error: "Failed to retrieve bonus balance",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	resp := BonusMoneyChangeResponse{
		Balance:     balance,
		RealAmount:  real_amount,
		BonusAmount: bonus_amount,
		Code:        200,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
