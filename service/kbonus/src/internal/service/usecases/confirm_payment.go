package usecases

import (
	"errors"
	"kbonus/internal/service"
	"kbonus/internal/stypes"
)

func ConfirmPayment(bonus_id stypes.BonusID, value float64, currency_id int8) error {
	bonus, err := service.GetBonus(bonus_id, 100)
	if err != nil {
		return err
	}
	if bonus == nil {
		return errors.New("bonus not found")
	}
	bonus.Actualize()

	if err := bonus.ConfirmPayment(value, currency_id); err != nil {
		return err
	}
	_, err = bonus.Save()
	if err != nil {
		return err
	}
	ActualizeUserBonuses(bonus.GetUserID())

	return nil
}
