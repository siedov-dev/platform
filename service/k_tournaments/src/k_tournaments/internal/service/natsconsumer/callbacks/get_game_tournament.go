package callbacks

import (
	"encoding/json"
	"ktournaments/internal/logger"
	"ktournaments/internal/service"
	"ktournaments/internal/service/natsconsumer/natsrequests"
	"log/slog"

	"github.com/nats-io/nats.go"
)

// GetGameTournament - получение турнира для игры
// Проверяет доступность турнира для игры
func GetGameTournament(msg *nats.Msg) {
	req := natsrequests.ActiveGameTournamentRequest{}
	err := json.Unmarshal(msg.Data, &req)
	if err != nil {
		logger.Get().Error("Error unmarshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error unmarshalling request"))
		return
	}

	// Получаем турнир
	service.TournamentMut.RLock()
	defer service.TournamentMut.RUnlock()

	for _, t := range service.ActiveTournaments {
		if t.IsAvailable(req.UserID, req.Currency, req.GameSystem, req.GameID, req.Test) {
			resp, err := json.Marshal(natsrequests.TournamentResponse{
				Error:      "",
				Tournament: t,
			})
			if err != nil {
				logger.Get().Error("Error marshalling response", slog.String("error", err.Error()))
				msg.Respond([]byte("Error marshalling response"))
				return
			}
			msg.Respond([]byte(resp))
			return
		}
	}

	// Если нет подходящего турнира, то отправляем ошибку
	resp, err := json.Marshal(natsrequests.TournamentResponse{
		Error: "Not found",
	})
	if err != nil {
		logger.Get().Error("Error marshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error marshalling response"))
		return
	}
	msg.Respond([]byte(resp))
}
