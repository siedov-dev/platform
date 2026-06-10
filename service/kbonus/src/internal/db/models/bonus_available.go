package models

import (
	"database/sql"
	"time"
)

// BonusAvailable - структура, описывающая доступный бонус
type BonusAvailable struct {
	ID                  int64          `db:"id" json:"id"`                                         // Уникальный идентификатор доступного бонуса
	BonusType           int8           `db:"bonus_type" json:"bonus_type"`                         // Тип бонуса (см. константы в bonus.go)
	CurrencyID          int8           `db:"currency_id" json:"currency_id"`                       // Идентификатор валюты
	Params              sql.NullString `db:"params" json:"params"`                                 // Параметры бонуса в формате JSON
	AllowedUsers        sql.NullString `db:"allowed_users" json:"allowed_users"`                   // Список разрешенных пользователей в формате JSON
	UsersActivated      sql.NullString `db:"users_activated" json:"users_activated"`               // Список пользователей, которые активировали бонус
	Games               sql.NullString `db:"games" json:"games"`                                   // Список разрешенных игр в формате JSON
	AvailableFrom       time.Time      `db:"available_from" json:"available_from"`                 // Дата и время, с которых бонус становится доступен
	AvailableTo         time.Time      `db:"available_to" json:"available_to"`                     // Дата и время, до которых бонус доступен
	CreatedAt           sql.NullTime   `db:"created_at" json:"created_at"`                         // Дата и время создания записи
	UpdatedAt           sql.NullTime   `db:"updated_at" json:"updated_at"`                         // Дата и время последнего обновления записи
	Title               sql.NullString `db:"title" json:"title"`                                   // Заголовок бонуса
	ImgURL              sql.NullString `db:"img_url" json:"img_url"`                               // URL изображения бонуса
	Descr               sql.NullString `db:"descr" json:"descr"`                                   // Описание бонуса
	PatternID           sql.NullInt64  `db:"pattern_id" json:"pattern_id"`                         // Идентификатор шаблона бонуса
	UsersCanceled       sql.NullString `db:"users_canceled" json:"users_canceled"`                 // Список пользователей, которые отменили бонус
	WagerGamesSegmentID sql.NullInt64  `db:"wager_games_segment_id" json:"wager_games_segment_id"` // Идентификатор сегмента игр для отыгрыша
}
