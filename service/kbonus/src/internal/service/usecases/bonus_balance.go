package usecases

func GetBonusBalance(bonusID int64, wagerPrc int16) (float64, error) {
	bonus, err := GetBonus(bonusID)
	if err != nil {
		return 0, err
	}
	bonus.SetWagerPrc(wagerPrc)
	return bonus.GetBalance(), nil
}
