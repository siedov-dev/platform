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
	"time"
)

type SmartWagerCreator struct {
}

func NewSmartWagerCreator() container.BonusCreator {
	return &SmartWagerCreator{}
}

func (c *SmartWagerCreator) CreateBonus(bonusType int8, jsonParams string, currency int8, userID stypes.UserID, keepParams bool) (*models.UserBonus, error) {
	if jsonParams == "" {
		return nil, errors.New("jsonParams cannot be empty")
	}
	params, err := GetBonusParamsFromJson(jsonParams, currency, keepParams)
	if err != nil {
		return nil, err
	}

	wager, err := GetBonusParamOfTypeInt(params, "wager")
	if err != nil {
		return nil, errors.New("invalid wager parameter")
	}
	money, err := GetBonusParamOfTypeFloat(params, "money")
	if err != nil {
		money = 0 // Если money не указан, устанавливаем значение по умолчанию
	}
	maxWin, err := GetBonusParamOfTypeFloat(params, "max_win")
	if err != nil {
		maxWin = 0 // Если max_win не указан, устанавливаем значение по умолчанию
	}
	activeDays, err := GetBonusParamOfTypeInt(params, "lifetime")
	if err != nil {
		activeDays = 3 // Если active_days не указан, устанавливаем значение по умолчанию
	}
	activeTo := time.Now().AddDate(0, 0, activeDays)

	minSum, err := GetBonusParamOfTypeFloat(params, "min_sum")
	if err != nil {
		return nil, errors.New("invalid min_sum parameter")
	}
	maxSum, err := GetBonusParamOfTypeFloat(params, "max_sum")
	if err != nil {
		return nil, errors.New("invalid max_sum parameter")
	}
	multiplier, err := GetBonusParamOfTypeFloat(params, "multiplier")
	if err != nil {
		return nil, errors.New("invalid multiplier parameter")
	}
	status, err := GetBonusParamOfTypeInt(params, "status")
	if err != nil {
		status = models.STATUS_WAITING_FOR_AGREEMENT // Если status не указан, устанавливаем значение по умолчанию
	}

	userBonus := &models.UserBonus{
		Status:               int8(status),
		Type:                 bonusType,
		UserID:               userID,
		JSONParams:           sql.NullString{String: jsonParams, Valid: true},
		Currency:             currency,
		Multiplier:           float32(wager),
		SpinsAvailable:       -1,
		SpinsTotal:           0,
		MaxWin:               sql.NullFloat64{Float64: maxWin, Valid: true},
		ActiveTo:             sql.NullTime{Time: activeTo, Valid: true},
		HoursActive:          24,
		MoneyAvailable:       money,
		WagerToComplete:      money * float64(wager),
		MoneyToActivate:      fmt.Sprintf("%.2f", minSum),
		MultiplierToComplete: maxSum,
		MultiplierToPayout:   multiplier,
	}

	if val, ok := params["enable_after"]; ok {
		jsonEncoded, err := json.Marshal(val)
		if err != nil {
			logger.Get().Error("failed to marshal enable_after parameter", "error", err.Error())
		} else {
			userBonus.EnableAfter = sql.NullString{String: string(jsonEncoded), Valid: true}
		}
	}

	bonusParams, err := json.Marshal(params)
	if err != nil {
		logger.Get().Error("failed to marshal bonus parameters", "error", err.Error())
	} else {
		userBonus.JSONParams = sql.NullString{String: string(bonusParams), Valid: true}
	}

	return userBonus, nil
}
