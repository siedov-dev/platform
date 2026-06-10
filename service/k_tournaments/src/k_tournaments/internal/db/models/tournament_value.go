package models

import "time"

// TournamentValueModel - модель значений турнира
// Содержит информацию о значениях турнира (к-во очков)
type TournamentValueModel struct {
	ID           uint64     `db:"id" json:"id"`                           // ID значения
	TournamentID uint64     `db:"tournament_id" json:"tournament_id"`     // ID турнира
	UserID       int        `db:"user_id" json:"user_id"`                 // ID пользователя
	Value        float32    `db:"value" json:"value"`                     // Значение
	SpinsCount   int32      `db:"spins_count" json:"spins_count"`         // Количество сделанных спинов
	CreatedAt    *time.Time `db:"created_at" json:"created_at,omitempty"` // Время создания
	UpdatedAt    *time.Time `db:"updated_at" json:"updated_at,omitempty"` // Время обновления
}
