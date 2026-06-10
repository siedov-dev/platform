package callbacks

import (
	"encoding/json"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func Click(msg *nats.Msg) {
	// Обработчик клика по реферальной ссылке
	// msg.Data содержит данные события в формате JSON
	var event ClickEvent
	if err := json.Unmarshal(msg.Data, &event); err != nil {
		logger.Get().Error("Error unmarshalling click event", "error", err.Error())
		return
	}

	// Логика обработки клика по реферальной ссылке
	logger.Get().Info("Click event received", "ref_code", event.RefCode, "referer", event.Referer)

	usecases.RefClick(event.RefCode, event.Referer, event.Fingerprint)
}

type ClickEvent struct {
	RefCode     string  `json:"ref_code"`    // Реферальный код
	Referer     string  `json:"referer"`     // Реферер
	Fingerprint *string `json:"fingerprint"` // Отпечаток пользователя, связанный с событием
}
