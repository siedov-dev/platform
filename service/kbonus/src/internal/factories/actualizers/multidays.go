package actualizers

import (
	"database/sql"
	"encoding/json"
	"errors"
	"fmt"
	"kbonus/internal/db/models"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
	jsonparams "kbonus/internal/utils/json_params"
	"log/slog"
	"strings"
	"time"
)

var _ stypes.BonusActualizer = &MultidaysActualizer{}

// NewMultidaysActualizer создает новый экземпляр MultidaysActualizer.
// Принимает GameWallet и указатель на UserBonus.
// Возвращает экземпляр MultidaysActualizer.
// Используется для бонуса Multidays.
func NewMultidaysActualizer(wallet stypes.GameWallet, bonus *models.UserBonus) stypes.BonusActualizer {
	return &MultidaysActualizer{wallet: wallet, bonus: bonus}
}

type MultidaysActualizer struct {
	wallet    stypes.GameWallet // Кошелек игрока
	bonus     *models.UserBonus // Бонус
	aboutDone bool              // Флаг, указывающий, что бонус завершается
}

// GetStatus возвращает статус бонуса для отображения.
func (a *MultidaysActualizer) GetStatus() stypes.BonusStatusPad {
	s := stypes.BonusStatusPad{
		BonusStatus: int(a.bonus.Status),
		ShowPad:     true,
	}

	if a.bonus.Status != stypes.STATUS_ACTIVE {
		s.ShowPad = false
		return s
	}

	if a.bonus.SpinsAvailable > 0 {
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
func (a *MultidaysActualizer) GetProgressHint() string {
	if a.bonus.SpinsAvailable > 0 && a.bonus.SpinsTotal > 0 {
		return fmt.Sprintf("%v/%v", a.bonus.SpinsTotal-a.bonus.SpinsAvailable, a.bonus.SpinsTotal)
	}
	if a.bonus.Status == stypes.STATUS_ACTIVE && a.bonus.WagerToComplete > 0 {
		return fmt.Sprintf("%.2f/%.2f", a.bonus.TotalBet, a.bonus.WagerToComplete)
	}
	return ""
}

// Actualize актуализирует состояние бонуса и возвращает сумму выигрыша.
func (a *MultidaysActualizer) Actualize() (win float64) {
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

		//cancel freespins campaign
		extfs, err := container.GetExtFsHandlersFactory().GetExtFsHandler(a.bonus.GameProvider.String, a.bonus)
		if err == nil {
			extfs.ExternFreeSpinsCancel(fmt.Sprintf("%dd%d", a.bonus.ID, a.getCurrentDay()))
		}

		return
	}

	if a.bonus.CompletedAt.Valid && !a.bonus.CompletedAt.Time.IsZero() && a.bonus.CompletedAt.Time.Before(now) {
		if err := a.nextDay(); err != nil {
			logger.Get().Error("failed to proceed to next day", slog.String("err", err.Error()))
		}
		return
	}

	// Если бонус не активный, ничего не делаем
	if a.bonus.Status != stypes.STATUS_ACTIVE {
		return
	}

	// Если спины закончились (0), устанавливаем -1 и пересчитываем wager
	if a.bonus.SpinsAvailable == 0 {
		a.bonus.SpinsAvailable = -1
		a.bonus.WagerToComplete = a.bonus.MoneyAvailable * a.bonus.WagerToComplete
		now := time.Now()

		// Записываем статистику по бонусу
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
		// Записываем статистику по бонусу
		container.GetBonusStatisticStorage().Add(&stat)
	}

	// Если спины закончились (-1) и wager >= wager_to_complete завершаем бонус (выигрыш)
	if a.bonus.SpinsAvailable < 0 && a.bonus.TotalBet >= a.bonus.WagerToComplete {
		// Если бонус переходит в статус завершен первый раз, устанавливаем флаг
		// Нужно для завершения бонуса только после ответа выигрыша
		if !a.aboutDone {
			a.aboutDone = true
			return
		}

		// Если максимальный выигрыш установлен и больше 0 и сумма доступных средств больше максимального выигрыша,
		// устанавливаем сумму доступных средств равной максимальному выигрышу
		if a.bonus.MaxWin.Valid && a.bonus.MaxWin.Float64 > 0 && a.bonus.MoneyAvailable > a.bonus.MaxWin.Float64 {
			a.bonus.MoneyAvailable = a.bonus.MaxWin.Float64
		}
		a.bonus.SpinsAvailable = -1
		a.bonus.MoneyPaidOut = a.bonus.MoneyAvailable
		a.writeTransaction(a.bonus.MoneyAvailable)
		a.wallet.AddMoney(a.bonus.MoneyAvailable)
		a.bonus.MoneyAvailable = 0
		if a.getCurrentDay() >= a.getLastDay() {
			a.bonus.Status = stypes.STATUS_DONE
		} else {
			a.bonus.Status = stypes.STATUS_DEPOSIT_LISTEN
		}
		win = a.bonus.MoneyPaidOut
		now := time.Now()

		// Записываем статистику по бонусу
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
	if a.bonus.MoneyAvailable <= 0 && a.bonus.SpinsAvailable < 0 {
		// Если бонус переходит в статус завершен первый раз, устанавливаем флаг
		// Если после этого прийдет выигрыш, то продолжаем играть
		if !a.aboutDone {
			a.aboutDone = true
			return
		}
		a.bonus.MoneyAvailable = 0
		// Если текущий день бонуса >= последнего дня, устанавливаем статус завершен
		// Иначе устанавливаем статус прослушивание депозита (для установки бонуса следующего дня)
		if a.getCurrentDay() >= a.getLastDay() {
			a.bonus.Status = stypes.STATUS_DONE
		} else {
			a.bonus.Status = stypes.STATUS_DEPOSIT_LISTEN
		}
		now := time.Now()

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
		// Записываем статистику по бонусу
		container.GetBonusStatisticStorage().Add(&stat)
	}

	// Если бонус завершен и сумма доступных средств больше 0, сбрасываем флаг
	if a.aboutDone && a.bonus.MoneyAvailable > 0 {
		a.aboutDone = false
	}

	return
}

// writeTransaction записывает транзакцию в базу данных.
func (a *MultidaysActualizer) writeTransaction(amount float64) {
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

// getCurrentDay возвращает текущий день бонуса.
func (a *MultidaysActualizer) getCurrentDay() int {
	return a.bonus.Tries
}

func (a *MultidaysActualizer) setCurrentDay(day int) {
	a.bonus.Tries = day
}

func (a *MultidaysActualizer) getLastDay() int {
	if lastDay, err := jsonparams.GetParamOfTypeInt(a.bonus.JSONParams.String, "day_e"); err == nil {
		return lastDay
	}
	return 4 // Если day_e не указан, устанавливаем значение по умолчанию
}

func (a *MultidaysActualizer) CancelBonus() {
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

	//cancel freespins campaign
	extfs, err := container.GetExtFsHandlersFactory().GetExtFsHandler(a.bonus.GameProvider.String, a.bonus)
	if err == nil {
		extfs.ExternFreeSpinsCancel(fmt.Sprintf("%dd%d", a.bonus.ID, a.getCurrentDay()))
	}

}

func (a *MultidaysActualizer) nextDay() error {
	if !a.bonus.CompletedAt.Valid || a.bonus.CompletedAt.Time.IsZero() {
		return nil
	}
	now := time.Now()
	days := 0
	if a.bonus.CompletedAt.Time.After(now) {
		days = int(a.bonus.CompletedAt.Time.Sub(now).Hours() / 24)
	}

	// Завершаем текущий день
	extFshandler, err := container.GetExtFsHandlersFactory().GetExtFsHandler(a.bonus.GameProvider.String, a.bonus)
	if err == nil {
		extFshandler.ExternFreeSpinsCancel(fmt.Sprintf("%dd%d", a.bonus.ID, a.getCurrentDay()))
	}
	logger.Get().Info("Multidays bonus: proceeding to next day", slog.Int("bonus_id", int(a.bonus.ID)), slog.Int("current_day", a.getCurrentDay()), slog.Int("days_to_add", days+1))
	// Переходим к следующему дню
	currentDay := a.getCurrentDay() + days + 1

	if currentDay > a.getLastDay() {
		a.bonus.Status = stypes.STATUS_DONE
		a.bonus.CompletedAt = sql.NullTime{Time: time.Now(), Valid: true}
		return nil
	}

	a.setCurrentDay(currentDay)
	a.bonus.CompletedAt = sql.NullTime{Time: a.bonus.CreatedAt.Time.AddDate(0, 0, currentDay), Valid: true}
	a.bonus.ActiveTo = sql.NullTime{Time: a.bonus.CompletedAt.Time.AddDate(0, 0, 1), Valid: true}

	// Запускаем следующий день
	dayParams := container.GetMultiDaysBonusPramsDataProvider().GetParamsForDay(a.getCurrentDay())
	if dayParams == nil {
		return fmt.Errorf("no parameters found for day %d", a.getCurrentDay())
	}
	maxWin := 0.0
	maxBet := 0.0
	if dayParams.Params.Valid {
		maxWinParams := make(map[string]any)
		err := json.Unmarshal([]byte(dayParams.Params.String), &maxWinParams)
		if err != nil {
			logger.Get().Error("failed to unmarshal max_win parameters", "error", err.Error())
		} else {
			currName, err := currencyid.CurrencyID(a.bonus.Currency).Name()
			if err != nil {
				logger.Get().Error("failed to get currency name", "error", err.Error())
			} else {
				if val, ok := maxWinParams["max_win_"+currName]; ok {
					if maxWinFloat, ok := val.(float64); ok {
						maxWin = maxWinFloat
					} else if maxWinInt, ok := val.(int64); ok {
						maxWin = float64(maxWinInt)
					} else {
						logger.Get().Error("max_win parameter is not a number", "value", val)
					}
				} else {
					logger.Get().Error("max_win parameter not found for currency", "currency", currName)
				}

				if val, ok := maxWinParams["max_bet_"+currName]; ok {
					if maxBetFloat, ok := val.(float64); ok {
						maxBet = maxBetFloat
					} else if maxBetInt, ok := val.(int64); ok {
						maxBet = float64(maxBetInt)
					} else {
						logger.Get().Error("max_bet parameter is not a float64", "value", val)
					}
				} else {
					logger.Get().Error("max_bet parameter not found for currency", "currency", currName)
				}
			}
		}
	}

	a.bonus.SetJsonParam("max_bet", maxBet)
	a.bonus.SetJsonParam("max_win", maxWin)

	a.bonus.MaxWin = sql.NullFloat64{Float64: maxWin, Valid: true}
	a.bonus.SpinsAvailable = dayParams.Spins
	a.bonus.SpinsTotal = dayParams.Spins
	a.bonus.Multiplier = float32(dayParams.Wager)
	a.bonus.MoneyAvailable = 0
	a.bonus.WagerToComplete = 0
	a.bonus.WagerToPayout = 0
	a.bonus.TotalBet = 0
	a.bonus.TotalWin = 0

	gameParams := strings.Split(dayParams.GameCID, "/")
	if len(gameParams) != 3 {
		return errors.New("invalid game CID format")
	}
	a.bonus.GameID = sql.NullString{String: gameParams[2], Valid: true}
	a.bonus.GameProvider = sql.NullString{String: gameParams[0], Valid: true}
	allowedGames := make(map[string][]string)
	allowedGames[a.bonus.GameProvider.String] = []string{a.bonus.GameID.String}
	jsonAllowedGames, err := json.Marshal(allowedGames)
	if err != nil {
		logger.Get().Error("failed to marshal allowed games", "error", err.Error())
	} else {
		a.bonus.AllowedGames = sql.NullString{String: string(jsonAllowedGames), Valid: true}
	}
	a.bonus.GamesLimited = true

	a.bonus.Status = stypes.STATUS_WAITING_FOR_AGREEMENT

	return nil
}

func (a *MultidaysActualizer) TransferBonusMoney() (transferred float64, err error) {
	return 0, errors.New("transfer not supported for this bonus type")
}
