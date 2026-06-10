package usecases

import (
	"fmt"
	"kbonus/internal/service"
	"kbonus/internal/service/entities"
	"kbonus/internal/stypes"
)

func GetBonusAvailable(avBonusID int64, currencyID int8) (*entities.Bonus, error) {
	av_bonus, err := service.GetBonusAvailable(avBonusID)
	if err != nil {
		return nil, err
	}
	if av_bonus == nil {
		return nil, fmt.Errorf("bonus not found")
	}
	av_params := av_bonus.GetParams()
	bonusCreateParams := stypes.BonusCreateParams{
		UserID:             0,
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
		Tmp:                true,
		AllowedGames:       av_params.Games,
		WagerGameSegmentID: av_params.WagerGameSegmentID,
	}
	bonus, err := CreateFromParams(bonusCreateParams)
	if err != nil {
		return nil, err
	}

	// Возвращаем информацию о бонусе
	return bonus, nil
}
