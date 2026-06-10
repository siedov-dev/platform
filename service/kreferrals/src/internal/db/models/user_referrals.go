package models

import "time"

// UserReferral представляет собой структуру для хранения информации о рефералах (кодах) пользователей.
type UserReferral struct {
	ID           uint64     `json:"id" db:"id"`                 // Уникальный идентификатор реферала
	UserID       *uint64    `json:"user_id" db:"user_id"`       // Может быть NULL, идентификатор пользователя, которому принадлежит реферал
	RefCode      string     `json:"ref_code" db:"ref_code"`     // Реферальный код пользователя
	ProfitPrc    int        `json:"-" db:"profit_prc"`          // Процент прибыли, связанный с рефералом (не используется в текущей версии)
	CreatedAt    *time.Time `json:"created_at" db:"created_at"` // Дата создания реферала
	UpdatedAt    *time.Time `json:"updated_at" db:"updated_at"` // Дата последнего обновления реферала
	BonusRefCode *string    `json:"-" db:"bonus_ref_code"`      // Может быть NULL, бонусный реферальный код, связанный с рефералом (не используется в текущей версии)
}
