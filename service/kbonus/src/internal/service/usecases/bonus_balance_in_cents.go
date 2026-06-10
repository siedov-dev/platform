package usecases

func GetBonusBalanceInCents(bonusID int64) (int64, error) {
	bonus, err := GetBonus(bonusID)
	if err != nil {
		return 0, err
	}
	return bonus.GetBalanceCents(), nil
}
