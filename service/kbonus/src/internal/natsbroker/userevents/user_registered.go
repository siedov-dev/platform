package userevents

import (
	"encoding/json"
	"kbonus/internal/logger"
	"log/slog"

	"github.com/nats-io/nats.go/jetstream"
)

func UserRegistered(msg jetstream.Msg) {
	// Обработчик события регистрации пользователя
	// msg.Data содержит данные события в формате JSON
	var event UserRegisteredEvent
	if err := json.Unmarshal(msg.Data(), &event); err != nil {
		logger.Get().Error("Error unmarshalling user registered event", slog.String("error", err.Error()))
		return
	}

	// Логика обработки события регистрации пользователя
	logger.Get().Info("User registered event received", slog.Uint64("ref_id", event.RefID), slog.Uint64("user_id", event.UserID))

}

type UserRegisteredEvent struct {
	RefID       uint64  `json:"referral_id"` // Реферальный код
	UserID      uint64  `json:"user_id"`     // ID пользователя
	Fingerprint *string `json:"fingerprint"` // Отпечаток пользователя, связанный с событием
}
