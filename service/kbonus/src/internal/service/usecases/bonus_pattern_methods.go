package usecases

import (
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
)

func GetRegPattern(currency string, pType string, num int) (*models.BonusPattern, error) {
	return container.GetBonusPatternsDataProvider().GetRegPattern(currency, pType, num)
}

func GetPatternByName(name string) (*models.BonusPattern, error) {
	return container.GetBonusPatternsDataProvider().GetPatternByName(name)
}

func GetRegPatternIds(currency string) ([]int64, error) {
	return container.GetBonusPatternsDataProvider().GetRegPatternIds(currency)
}

func GetRegNonDepPatternIds(currency string) ([]int64, error) {
	return container.GetBonusPatternsDataProvider().GetRegNonDepPatternIds(currency)
}

func GetBonusPattern(id int64) (*models.BonusPattern, error) {
	return container.GetBonusPatternsDataProvider().GetBonusPattern(id)
}

func GetRegFsPatternIds(currency string) ([]int64, error) {
	return container.GetBonusPatternsDataProvider().GetRegFsPatternIds(currency)
}
