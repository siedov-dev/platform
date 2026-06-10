package repositories

import (
	"games_manager/internal/db"
	"games_manager/internal/db/models"
	"log/slog"
)

type DisabledBonusGamesRepo struct{}

func (g DisabledBonusGamesRepo) GetDisabledGamesCIDs() ([]models.BonusDisabledGames, error) {
	db := db.New()
	defer db.Close()

	data := []models.BonusDisabledGames{}
	err := db.Select(&data, "SELECT id, disabled_cid, bonus_type FROM bonus_disabled_games")
	if err != nil {
		slog.Error(err.Error())
		return nil, err
	}

	return data, nil
}
