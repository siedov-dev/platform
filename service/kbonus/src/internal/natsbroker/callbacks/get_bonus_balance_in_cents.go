package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func GetBonusBalanceInCents(msg *nats.Msg) {
	var req GetBonusRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		resp := BonusBalanceInCentsResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	bonus, err := usecases.GetBonus(req.BonusID)
	if err != nil {
		resp := BonusBalanceInCentsResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	if bonus == nil {
		resp := BonusBalanceInCentsResponse{
			Code:  404,
			Error: "Bonus not found",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	balance, err := usecases.GetBonusBalanceInCents(req.BonusID)
	if err != nil {
		resp := BonusBalanceInCentsResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	resp := BonusBalanceInCentsResponse{
		Balance: balance,
		Code:    200,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
