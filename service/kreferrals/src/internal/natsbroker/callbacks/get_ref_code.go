package callbacks

import (
	"encoding/json"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func GetRefCode(msg *nats.Msg) {
	var req getRefCodeRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		logger.Get().Error("Error unmarshalling request", "error", err.Error())
		resp := getRefCodeResponse{
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

	referral, err := usecases.GetRefCode(req.UserID)
	if err != nil {
		logger.Get().Error("Error getting ref code", "error", err.Error())
		resp := getRefCodeResponse{
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

	if referral == nil {
		logger.Get().Error("Error getting ref code is nil")
		resp := getRefCodeResponse{
			Code:  500,
			Error: "Error creating ref code",
		}
		jsonResp, err := json.Marshal(resp)
		if err != nil {
			logger.Get().Error("Error marshalling response", "error", err.Error())
			return
		}
		msg.Respond(jsonResp)
		return
	}

	resp := getRefCodeResponse{
		Code:    200,
		Error:   "",
		UserID:  req.UserID,
		RefCode: referral.RefCode,
		RefID:   referral.ID,
	}
	jsonResp, err := json.Marshal(resp)
	if err != nil {
		logger.Get().Error("GetRefCode error marshalling response", "error", err.Error())
		return
	}
	msg.Respond(jsonResp)
}

type getRefCodeRequest struct {
	UserID uint64 `json:"user_id"`
}

type getRefCodeResponse struct {
	Code    int    `json:"cd"`
	Error   string `json:"err"`
	UserID  uint64 `json:"user_id"`
	RefCode string `json:"ref_code"`
	RefID   uint64 `json:"ref_id"`
}
