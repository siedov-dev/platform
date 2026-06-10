package usecases

import (
	"kbonus/internal/service"
	"kbonus/internal/stypes"
)

func ActivateBonus(id stypes.BonusID, value float64) error {
	bonus, err := service.GetBonus(id, 100)
	if err != nil {
		return err
	}
	bonus.Actualize()
	err = bonus.Activate(value)
	if err != nil {
		return err
	}
	_, err = bonus.Save()
	if err != nil {
		return err
	}
	ActualizeUserBonuses(bonus.GetUserID())
	return nil
}
