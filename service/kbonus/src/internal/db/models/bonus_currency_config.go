package models

import (
	"database/sql"
)

// BonusCurrencyConfig представляет конфигурацию коэффициентов валют для параметров бонусов в системе.
// Используется для хранения параметров, связанных с коэффициентами приведения значений параметров бонуса для разных валют.
// Используется только поле MoneyK для приведения всех денежных значений бонуса к валюте пользователя.
type BonusCurrencyConfig struct {
	ID               uint64          `db:"id" json:"id"`                                   // Уникальный идентификатор конфигурации
	BonusType        int             `db:"bonus_type" json:"bonus_type"`                   // Тип бонуса, к которому относится эта конфигурация (всегда 0, не используется)
	Currency         int8            `db:"currency" json:"currency"`                       // Идентификатор валюты, к которой относится эта конфигурация
	Type             int             `db:"type" json:"type"`                               // не используется
	RoundNumbers     int             `db:"round_numbers" json:"round_numbers"`             // не используется
	SpinsK           float64         `db:"spins_k" json:"spins_k"`                         // Коэффициент для спинов (всегда 1, не используется)
	MoneyK           sql.NullFloat64 `db:"money_k" json:"money_k"`                         // Коэффициент для денег, используемых в бонусах
	WagerK           sql.NullFloat64 `db:"wager_k" json:"wager_k"`                         // Коэффициент для ставок, связанных с бонусами (не используется)
	MaxBetK          sql.NullFloat64 `db:"max_bet_k" json:"max_bet_k"`                     // Коэффициент для максимальной ставки, связанной с бонусами (не используется)
	MoneyToActivateK sql.NullFloat64 `db:"money_to_activate_k" json:"money_to_activate_k"` // Коэффициент для денег, необходимых для активации бонуса (не используется)
	MinDepositK      sql.NullFloat64 `db:"min_deposit_k" json:"min_deposit_k"`             // Коэффициент для минимального депозита, связанного с бонусами (не используется)
	MaxDepositK      sql.NullFloat64 `db:"max_deposit_k" json:"max_deposit_k"`             // Коэффициент для максимального депозита, связанного с бонусами (не используется)
	MinWinSumK       sql.NullFloat64 `db:"min_win_sum_k" json:"min_win_sum_k"`             // Коэффициент для минимальной суммы выигрыша, связанного с бонусами (не используется)
	MaxWinSumK       sql.NullFloat64 `db:"max_win_sum_k" json:"max_win_sum_k"`             // Коэффициент для максимальной суммы выигрыша, связанного с бонусами (не используется)
	MinWinCoefK      sql.NullFloat64 `db:"min_win_coef_k" json:"min_win_coef_k"`           // Коэффициент для минимального коэффициента выигрыша, связанного с бонусами (не используется)
	MaxWinCoefK      sql.NullFloat64 `db:"max_win_coef_k" json:"max_win_coef_k"`           // Коэффициент для максимального коэффициента выигрыша, связанного с бонусами (не используется)
	CreatedAt        sql.NullTime    `db:"created_at" json:"created_at"`                   // Время создания конфигурации
	UpdatedAt        sql.NullTime    `db:"updated_at" json:"updated_at"`                   // Время последнего обновления конфигурации
}
