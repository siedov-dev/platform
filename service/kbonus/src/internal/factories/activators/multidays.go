package activators

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
	"strconv"
	"strings"
	"time"
)

type MultidaysActivator struct {
	bonus *models.UserBonus
}

func NewMultidaysActivator(bonus *models.UserBonus) stypes.BonusActivator {
	return &MultidaysActivator{bonus: bonus}
}

func (a *MultidaysActivator) ActivateBonus(value float64) error {
	if a.bonus == nil {
		return errors.New("bonus is not set")
	}
	if a.bonus.Status == stypes.STATUS_NOT_ACTIVATED {
		a.bonus.Status = stypes.STATUS_WAITING_FOR_PAYMENT
		return nil
	}
	if a.bonus.Status != stypes.STATUS_WAITING_FOR_AGREEMENT {
		return errors.New("bonus is not in a valid state")
	}

	a.bonus.Status = stypes.STATUS_ACTIVE

	extFshandler, err := container.GetExtFsHandlersFactory().GetExtFsHandler(a.bonus.GameProvider.String, a.bonus)
	if err != nil {
		return err
	}
	err = extFshandler.ExternFreeSpinsCreate(fmt.Sprintf("%dd%d", a.bonus.ID, a.getCurrentDay()))
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

func (a *MultidaysActivator) ConfirmPayment(amount float64) (err error) {
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
	totalDays := a.getLastDay() - a.getFirstDay() + 1
	if totalDays <= 0 {
		return errors.New("invalid bonus days configuration")
	}
	a.bonus.ActiveTo = sql.NullTime{Time: time.Now().AddDate(0, 0, totalDays), Valid: true}
	a.bonus.CompletedAt = sql.NullTime{Time: time.Now().AddDate(0, 0, 1), Valid: true}

	a.bonus.Status = stypes.STATUS_ACTIVE

	extFshandler, err := container.GetExtFsHandlersFactory().GetExtFsHandler(a.bonus.GameProvider.String, a.bonus)
	if err != nil {
		return err
	}
	err = extFshandler.ExternFreeSpinsCreate(fmt.Sprintf("%dd%d", a.bonus.ID, a.getCurrentDay()))
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

func (a *MultidaysActivator) OnDeposit(amount float64) (err error) {
	if a.bonus == nil {
		return errors.New("bonus is not set")
	}
	if a.bonus.Status != stypes.STATUS_DEPOSIT_LISTEN {
		return errors.New("bonus is not in a valid state")
	}
	if a.bonus.MoneyToActivate == "" {
		return errors.New("bonus money to activate not found")
	}
	buy_sum_param, ok := a.bonus.GetJsonParam("buy_sum")
	if !ok {
		return errors.New("failed to parse bonus buy sum to activate ")
	}
	buy_sum := 0.0

	if sum, ok := buy_sum_param.(float64); ok {
		buy_sum = sum
	} else if sum, ok := buy_sum_param.(int64); ok {
		buy_sum = float64(sum)
	} else if sum, ok := buy_sum_param.(string); ok {
		if sumFloat, err := strconv.ParseFloat(sum, 64); err == nil {
			buy_sum = sumFloat
		} else {
			return errors.New("failed to parse bonus buy sum to activate ")
		}
	}

	if buy_sum > amount {
		return errors.New("insufficient payment amount")
	}

	a.nextDay()
	return nil
}

func (a *MultidaysActivator) getCurrentDay() int {
	return a.bonus.Tries
}

func (a *MultidaysActivator) setCurrentDay(day int) {
	a.bonus.Tries = day
}

func (a *MultidaysActivator) getLastDay() int {
	if lastDay, err := jsonparams.GetParamOfTypeInt(a.bonus.JSONParams.String, "day_e"); err == nil {
		return lastDay
	}
	return 4 // Если day_e не указан, устанавливаем значение по умолчанию
}

func (a *MultidaysActivator) getFirstDay() int {
	if firstDay, err := jsonparams.GetParamOfTypeInt(a.bonus.JSONParams.String, "day_s"); err == nil {
		return firstDay
	}
	return 1 // Если day_s не указан, устанавливаем значение по умолчанию
}

func (a *MultidaysActivator) nextDay() error {
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

	currentDay := a.getCurrentDay() + days + 1

	if currentDay > a.getLastDay() {
		a.bonus.Status = stypes.STATUS_DONE
		a.bonus.CompletedAt = sql.NullTime{Time: time.Now(), Valid: true}
		return nil
	}

	a.setCurrentDay(currentDay)
	a.bonus.CompletedAt = sql.NullTime{Time: a.bonus.CompletedAt.Time.AddDate(0, 0, days+1), Valid: true}
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
