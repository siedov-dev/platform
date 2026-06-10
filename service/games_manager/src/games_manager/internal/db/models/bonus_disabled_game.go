package models

// BonusDisabledGames - структура для хранения информации о отключенных играх по бонусам
type BonusDisabledGames struct {
	ID        int64  `db:"id"`
	BonusType int16  `db:"bonus_type"`
	CID       string `db:"disabled_cid"`
}
