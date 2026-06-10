package usecases

import (
	"kbonus/internal/logger"
	"kbonus/internal/service"
	"kbonus/internal/service/entities"
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
)

func GetUserBonuses(userID stypes.UserID, currencyID currencyid.CurrencyID) ([]*entities.Bonus, error) {
	userBonuses := []*entities.Bonus{}
	bonuses, err := service.GetUserBonuses(userID)
	if err != nil {
		return userBonuses, err
	}
	for _, ub := range bonuses.GetBonusIDs() {
		bonus, err := service.GetBonus(ub, 100) // 100 - процент вейджеринга по умолчанию, не имеет значения для информации о бонусе
		if err != nil {
			logger.Get().Error("Failed to get bonus", "userID", userID, "bonusID", ub, "error", err)
			continue
		}
		if currencyID == 0 || bonus.GetCurrencyID() == currencyID {
			userBonuses = append(userBonuses, bonus)
		}
	}
	av_bonuses, err := GetAvailableBonuses(userID, int8(currencyID))
	if err != nil {
		logger.Get().Error("Failed to get available bonuses", "userID", userID, "currencyID", currencyID, "error", err)
	} else {
		userBonuses = append(userBonuses, av_bonuses...)
	}
	return userBonuses, nil
}
