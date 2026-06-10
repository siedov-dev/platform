package usecases

import (
	"kbonus/internal/service"
	"kbonus/internal/stypes"
)

func TransferBonusMoney(bonus_id stypes.BonusID) (transferred float64, err error) {
	bonus, err := service.GetBonus(bonus_id, 100)
	if err != nil {
		return 0, err
	}
	return bonus.TransferBonusMoney()
}
