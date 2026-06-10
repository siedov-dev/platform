package callbacks

import (
	"encoding/json"
	"ktournaments/internal/logger"
	"ktournaments/internal/service"
	"ktournaments/internal/service/natsconsumer/natsrequests"
	"log/slog"

	"github.com/nats-io/nats.go"
)

// GetUserPosition - получение позиции пользователя в турнире
func GetUserPosition(msg *nats.Msg) {
	// Парсим запрос
	req := natsrequests.TournamentUserRequest{}
	err := json.Unmarshal(msg.Data, &req)
	if err != nil {
		logger.Get().Error("Error unmarshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error unmarshalling request"))
		return
	}

	// Получаем турнир по ID
	service.TournamentMut.RLock()
	defer service.TournamentMut.RUnlock()

	// Если турнир не найден, то отправляем ошибку
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

	// Получаем позицию пользователя в турнире
	position, winner := t.Values.GetUserPosition(req.UserID)

	// Формируем ответ
	resp, err := json.Marshal(natsrequests.UserPositionResponse{Position: position, Winner: winner})
	if err != nil {
		logger.Get().Error("Error marshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error marshalling response"))
		return
	}
	// Отправляем ответ
	msg.Respond([]byte(resp))
}
