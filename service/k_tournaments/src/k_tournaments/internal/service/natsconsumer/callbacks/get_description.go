package callbacks

import (
	"encoding/json"
	"ktournaments/internal/logger"
	"ktournaments/internal/service"
	"ktournaments/internal/service/natsconsumer/natsrequests"
	"log/slog"

	"github.com/nats-io/nats.go"
)

// GetDescription - получение описания турнира
// Используется для получения описания турнира на разных языках
func GetDescription(msg *nats.Msg) {
	req := natsrequests.TournamentDescriptionRequest{}
	err := json.Unmarshal(msg.Data, &req)
	if err != nil {
		logger.Get().Error("Error unmarshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error unmarshalling request"))
		return
	}

	// Получаем турнир
	service.TournamentMut.RLock()
	defer service.TournamentMut.RUnlock()

	t, ok := service.Tournaments[req.TournamentId]
	if !ok {
		resp, err := json.Marshal(natsrequests.ErrorResponse{
			Error: "Tournament not found",
		})
		if err != nil {
			logger.Get().Error("Error marshalling response", slog.String("error", err.Error()))
			msg.Respond([]byte("Error marshalling response"))
			return
		}
		msg.Respond([]byte(resp))
		return
	}

	// Если есть описание на нужном языке, то отправляем его
	if val, ok := t.Conditions[req.Lang]; ok {
		msg.Respond([]byte(val))
		return
	}

	// Если нет описания на нужном языке, то отправляем описание на английском
	if val, ok := t.Conditions["en"]; ok {
		msg.Respond([]byte(val))
		return
	}

	// Если нет описания на английском, то отправляем ошибку
	msg.Respond([]byte("Error: no description found"))
}
