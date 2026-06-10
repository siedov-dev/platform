package repositories

import (
	"games_manager/internal/db"
	"games_manager/internal/db/models"
)

type ProviderNamesRepo struct {
}

func (ub ProviderNamesRepo) GetAll() ([]*models.ProviderNames, error) {
	db := db.New()
	defer db.Close()

	var data []*models.ProviderNames
	err := db.Select(&data, "SELECT id, `system`, `group`, `name` FROM provider_names")
	if err != nil {
		return nil, err
	}

	return data, nil
}
