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

type NonDepositBonusCreator struct {
}

func NewNonDepositBonusCreator() container.BonusCreator {
	return &NonDepositBonusCreator{}
}

func (c *NonDepositBonusCreator) CreateBonus(bonusType int8, jsonParams string, currency int8, userID stypes.UserID, keepParams bool) (*models.UserBonus, error) {
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
		return nil, errors.New("invalid money parameter")
	}
	maxWin, err := GetBonusParamOfTypeFloat(params, "max_win")
	if err != nil {
		maxWin = 0 // Если max_win не указан, устанавливаем значение по умолчанию
	}
	activeDays, err := GetBonusParamOfTypeInt(params, "days_active")
	if err != nil {
		activeDays = 5 // Если days_active не указан, устанавливаем значение по умолчанию
	}
	activeTo := time.Now().AddDate(0, 0, activeDays)

	userBonus := &models.UserBonus{
		Type:            bonusType,
		UserID:          userID,
		JSONParams:      sql.NullString{String: jsonParams, Valid: true},
		Currency:        currency,
		Multiplier:      0,
		SpinsAvailable:  -1,
		SpinsTotal:      0,
		ActiveTo:        sql.NullTime{Time: activeTo, Valid: true},
		MoneyAvailable:  money,
		WagerToComplete: money * float64(wager),
		WagerToPayout:   float64(wager),
		MaxWin:          sql.NullFloat64{Float64: maxWin, Valid: maxWin > 0},
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
