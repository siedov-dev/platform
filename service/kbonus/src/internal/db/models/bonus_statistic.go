package models

import "time"

// BonusStatistic представляет статистику, связанную с бонусами пользователя.
// Используется для сохранения истории изменений статуса бонуса.
type BonusStatistic struct {
	BonusID      int64      `db:"bonus_id"`      // BonusID идентификатор бонуса
	BonusType    int16      `db:"bonus_type"`    // BonusType тип бонуса
	PatternID    int64      `db:"pattern_id"`    // PatternID ид шаблона бонуса
	UserID       int64      `db:"user_id"`       // UserID идентификатор пользователя
	Action       int8       `db:"action"`        // Action определяет тип действия, связанного с изменением статистики бонуса.
	Amount       float64    `db:"amount"`        // Amount представляет количество бонусных средств, задействованных в действиях статистики.
	Currency     int8       `db:"currency"`      // Currency представляет ид валюты, связанную с бонусом.
	StatusBefore int8       `db:"status_before"` // StatusBefore представляет статус бонуса до изменения.
	Status       int8       `db:"status"`        // Status представляет текущий статус бонуса в процессе его использования или изменения.
	CreatedAt    *time.Time `db:"created_at"`    // CreatedAt указывает на время создания записи в таблице базы данных.
	UpdatedAt    *time.Time `db:"updated_at"`    // UpdatedAt хранит время последнего обновления записи в базе данных.
	Data         *string    `db:"data"`          // Data содержит дополнительную информацию в JSON.
	PromocodeID  *int64     `db:"promocode_id"`  // PromocodeID представляет идентификатор промокода, связанный с бонусной статистикой, может быть null.
}

// Action бонуса для записи истории бонусов
const (
	ACTION_CREATED  = 0
	ACTION_ACTIVATE = 1
	ACTION_CANCEL   = 2
	ACTION_DONE     = 3
	ACTION_FAIL     = 4
	ACTION_WIN      = 5
	ACTION_DEPOSIT  = 6
	ACTION_EXPIRED  = 7
	ACTION_FS_DONE  = 8
)
