package usecases

import (
	"kbonus/internal/logger"
	"kbonus/internal/service"
	"kbonus/internal/service/entities"
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
)

func GetUserCompletedBonuses(userID stypes.UserID, currencyID currencyid.CurrencyID) ([]*entities.Bonus, error) {
	userBonuses := []*entities.Bonus{}
	bonuses, err := service.GetUserBonuses(userID)
	if err != nil {
		return userBonuses, err
	}
	for _, ub := range bonuses.GetCompletedBonuses() {
		bonus, err := service.GetBonus(ub, 100) // 100 - процент вейджеринга по умолчанию, не имеет значения для информации о бонусе
		if err != nil {
			logger.Get().Error("Failed to get bonus", "userID", userID, "bonusID", ub, "error", err)
			continue
		}
		if currencyID == 0 || bonus.GetCurrencyID() == currencyID {
			userBonuses = append(userBonuses, bonus)
		}
	}

	return userBonuses, nil
}
