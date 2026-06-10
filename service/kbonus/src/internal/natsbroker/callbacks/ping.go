package callbacks

import (
	"kbonus/internal/logger"
	"log/slog"

	"github.com/nats-io/nats.go"
)

func Ping(msg *nats.Msg) {
	// Обработчик пинга для тестирования соединения с NATS
	// msg.Data содержит данные запроса, но в данном случае они не используются
	// Отправляем ответ "pong"
	if err := msg.Respond([]byte("pong")); err != nil {
		logger.Get().Error("Error responding to ping", slog.String("error", err.Error()))
	}
}
