package usecases

import (
	"kbonus/internal/service/entities"
	"kbonus/internal/utils/currencyid"
)

func GetFirstNotActiveBonus(userID int64, currency int8) (*entities.Bonus, error) {
	// Получаем список всех бонусов пользователя
	bonuses, err := GetUserBonuses(userID, currencyid.CurrencyID(currency))
	if err != nil {
		return nil, err
	}

	for _, bonus := range bonuses {
		if bonus.GetBonusInfo().IsAgreementRequired || bonus.GetBonusInfo().IsPaymentRequired {
			return bonus, nil
		}
	}

	return nil, nil // Если не найдено ни одного неактивированного бонуса
}
