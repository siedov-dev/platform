package callbacks

import (
	"encoding/json"
	"kbonus/internal/logger"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func SaveBonusFromModel(msg *nats.Msg) {
	var req BonusDataRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		resp := GetBonusResponse{
			Code:  400,
			Error: err.Error(),
		}
		logger.Get().Error("SaveBonusFromModel: invalid request format", "err", err)
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	bonus, err := usecases.SaveBonusFromModel(&req.Attributes)
	if err != nil {
		resp := GetBonusResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	if bonus == nil {
		resp := GetBonusResponse{
			Code:  404,
			Error: "Bonus not found",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	resp := GetBonusResponse{
		Attributes: *bonus.UserBonus,
		Meta:       bonus.GetBonusInfo(),
		Code:       200,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
