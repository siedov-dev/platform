package activators

import (
	"database/sql"
	"errors"
	"fmt"
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	jsonparams "kbonus/internal/utils/json_params"
	"strconv"
	"time"
)

type PSFreeSpinsActivator struct {
	bonus *models.UserBonus
}

func NewPSFreeSpinsActivator(bonus *models.UserBonus) stypes.BonusActivator {
	return &PSFreeSpinsActivator{bonus: bonus}
}

func (a *PSFreeSpinsActivator) ActivateBonus(value float64) error {
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

func (a *PSFreeSpinsActivator) ConfirmPayment(amount float64) (err error) {
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

	// Если указано ограничение по максимальной сумме для активации, применяем его
	maxSum := 0.0
	if maxSumVal, ok := a.bonus.GetJsonParam("max_sum"); ok {
		switch v := maxSumVal.(type) {
		case float64:
			maxSum = v
		case int:
			maxSum = float64(v)
		case int64:
			maxSum = float64(v)
		case string:
			maxSum, _ = strconv.ParseFloat(v, 64)
		}
	}
	if maxSum > 0 && amount > maxSum {
		amount = maxSum
	}
	a.bonus.Status = stypes.STATUS_ACTIVE

	a.bonus.MoneyAvailable = amount * a.bonus.MultiplierToPayout / 100
	a.bonus.WagerToComplete = a.bonus.MoneyAvailable * float64(a.bonus.Multiplier)

	activeTo := time.Now().Add(time.Duration(a.bonus.HoursActive) * 24 * time.Hour)
	a.bonus.ActiveTo = sql.NullTime{Time: activeTo, Valid: true}

	extFshandler, err := container.GetExtFsHandlersFactory().GetExtFsHandler(a.bonus.GameProvider.String, a.bonus)
	if err != nil {
		return err
	}
	max_x_win, err := jsonparams.GetParamOfTypeFloat(a.bonus.JSONParams.String, "max_x_win")
	if err == nil && max_x_win > 0 {
		a.bonus.MaxWin = sql.NullFloat64{Float64: max_x_win * amount, Valid: true}
	}
	err = extFshandler.ExternFreeSpinsCreate(fmt.Sprintf("%d", a.bonus.ID))
	if err != nil {
		return err
	}

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

func (a *PSFreeSpinsActivator) OnDeposit(amount float64) (err error) {
	return nil
}
