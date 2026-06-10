package activators

import (
	"database/sql"
	"errors"
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	jsonparams "kbonus/internal/utils/json_params"
	"strconv"
	"time"
)

type PlusMinusDepActivator struct {
	bonus *models.UserBonus
}

func NewPlusMinusDepActivator(bonus *models.UserBonus) stypes.BonusActivator {
	return &PlusMinusDepActivator{bonus: bonus}
}

func (a *PlusMinusDepActivator) ActivateBonus(value float64) error {
	if a.bonus == nil {
		return errors.New("bonus is not set")
	}
	if a.bonus.Status != stypes.STATUS_NOT_ACTIVATED && a.bonus.Status != stypes.STATUS_WAITING_FOR_AGREEMENT {
		return errors.New("bonus is not in a valid state")
	}
	a.bonus.Status = stypes.STATUS_WAITING_FOR_PAYMENT
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

	return nil
}

func (a *PlusMinusDepActivator) ConfirmPayment(amount float64) (err error) {
	if a.bonus == nil {
		return errors.New("bonus is not set")
	}
	if a.bonus.Status != stypes.STATUS_WAITING_FOR_PAYMENT {
		return errors.New("bonus is not in a valid state")
	}
	if a.bonus.MoneyToActivate == "" {
		return errors.New("bonus money to activate not found")
	}
	moneyToActivate, err := strconv.ParseFloat(a.bonus.MoneyToActivate, 64)
	if err != nil {
		return errors.New("failed to parse bonus money to activate ")
	}

	if moneyToActivate > amount {
		return errors.New("insufficient payment amount")
	}

	a.bonus.MoneyAvailable = amount * 2
	a.bonus.WagerToComplete = amount * 2
	a.bonus.WagerToPayout = a.bonus.WagerToComplete - amount
	a.bonus.Status = stypes.STATUS_ACTIVE

	activeDays := 3
	if val, err := jsonparams.GetParamOfTypeInt(a.bonus.JSONParams.String, "active_days"); err == nil {
		activeDays = val
	}
	activeTo := time.Now().AddDate(0, 0, int(activeDays))
	a.bonus.ActiveTo = sql.NullTime{Time: activeTo, Valid: true}

	// Записываем статистику по бонусу
	now := time.Now()
	stat := models.BonusStatistic{
		BonusID:      int64(a.bonus.ID),
		BonusType:    int16(a.bonus.Type),
		PatternID:    a.bonus.PatternID.Int64,
		UserID:       int64(a.bonus.UserID),
		Action:       stypes.ACTION_DEPOSIT,
		Amount:       amount,
		Currency:     int8(a.bonus.Currency),
		StatusBefore: stypes.STATUS_WAITING_FOR_PAYMENT,
		Status:       a.bonus.Status,
		CreatedAt:    &now,
		UpdatedAt:    &now,
	}
	container.GetBonusStatisticStorage().Add(&stat)

	return nil
}

func (a *PlusMinusDepActivator) OnDeposit(amount float64) (err error) {
	return nil
}
