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

type PlusMinusDepCreator struct {
}

func NewPlusMinusDepCreator() container.BonusCreator {
	return &PlusMinusDepCreator{}
}

func (c *PlusMinusDepCreator) CreateBonus(bonusType int8, jsonParams string, currency int8, userID stypes.UserID, keepParams bool) (*models.UserBonus, error) {
	if jsonParams == "" {
		return nil, errors.New("jsonParams cannot be empty")
	}
	params, err := GetBonusParamsFromJson(jsonParams, currency, keepParams)
	if err != nil {
		return nil, err
	}

	activeDays, err := GetBonusParamOfTypeInt(params, "active_days")
	if err != nil {
		activeDays = 3 // Если active_days не указан, устанавливаем значение по умолчанию
	}
	activeTo := time.Now().AddDate(0, 0, activeDays)

	minSum, err := GetBonusParamOfTypeFloat(params, "min_sum")
	if err != nil {
		minSum = 0
	}
	maxSum, err := GetBonusParamOfTypeFloat(params, "max_sum")
	if err != nil {
		maxSum = 0
	}

	userBonus := &models.UserBonus{
		Type:            bonusType,
		UserID:          userID,
		JSONParams:      sql.NullString{String: jsonParams, Valid: true},
		Currency:        currency,
		SpinsAvailable:  -1,
		SpinsTotal:      0,
		ActiveTo:        sql.NullTime{Time: activeTo, Valid: true},
		HoursActive:     activeDays * 24,
		MoneyAvailable:  0,
		MoneyToActivate: fmt.Sprintf("%.2f", minSum),
		MaxWin:          sql.NullFloat64{Float64: maxSum, Valid: true},
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
