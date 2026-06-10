package usecases

import "kbonus/internal/stypes"

func BonusStatusPad(bonus_id int64) (stypes.BonusStatusPad, error) {
	bonus, err := GetBonus(bonus_id)
	if err != nil {
		return stypes.BonusStatusPad{}, err
	}
	return bonus.GetStatusPad(), nil
}
