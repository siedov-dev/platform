package callbacks

import (
	"encoding/json"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func GetRefConfig(msg *nats.Msg) {
	var req getRefCofigRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		logger.Get().Error("Error unmarshalling request", "error", err.Error())
		resp := getRefCofigResponse{
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

	conf := usecases.GetRefConfig(req.UserID)

	resp := getRefCofigResponse{
		Code:   200,
		Error:  "",
		GgrPrc: conf.GetGGRPrc(),
		FdSum:  conf.GetFdSum(req.Currency),
	}
	jsonResp, err := json.Marshal(resp)
	if err != nil {
		logger.Get().Error("Error marshalling response", "error", err.Error())
		return
	}
	msg.Respond(jsonResp)
}

type getRefCofigRequest struct {
	UserID   uint64 `json:"user_id"`
	Currency string `json:"currency"`
}

type getRefCofigResponse struct {
	Code   int     `json:"cd"`
	Error  string  `json:"err"`
	GgrPrc int     `json:"ggr_prc"`
	FdSum  float64 `json:"fd_sum"`
}
