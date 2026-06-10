package callbacks

import (
	"encoding/json"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func GetAchievementRewards(msg *nats.Msg) {
	var req achieveRewardsRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		logger.Get().Error("Error unmarshalling request", "error", err.Error())
		resp := achieveRewardsResponse{
			Code:  500,
			Error: "Error unmarshalling request",
		}
		jsonResp, err := json.Marshal(resp)
		if err != nil {
			logger.Get().Error("Error marshalling response", "error", err.Error())
		}
		msg.Respond(jsonResp)
		return
	}

	rewards := usecases.GetAchievementRewards(req.Currency)
	resp := achieveRewardsResponse{
		Code:    200,
		Error:   "",
		Rewards: rewards,
	}
	jsonResp, err := json.Marshal(resp)
	if err != nil {
		logger.Get().Error("GetAchievementRewards error marshalling response", "error", err.Error())
		return
	}
	msg.Respond(jsonResp)
}

type achieveRewardsRequest struct {
	Currency string `json:"currency"`
}

type achieveRewardsResponse struct {
	Code    int                `json:"cd"`
	Error   string             `json:"err"`
	Rewards map[uint64]float64 `json:"rewards"`
}
