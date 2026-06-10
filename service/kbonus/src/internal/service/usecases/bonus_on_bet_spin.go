package usecases

func BonusOnBetSpin(bonusID int64, wagerPrc int16, amount float64) error {
	bonus, err := GetBonus(bonusID)
	if err != nil {
		return err
	}
	bonus.SetWagerPrc(wagerPrc)
	bonus.OnUserBet(amount)
	return nil
}
