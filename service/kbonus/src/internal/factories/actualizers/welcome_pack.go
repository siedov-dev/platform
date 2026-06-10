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

var _ stypes.BonusActualizer = &WelcomeBonusPackActualizer{}

// NewWelcomeBonusPackActualizer создает новый экземпляр WelcomeBonusPackActualizer.
// Принимает GameWallet и указатель на UserBonus.
// Возвращает экземпляр WelcomeBonusPackActualizer.
func NewWelcomeBonusPackActualizer(wallet stypes.GameWallet, bonus *models.UserBonus) stypes.BonusActualizer {
	return &WelcomeBonusPackActualizer{wallet: wallet, bonus: bonus}
}

// WelcomeBonusPackActualizer реализует интерфейс BonusActualizer.
type WelcomeBonusPackActualizer struct {
	wallet stypes.GameWallet
	bonus  *models.UserBonus
}

// GetStatus возвращает статус бонуса для отображения.
func (a *WelcomeBonusPackActualizer) GetStatus() stypes.BonusStatusPad {
	s := stypes.BonusStatusPad{
		BonusStatus: int(a.bonus.Status),
		ShowPad:     false,
	}
	return s
}

// GetProgressHint возвращает строку с прогрессом бонуса для отображения.
func (a *WelcomeBonusPackActualizer) GetProgressHint() string {
	return ""
}

// Actualize актуализирует состояние бонуса и возвращает сумму выигрыша.
func (a *WelcomeBonusPackActualizer) Actualize() (win float64) {
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

	if a.getStep() == 4 {
		now := time.Now()
		if a.bonus.Status == stypes.STATUS_PENDING {
			nextDayDate := a.bonus.CompletedAt.Time.Add(24 * time.Hour)
			if nextDayDate.Unix() < now.Unix() {
				a.bonus.Status = stypes.STATUS_WAITING_FOR_AGREEMENT
			}
		}
		if a.bonus.Status == stypes.STATUS_WAITING_FOR_AGREEMENT || a.bonus.Status == stypes.STATUS_NOT_ACTIVATED {
			nextDayDate := a.bonus.CompletedAt.Time.Add(2 * 24 * time.Hour)
			if nextDayDate.Unix() < now.Unix() {
				a.setStep(5)
			}
		}
	}
	if a.getStep() == 5 {
		now := time.Now()
		if a.bonus.Status == stypes.STATUS_PENDING {
			nextDayDate := a.bonus.CompletedAt.Time.Add(2 * 24 * time.Hour)
			if nextDayDate.Unix() < now.Unix() {
				a.bonus.Status = stypes.STATUS_WAITING_FOR_AGREEMENT
			}
		}
		if a.bonus.Status == stypes.STATUS_WAITING_FOR_AGREEMENT || a.bonus.Status == stypes.STATUS_NOT_ACTIVATED {
			nextDayDate := a.bonus.CompletedAt.Time.Add(3 * 24 * time.Hour)
			if nextDayDate.Unix() < now.Unix() {
				a.bonus.Status = stypes.STATUS_DONE
			}
		}
	}

	return
}

func (a *WelcomeBonusPackActualizer) CancelBonus() {
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

func (a *WelcomeBonusPackActualizer) getStep() int {
	return int(a.bonus.Tries)
}

func (a *WelcomeBonusPackActualizer) setStep(day_n int) {
	a.bonus.Tries = day_n
}

func (a *WelcomeBonusPackActualizer) TransferBonusMoney() (transferred float64, err error) {
	return 0, errors.New("transfer not supported for this bonus type")
}
