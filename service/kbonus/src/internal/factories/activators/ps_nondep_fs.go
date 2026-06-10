package activators

import (
	"errors"
	"fmt"
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"time"
)

type PSNonDepFSActivator struct {
	bonus *models.UserBonus
}

func NewPSNonDepFSActivator(bonus *models.UserBonus) stypes.BonusActivator {
	return &PSNonDepFSActivator{bonus: bonus}
}

func (a *PSNonDepFSActivator) ActivateBonus(value float64) error {
	if a.bonus == nil {
		return errors.New("bonus is not set")
	}
	if a.bonus.Status != stypes.STATUS_NOT_ACTIVATED && a.bonus.Status != stypes.STATUS_WAITING_FOR_AGREEMENT {
		return errors.New("bonus is not in a valid state")
	}
	a.bonus.Status = stypes.STATUS_ACTIVE

	extFshandler, err := container.GetExtFsHandlersFactory().GetExtFsHandler(a.bonus.GameProvider.String, a.bonus)
	if err != nil {
		return err
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

func (a *PSNonDepFSActivator) ConfirmPayment(amount float64) (err error) {
	return nil
}

func (a *PSNonDepFSActivator) OnDeposit(amount float64) (err error) {
	return nil
}
