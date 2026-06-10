package usecases

import (
	"database/sql"
	"fmt"
	"kbonus/internal/logger"
	"kbonus/internal/service"
	"kbonus/internal/service/container"
	"kbonus/internal/service/entities"
	"kbonus/internal/stypes"
)

func CreateFromParams(createParams stypes.BonusCreateParams) (*entities.Bonus, error) {
	creator := container.GetBonusCreatorFactory().GetBonusCreator(createParams.BonusType)
	if creator == nil {
		return nil, fmt.Errorf("unknown bonus type: %d", createParams.BonusType)
	}
	bonusModel, err := creator.CreateBonus(
		createParams.BonusType,
		createParams.Params,
		createParams.Currency,
		createParams.UserID,
		createParams.KeepParams,
	)
	if err != nil {
		return nil, err
	}
	bonus, err := entities.NewBonusFromModel(bonusModel, 100)
	if err != nil {
		return nil, err
	}
	if createParams.PatternID != 0 {
		bonus.UserBonus.PatternID = sql.NullInt64{Int64: createParams.PatternID, Valid: true}
	}
	if createParams.CreatorID != 0 {
		bonus.UserBonus.CreatorID = sql.NullInt64{Int64: createParams.CreatorID, Valid: true}
		bonus.UserBonus.CreatorType = createParams.CreatorType
	}
	if createParams.Title != "" {
		bonus.UserBonus.Title = sql.NullString{String: createParams.Title, Valid: true}
	}
	if createParams.ImgURL != "" {
		bonus.UserBonus.ImgURL = sql.NullString{String: createParams.ImgURL, Valid: true}
	}
	bonus.UserBonus.Marker = createParams.Marker

	if createParams.AllowedGames != "" {
		bonus.SetAllowedGames(&createParams.AllowedGames)
	}
	bonus.UserBonus.WagerGamesSegmentID = sql.NullInt64{Int64: createParams.WagerGameSegmentID, Valid: createParams.WagerGameSegmentID != 0}

	if !createParams.Tmp {
		id, err := bonus.Save()
		if err != nil {
			return nil, err
		}
		service.AddBonusToCache(bonus)
		userBonuses, err := service.GetUserBonuses(bonusModel.UserID)
		if err != nil {
			logger.Get().Error("Failed to get user bonuses", "err", err.Error())
		} else {
			userBonuses.AddBonus(id)
		}
	}

	return bonus, nil
}
