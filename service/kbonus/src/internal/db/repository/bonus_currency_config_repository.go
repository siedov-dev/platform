package repository

import (
	"kbonus/internal/db"
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
)

func NewBonusCurrencyConfigDbRepository() container.BonusCurrencyConfigRepository {
	return &dbBonusCurrencyConfigRepository{}
}

type dbBonusCurrencyConfigRepository struct {
}

func (r *dbBonusCurrencyConfigRepository) GetConfigs() ([]*models.BonusCurrencyConfig, error) {
	db := db.Instance()
	query := "SELECT * FROM bonus_currency_configs"
	var configs []*models.BonusCurrencyConfig
	err := db.Select(&configs, query)
	if err != nil {
		return nil, err
	}
	if len(configs) == 0 {
		return nil, nil // Возвращаем nil, если нет конфигураций
	}
	return configs, nil
}
