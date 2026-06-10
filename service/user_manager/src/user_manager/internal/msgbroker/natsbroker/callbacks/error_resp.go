package callbacks

import (
	"encoding/json"
	"log/slog"
	"user-manager/internal/logger"
	"user-manager/internal/server/responses"

	"github.com/nats-io/nats.go"
)

func sendErrorResponse(msg *nats.Msg, err error) {
	errResp := responses.MakeErrorResponse(500, err)
	respJSON, err := json.Marshal(errResp)
	if err != nil {
		logger.Get().Error("Error marshalling error response", slog.String("error", err.Error()))
		// Если не удалось сериализовать ответ, отправляем простое сообщение об ошибке
		if err := msg.Respond([]byte("error: unable to create response")); err != nil {
			logger.Get().Error("Error responding to NATS", slog.String("error", err.Error()))
		}
		return
	}
	// Отправляем ответ с ошибкой в формате JSON
	if err := msg.Respond(respJSON); err != nil {
		logger.Get().Error("Error responding to NATS", slog.String("error", err.Error()))
	}
}
