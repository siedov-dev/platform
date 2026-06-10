package models

import "time"

// ReferralConfig представляет собой структуру для хранения конфигурации рефералов.
// Она содержит информацию о типе конфигурации.
// Содержит конфигурации трёх типов:
// 1. Основная конфигурация (ConfigTypeMain)
// 2. Промо-конфигурация (ConfigTypePromo)
// 3. Конфигурация пользователя (ConfigTypeUser)
type ReferralConfig struct {
	ID        uint64     `json:"id" db:"id"`                 // Уникальный идентификатор конфигурации
	Type      int        `json:"type" db:"type"`             // Тип конфигурации (ReferralConfigType)
	UserID    uint64     `json:"user_id" db:"user_id"`       // Идентификатор пользователя, для конфигурации пользователя
	GGRPrc    int        `json:"ggr_prc" db:"ggr_prc"`       // Процент GGR (Gross Gaming Revenue)
	FdSums    string     `json:"fd_sums" db:"fd_sums"`       // Суммы FD (First Deposit) в формате JSON по валютам
	StartDate *time.Time `json:"start_date" db:"start_date"` // Дата начала действия конфигурации, для конфигурации промо (акции)
	EndDate   *time.Time `json:"end_date" db:"end_date"`     // Дата окончания действия конфигурации, для конфигурации промо (акции)
	CreatedAt *time.Time `json:"created_at" db:"created_at"` // Дата создания конфигурации
	UpdatedAt *time.Time `json:"updated_at" db:"updated_at"` // Дата последнего обновления конфигурации
}

type ReferralConfigType int8

const (
	ConfigTypeMain ReferralConfigType = iota
	ConfigTypePromo
	ConfigTypeUser
)
