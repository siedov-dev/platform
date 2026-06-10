package usecases

import (
	"kbonus/internal/service/entities"
	"kbonus/internal/utils/currencyid"
)

func GetFirstWaitingDepositBonus(userID int64, currencyID int8) (*entities.Bonus, error) {
	// Получаем список всех бонусов пользователя
	bonuses, err := GetUserBonuses(userID, currencyid.CurrencyID(currencyID))
	if err != nil {
		return nil, err
	}

	for _, bonus := range bonuses {
		if bonus.GetBonusInfo().IsPaymentRequired {
			return bonus, nil
		}
	}

	return nil, nil // Если не найдено ни одного ожидающего бонуса
}
