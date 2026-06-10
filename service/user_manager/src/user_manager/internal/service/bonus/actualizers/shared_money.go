package actualizers

import (
	"encoding/json"
	"fmt"
	"log/slog"
	"strconv"
	"time"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/repository"
	"user-manager/internal/slotegrator/slegapi"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"
)

var _ stypes.BonusActualizer = &DefaultActualizer{}

// NewSharedMoneyAcualizer создает новый экземпляр SharedMoneyAcualizer.
// Принимает RealMoneyWallet и указатель на UserBonus.
// Возвращает экземпляр SharedMoneyAcualizer.
// Используется для бонуса SharedMoney.
func NewSharedMoneyAcualizer(wallet stypes.RealMoneyWallet, bonus *models.UserBonus) stypes.BonusActualizer {
	return &SharedMoneyAcualizer{wallet: wallet, bonus: bonus}
}

// SharedMoneyAcualizer структура для бонуса SharedMoney.
type SharedMoneyAcualizer struct {
	wallet    stypes.RealMoneyWallet // Кошелек игрока
	bonus     *models.UserBonus      // Бонус
	aboutDone bool                   // Флаг, указывающий, что бонус завершается
}

// GetStatus возвращает статус бонуса для отображения.
func (a *SharedMoneyAcualizer) GetStatus() stypes.BonusStatusPad {
	s := stypes.BonusStatusPad{
		BonusStatus: int(a.bonus.Status),
		ShowPad:     true,
	}

	if a.bonus.Status != stypes.STATUS_ACTIVE {
		s.ShowPad = false
		return s
	}

	if a.bonus.SpinsAvailable > 0 {
		s.ShowPad = false
		s.Title = "spins_made"
		s.Progress = a.GetProgressHint()
		return s
	}

	s.Title = "status_active"
	s.Value = "played"
	s.Progress = a.GetProgressHint()
	return s
}

// GetProgressHint возвращает строку с прогрессом бонуса для отображения.
func (a *SharedMoneyAcualizer) GetProgressHint() string {
	if a.bonus.SpinsAvailable > 0 && a.bonus.SpinsTotal > 0 {
		return fmt.Sprintf("%v/%v", a.bonus.SpinsTotal-a.bonus.SpinsAvailable, a.bonus.SpinsTotal)
	}
	if a.bonus.Status == stypes.STATUS_ACTIVE && a.bonus.WagerToComplete > 0 {
		return fmt.Sprintf("%.2f/%.2f", a.bonus.TotalBet, a.bonus.WagerToComplete)
	}
	return ""
}

// Actualize актуализирует состояние бонуса и возвращает сумму выигрыша.
func (a *SharedMoneyAcualizer) Actualize() (win float64) {
	// Если бонус просрочен, ничего не делаем
	if a.bonus.Status == stypes.STATUS_EXPIRED {
		return
	}

	// Если срок действия бонуса истек, устанавливаем статус просрочен
	now := time.Now()
	if a.bonus.ActiveTo != nil && a.bonus.ActiveTo.Unix() < now.Unix() {
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

		repository.GetBonusStatisticRepository().Insert(&stat)

		// Отменяем фриспины в слотиграторе
		slegapi.FreeSpinsCancel(strconv.Itoa(int(a.bonus.ID)))

		return
	}

	// Если бонус не активный, ничего не делаем
	if a.bonus.Status != stypes.STATUS_ACTIVE {
		return
	}

	// Если фриспины завершены, пересчитываем wager_to_complete
	if a.bonus.SpinsAvailable == 0 {
		a.bonus.SpinsAvailable = -1
		a.bonus.WagerToComplete = a.bonus.MoneyAvailable * a.bonus.WagerToComplete
		now := time.Now()

		stat := models.BonusStatistic{
			BonusID:      int64(a.bonus.ID),
			BonusType:    int16(a.bonus.Type),
			PatternID:    a.bonus.PatternID.Int64,
			UserID:       int64(a.bonus.UserID),
			Action:       stypes.ACTION_FS_DONE,
			Amount:       a.bonus.MoneyAvailable,
			Currency:     int8(a.bonus.Currency),
			StatusBefore: a.bonus.Status,
			Status:       a.bonus.Status,
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
		repository.GetBonusStatisticRepository().Insert(&stat)
	}

	// Если фриспины завершены и сумма доступных средств >= wager_to_complete завершаем бонус (выигрыш)
	if a.bonus.SpinsAvailable < 0 && a.bonus.TotalBet >= a.bonus.WagerToComplete {
		// Если бонус не в статусе завершается, устанавливаем флаг
		if !a.aboutDone {
			a.aboutDone = true
			return
		}

		// Если сумма доступных средств больше максимальной суммы выигрыша, устанавливаем сумму выигрыша равной максимальной сумме выигрыша
		if a.bonus.MaxWin.Valid && a.bonus.MaxWin.Float64 > 0 && a.bonus.MoneyAvailable > a.bonus.MaxWin.Float64 {
			a.bonus.MoneyAvailable = a.bonus.MaxWin.Float64
		}

		a.bonus.SpinsAvailable = -1
		a.bonus.MoneyPaidOut = a.bonus.MoneyAvailable
		a.writeTransaction(a.bonus.MoneyAvailable)

		// Добавляем сумму выигрыша в кошелек игрока
		a.wallet.AddMoney(a.bonus.MoneyAvailable)
		// Устанавливаем заблокированные средства равными 0
		a.wallet.SetLockedMoney(0)

		a.bonus.MoneyAvailable = 0
		a.bonus.Status = stypes.STATUS_DONE
		win = a.bonus.MoneyPaidOut

		now := time.Now()
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
		repository.GetBonusStatisticRepository().Insert(&stat)
		return
	}

	// Если сумма доступных средств <= 0 завершаем бонус (проигрыш)
	if a.bonus.MoneyAvailable <= 0 {
		// Если бонус не в статусе завершается, устанавливаем флаг
		if !a.aboutDone {
			a.aboutDone = true
			return
		}
		a.bonus.MoneyAvailable = 0
		a.bonus.Status = stypes.STATUS_DONE
		now := time.Now()

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
		repository.GetBonusStatisticRepository().Insert(&stat)
	}

	if a.aboutDone && a.bonus.MoneyAvailable > 0 {
		a.aboutDone = false
	}

	return win
}

// writeTransaction записывает транзакцию в базу данных.
func (a *SharedMoneyAcualizer) writeTransaction(amount float64) {
	curr, _ := currency.CurrencyID(a.bonus.Currency).Name()
	tr := models.BonusTransaction{
		UserID:    int64(a.bonus.UserID),
		HallID:    0,
		Date:      time.Now(),
		Sum:       amount,
		StatusID:  35,
		Currency:  curr,
		Operation: fmt.Sprintf("bonus win id# %d", a.bonus.ID),
	}

	repository.GetBonusTransactionRepository().Insert(&tr)

}
