package models

import "time"

// TournamentSpinLog представляет историю спинов турнира
type TournamentSpinLog struct {
	ID           uint64    `json:"id" db:"id"`                       // ID записи
	TournamentID uint64    `json:"tournament_id" db:"tournament_id"` // ID турнира
	UserID       int       `json:"user_id" db:"user_id"`             // ID пользователя
	Currency     string    `json:"currency" db:"currency"`           // Валюта спина
	GameSystem   string    `json:"game_system" db:"game_system"`     // Система игры
	GameID       string    `json:"game_id" db:"game_id"`             // ID игры
	Bet          float64   `json:"bet" db:"bet"`                     // Ставка спина
	Win          float64   `json:"win" db:"win"`                     // Выигрыш спина
	Points       float32   `json:"points" db:"points"`               // Очки, начисленные за спин
	CreatedAt    time.Time `json:"created_at" db:"created_at"`       // Дата и время создания записи
	UpdatedAt    time.Time `json:"updated_at" db:"updated_at"`       // Дата и время обновления записи
}
