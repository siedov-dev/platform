package models

import "database/sql"

// UserCurrency представляет сущность валюты, используемой пользователем в системе.
type UserCurrency struct {
	ID            int64          `db:"id" json:"id"`                           // ID уникальный идентификатор валюты.
	Name          string         `db:"name" json:"name"`                       // Name название валюты.
	GameCurrency  sql.NullString `db:"game_currency" json:"gc"`                // GameCurrency игровая валюта (может быть Null). // Например для USDT игровая валюта может быть USD
	HallId        int64          `db:"hall_id" json:"hall_id"`                 // HallId id хола, не используется
	Symbol        string         `db:"symbol" json:"symbol"`                   // Symbol символ валюты (например, $, €, ₽).
	Code          int64          `db:"code" json:"code"`                       // Code числовой код валюты по международному стандарту.
	IsCrypto      bool           `db:"is_crypto" json:"is_crypto"`             // IsCrypto флаг, указывающий, является ли валюта криптовалютой.
	MaxAutoPayout float64        `db:"max_auto_payout" json:"max_auto_payout"` // MaxAutoPayout максимальная сумма автоматической выплаты.
}
