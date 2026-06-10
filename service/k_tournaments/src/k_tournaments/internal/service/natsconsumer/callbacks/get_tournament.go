package callbacks

import (
	"encoding/json"
	"ktournaments/internal/logger"
	"ktournaments/internal/service"
	"ktournaments/internal/service/natsconsumer/natsrequests"
	"log/slog"

	"github.com/nats-io/nats.go"
)

// GetTournament - получение турнира по ID
func GetTournament(msg *nats.Msg) {
	// Парсим запрос
	req := natsrequests.GetTournamentRequest{}
	err := json.Unmarshal(msg.Data, &req)
	if err != nil {
		logger.Get().Error("Error unmarshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error unmarshalling request"))
		return
	}
	service.TournamentMut.RLock()
	defer service.TournamentMut.RUnlock()

	// Получаем турнир по ID
	t, ok := service.Tournaments[req.TournamentId]
	if !ok {
		// Если турнир не найден, то отправляем ошибку
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
	// Формируем ответ
	resp, err := json.Marshal(t)
	if err != nil {
		logger.Get().Error("Error marshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error marshalling response"))
		return
	}
	// Отправляем ответ
	msg.Respond([]byte(resp))
}
