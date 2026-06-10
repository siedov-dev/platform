package usecases

import (
	"kbonus/internal/service"
	"slices"
)

// UserHasBonusesWithPatternIDs - проверяет, есть ли у пользователя бонусы с указанными patternID.
func UserHasBonusesWithPatternIDs(userID int64, patternIDs []int64) (bool, error) {
	bonuses, err := service.GetUserBonuses(userID)
	if err != nil {
		return false, err
	}
	for _, ub := range bonuses.GetBonusIDs() {
		bonus, err := service.GetBonus(ub, 100) // 100 - процент вейджеринга по умолчанию, не имеет значения для информации о бонусе
		if err != nil {
			return false, err
		}
		if !bonus.UserBonus.PatternID.Valid || bonus.UserBonus.PatternID.Int64 == 0 {
			continue
		}
		if slices.Contains(patternIDs, bonus.UserBonus.PatternID.Int64) {
			return true, nil
		}
	}
	return false, nil
}
