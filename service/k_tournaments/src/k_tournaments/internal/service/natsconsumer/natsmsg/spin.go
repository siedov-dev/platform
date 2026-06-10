package natsmsg

import "time"

// SpinMessage - сообщение о спине турнира.
type SpinMessage struct {
	UserID     int       `json:"user_id"`     // ID пользователя
	GameSystem string    `json:"game_system"` // Система игры
	Currency   string    `json:"currency"`    // Валюта
	GameID     string    `json:"game_id"`     // ID игры
	Bet        float64   `json:"bet"`         // Ставка
	Win        float64   `json:"win"`         // Выигрыш
	SpinTime   time.Time `json:"spin_time"`   // Время спина
}
