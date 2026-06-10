package usecases

import (
	"fmt"
	"kbonus/internal/service"
	"kbonus/internal/service/entities"
)

func DropBonus(bonusID int64) (*entities.Bonus, error) {
	bonus, err := service.GetBonus(bonusID, 100)
	if err != nil {
		return nil, err
	}
	if bonus == nil {
		return nil, fmt.Errorf("bonus %d not found", bonusID)
	}
	bonus.Drop()
	bonus.Actualize()
	if _, err := bonus.Save(); err != nil {
		return nil, err
	}
	return bonus, nil
}
