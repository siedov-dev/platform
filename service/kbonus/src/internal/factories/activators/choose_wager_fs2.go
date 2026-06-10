package activators

import (
	"database/sql"
	"errors"
	"fmt"
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"strconv"
	"time"
)

type ChooseWagerFs2Activator struct {
	bonus *models.UserBonus
}

func NewChooseWagerFs2Activator(bonus *models.UserBonus) stypes.BonusActivator {
	return &ChooseWagerFs2Activator{bonus: bonus}
}

func (a *ChooseWagerFs2Activator) ActivateBonus(value float64) error {
	if a.bonus == nil {
		return errors.New("bonus is not set")
	}
	if a.bonus.Status != stypes.STATUS_NOT_ACTIVATED && a.bonus.Status != stypes.STATUS_WAITING_FOR_AGREEMENT {
		return errors.New("bonus is not in a valid state")
	}
	params, err := container.GetBonusParamsDataProvider().GetParamByKey(a.bonus.Type, a.bonus.Currency, value)
	if err != nil {
		return err
	}
	if params == nil {
		return errors.New("bonus params not found")
	}
	if !params.Value.Valid {
		return errors.New("bonus param value not found")
	}
	if !params.MinValue.Valid {
		return errors.New("bonus param min value not found")
	}
	wager := params.Value.Float64
	a.bonus.WagerToComplete = wager * a.bonus.MoneyAvailable
	a.bonus.WagerToPayout = wager
	a.bonus.MultiplierToPayout = wager
	a.bonus.MultiplierToComplete = wager
	a.bonus.Multiplier = float32(wager)
	a.bonus.MaxWin = sql.NullFloat64{Float64: a.bonus.MoneyAvailable, Valid: true}
	a.bonus.MoneyToActivate = fmt.Sprintf("%.2f", params.MinValue.Float64)
	a.bonus.Status = stypes.STATUS_WAITING_FOR_PAYMENT

	if a.bonus.MoneyToActivate != "" && a.bonus.MoneyToActivate != "0" {
		a.bonus.Status = stypes.STATUS_WAITING_FOR_PAYMENT
	} else {
		a.bonus.Status = stypes.STATUS_ACTIVE
	}

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

func (a *ChooseWagerFs2Activator) ConfirmPayment(amount float64) (err error) {
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

	if amount < moneyToActivate {
		return errors.New("insufficient payment amount")
	}

	ext_fs_handler, err := container.GetExtFsHandlersFactory().GetExtFsHandler(a.bonus.GameProvider.String, a.bonus)
	if err != nil {
		return err
	}
	if err := ext_fs_handler.ExternFreeSpinsCreate(fmt.Sprintf("%d", a.bonus.ID)); err != nil {
		return err
	}

	a.bonus.MoneyAvailable += amount
	a.bonus.WagerToComplete = a.bonus.MoneyAvailable * float64(a.bonus.MultiplierToComplete)
	a.bonus.MaxWin = sql.NullFloat64{Float64: amount * 5.0, Valid: true}
	a.bonus.Status = stypes.STATUS_ACTIVE

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

func (a *ChooseWagerFs2Activator) OnDeposit(amount float64) (err error) {
	return nil
}
