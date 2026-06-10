package callbacks

import (
	"encoding/json"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func DropBonusCallback(msg *nats.Msg) {
	var data struct {
		BonusID int64 `json:"bonus_id"`
	}
	if err := json.Unmarshal(msg.Data, &data); err != nil {
		resp := GetBonusResponse{Error: "invalid json"}
		respBytes, _ := json.Marshal(resp)
		msg.Respond(respBytes)
		return
	}
	bonus, err := usecases.DropBonus(data.BonusID)
	if err != nil {
		resp := GetBonusResponse{Error: err.Error()}
		respBytes, _ := json.Marshal(resp)
		msg.Respond(respBytes)
		return
	}
	resp := GetBonusResponse{
		Attributes: *bonus.UserBonus,
		Meta:       bonus.GetBonusInfo(),
		Code:       200,
	}
	respBytes, _ := json.Marshal(resp)
	msg.Respond(respBytes)
}
