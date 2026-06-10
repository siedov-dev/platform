package repository

import (
	"kreferrals/internal/db"
	"kreferrals/internal/db/models"
	"kreferrals/internal/service/container"
	"strconv"

	"github.com/jmoiron/sqlx"
)

var _ container.ReferralConfigRepository = (*ReferralConfigDbRepository)(nil)

// ReferralConfigDbRepository представляет собой реализацию репозитория для работы с конфигурациями рефералов в базе данных.
type ReferralConfigDbRepository struct {
	// db - база данных
	db *sqlx.DB
}

// NewReferralConfigDbRepository создает новый экземпляр ReferralConfigDbRepository с подключением к базе данных.
func NewReferralConfigDbRepository() *ReferralConfigDbRepository {
	db := db.Instance()
	return &ReferralConfigDbRepository{
		db: db,
	}
}

// GetReferralUsersConfigs возвращает список конфигураций рефералов для пользователей.
func (r *ReferralConfigDbRepository) GetReferralUsersConfigs() ([]*models.ReferralConfig, error) {
	var configs []*models.ReferralConfig
	query := `SELECT * FROM referral_configs WHERE type = '` + strconv.FormatInt(int64(models.ConfigTypeUser), 10) + `'`
	err := r.db.Select(&configs, query)
	if err != nil {
		return nil, err
	}
	return configs, nil
}

// GetReferralMainConfig возвращает основную конфигурацию рефералов.
func (r *ReferralConfigDbRepository) GetReferralMainConfig() (*models.ReferralConfig, error) {
	var config models.ReferralConfig
	query := `SELECT * FROM referral_configs WHERE type = '` + strconv.FormatInt(int64(models.ConfigTypeMain), 10) + `'`
	err := r.db.Get(&config, query)
	if err != nil {
		return nil, err
	}
	return &config, nil
}

// GetReferralPromoConfigs возвращает список промо-конфигураций рефералов.
func (r *ReferralConfigDbRepository) GetReferralPromoConfigs() ([]*models.ReferralConfig, error) {
	var configs []*models.ReferralConfig
	query := `SELECT * FROM referral_configs WHERE type = '` + strconv.FormatInt(int64(models.ConfigTypePromo), 10) + `'`
	err := r.db.Select(&configs, query)
	if err != nil {
		return nil, err
	}
	return configs, nil
}

// GetReferralActiveMainConfig возвращает ожидающие акционные конфигурации.
func (r *ReferralConfigDbRepository) GetReferralActivePromoConfigs() ([]*models.ReferralConfig, error) {
	var configs []*models.ReferralConfig
	query := `SELECT * FROM referral_configs WHERE type = '` + strconv.FormatInt(int64(models.ConfigTypePromo), 10) + `'
	  			AND end_date > NOW()`
	err := r.db.Select(&configs, query)
	if err != nil {
		return nil, err
	}
	return configs, nil
}
