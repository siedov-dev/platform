package models

import "time"

// ReferralAchievementReward представляет собой структуру для хранения наград за достижения рефералов.
// Она содержит информацию о награде, связанной с определённым достижением реферала.
type ReferralAchievementReward struct {
	ID            uint64     `json:"id" db:"id"`                         // Уникальный идентификатор награды
	AchievementID uint64     `json:"achievement_id" db:"achievement_id"` // Идентификатор достижения, к которому относится награда
	RewardSums    string     `json:"reward_sums" db:"reward_sums"`       // Суммы наград в формате JSON по валютам
	CreatedAt     *time.Time `json:"created_at" db:"created_at"`         // Дата создания награды
	UpdatedAt     *time.Time `json:"updated_at" db:"updated_at"`         // Дата последнего обновления награды
}
