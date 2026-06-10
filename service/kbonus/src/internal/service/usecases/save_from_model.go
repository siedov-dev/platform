package usecases

import (
	"errors"
	"kbonus/internal/db/models"
	"kbonus/internal/service"
	"kbonus/internal/service/entities"
)

func SaveBonusFromModel(bonus_model *models.UserBonus) (*entities.Bonus, error) {
	if bonus_model == nil {
		return nil, errors.New("bonus model is nil")
	}

	if bonus_model.ID == 0 {
		bonus, err := entities.NewBonusFromModel(bonus_model, 100)
		if err != nil {
			return nil, err
		}
		id, err := bonus.Save()
		if err != nil {
			return nil, err
		}
		if id == 0 {
			return nil, errors.New("failed to save bonus")
		}
		service.AddBonusToCache(bonus)
		ub, err := service.GetUserBonuses(bonus_model.UserID)
		if err == nil && ub != nil {
			ub.AddBonus(bonus.GetID())
		}
		return bonus, nil
	}
	bonus, err := service.GetBonus(bonus_model.ID, 100)
	if err != nil {
		return nil, err
	}
	if bonus == nil {
		return nil, errors.New("bonus not found")
	}
	updated_bonus, err := bonus.UpdateModel(bonus_model)
	if err != nil {
		return nil, err
	}
	_, err = updated_bonus.Save()
	if err != nil {
		return nil, err
	}
	return updated_bonus, nil

}
