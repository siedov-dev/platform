package models

import "database/sql"

type BonusMenuParam struct {
	ID           uint64          `db:"id" json:"id"`
	Currency     int8            `db:"currency" json:"currency"`
	BonusType    int8            `db:"bonus_type" json:"bonus_type"`
	BonusPattern sql.NullInt64   `db:"bonus_pattern" json:"bonus_pattern"`
	Name         sql.NullString  `db:"name" json:"name"`
	MinValue     sql.NullFloat64 `db:"min_value" json:"min_value"`
	MaxValue     sql.NullFloat64 `db:"max_value" json:"max_value"`
	Value        sql.NullFloat64 `db:"value" json:"value"`
	CreatedAt    sql.NullTime    `db:"created_at" json:"created_at"`
	UpdatedAt    sql.NullTime    `db:"updated_at" json:"updated_at"`
	Value1       sql.NullFloat64 `db:"value1" json:"value1"`
	Value2       sql.NullFloat64 `db:"value2" json:"value2"`
	Value3       sql.NullFloat64 `db:"value3" json:"value3"`
	Value4       sql.NullFloat64 `db:"value4" json:"value4"`
	Value5       sql.NullFloat64 `db:"value5" json:"value5"`
}
