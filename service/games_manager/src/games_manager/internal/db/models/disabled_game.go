package models

// BonusDisabledGames - структура для хранения информации о отключенных играх
type DisabledGame struct {
	ID     int64  `db:"id"`
	Device string `db:"device"`
	Type   string `db:"type"`
	CID    string `db:"cid"`
}
