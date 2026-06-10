package usecases

import (
	"kbonus/internal/logger"
	"kbonus/internal/service"
	"kbonus/internal/service/entities"
	"kbonus/internal/stypes"
)

func GetAvailableBonuses(UserID stypes.UserID, currency_id int8) ([]*entities.Bonus, error) {
	logger.Get().Debug("GetAvailableBonuses", "UserID", UserID, "currency_id", currency_id)
	availableBonusesParams, err := service.GetAvailableBonusesParams(UserID, currency_id)
	if err != nil {
		return nil, err
	}
	logger.Get().Debug("AvailableBonusesParams", "params", availableBonusesParams)
	bonuses := []*entities.Bonus{}
	for _, param := range availableBonusesParams {
		bonusCreateParams := stypes.BonusCreateParams{
			UserID:             UserID,
			BonusType:          param.BonusType,
			Currency:           currency_id,
			Params:             param.Params,
			PatternID:          param.PatternID,
			CreatorType:        stypes.CREATOR_BONUS_AVAILABLE,
			CreatorID:          param.BonusAvailableID,
			Title:              param.Title,
			ImgURL:             param.ImgURL,
			Marker:             0,
			KeepParams:         false,
			Tmp:                true,
			AllowedGames:       param.Games,
			WagerGameSegmentID: param.WagerGameSegmentID,
		}
		bonus, err := CreateFromParams(bonusCreateParams)
		if err != nil {
			return nil, err
		}
		bonuses = append(bonuses, bonus)
	}

	return bonuses, nil
}
