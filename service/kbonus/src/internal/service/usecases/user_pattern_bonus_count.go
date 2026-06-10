package usecases

import "kbonus/internal/service"

// GetUserBonusWithPatternsIDsCount возвращает количество бонусов пользователя с заданными ID паттернов.
func GetUserBonusWithPatternsIDsCount(userID int64, patternIDs []int64) (int, error) {
	ub, err := service.GetUserBonuses(userID)
	if err != nil {
		return 0, err
	}

	count := 0
	for _, b := range ub.GetBonusIDs() {
		bonus, err := service.GetBonus(b, 100)
		if err != nil {
			return 0, err
		}
		for _, p := range patternIDs {
			if bonus.UserBonus.PatternID.Int64 == p {
				count++
			}
		}
	}

	return count, nil
}
