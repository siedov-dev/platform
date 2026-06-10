package actualizers

import (
	"encoding/json"
	"fmt"
	"log/slog"
	"time"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/repository"
	"user-manager/internal/stypes"
)

var _ stypes.BonusActualizer = &DefaultActualizer{}

// NewFilthyFsActualizer создает новый экземпляр FilthyFsActualizer.
// Принимает GameWallet и указатель на UserBonus.
// Возвращает экземпляр FilthyFsActualizer.
// использовался для бонусов с отыгрыванием на живые деньги.
// В настоящее время не используется.
func NewFilthyFsActualizer(wallet stypes.GameWallet, bonus *models.UserBonus) stypes.BonusActualizer {
	return &FilthyFsActualizer{wallet: wallet, bonus: bonus}
}

type FilthyFsActualizer struct {
	wallet    stypes.GameWallet
	bonus     *models.UserBonus
	aboutDone bool
}

func (a *FilthyFsActualizer) GetStatus() stypes.BonusStatusPad {
	s := stypes.BonusStatusPad{
		BonusStatus: int(a.bonus.Status),
		ShowPad:     true,
	}

	if a.bonus.Status != stypes.STATUS_ACTIVE {
		s.ShowPad = false
		return s
	}

	if a.bonus.SpinsAvailable > 0 {
		s.ShowPad = true
		s.Title = "spins_made"
		s.Progress = a.GetProgressHint()
		return s
	}

	return s
}

func (a *FilthyFsActualizer) GetProgressHint() string {
	if a.bonus.SpinsAvailable > 0 && a.bonus.SpinsTotal > 0 {
		return fmt.Sprintf("%v/%v", a.bonus.SpinsTotal-a.bonus.SpinsAvailable, a.bonus.SpinsTotal)
	}
	return ""
}

func (a *FilthyFsActualizer) Actualize() (win float64) {
	// do nothing if expired
	if a.bonus.Status == stypes.STATUS_EXPIRED {
		return
	}

	//check expired
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

		return
	}

	if a.bonus.Status != stypes.STATUS_ACTIVE {
		return
	}

	if a.bonus.SpinsAvailable == 0 {
		a.bonus.SpinsAvailable = -1
		a.bonus.MoneyAvailable = a.bonus.TotalWin
		a.bonus.TotalWin = 0
		a.bonus.TotalBet = 0
		a.bonus.TotalBetForPayout = 0
		a.bonus.Type = stypes.TYPE_FILTHY_FS_WASH_OFF
		a.bonus.Status = stypes.STATUS_WAITING_FOR_AGREEMENT

		if a.bonus.MultiplierToComplete > 0 && a.bonus.MoneyAvailable > a.bonus.MultiplierToComplete {
			a.bonus.MoneyAvailable = a.bonus.MultiplierToComplete
		}
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

	if a.bonus.MoneyAvailable <= 0 {
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

	return
}
