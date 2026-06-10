package callbacks

import (
	"encoding/json"
	"ktournaments/internal/logger"
	"ktournaments/internal/service"
	"ktournaments/internal/service/natsconsumer/natsrequests"
	"log/slog"

	"github.com/nats-io/nats.go"
)

// UnloadTournament - обработчик запроса на выгрузку турнира.
func UnloadTournament(msg *nats.Msg) {
	// Парсим запрос
	req := natsrequests.GetTournamentRequest{}
	err := json.Unmarshal(msg.Data, &req)
	if err != nil {
		logger.Get().Error("Error unmarshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error unmarshalling request"))
		return
	}

	// Выгружаем турнир
	logger.Get().Info("Unload tournament")
	service.TournamentMut.Lock()
	defer service.TournamentMut.Unlock()

	service.DeleteTournament(req.TournamentId)

	// Отправляем ответ
	logger.Get().Info("Unload deleted")
	msg.Respond([]byte("ok"))
}
