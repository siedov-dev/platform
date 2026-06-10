package usecases

import (
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
)

// GetBonusCurrencyConfigs возвращает конфигурации коэффициентов валют для параметров бонусов.
func GetBonusCurrencyConfig(currency int8) (*models.BonusCurrencyConfig, error) {
	return container.GetBonusCurrencyConfigDataProvider().GetBonusCurrencyConfig(currency)
}
