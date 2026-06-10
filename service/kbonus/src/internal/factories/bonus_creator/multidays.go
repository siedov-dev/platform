package bonuscreator

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
	"strings"
	"time"
)

type MultiDaysBonusCreator struct {
}

func NewMultiDaysBonusCreator() container.BonusCreator {
	return &MultiDaysBonusCreator{}
}

func (c *MultiDaysBonusCreator) CreateBonus(bonusType int8, jsonParams string, currency int8, userID stypes.UserID, keepParams bool) (*models.UserBonus, error) {
	if jsonParams == "" {
		return nil, errors.New("jsonParams cannot be empty")
	}
	params, err := GetBonusParamsFromJson(jsonParams, currency, keepParams)
	if err != nil {
		return nil, err
	}

	DayStart, err := GetBonusParamOfTypeInt(params, "day_s")
	if err != nil {
		DayStart = 0 // Если day_s не указан, устанавливаем значение по умолчанию
	}

	activeDays, err := GetBonusParamOfTypeInt(params, "active_days")
	if err != nil {
		activeDays = 5 // Если active_days не указан, устанавливаем значение по умолчанию
	}
	activeTo := time.Now().AddDate(0, 0, activeDays)

	activationSum, err := GetBonusParamOfTypeFloat(params, "activation_sum")
	if err != nil {
		activationSum = 0 // Если activation_sum не указан, устанавливаем значение по умолчанию
	}

	userBonus := &models.UserBonus{
		Type:            bonusType,
		UserID:          userID,
		JSONParams:      sql.NullString{String: jsonParams, Valid: true},
		Currency:        currency,
		SpinsAvailable:  -1,
		SpinsTotal:      0,
		ActiveTo:        sql.NullTime{Time: activeTo, Valid: true},
		HoursActive:     24,
		MoneyAvailable:  0,
		MoneyToActivate: fmt.Sprintf("%.2f", activationSum),
		WagerToPayout:   0,
		Tries:           DayStart,
	}

	dayParams := container.GetMultiDaysBonusPramsDataProvider().GetParamsForDay(DayStart)
	if dayParams == nil {
		return nil, fmt.Errorf("no parameters found for day %d", DayStart)
	}
	maxWin := 0.0
	maxBet := 0.0
	if dayParams.Params.Valid {
		maxWinParams := make(map[string]any)
		err := json.Unmarshal([]byte(dayParams.Params.String), &maxWinParams)
		if err != nil {
			logger.Get().Error("failed to unmarshal max_win parameters", "error", err.Error())
		} else {
			currName, err := currencyid.CurrencyID(userBonus.Currency).Name()
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

	userBonus.MaxWin = sql.NullFloat64{Float64: maxWin, Valid: true}
	userBonus.SpinsAvailable = dayParams.Spins
	userBonus.SpinsTotal = dayParams.Spins
	userBonus.Multiplier = float32(dayParams.Wager)

	if val, ok := params["enable_after"]; ok {
		jsonEncoded, err := json.Marshal(val)
		if err != nil {
			logger.Get().Error("failed to marshal enable_after parameter", "error", err.Error())
		} else {
			userBonus.EnableAfter = sql.NullString{String: string(jsonEncoded), Valid: true}
		}
	}

	gameParams := strings.Split(dayParams.GameCID, "/")
	if len(gameParams) != 3 {
		return nil, errors.New("invalid game CID format")
	}
	userBonus.GameID = sql.NullString{String: gameParams[2], Valid: true}
	userBonus.GameProvider = sql.NullString{String: gameParams[0], Valid: true}
	allowedGames := make(map[string][]string)
	allowedGames[userBonus.GameProvider.String] = []string{userBonus.GameID.String}
	jsonAllowedGames, err := json.Marshal(allowedGames)
	if err != nil {
		logger.Get().Error("failed to marshal allowed games", "error", err.Error())
	} else {
		userBonus.AllowedGames = sql.NullString{String: string(jsonAllowedGames), Valid: true}
	}
	userBonus.GamesLimited = true

	bonusParams, err := json.Marshal(params)
	if err != nil {
		logger.Get().Error("failed to marshal bonus parameters", "error", err.Error())
	} else {
		userBonus.JSONParams = sql.NullString{String: string(bonusParams), Valid: true}
	}
	userBonus.SetJsonParam("max_bet", maxBet)
	userBonus.SetJsonParam("max_win", maxWin)

	return userBonus, nil
}
