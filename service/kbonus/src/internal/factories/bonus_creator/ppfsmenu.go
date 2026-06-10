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

type PPlayFsMenuCreator struct {
}

func NewPPlayFsMenuCreator() container.BonusCreator {
	return &PPlayFsMenuCreator{}
}

func (c *PPlayFsMenuCreator) CreateBonus(bonusType int8, jsonParams string, currency int8, userID stypes.UserID, keepParams bool) (*models.UserBonus, error) {
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

	freeSpins, err := GetBonusParamOfTypeInt(params, "freespins")
	spinsTotal := freeSpins
	if err != nil {
		freeSpins = -1 // Если free_spins не указан, устанавливаем значение по умолчанию
		spinsTotal = 0
	}
	maxWin, err := GetBonusParamOfTypeFloat(params, "max_win")
	if err != nil {
		maxWin = 0 // Если max_win не указан, устанавливаем значение по умолчанию
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

	userBonus := &models.UserBonus{
		Type:                 bonusType,
		UserID:               userID,
		JSONParams:           sql.NullString{String: jsonParams, Valid: true},
		Currency:             currency,
		Multiplier:           float32(wager),
		SpinsAvailable:       freeSpins,
		SpinsTotal:           spinsTotal,
		MaxWin:               sql.NullFloat64{Float64: maxWin, Valid: true},
		ActiveTo:             sql.NullTime{Time: activeTo, Valid: true},
		HoursActive:          activeDays * 24,
		MoneyAvailable:       0,
		MoneyToActivate:      fmt.Sprintf("%.2f", minSum),
		MultiplierToComplete: 0,
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
