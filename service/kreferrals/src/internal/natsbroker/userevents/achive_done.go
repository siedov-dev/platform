package userevents

import (
	"encoding/json"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/usecases"
	"log/slog"

	"github.com/nats-io/nats.go/jetstream"
)

func AchiveDoneUserEvent(msg jetstream.Msg) {
	// Обработчик события завершения достижения
	// msg.Data содержит данные события в формате JSON
	var event AchiveDoneEvent
	if err := json.Unmarshal(msg.Data(), &event); err != nil {
		logger.Get().Error("Error unmarshalling archive done event", slog.String("error", err.Error()))
		return
	}

	// Логика обработки события завершения достижения
	logger.Get().Info("Achivement done event received", slog.Uint64("user_id", event.UserID), slog.String("currency", event.Currency))

	if event.ReferralID != 0 {
		usecases.AchiveDone(event.ReferralID, event.UserID, event.AchID, event.Currency, event.CurrencyID, event.Fingerprint)
	}
}

type AchiveDoneEvent struct {
	UserID      uint64  `json:"user_id"`     // ID пользователя, завершившего достижение
	AchID       uint64  `json:"ach_id"`      // ID достижения
	Currency    string  `json:"currency"`    // Валюта достижения
	CurrencyID  int     `json:"currency_id"` // ID валюты
	ReferralID  uint64  `json:"referral_id"` // Реферальный код, если есть
	ClickID     uint64  `json:"click_id"`    // ID клика пользователя, связанного с достижением
	Fingerprint *string `json:"fingerprint"` // Отпечаток пользователя, связанный с событием
}
