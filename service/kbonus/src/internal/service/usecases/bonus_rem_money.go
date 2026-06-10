package usecases

func BonusRemMoney(bonusID int64, wagerPrc int16, amount float64) (balance float64, real_amount float64, bonus_amount float64, err error) {
	bonus, err := GetBonus(bonusID)
	if err != nil {
		return 0, 0, 0, err
	}
	bonus.SetWagerPrc(wagerPrc)
	return bonus.RemMoney(amount)
}
