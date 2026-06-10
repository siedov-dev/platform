package usecases

import (
	"fmt"
	"kbonus/internal/service"
)

func CancelBonus(bonusID int64) error {
	bonus, err := service.GetBonus(bonusID, 100)
	if err != nil {
		return fmt.Errorf("failed to get bonus: %w", err)
	}
	if bonus == nil {
		return fmt.Errorf("bonus not found")
	}

	bonus.CancelBonus()
	_, err = bonus.Save()
	if err != nil {
		return fmt.Errorf("failed to save bonus: %w", err)
	}

	ActualizeUserBonuses(bonus.GetUserID())

	return nil
}
