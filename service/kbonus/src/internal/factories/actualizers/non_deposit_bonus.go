package actualizers

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"kbonus/internal/db/models"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
	"log/slog"
	"time"
)

var _ stypes.BonusActualizer = &NonDepositBonusActualizer{}

// NewNonDepositBonusActualizer создает новый экземпляр NonDepositBonusActualizer.
// Принимает GameWallet и указатель на UserBonus.
// Возвращает экземпляр NonDepositBonusActualizer.
func NewNonDepositBonusActualizer(wallet stypes.GameWallet, bonus *models.UserBonus) stypes.BonusActualizer {
	return &NonDepositBonusActualizer{wallet: wallet, bonus: bonus}
}

// NonDepositBonusActualizer реализует интерфейс BonusActualizer.
type NonDepositBonusActualizer struct {
	wallet stypes.GameWallet
	bonus  *models.UserBonus
}

// GetStatus возвращает статус бонуса для отображения.
func (a *NonDepositBonusActualizer) GetStatus() stypes.BonusStatusPad {
	s := stypes.BonusStatusPad{
		BonusStatus: int(a.bonus.Status),
		ShowPad:     true,
	}

	if a.bonus.Status != stypes.STATUS_ACTIVE {
		s.ShowPad = false
		return s
	}

	s.Title = "status_active"
	s.Value = "played"
	s.Progress = a.GetProgressHint()
	return s
}

// GetProgressHint возвращает строку с прогрессом бонуса для отображения.
func (a *NonDepositBonusActualizer) GetProgressHint() string {

	if a.bonus.Status == stypes.STATUS_ACTIVE && a.bonus.WagerToComplete > 0 {
		return fmt.Sprintf("%.2f/%.2f", a.bonus.TotalBet, a.bonus.WagerToComplete)
	}
	if a.bonus.Status == stypes.STATUS_FILTHY {
		return fmt.Sprintf("%.2f/%.2f", a.bonus.TotalBetForPayout, a.bonus.MoneyAvailable)
	}
	return ""
}

// Actualize актуализирует состояние бонуса и возвращает сумму выигрыша.
func (a *NonDepositBonusActualizer) Actualize() (win float64) {
	// Если бонус просрочен, ничего не делаем
	if a.bonus.Status == stypes.STATUS_EXPIRED {
		return 0
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

		return 0
	}

	// Если бонус не активен, ничего не делаем
	if a.bonus.Status != stypes.STATUS_ACTIVE {
		return 0
	}

	if a.bonus.Status == stypes.STATUS_FILTHY && a.bonus.MoneyAvailable <= 0 {
		a.bonus.Status = stypes.STATUS_DONE
		now := time.Now()
		a.bonus.CompletedAt = sql.NullTime{Time: now, Valid: true}

		// Записываем статистику по бонусу
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
		container.GetBonusStatisticStorage().Add(&stat)
		return
	}

	if a.bonus.Status == stypes.STATUS_DONE && a.bonus.MoneyAvailable > 0 {
		a.bonus.Status = stypes.STATUS_FILTHY
	}
	if a.bonus.Status == stypes.STATUS_ACTIVE {
		a.bonus.Status = stypes.STATUS_FILTHY
	}

	return 0
}

func (a *NonDepositBonusActualizer) CancelBonus() {
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

// writeTransaction записывает транзакцию в базу данных.
func (a *NonDepositBonusActualizer) writeTransaction(amount float64) {
	curr, _ := currencyid.CurrencyID(a.bonus.Currency).Name()
	tr := models.BonusTransaction{
		UserID:    int64(a.bonus.UserID),
		HallID:    0,
		Date:      time.Now(),
		Sum:       amount,
		StatusID:  35,
		Currency:  curr,
		Operation: fmt.Sprintf("bonus transfer id# %d", a.bonus.ID),
	}

	container.GetBonusTransactionRepository().Insert(&tr)
}

func (a *NonDepositBonusActualizer) TransferBonusMoney() (transferred float64, err error) {
	if a.bonus.TotalBetForPayout > a.bonus.MoneyAvailable {
		a.bonus.TotalBetForPayout = a.bonus.MoneyAvailable
	}

	if a.bonus.Status == stypes.STATUS_FILTHY && a.bonus.TotalBetForPayout > 0 {
		// Переводим деньги с бонуса на игровой кошелек
		a.bonus.MoneyAvailable -= a.bonus.TotalBetForPayout
		a.bonus.MoneyPaidOut += a.bonus.TotalBetForPayout

		moneyToPayout := a.bonus.TotalBetForPayout

		if a.bonus.MoneyAvailable <= 0 {
			a.bonus.Status = stypes.STATUS_DONE
		}
		a.bonus.TotalBetForPayout = 0

		a.wallet.AddMoney(moneyToPayout)
		a.writeTransaction(moneyToPayout)
		return moneyToPayout, nil
	}
	return 0, nil
}
