package usecases

import (
	"fmt"
	"kbonus/internal/logger"
	"kbonus/internal/service"
	"kbonus/internal/service/container"
	"kbonus/internal/service/entities"
	"kbonus/internal/stypes"
)

func CreateBonusFromAvailable(avBonusID int64, user_id stypes.UserID, currencyID int8) (*entities.Bonus, error) {
	av_bonus, err := service.GetBonusAvailable(avBonusID)
	if err != nil {
		return nil, err
	}
	if av_bonus == nil {
		return nil, fmt.Errorf("bonus not found")
	}
	if !av_bonus.IsUserAllowed(user_id) {
		return nil, fmt.Errorf("user not allowed")
	}
	av_params := av_bonus.GetParams()
	bonusCreateParams := stypes.BonusCreateParams{
		UserID:             user_id,
		BonusType:          av_params.BonusType,
		Currency:           currencyID,
		Params:             av_params.Params,
		PatternID:          av_params.PatternID,
		CreatorType:        stypes.CREATOR_BONUS_AVAILABLE,
		CreatorID:          av_params.BonusAvailableID,
		Title:              av_params.Title,
		ImgURL:             av_params.ImgURL,
		Marker:             0,
		KeepParams:         false,
		Tmp:                false,
		AllowedGames:       av_params.Games,
		WagerGameSegmentID: av_params.WagerGameSegmentID,
	}
	bonus, err := CreateFromParams(bonusCreateParams)
	if err != nil {
		return nil, err
	}
	av_bonus.AddActivatedUser(user_id)

	baSegmentIds, err := container.GetBonusSegmentsStorage().GetBonusAvailableSegmentIds(av_bonus.GetID())
	if err != nil {
		logger.Get().Error("Failed to get bonus available segment IDs", "error", err)
	} else {
		err := BonusSegmentAddToIds(bonus.GetID(), baSegmentIds)
		if err != nil {
			logger.Get().Error("Failed to add bonus segment IDs", "error", err)
		}
	}

	// Возвращаем информацию о бонусе
	return bonus, nil
}
