package repositories

import (
	"games_manager/internal/db"
	"games_manager/internal/db/models"
	"log/slog"
)

type GroupPriorytyRepo struct{}

func (g GroupPriorytyRepo) GetPriorities() ([]models.GameGroupPriority, error) {
	db := db.New()
	defer db.Close()

	data := []models.GameGroupPriority{}
	err := db.Select(&data, "SELECT `system_id`, `group_id`, `priority` FROM `game_group_priorities`")
	if err != nil {
		slog.Error(err.Error())
		return nil, err
	}

	return data, nil
}
