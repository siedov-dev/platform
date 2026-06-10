package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func TransferBonusMoney(msg *nats.Msg) {
	var req GetBonusRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		resp := TransferBonusMoneyResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	bonus, err := usecases.GetBonus(req.BonusID)
	if err != nil {
		resp := TransferBonusMoneyResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	if bonus == nil {
		resp := TransferBonusMoneyResponse{
			Code:  404,
			Error: "Bonus not found",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	// Переводим бонус
	amount, err := usecases.TransferBonusMoney(bonus.GetID())
	if err != nil {
		resp := TransferBonusMoneyResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	resp := TransferBonusMoneyResponse{
		Attributes: *bonus.UserBonus,
		Meta:       bonus.GetBonusInfo(),
		Code:       200,
		Amount:     amount,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
