package usecases

import (
	"kbonus/internal/logger"
	"kbonus/internal/service"
	"kbonus/internal/stypes"
)

func CancelBonusesOnPayout(userID int64) {
	userBonuses, err := service.GetUserBonuses(userID)
	if err != nil {
		logger.Get().Error("CancelBonusesOnPayout: get user bonuses", "userID", userID, "error", err)
		return
	}
	for _, bonusID := range userBonuses.GetBonusIDs() {
		if _, ok := stypes.OnPayoutCancelableTypes[int8(bonusID)]; !ok {
			continue
		}
		bonus, err := service.GetBonus(bonusID, 100)
		if err != nil {
			logger.Get().Error("CancelBonusesOnPayout: get bonus", "userID", userID, "bonusID", bonusID, "error", err)
			continue
		}
		if bonus == nil {
			logger.Get().Error("CancelBonusesOnPayout: bonus not found", "userID", userID, "bonusID", bonusID)
			continue
		}
		bonus.CancelBonus()
	}
}
