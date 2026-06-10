package models

// LandingGames - структура для хранения информации о играх на лэндинге
type LandingGames struct {
	ID   int64  `db:"id"`
	Type string `db:"type"`
	Lang string `db:"lang"`
	CID  string `db:"cid"`
}
