package models

import "time"

// BonusTransaction представляет собой транзакцию, связанную с бонусами пользователей.
// Используется для сохранения информации о выигрышах на бонусах.
type BonusTransaction struct {
	UserID    int64     `db:"user_id"`   // UserID идентификатор пользователя
	HallID    int64     `db:"hall_id"`   // HallID идентификатор зала (не используется)
	Date      time.Time `db:"date"`      // Date дата и время выполнения транзакции.
	Sum       float64   `db:"sum"`       // Sum сумма транзакции.
	StatusID  int64     `db:"status_id"` // StatusID идентификатор типа транзакции.
	Currency  string    `db:"currency"`  // Currency строковый код валюты, используемой в транзакции.
	Operation string    `db:"operation"` // Operation описание операции, выполняемой в рамках транзакции.
}
