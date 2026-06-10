package callbacks

import (
	"encoding/json"
	"kbonus/internal/logger"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func GetRegPattern(msg *nats.Msg) {
	// Парсим запрос
	var req struct {
		Currency string `json:"currency"`
		Type     string `json:"type"`
		Num      int    `json:"num"`
	}
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		logger.Get().Error("Failed to parse GetRegPattern request", "error", err)
		resp := GetPatternResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	pattern, err := usecases.GetRegPattern(req.Currency, req.Type, req.Num)
	if err != nil {
		logger.Get().Error("Failed to get registration pattern", "error", err)
		resp := GetPatternResponse{
			Code:  500,
			Error: "Failed to get registration pattern",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	resp := GetPatternResponse{
		Attributes: *pattern,
		Code:       200,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
