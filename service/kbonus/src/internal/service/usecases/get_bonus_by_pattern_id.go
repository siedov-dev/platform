package usecases

import (
	"kbonus/internal/service"
	"kbonus/internal/service/entities"
	"kbonus/internal/stypes"
)

func GetUserBonusByPatternId(user_id stypes.UserID, pattern_id int64) (*entities.Bonus, error) {
	userBonuses, err := service.GetUserBonuses(user_id)
	if err != nil {
		return nil, err
	}
	for _, ub := range userBonuses.GetBonusIDs() {
		bonus, err := service.GetBonus(ub, 100)
		if err != nil {
			return nil, err
		}
		if bonus != nil && bonus.GetPatternID() == pattern_id {
			return bonus, nil
		}
	}
	return nil, nil
}
