package bonuscreator

import (
	"database/sql"
	"encoding/json"
	"errors"
	"kbonus/internal/db/models"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"time"
)

type CashBackMoneyCreator struct {
}

func NewCashBackMoneyCreator() container.BonusCreator {
	return &CashBackMoneyCreator{}
}

func (c *CashBackMoneyCreator) CreateBonus(bonusType int8, jsonParams string, currency int8, userID stypes.UserID, keepParams bool) (*models.UserBonus, error) {
	if jsonParams == "" {
		return nil, errors.New("jsonParams cannot be empty")
	}
	params, err := GetBonusParamsFromJson(jsonParams, currency, keepParams)
	if err != nil {
		return nil, err
	}

	freeSpins := -1
	spinsTotal := 0

	money, err := GetBonusParamOfTypeFloat(params, "money")
	if err != nil {
		return nil, errors.New("invalid money parameter")
	}
	activeDays, err := GetBonusParamOfTypeInt(params, "days")
	if err != nil {
		activeDays = 3 // Если days не указан, устанавливаем значение по умолчанию
	}
	activeTo := time.Now().AddDate(0, 0, activeDays)

	userBonus := &models.UserBonus{
		Type:           bonusType,
		UserID:         userID,
		JSONParams:     sql.NullString{String: jsonParams, Valid: true},
		Currency:       currency,
		SpinsAvailable: freeSpins,
		SpinsTotal:     spinsTotal,
		ActiveTo:       sql.NullTime{Time: activeTo, Valid: true},
		HoursActive:    24,
		MoneyAvailable: money,
	}

	if _, ok := params["max_win_sum"]; ok {
		maxWinSum, err := GetBonusParamOfTypeFloat(params, "max_win_sum")
		if err == nil {
			userBonus.MultiplierToComplete = 0
			userBonus.WagerToPayout = maxWinSum
		}
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
