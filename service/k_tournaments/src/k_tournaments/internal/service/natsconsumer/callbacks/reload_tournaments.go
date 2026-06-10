package callbacks

import (
	"ktournaments/internal/logger"
	"ktournaments/internal/service"

	"github.com/nats-io/nats.go"
)

// ReloadTournaments - обработчик запроса на перезагрузку всех турниров.
func ReloadTournaments(msg *nats.Msg) {
	logger.Get().Info("Reloading tournaments")
	// Перезагружаем все турниры
	service.LoadTournaments()
	// Отправляем ответ
	logger.Get().Info("Tournaments reloaded")
	msg.Respond([]byte("ok"))
}
