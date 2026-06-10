package models

import (
	"database/sql"
	"encoding/json"
	"errors"
)

// UserBonus - структура для хранения информации о бонусах пользователей
type UserBonus struct {
	Id                  int            `db:"id"`
	UserId              int            `db:"user_id"`
	Type                int            `db:"type"`
	AllowedGames        sql.NullString `db:"allowed_games"`
	GameProvider        sql.NullString `db:"game_provider"`
	GameID              sql.NullString `db:"game_id"`
	WagerGamesSegmentID sql.NullInt64  `db:"wager_games_segment_id"`
}

// BonusGames - список игр, доступных для бонуса
type BonusGames map[string][]string

// GetGames - метод для получения списка игр, доступных для бонуса
func (b *UserBonus) GetGames() (BonusGames, error) {
	var bonus_games BonusGames
	if !b.AllowedGames.Valid {
		return nil, errors.New("empty allowed games")
	}

	err := json.Unmarshal([]byte(b.AllowedGames.String), &bonus_games)
	if err != nil {
		return nil, err
	}

	return bonus_games, nil
}
