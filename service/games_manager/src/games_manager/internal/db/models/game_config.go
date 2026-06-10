package models

import "database/sql"

// GameConfig - структура для хранения информации о конфигурации игр
type GameConfig struct {
	ID              int64          `db:"id"`               // ID в базе
	CID             string         `db:"cid"`              // CID игры
	System          string         `db:"system"`           // игровая система
	Group           sql.NullString `db:"group"`            // группа(провайдер) игры
	GameID          string         `db:"game_id"`          // ID игры в игровой системе
	Name            sql.NullString `db:"name"`             // название игры
	Img             sql.NullString `db:"img_url"`          // URL изображения игры
	Category        sql.NullString `db:"category"`         // категория игры
	LandingCategory sql.NullString `db:"landing_category"` // категория игры на лэндинге
	LandingGroup    sql.NullString `db:"landing_group"`    // группа игры на лэндинге
	Title           sql.NullString `db:"title"`            // заголовок игры (заменяет название)
	DeviceType      sql.NullString `db:"device_type"`      // тип устройства (мобильный, десктоп, any - любой)
	CustomImg       sql.NullString `db:"custom_img"`       // URL кастомного(установленного вручную) изображения игры
}
