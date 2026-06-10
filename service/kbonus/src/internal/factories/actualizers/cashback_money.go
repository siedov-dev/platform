package actualizers

import (
	"encoding/json"
	"errors"
	"kbonus/internal/db/models"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"log/slog"
	"time"
)

var _ stypes.BonusActualizer = &CashBackMoneyActualizer{}

// NewCashBackMoneyActualizer создает новый экземпляр CashBackMoneyActualizer.
// Принимает GameWallet и указатель на UserBonus.
// Возвращает экземпляр CashBackMoneyActualizer.
func NewCashBackMoneyActualizer(wallet stypes.GameWallet, bonus *models.UserBonus) stypes.BonusActualizer {
	return &CashBackMoneyActualizer{wallet: wallet, bonus: bonus}
}

// CashBackMoneyActualizer реализует интерфейс BonusActualizer.
type CashBackMoneyActualizer struct {
	wallet stypes.GameWallet
	bonus  *models.UserBonus
}

// GetStatus возвращает статус бонуса для отображения.
func (a *CashBackMoneyActualizer) GetStatus() stypes.BonusStatusPad {
	s := stypes.BonusStatusPad{
		BonusStatus: int(a.bonus.Status),
		ShowPad:     false,
	}

	return s
}

// GetProgressHint возвращает строку с прогрессом бонуса для отображения.
func (a *CashBackMoneyActualizer) GetProgressHint() string {
	return ""
}

// Actualize актуализирует состояние бонуса и возвращает сумму выигрыша.
func (a *CashBackMoneyActualizer) Actualize() (win float64) {
	// Если бонус просрочен, ничего не делаем
	if a.bonus.Status == stypes.STATUS_EXPIRED {
		return
	}

	// Проверяем, просрочен ли бонус
	now := time.Now()
	if a.bonus.ActiveTo.Valid && a.bonus.ActiveTo.Time.Unix() < now.Unix() {
		now := time.Now()

		stat := models.BonusStatistic{
			BonusID:      int64(a.bonus.ID),
			BonusType:    int16(a.bonus.Type),
			PatternID:    a.bonus.PatternID.Int64,
			UserID:       int64(a.bonus.UserID),
			Action:       stypes.ACTION_EXPIRED,
			Amount:       a.bonus.MoneyAvailable,
			Currency:     int8(a.bonus.Currency),
			StatusBefore: a.bonus.Status,
			Status:       stypes.STATUS_EXPIRED,
			CreatedAt:    &now,
			UpdatedAt:    &now,
		}
		data, err := json.Marshal(map[string]float64{
			"money": a.bonus.MoneyAvailable,
			"wager": a.bonus.WagerToComplete,
		})
		if err != nil {
			logger.Get().Error("bonus actualizer error", slog.String("err", err.Error()))
		} else {
			datas := string(data)
			stat.Data = &datas
		}

		a.bonus.Status = stypes.STATUS_EXPIRED

		container.GetBonusStatisticStorage().Add(&stat)

		return
	}
	return
}

func (a *CashBackMoneyActualizer) CancelBonus() {
	statusBefore := a.bonus.Status
	a.bonus.Status = stypes.STATUS_CANCELED
	now := time.Now()

	// Записываем статистику по бонусу
	stat := models.BonusStatistic{
		BonusID:      int64(a.bonus.ID),
		BonusType:    int16(a.bonus.Type),
		PatternID:    a.bonus.PatternID.Int64,
		UserID:       int64(a.bonus.UserID),
		Action:       stypes.ACTION_CANCEL,
		Amount:       a.bonus.MoneyAvailable,
		Currency:     int8(a.bonus.Currency),
		StatusBefore: statusBefore,
		Status:       a.bonus.Status,
		CreatedAt:    &now,
		UpdatedAt:    &now,
	}
	container.GetBonusStatisticStorage().Add(&stat)
}

func (a *CashBackMoneyActualizer) TransferBonusMoney() (transferred float64, err error) {
	return 0, errors.New("transfer not supported for this bonus type")
}
