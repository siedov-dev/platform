package userevents

import (
	"encoding/json"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/usecases"
	"log/slog"

	"github.com/nats-io/nats.go/jetstream"
)

func FirstDepUserEvent(msg jetstream.Msg) {
	// Обработчик события первого депозита пользователя
	// msg.Data содержит данные события в формате JSON
	var event FirstDepEvent
	if err := json.Unmarshal(msg.Data(), &event); err != nil {
		logger.Get().Error("Error unmarshalling first deposit event", slog.String("error", err.Error()))
		return
	}

	// Логика обработки события первого депозита
	logger.Get().Info("First deposit event received", slog.Int64("user_id", event.UserID), slog.Float64("amount", event.Amount))

	if event.ReferralID != 0 {
		usecases.FirstDep(event.ReferralID, uint64(event.UserID), event.Amount, event.Currency, event.CurrencyID, event.Fingerprint)
	}
}

type FirstDepEvent struct {
	UserID      int64   `json:"user_id"`     // ID пользователя, совершившего первый депозит
	Amount      float64 `json:"amount"`      // Сумма первого депозита
	Currency    string  `json:"currency"`    // Валюта депозита
	CurrencyID  int     `json:"currency_id"` // ID валюты депозита
	ReferralID  uint64  `json:"referral_id"` // Реферальный код, если есть
	ClickID     uint64  `json:"click_id"`    // ID клика, связанного с депозитом
	Fingerprint *string `json:"fingerprint"` // Отпечаток пользователя, связанный с событием
}
