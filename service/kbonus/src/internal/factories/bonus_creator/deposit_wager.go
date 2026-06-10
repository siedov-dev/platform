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

type DepositWagerBonusCreator struct {
}

func NewDepositWagerBonusCreator() container.BonusCreator {
	return &DepositWagerBonusCreator{}
}

func (c *DepositWagerBonusCreator) CreateBonus(bonusType int8, jsonParams string, currency int8, userID stypes.UserID, keepParams bool) (*models.UserBonus, error) {
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

	status, err := GetBonusParamOfTypeInt(params, "status")
	if err != nil {
		status = stypes.STATUS_NOT_ACTIVATED // Если статус не указан или некорректен, устанавливаем значение по умолчанию
	}

	minSum, err := GetBonusParamOfTypeFloat(params, "min_sum")
	if err != nil {
		minSum = 0 // Если min_sum не указан, устанавливаем значение по умолчанию
	}

	maxSum, err := GetBonusParamOfTypeFloat(params, "max_sum")
	if err != nil {
		maxSum = 0 // Если max_sum не указан, устанавливаем значение по умолчанию
	}

	maxWin, err := GetBonusParamOfTypeFloat(params, "max_win")
	if err != nil {
		maxWin = 0 // Если max_win не указан, устанавливаем значение по умолчанию
	}

	activeDays, err := GetBonusParamOfTypeInt(params, "days_active")
	if err != nil {
		activeDays = 3 // Если active_days не указан, устанавливаем значение по умолчанию
	}
	activeTo := time.Now().AddDate(0, 0, activeDays)

	multiplier, err := GetBonusParamOfTypeFloat(params, "multiplier")
	if err != nil {
		multiplier = 100 // Если multiplier не указан, устанавливаем значение по умолчанию
	}

	// Логика создания бонуса для PSNonDepFS
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
		MoneyToActivate:      fmt.Sprintf("%.2f", minSum),
		MultiplierToPayout:   multiplier,
		MultiplierToComplete: maxSum,
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
