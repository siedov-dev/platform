package models

import "time"

// ReferralEvent представляет собой структуру для хранения событий рефералов.
type ReferralEvent struct {
	ID          uint64     `json:"id" db:"id"`                   // Уникальный идентификатор события
	RefID       uint64     `json:"ref_id" db:"ref_id"`           // Идентификатор реферала, к которому относится событие
	Type        int        `json:"type" db:"type"`               // Тип события (ReferralEventType)
	UserID      *uint64    `json:"user_id" db:"user_id"`         // Может быть NULL, идентификатор пользователя, связанного с событием
	Amount      float64    `json:"amount" db:"amount"`           // Сумма, связанная с событием
	Profit      float64    `json:"profit" db:"profit"`           // Прибыль, связанная с событием
	Currency    *int       `json:"currency" db:"currency"`       // Может быть NULL, идентификатор валюты события
	Data        *string    `json:"data" db:"data"`               // Может быть NULL, дополнительные данные в формате JSON
	CreatedAt   *time.Time `json:"created_at" db:"created_at"`   // Дата создания события
	UpdatedAt   *time.Time `json:"updated_at" db:"updated_at"`   // Дата последнего обновления события
	Status      int        `json:"status" db:"status"`           // Статус события (ReferralEventStatus)
	ConfigID    *uint64    `json:"config_id" db:"config_id"`     // Может быть NULL, идентификатор конфигурации события
	Fingerprint *string    `json:"fingerprint" db:"fingerprint"` // Может быть NULL, отпечаток пользолователя, связанный с событием
}

type ReferralEventType int

const (
	ReferralEventTypeClick                        ReferralEventType = iota // Событие перехода по ссылке
	ReferralEventTypeRegister                                              // Событие регистрации пользователя
	ReferralEventTypeMoneySpent                                            // Событие потраченных денег пользователем
	ReferralEventTypeDeposit                                               // Событие первого депозита
	ReferralEventTypeFirstDeposit                                          // Событие первого депозита пользователя
	ReferralEventTypeAchievementCompleted                                  // Событие завершения достижения пользователем
	ReferralEventTypeReferralMoneyTransferred                              // Событие перевода реферальных денег пользователю
	ReferralEventTypeAccumulationMoneyTransferred                          // Событие перевода накопительных денег пользователю
)
