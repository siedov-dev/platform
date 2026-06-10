package models

import (
	"database/sql"
)

// M_User представляет данные пользователя, связанные с базой данных.
type M_User struct {
	ID         int64          `db:"id"`       // ID — уникальный идентификатор пользователя.
	Login      sql.NullString `db:"login"`    // Login — логин пользователя. Поле создано как nullable, но не может быть пустым.
	Email      sql.NullString `db:"email"`    // Email — электронная почта пользователя, может быть NULL.
	Phone      sql.NullString `db:"phone"`    // Phone — номер телефона пользователя, может быть NULL.
	CurrencyId int            `db:"currency"` // CurrencyId — идентификатор текущей валюты пользователя.
	Token      sql.NullString `db:"token"`    // Token — токен доступа пользователя, может быть NULL.
	LastIp     sql.NullString `db:"last_ip"`  // LastIp — последний IP-адрес пользователя, может быть NULL.
}
