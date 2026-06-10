package models

import "time"

// UserGameStats представляет статистику игрового сеанса пользователя.
// Содержит данные о балансе, ставках, выигрыше и временных отметках.
// Используется для хранения игровых сессий.
type UserGameStats struct {
	ID             int64      `json:"id" db:"id"`                                       // ID является уникальным идентификатором записи в таблице user_game_stats.
	UserID         int64      `json:"user_id" db:"user_id"`                             // UserID представляет идентификатор пользователя, связанный с игровой сессией.
	Currency       string     `json:"currency" db:"currency"`                           // Currency указывает валюту, использующуюся в игровом сеансе.
	SessionID      *string    `json:"session_id,omitempty" db:"session_id"`             // SessionID указывает идентификатор сессии пользователя.
	System         *string    `json:"system,omitempty" db:"system"`                     // System ид игровой системы (sleg, qt, pragplay).
	Group          *string    `json:"group,omitempty" db:"group"`                       // Group игровая группа (провайдер).
	GameID         *string    `json:"game_id,omitempty" db:"game_id"`                   // GameID представляет идентификатор игры в формате строки.
	LoginBalance   float64    `json:"login_balance" db:"login_balance"`                 // LoginBalance баланс пользователя на момент запуска игровой сессии
	LogoutBalance  float64    `json:"logout_balance" db:"logout_balance"`               // LogoutBalance баланс пользователя на момент завершения игровой сессии
	BalanceDiff    float64    `json:"balance_diff" db:"balance_diff"`                   // BalanceDiff Разница балансов на момент завершения игровой сессии
	FullTotalBet   float64    `json:"full_total_bet" db:"full_total_bet"`               // FullTotalBet представляет общую сумму ставок за сессию, включая бонусные и реальные средства.
	FullTotalWin   float64    `json:"full_total_win" db:"full_total_win"`               // FullTotalWin представляет общую сумму выигрышей за игровую сессию, включая бонусные и реальные средства.
	RealTotalBet   float64    `json:"real_total_bet" db:"real_total_bet"`               // RealTotalBet отражает сумму всех ставок, сделанных пользователем за игровой сеанс (без бонусных ставок).
	RealTotalWin   float64    `json:"real_total_win" db:"real_total_win"`               // RealTotalWin отражает сумму всех выигрышей, сделанных пользователем за игровой сеанс (без бонусных ставок).
	TotalBet       float64    `json:"total_bet" db:"total_bet"`                         // TotalBet представляет общую сумму ставок пользователя за игровой сеанс (без бонусных ставок).
	TotalWin       float64    `json:"total_win" db:"total_win"`                         // TotalWin представляет суммарный выигрыш пользователя за игровой сеанс (без бонусных ставок).
	LoginDate      *time.Time `json:"login_date,omitempty" db:"login_date"`             // LoginDate представляет дату и время входа пользователя в игровую сессию.
	LogoutDate     *time.Time `json:"logout_date,omitempty" db:"logout_date"`           // LogoutDate указывает время выхода пользователя из игровой сессии или окончания сеанса.
	LastActiveDate *time.Time `json:"last_active_date,omitempty" db:"last_active_date"` // LastActiveDate указывает на дату и время последней активности пользователя в игровой сессии.
	CreatedAt      *time.Time `json:"created_at,omitempty" db:"created_at"`             // CreatedAt указывает время создания записи в базу данных.
	UpdatedAt      *time.Time `json:"updated_at,omitempty" db:"updated_at"`             // UpdatedAt указывает время последнего обновления записи в базе данных.
	OuterTable     *string    `json:"outer_table,omitempty" db:"outer_table"`           // OuterTable не используется
	OuterID        *int64     `json:"outer_id,omitempty" db:"outer_id"`                 // OuterID не используется
	Status         int        `json:"status" db:"status"`                               // Status не используется
	BonusID        *int64     `json:"bonus_id" db:"bonus_id"`                           // BonusID указывает на идентификатор бонуса, связанный с игровой сессией пользователя, может быть пустым.
}
