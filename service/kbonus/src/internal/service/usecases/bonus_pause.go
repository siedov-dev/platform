package usecases

import (
	"kbonus/internal/service"
	"kbonus/internal/service/entities"
	"kbonus/internal/stypes"
)

func BonusPause(id stypes.BonusID) (*entities.Bonus, error) {
	bonus, err := service.GetBonus(id, 100)
	if err != nil {
		return nil, err
	}
	bonus.Actualize()
	err = bonus.Pause()
	if err != nil {
		return nil, err
	}
	_, err = bonus.Save()
	if err != nil {
		return nil, err
	}
	return bonus, nil
}
