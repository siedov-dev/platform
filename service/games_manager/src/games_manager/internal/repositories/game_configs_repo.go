package repositories

import (
	"games_manager/internal/db"
	"games_manager/internal/db/models"
	"log/slog"
)

type GameConfigRepo struct{}

func (g GameConfigRepo) GetGameConfigs() ([]models.GameConfig, error) {
	db := db.New()
	defer db.Close()

	data := []models.GameConfig{}
	err := db.Select(&data, "SELECT id, cid, `system`, `group`, game_id, name, img_url, category,"+
		" landing_category, landing_group, title, device_type, custom_img FROM game_configs"+
		" WHERE deleted=0")
	if err != nil {
		slog.Error(err.Error())
		return nil, err
	}

	return data, nil
}
