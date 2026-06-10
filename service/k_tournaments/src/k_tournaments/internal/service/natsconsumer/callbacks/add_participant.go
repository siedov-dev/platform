package callbacks

import (
	"encoding/json"
	"ktournaments/internal/logger"
	"ktournaments/internal/service"
	"ktournaments/internal/service/natsconsumer/natsrequests"
	"log/slog"

	"github.com/nats-io/nats.go"
)

// AddParticipant - добавление участника в турнир
// Используется для турнирив требующих принятие участия в турнире
func AddParticipant(msg *nats.Msg) {
	req := natsrequests.TournamentUserRequest{}
	err := json.Unmarshal(msg.Data, &req)
	if err != nil {
		logger.Get().Error("Error unmarshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error unmarshalling request"))
		return
	}

	// Получаем турнир
	service.TournamentMut.RLock()
	t, ok := service.Tournaments[req.TournamentId]
	defer service.TournamentMut.RUnlock()
	if !ok {
		msg.Respond([]byte("tournament not found"))
		return
	}

	// Добавление участника в турнир
	// Просто добавляем 0 очков
	t.Values.AddPointsAndSpinsCount(req.UserID, 0, 0)

	msg.Respond([]byte("ok"))
}
