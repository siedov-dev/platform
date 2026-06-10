package callbacks

import (
	"encoding/json"
	"kbonus/internal/logger"
	"kbonus/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func GetRegFsPatternIds(msg *nats.Msg) {
	// Парсим запрос
	req := struct {
		Currency string `json:"currency"`
	}{}
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		logger.Get().Error("Failed to parse GetRegFsPatternIds request", "error", err)
		resp := GetRegPatternIdsResponse{
			Code:  400,
			Error: "Invalid request format",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	ids, err := usecases.GetRegFsPatternIds(req.Currency)
	if err != nil {
		logger.Get().Error("Failed to get registration pattern IDs", "error", err)
		resp := GetRegPatternIdsResponse{
			Code:  500,
			Error: "Failed to get registration pattern IDs",
		}
		respData, _ := json.Marshal(resp)
		msg.Respond(respData)
		return
	}
	resp := GetRegPatternIdsResponse{
		IDs:  ids,
		Code: 200,
	}
	respData, _ := json.Marshal(resp)
	msg.Respond(respData)
}
