package repositories

import (
	"games_manager/internal/db"
	"games_manager/internal/db/models"
	"log/slog"
)

type LandingGamesRepo struct{}

func (g LandingGamesRepo) GetLandingGames() ([]models.LandingGames, error) {
	db := db.New()
	defer db.Close()

	data := []models.LandingGames{}
	err := db.Select(&data, "SELECT id, cid, `type`, `lang` FROM landing_games")
	if err != nil {
		slog.Error(err.Error())
		return nil, err
	}

	return data, nil
}
