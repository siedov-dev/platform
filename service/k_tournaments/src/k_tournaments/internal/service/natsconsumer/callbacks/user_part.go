package callbacks

import (
	"encoding/json"
	"ktournaments/internal/logger"
	"ktournaments/internal/service"
	"ktournaments/internal/service/natsconsumer/natsrequests"
	"log/slog"

	"github.com/nats-io/nats.go"
)

// UserParticipates - обработчик запроса на проверку участия пользователя в турнире.
func UserParticipates(msg *nats.Msg) {
	req := natsrequests.TournamentUserRequest{}
	err := json.Unmarshal(msg.Data, &req)
	if err != nil {
		logger.Get().Error("Error unmarshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error unmarshalling request"))
		return
	}

	// Блокируем доступ к мапе турниров
	service.TournamentMut.RLock()
	t, ok := service.Tournaments[req.TournamentId]
	defer service.TournamentMut.RUnlock()
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

	// Проверяем участие пользователя в турнире
	_, r := t.Values.Values[req.UserID]

	// Сериализуем ответ
	resp, err := json.Marshal(natsrequests.BoolResponse{
		Resp: r,
	})
	if err != nil {
		logger.Get().Error("Error marshalling response", slog.String("error", err.Error()))
		msg.Respond([]byte("Error marshalling response"))
		return
	}
	msg.Respond([]byte(resp))
}
