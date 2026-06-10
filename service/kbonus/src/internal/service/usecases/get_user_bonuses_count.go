package usecases

import (
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
)

func GetUserBonusesCount(userID stypes.UserID, currencyID currencyid.CurrencyID) (int, error) {
	bonuses, err := GetUserBonuses(userID, currencyID)
	if err != nil {
		return 0, err
	}
	return len(bonuses), nil
}
