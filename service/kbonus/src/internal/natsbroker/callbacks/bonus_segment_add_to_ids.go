package callbacks

import (
	"encoding/json"
	"kbonus/internal/logger"
	"kbonus/internal/service/usecases"
	"log/slog"

	"github.com/nats-io/nats.go"
)

func BonusSegmentAddToIds(msg *nats.Msg) {
	var data struct {
		BonusID    int64   `json:"bonus_id"`
		SegmentIDs []int64 `json:"segment_ids"`
	}
	if err := json.Unmarshal(msg.Data, &data); err != nil {
		resp := BoolResponse{
			Data:  false,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		err = msg.Respond(respData)
		if err != nil {
			logger.Get().Error("Failed to respond to NATS message", slog.String("error", err.Error()))
		}
		return
	}

	if err := usecases.BonusSegmentAddToIds(data.BonusID, data.SegmentIDs); err != nil {
		resp := BoolResponse{
			Data:  false,
			Error: err.Error(),
		}
		respData, _ := json.Marshal(resp)
		err = msg.Respond(respData)
		if err != nil {
			logger.Get().Error("Failed to respond to NATS message", slog.String("error", err.Error()))
		}
		return
	}
	resp := BoolResponse{
		Data:  true,
		Error: "",
	}
	respData, _ := json.Marshal(resp)
	err := msg.Respond(respData)
	if err != nil {
		logger.Get().Error("Failed to respond to NATS message", slog.String("error", err.Error()))
	}
}
