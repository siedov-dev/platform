package callbacks

import (
	"encoding/json"
	"ktournaments/internal/logger"
	"ktournaments/internal/service"
	"ktournaments/internal/service/natsconsumer/natsrequests"
	"log/slog"

	"github.com/nats-io/nats.go"
)

// TournamentSetState - обработчик запроса на установку состояния турнира.
func TournamentSetState(msg *nats.Msg) {
	// Парсим запрос
	req := natsrequests.TournamentStateRequest{}
	err := json.Unmarshal(msg.Data, &req)
	if err != nil {
		logger.Get().Error("Error unmarshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error unmarshalling request"))
		return
	}

	// Устанавливаем состояние турнира
	logger.Get().Info("Set tournament state")
	err = service.SetTournamentState(req.TournamentId, req.State)
	if err != nil {
		logger.Get().Error("Error set tournament state", slog.String("error", err.Error()))
		msg.Respond([]byte(err.Error()))
		return
	}

	// Отправляем ответ
	logger.Get().Info("Tournament state set")
	msg.Respond([]byte("ok"))
}
