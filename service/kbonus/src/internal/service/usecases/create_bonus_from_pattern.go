package usecases

import (
	"encoding/json"
	"fmt"
	"kbonus/internal/logger"
	"kbonus/internal/service"
	"kbonus/internal/service/container"
	"kbonus/internal/service/entities"
	"kbonus/internal/stypes"
	"strconv"
)

func CreateBonusFromPattern(patternParams stypes.BonusPatternCreateParams) (*entities.Bonus, error) {
	pattern, err := container.GetBonusPatternsDataProvider().GetBonusPattern(patternParams.PatternID)
	if err != nil {
		return nil, err
	}
	if pattern == nil {
		return nil, fmt.Errorf("bonus pattern not found")
	}

	bonusCreationParams := stypes.BonusCreateParams{
		UserID:       patternParams.UserID,
		BonusType:    int8(pattern.BonusType),
		Currency:     patternParams.Currency,
		Params:       pattern.BonusParams,
		PatternID:    pattern.ID,
		CreatorType:  stypes.CREATOR_BONUS_PATTERN,
		CreatorID:    pattern.ID,
		Title:        pattern.Title.String,
		ImgURL:       pattern.ImgURL.String,
		Marker:       patternParams.Marker,
		KeepParams:   false,
		Tmp:          patternParams.Tmp,
		AllowedGames: pattern.AllowedGames.String,
	}
	if pattern.WagerGamesSegmentID.Valid {
		bonusCreationParams.WagerGameSegmentID = pattern.WagerGamesSegmentID.Int64
	}

	if patternParams.Title != "" {
		bonusCreationParams.Title = patternParams.Title
	}
	if patternParams.ImgURL != "" {
		bonusCreationParams.ImgURL = patternParams.ImgURL
	}
	if patternParams.AllowedGames != "" {
		bonusCreationParams.AllowedGames = patternParams.AllowedGames
	}
	if patternParams.CreatorType != 0 {
		bonusCreationParams.CreatorType = patternParams.CreatorType
	}
	if patternParams.CreatorID != 0 {
		bonusCreationParams.CreatorID = patternParams.CreatorID
	}

	bonus, err := CreateFromParams(bonusCreationParams)
	if err != nil {
		return nil, err
	}

	if !patternParams.Tmp && pattern.SegmentIDs.Valid && bonus.GetID() > 0 {
		// Добавляем бонус в пользовательские бонусы
		ub, err := service.GetUserBonuses(patternParams.UserID)
		if err != nil {
			logger.Get().Error("Failed to get user bonuses", "error", err.Error())
		} else {
			ub.AddBonus(bonus.GetID())
		}

		if pattern.SegmentIDs.String != "" {
			// Добавляем бонус в сегмент по идентификаторам
			jsonIds := []any{}
			if err := json.Unmarshal([]byte(pattern.SegmentIDs.String), &jsonIds); err != nil {
				logger.Get().Error("Failed to unmarshal segment IDs", "error", err.Error())
			} else {
				if len(jsonIds) > 0 {
					ids := make([]int64, 0, len(jsonIds))
					for _, id := range jsonIds {
						if idInt, ok := id.(int64); ok {
							ids = append(ids, idInt)
						} else if idStr, ok := id.(string); ok {
							if idInt, err := strconv.ParseInt(idStr, 10, 64); err == nil {
								ids = append(ids, idInt)
							}
						} else if idFloat, ok := id.(float64); ok {
							ids = append(ids, int64(idFloat))
						} else {
							logger.Get().Error("Invalid segment ID type", "type", fmt.Sprintf("%T", id))
						}
					}
					// Добавляем бонус в сегмент по идентификаторам
					if err := BonusSegmentAddToIds(bonus.GetID(), ids); err != nil {
						logger.Get().Error("Failed to add bonus to segment by IDs", "error", err.Error())
					}
				}
			}
		}
	}

	return bonus, nil
}
