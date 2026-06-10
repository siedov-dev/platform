package callbacks

import (
	"encoding/json"
	"ktournaments/internal/logger"
	"ktournaments/internal/service"
	"ktournaments/internal/service/natsconsumer/natsrequests"
	"log/slog"

	"github.com/nats-io/nats.go"
)

// ReloadTournament - обработчик запроса на перезагрузку турнира.
func ReloadTournament(msg *nats.Msg) {
	// Парсим запрос
	req := natsrequests.GetTournamentRequest{}
	err := json.Unmarshal(msg.Data, &req)
	if err != nil {
		logger.Get().Error("Error unmarshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error unmarshalling request"))
		return
	}

	// Перезагружаем турнир
	logger.Get().Info("Reloading tournament")
	err = service.ReloadTournament(req.TournamentId)
	if err != nil {
		logger.Get().Error("Error reloading tournament", slog.String("error", err.Error()))
		msg.Respond([]byte(err.Error()))
		return
	}

	// Отправляем ответ
	logger.Get().Info("Tournament reloaded")
	msg.Respond([]byte("ok"))
}
