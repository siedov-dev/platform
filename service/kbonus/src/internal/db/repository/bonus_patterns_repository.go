package repository

import (
	"kbonus/internal/db"
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
)

type DbBonusPatternsRepository struct {
}

func NewBonusPatternsRepository() container.BonusPatternsRepository {
	return &DbBonusPatternsRepository{}
}

func (r *DbBonusPatternsRepository) GetAllPatterns() ([]*models.BonusPattern, error) {
	db := db.Instance()
	var patterns []*models.BonusPattern
	err := db.Select(&patterns, "SELECT * FROM bonus_patterns")
	if err != nil {
		return nil, err
	}
	return patterns, nil
}
