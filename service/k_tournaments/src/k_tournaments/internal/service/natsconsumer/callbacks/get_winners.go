package callbacks

import (
	"encoding/json"
	"ktournaments/internal/logger"
	"ktournaments/internal/service"
	"ktournaments/internal/service/natsconsumer/natsrequests"
	"log/slog"

	"github.com/nats-io/nats.go"
)

// GetWinners - обработчик запроса на получение таблицы лидеров турнира.
func GetWinners(msg *nats.Msg) {
	// Парсим запрос
	req := natsrequests.GetWinnersRequest{}
	err := json.Unmarshal(msg.Data, &req)
	if err != nil {
		logger.Get().Error("Error unmarshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error unmarshalling request"))
		return
	}

	// Блокируем доступ к мапе турниров
	service.TournamentMut.RLock()
	defer service.TournamentMut.RUnlock()

	// Получаем турнир из мапы
	t, ok := service.Tournaments[req.TournamentId]
	if !ok {
		// Если турнир не найден, возвращаем ошибку
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

	// Получаем список победителей
	resp, err := json.Marshal(t.Values.GetWinners(req.Limit))
	if err != nil {
		logger.Get().Error("Error marshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error marshalling response"))
		return
	}

	// Отправляем ответ
	msg.Respond([]byte(resp))
}
