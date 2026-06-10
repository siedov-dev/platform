package actualizers

import (
	"encoding/json"
	"errors"
	"fmt"
	"kbonus/internal/db/models"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
	"log/slog"
	"strconv"
	"time"
)

var _ stypes.BonusActualizer = &PlusMinusActualizer{}

// NewPlusMinusActualizer создает новый экземпляр PlusMinusActualizer.
// Принимает GameWallet и указатель на UserBonus.
// Возвращает экземпляр PlusMinusActualizer.
// Используется для бонуса PlusMinus.
func NewPlusMinusActualizer(wallet stypes.GameWallet, bonus *models.UserBonus) stypes.BonusActualizer {
	return &PlusMinusActualizer{wallet: wallet, bonus: bonus}
}

// PlusMinusActualizer структура для бонуса PlusMinus.
type PlusMinusActualizer struct {
	wallet    stypes.GameWallet // Кошелек игрока
	bonus     *models.UserBonus // Бонус
	aboutDone bool              // Флаг, указывающий, что бонус завершается
}

// GetStatus возвращает статус бонуса для отображения.
func (a *PlusMinusActualizer) GetStatus() stypes.BonusStatusPad {
	s := stypes.BonusStatusPad{
		BonusStatus: int(a.bonus.Status),
		ShowPad:     true,
	}

	if a.bonus.Status != stypes.STATUS_ACTIVE {
		s.ShowPad = false
		return s
	}

	s.Title = "bonus_5_win"
	s.Value = ""
	s.Progress = a.GetProgressHint()
	return s
}

// GetProgressHint возвращает строку с прогрессом бонуса для отображения.
func (a *PlusMinusActualizer) GetProgressHint() string {
	if a.bonus.Status == stypes.STATUS_ACTIVE {
		return fmt.Sprintf("%.2f/%.2f", a.bonus.MoneyAvailable, a.bonus.WagerToComplete)
	}
	return ""
}

// Actualize актуализирует состояние бонуса и возвращает сумму выигрыша.
func (a *PlusMinusActualizer) Actualize() (win float64) {
	// Если бонус просрочен, ничего не делаем
	if a.bonus.Status == stypes.STATUS_EXPIRED {
		return
	}

	// Если срок действия бонуса истек, устанавливаем статус просрочен
	now := time.Now()
	if a.bonus.ActiveTo.Valid && a.bonus.ActiveTo.Time.Unix() < now.Unix() {
		now := time.Now()

		// Записываем статистику по бонусу
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
		// Записываем данные в статистику
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

		// Устанавливаем статус просрочен
		a.bonus.Status = stypes.STATUS_EXPIRED

		container.GetBonusStatisticStorage().Add(&stat)

		return
	}

	// Если бонус не активный, ничего не делаем
	if a.bonus.Status != stypes.STATUS_ACTIVE {
		return
	}

	// Если сумма доступных средств >= wager_to_complete завершаем бонус (выигрыш)
	if a.bonus.MoneyAvailable >= a.bonus.WagerToComplete {
		if !a.aboutDone {
			a.aboutDone = true
			return
		}

		// Переводим сумму доступных средств в float64
		money, _ := strconv.ParseFloat(a.bonus.MoneyToActivate, 64)
		a.bonus.MoneyAvailable = 0
		a.bonus.MoneyPaidOut = money
		a.writeTransaction(a.bonus.MoneyAvailable)
		a.wallet.AddMoney(money)
		a.bonus.MoneyAvailable = 0
		a.bonus.Status = stypes.STATUS_DONE
		win = a.bonus.MoneyPaidOut
		now := time.Now()

		// Записываем статистику по бонусу (выигрыш)
		stat := models.BonusStatistic{
			BonusID:      int64(a.bonus.ID),
			BonusType:    int16(a.bonus.Type),
			PatternID:    a.bonus.PatternID.Int64,
			UserID:       int64(a.bonus.UserID),
			Action:       stypes.ACTION_WIN,
			Amount:       a.bonus.MoneyPaidOut,
			Currency:     int8(a.bonus.Currency),
			StatusBefore: stypes.STATUS_ACTIVE,
			Status:       a.bonus.Status,
			CreatedAt:    &now,
			UpdatedAt:    &now,
		}
		// Записываем статистику по бонусу
		container.GetBonusStatisticStorage().Add(&stat)
		return
	}

	// Если сумма доступных средств <= 0 завершаем бонус (проигрыш)
	if a.bonus.MoneyAvailable <= 0 {
		if !a.aboutDone {
			a.aboutDone = true
			return
		}
		a.bonus.MoneyAvailable = 0
		a.bonus.Status = stypes.STATUS_DONE
		now := time.Now()

		// Записываем статистику по бонусу (проигрыш)
		stat := models.BonusStatistic{
			BonusID:      int64(a.bonus.ID),
			BonusType:    int16(a.bonus.Type),
			PatternID:    a.bonus.PatternID.Int64,
			UserID:       int64(a.bonus.UserID),
			Action:       stypes.ACTION_FAIL,
			Amount:       a.bonus.MoneyAvailable,
			Currency:     int8(a.bonus.Currency),
			StatusBefore: stypes.STATUS_ACTIVE,
			Status:       a.bonus.Status,
			CreatedAt:    &now,
			UpdatedAt:    &now,
		}
		// Записываем статистику по бонусу
		container.GetBonusStatisticStorage().Add(&stat)
	}

	// Если бонус завершается и сумма доступных средств больше 0, сбрасываем флаг
	if a.aboutDone && a.bonus.MoneyAvailable > 0 {
		a.aboutDone = false
	}

	return
}

// writeTransaction записывает транзакцию в базу данных.
func (a *PlusMinusActualizer) writeTransaction(amount float64) {
	curr, _ := currencyid.CurrencyID(a.bonus.Currency).Name()
	tr := models.BonusTransaction{
		UserID:    int64(a.bonus.UserID),
		HallID:    0,
		Date:      time.Now(),
		Sum:       amount,
		StatusID:  35,
		Currency:  curr,
		Operation: fmt.Sprintf("bonus win id# %d", a.bonus.ID),
	}

	container.GetBonusTransactionRepository().Insert(&tr)

}

func (a *PlusMinusActualizer) CancelBonus() {
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

func (a *PlusMinusActualizer) TransferBonusMoney() (transferred float64, err error) {
	return 0, errors.New("transfer not supported for this bonus type")
}
