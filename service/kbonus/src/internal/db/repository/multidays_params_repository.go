package repository

import (
	"kbonus/internal/db"
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
)

type BonusMultiDaysParamsDbRepository struct {
}

func NewBonusMultiDaysParamsDbRepository() container.BonusMultiDaysParamsRepository {
	return &BonusMultiDaysParamsDbRepository{}
}

func (r *BonusMultiDaysParamsDbRepository) GetAllParams() ([]*models.BonusMultiDaysParam, error) {
	db := db.Instance()
	query := "SELECT * FROM bonus_multi_days_params"
	var params []*models.BonusMultiDaysParam
	err := db.Select(&params, query)
	if err != nil {
		return nil, err
	}
	if len(params) == 0 {
		return nil, nil // Возвращаем nil, если нет параметров
	}
	return params, nil
}
