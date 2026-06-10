package repositories

import (
	"games_manager/internal/db"
	"games_manager/internal/db/models"
	"log/slog"
)

type DisabledGamesRepo struct{}

func (g DisabledGamesRepo) GetDisabledGames() ([]models.DisabledGame, error) {
	db := db.New()
	defer db.Close()

	data := []models.DisabledGame{}
	err := db.Select(&data, "SELECT id, cid, device, type FROM disabled_games")
	if err != nil {
		slog.Error(err.Error())
		return nil, err
	}

	return data, nil
}
