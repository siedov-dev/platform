package usecases

import (
	"kbonus/internal/service"
	"kbonus/internal/service/entities"
)

func GetBonus(bonusID int64) (*entities.Bonus, error) {
	// Получаем информацию о бонусе по его ID
	bonus, err := service.GetBonus(bonusID, 100)
	if err != nil {
		return nil, err
	}

	// Возвращаем информацию о бонусе
	return bonus, nil
}
