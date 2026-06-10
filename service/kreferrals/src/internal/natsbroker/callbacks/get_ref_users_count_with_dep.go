package callbacks

import (
	"encoding/json"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func GetReferralUsersCountwithDeps(msg *nats.Msg) {
	var req getRefUsersCountwithDepsRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		logger.Get().Error("Error unmarshalling request", "error", err.Error())
		resp := GetReferralUsersCountwithDepsResponse{
			Code:  500,
			Error: "Error unmarshalling request",
		}
		jsonResp, err := json.Marshal(resp)
		if err != nil {
			logger.Get().Error("Error marshalling response", "error", err.Error())
			return
		}
		msg.Respond(jsonResp)
		return
	}

	count, err := usecases.GetReferralUsersCountwithDeps(req.RefID)
	if err != nil {
		resp := GetReferralUsersCountwithDepsResponse{
			Code:  500,
			Error: err.Error(),
		}
		jsonResp, err := json.Marshal(resp)
		if err != nil {
			logger.Get().Error("Error marshalling response", "error", err.Error())
			return
		}
		msg.Respond(jsonResp)
		return
	}

	resp := GetReferralUsersCountwithDepsResponse{
		Code:  200,
		Error: "",
		Count: count,
	}
	jsonResp, err := json.Marshal(resp)
	if err != nil {
		logger.Get().Error("Error marshalling response", "error", err.Error())
		return
	}
	msg.Respond(jsonResp)

}

type getRefUsersCountwithDepsRequest struct {
	RefID uint64 `json:"ref_id"`
}

type GetReferralUsersCountwithDepsResponse struct {
	Code  int    `json:"cd"`
	Error string `json:"err"`
	Count uint64 `json:"count"`
}
