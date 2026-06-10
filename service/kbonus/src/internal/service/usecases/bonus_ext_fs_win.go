package usecases

func BonusExtFsWin(bonusID int64, wagerPrc int16, amount float64, provider string) (float64, error) {
	bonus, err := GetBonus(bonusID)
	if err != nil {
		return 0, err
	}
	bonus.SetWagerPrc(wagerPrc)
	bonus.ExternFreeSpinWin(amount, provider)
	return bonus.GetBalance(), nil
}
