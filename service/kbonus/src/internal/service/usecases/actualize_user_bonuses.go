package usecases

import (
	"encoding/json"
	"fmt"
	"kbonus/internal/logger"
	"kbonus/internal/service"
	"kbonus/internal/stypes"
	"strconv"
)

func ActualizeUserBonuses(userID int64) error {
	var err error
	hasErr := false
	bonuses, err := service.GetUserBonuses(userID)
	if err != nil {
		return err
	}
	for _, ub := range bonuses.GetBonusIDs() {
		bonus, err := service.GetBonus(ub, 100) // 100 - процент вейджеринга по умолчанию, не имеет значения для информации о бонусе
		if err != nil {
			logger.Get().Error("Failed to get bonus", "userID", userID, "bonusID", ub, "error", err)
			hasErr = true
			continue
		}
		bonus.Actualize()
		if _, err = bonus.Save(); err != nil {
			logger.Get().Error("Failed to save bonus", "userID", userID, "bonusID", ub, "error", err)
			hasErr = true
			continue
		}
		if bonus.GetBonusInfo().IsCompleted {
			bonuses.RemoveBonus(ub)
			bonuses.AddCompletedBonus(ub)
		}
	}
	if hasErr {
		return fmt.Errorf("some bonuses could not be actualized for user %d, %w", userID, err)
	}
	// Проверяем заблокированные бонусы
	for _, ub := range bonuses.GetBonusIDs() {
		bonus, err := service.GetBonus(ub, 100) // 100 - процент вейджеринга по умолчанию, не имеет значения для информации о бонусе
		if err != nil {
			logger.Get().Error("Failed to get bonus", "userID", userID, "bonusID", ub, "error", err)
			hasErr = true
			continue
		}
		if bonus.GetBonusInfo().IsLocked {
			if !bonus.UserBonus.EnableAfter.Valid {
				logger.Get().Info("Locked bonus has no EnableAfter, unlock", "userID", userID, "bonusID", ub)
				bonus.UserBonus.Status = stypes.STATUS_NOT_ACTIVATED
				bonus.Actualize()
				bonus.Save()
				continue
			}
			patternIDs := []int64{}
			enableAfter := []any{}
			err := json.Unmarshal([]byte(bonus.UserBonus.EnableAfter.String), &enableAfter)
			if err != nil {
				logger.Get().Error("Failed to unmarshal EnableAfter", "userID", userID, "bonusID", ub, "error", err)
				continue
			}
			for _, v := range enableAfter {
				if m, ok := v.(string); ok {
					val, err := strconv.ParseInt(m, 10, 64)
					if err != nil {
						logger.Get().Error("Failed to parse patternID from EnableAfter", "userID", userID, "bonusID", ub, "value", m, "error", err)
						continue
					}
					patternIDs = append(patternIDs, val)
				} else if m, ok := v.(float64); ok {
					patternIDs = append(patternIDs, int64(m))
				} else if m, ok := v.(int64); ok {
					patternIDs = append(patternIDs, m)
				} else {
					logger.Get().Error("Unknown type in EnableAfter", "userID", userID, "bonusID", ub, "value", v)
				}
			}
			hasBonuses, err := UserHasBonusesWithPatternIDs(userID, patternIDs)
			if err != nil {
				logger.Get().Error("Failed to check user bonuses", "userID", userID, "patternID", bonus.UserBonus.PatternID.Int64, "error", err)
				hasErr = true
				continue
			}
			if !hasBonuses {
				bonus.UserBonus.Status = stypes.STATUS_NOT_ACTIVATED
				bonus.Actualize()
				bonus.Save()
			}
		}
	}
	return nil
}
