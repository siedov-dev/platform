package msgbroker

import (
	"encoding/json"
	"log/slog"
	"time"
	"user-manager/internal/logger"
)

// Message представляет собой интерфейс для описания сообщения с двумя методами: Subject и Payload.
// Subject возвращает тему сообщения в виде строки.
// Payload возвращает содержимое сообщения в виде строки (json).
type Message interface {
	Subject() string
	Payload() string
}

// MsgBroker представляет интерфейс для работы с брокером сообщений.
// Обеспечивает отправку сообщений и закрытие соединения с брокером.
type MsgBroker interface {
	Request(subject string, data any) (any, error)
	Send(Message) error
	Close()
}

// NewSpinMsg создает новое сообщение типа "spin" с информацией о пользователе, системе, валюте, ставке, выигрыше и времени.
func NewSpinMsg(
	user_id int,
	game_system string,
	currency string,
	game_id string,
	bet float64,
	win float64,
	stime time.Time,
) Message {
	return &spinMessage{
		subject: "spins",
		payload: spinData{
			UserID:     user_id,
			GameSystem: game_system,
			Currency:   currency,
			GameID:     game_id,
			Bet:        bet,
			Win:        win,
			SpinTime:   stime,
		},
	}
}

// spinData представляет структуру данных для хранения информации о спине в игре.
// Содержит идентификатор пользователя, игры, валюту, ставку, выигрыш и время вращения.
type spinData struct {
	UserID     int       `json:"user_id"`     // UserID представляет уникальный идентификатор игрока в системе.
	GameSystem string    `json:"game_system"` // GameSystem представляет игровую систему, в которой был выполнен данный спин.
	Currency   string    `json:"currency"`    // Currency представляет валюту, использованную в данном спине.
	GameID     string    `json:"game_id"`     // GameID представляет идентификатор игры, в которой был совершён данный спин.
	Bet        float64   `json:"bet"`         // Bet представляет сумму ставки, сделанной игроком в текущем спине.
	Win        float64   `json:"win"`         // Win представляет сумму выигрыша, полученного игроком в результате данного спина.
	SpinTime   time.Time `json:"spin_time"`   // SpinTime представляет время, когда был выполнен спин в игровом процессе.
}

// spinMessage представляет собой тип данных для сообщения о спине, содержит тему и тело сообщения (payload).
// subject указывает тему сообщения, payload используется для хранения информации о данных спина.
type spinMessage struct {
	subject string
	payload spinData
}

// Subject возвращает строку, содержащую тему сообщения из структуры spinMessage.
func (s *spinMessage) Subject() string {
	return s.subject
}

// Payload сериализует поле payload структуры spinMessage в строку JSON.
// Если происходит ошибка сериализации, она логируется. Возвращает результирующую строку JSON.
func (s *spinMessage) Payload() string {
	p, err := json.Marshal(s.payload)
	if err != nil {
		logger.Get().Error("MsgBroker", slog.String("error marshaling payload", err.Error()))
	}
	return string(p)
}
