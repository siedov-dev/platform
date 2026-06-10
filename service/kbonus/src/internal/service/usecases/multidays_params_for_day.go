package usecases

import (
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
)

func MultiDaysParamsForDay(day int) *models.BonusMultiDaysParam {
	return container.GetMultiDaysBonusPramsDataProvider().GetParamsForDay(day)
}
