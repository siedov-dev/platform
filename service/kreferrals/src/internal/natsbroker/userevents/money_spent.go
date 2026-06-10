package userevents

import (
	"encoding/json"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/usecases"
	"log/slog"

	"github.com/nats-io/nats.go/jetstream"
)

func MoneySpentUserEvent(msg jetstream.Msg) {
	// Обработчик события потраченных денег пользователем
	// msg.Data содержит данные события в формате JSON
	var event MoneySpentEvent
	if err := json.Unmarshal(msg.Data(), &event); err != nil {
		logger.Get().Error("Error unmarshalling money spent event", slog.String("error", err.Error()))
		return
	}

	// Логика обработки события потраченных денег
	logger.Get().Info("Money spent event received", slog.Uint64("user_id", event.UserID), slog.Float64("amount", event.Amount))

	if event.ReferralID != 0 {
		usecases.MoneySpent(event.ReferralID, event.UserID, event.Amount, event.Currency, event.CurrencyID, event.Fingerprint)
	}
}

type MoneySpentEvent struct {
	UserID      uint64  `json:"user_id"`     // ID пользователя, совершившего покупку
	Amount      float64 `json:"amount"`      // Сумма
	Currency    string  `json:"currency"`    // Валюта
	CurrencyID  int     `json:"currency_id"` // ID валюты
	ReferralID  uint64  `json:"referral_id"` // Реферальный код, если есть
	ClickID     uint64  `json:"click_id"`    // ID клика, связанного с покупкой
	Fingerprint *string `json:"fingerprint"` // Отпечаток пользователя, связанный с событием
}
