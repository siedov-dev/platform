package callbacks

import (
	"encoding/json"
	"kbonus/internal/logger"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func GetPatternById(msg *nats.Msg) {
	// Парсим запрос
	var req struct {
		ID int64 `json:"id"`
	}
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		logger.Get().Error("Failed to parse GetPatternById request", "error", err)
		resp := GetPatternResponse{
			Code:  500,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	pattern, err := usecases.GetBonusPattern(req.ID)
	if err != nil {
		logger.Get().Error("Failed to get pattern", "error", err)
		resp := GetPatternResponse{
			Code:  404,
			Error: "Failed to get pattern",
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
