package activators

import (
	"errors"
	"fmt"
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
	"time"
)

type CashbackMoneyActivator struct {
	bonus  *models.UserBonus
	wallet stypes.GameWallet
}

func NewCashbackMoneyActivator(bonus *models.UserBonus, wallet stypes.GameWallet) stypes.BonusActivator {
	return &CashbackMoneyActivator{bonus: bonus, wallet: wallet}
}

func (a *CashbackMoneyActivator) ActivateBonus(value float64) error {
	if a.bonus == nil {
		return errors.New("bonus is not set")
	}
	if a.bonus.Status != stypes.STATUS_NOT_ACTIVATED && a.bonus.Status != stypes.STATUS_WAITING_FOR_AGREEMENT {
		return errors.New("bonus is not in a valid state")
	}

	a.bonus.Status = stypes.STATUS_DONE
	a.wallet.AddMoney(a.bonus.MoneyAvailable)
	a.bonus.MoneyPaidOut = a.bonus.MoneyAvailable
	a.bonus.MoneyAvailable = 0
	// Записываем статистику по бонусу
	now := time.Now()
	stat := models.BonusStatistic{
		BonusID:      int64(a.bonus.ID),
		BonusType:    int16(a.bonus.Type),
		PatternID:    a.bonus.PatternID.Int64,
		UserID:       int64(a.bonus.UserID),
		Action:       stypes.ACTION_ACTIVATE,
		Amount:       a.bonus.MoneyPaidOut,
		Currency:     int8(a.bonus.Currency),
		StatusBefore: stypes.STATUS_NOT_ACTIVATED,
		Status:       a.bonus.Status,
		CreatedAt:    &now,
		UpdatedAt:    &now,
	}
	container.GetBonusStatisticStorage().Add(&stat)
	a.writeTransaction(a.bonus.MoneyPaidOut)

	return nil
}

func (a *CashbackMoneyActivator) ConfirmPayment(amount float64) (err error) {

	return nil
}

func (a *CashbackMoneyActivator) OnDeposit(amount float64) (err error) {
	return nil
}

// writeTransaction записывает транзакцию в базу данных.
func (a *CashbackMoneyActivator) writeTransaction(amount float64) {
	curr, _ := currencyid.CurrencyID(a.bonus.Currency).Name()
	tr := models.BonusTransaction{
		UserID:    int64(a.bonus.UserID),
		HallID:    0,
		Date:      time.Now(),
		Sum:       amount,
		StatusID:  35,
		Currency:  curr,
		Operation: fmt.Sprintf("cashback activated id# %d", a.bonus.ID),
	}

	container.GetBonusTransactionRepository().Insert(&tr)
}
