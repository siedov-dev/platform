package callbacks

import (
	"encoding/json"
	"kbonus/internal/logger"
	"kbonus/internal/service/usecases"
	"log/slog"

	"github.com/nats-io/nats.go"
)

func BonusSegmentAddToName(msg *nats.Msg) {
	var data struct {
		BonusID     int64  `json:"bonus_id"`
		SegmentName string `json:"segment_name"`
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

	if err := usecases.BonusSegmentAddToName(data.BonusID, data.SegmentName); err != nil {
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
