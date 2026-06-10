package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"
	"kbonus/internal/utils/currencyid"

	"github.com/nats-io/nats.go"
)

func GetUserCompletedBonuses(msg *nats.Msg) {
	var req GetUserBonusesRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		resp := GetUserBonusesResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}

	bonuses, err := usecases.GetUserCompletedBonuses(req.UserID, currencyid.CurrencyID(req.CurrencyID))
	if err != nil {
		resp := GetUserBonusesResponse{
			Code:  500,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	var bonusData []BonusData
	for _, bonus := range bonuses {
		bonusInfo := bonus.GetBonusInfo()
		bonusData = append(bonusData, BonusData{
			Attributes: *bonus.UserBonus,
			Meta:       bonusInfo,
		})
	}

	resp := GetUserBonusesResponse{
		Bonuses: bonusData,
		Code:    200,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
