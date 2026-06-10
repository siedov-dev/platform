package usecases

import (
	"kbonus/internal/service"
	"kbonus/internal/stypes"
)

func BonusGameActualize(id stypes.BonusID, WagerPrc int16) (win float64, err error) {
	bonus, err := service.GetBonus(id, 100)
	if err != nil {
		return 0, err
	}
	bonus.SetWagerPrc(WagerPrc)
	win = bonus.Actualize()
	_, err = bonus.Save()
	if err != nil {
		return 0, err
	}
	return win, nil
}
