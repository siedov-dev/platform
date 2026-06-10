package models

import (
	"database/sql"
)

// BonusMultiDaysParam представляет параметры многодневного бонуса.
type BonusMultiDaysParam struct {
	ID        uint64         `db:"id" json:"id"`                 // Уникальный идентификатор параметра
	Day       int            `db:"day" json:"day"`               // День, к которому относится параметр
	GameCID   string         `db:"game_cid" json:"game_cid"`     // Идентификатор игры, к которой относится параметр
	Spins     int            `db:"spins" json:"spins"`           // Количество бесплатных спинов
	Wager     int            `db:"wager" json:"wager"`           // Ставка, связанная с бонусом
	Params    sql.NullString `db:"params" json:"params"`         // Дополнительные параметры бонуса в формате JSON
	CreatedAt sql.NullTime   `db:"created_at" json:"created_at"` // Дата и время создания параметра
	UpdatedAt sql.NullTime   `db:"updated_at" json:"updated_at"` // Дата и время последнего обновления параметра
	GameName  sql.NullString `db:"game_name" json:"-"`           // Название игры, к которой относится параметр (не используется)
}
