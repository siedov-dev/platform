package repository

import (
	"kbonus/internal/db"
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
)

type dbBonusMenuParamsRepository struct {
}

func NewBonusMenuParamsDbRepository() container.BonusMenuParamsRepository {
	return &dbBonusMenuParamsRepository{}
}

func (r *dbBonusMenuParamsRepository) GetAllParams() ([]*models.BonusMenuParam, error) {
	db := db.Instance()
	query := "SELECT * FROM bonus_menu_params"
	var params []*models.BonusMenuParam
	err := db.Select(&params, query)
	if err != nil {
		return nil, err
	}
	if len(params) == 0 {
		return nil, nil // Возвращаем nil, если нет параметров
	}
	return params, nil
}
