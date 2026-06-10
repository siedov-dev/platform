package repository

import (
	"kbonus/internal/db"
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
)

type bonusAvailableRepository struct {
}

func NewBonusAvailableRepository() container.BonusAvailableRepository {
	return &bonusAvailableRepository{}
}

func (r *bonusAvailableRepository) GetAllAvailable() ([]*models.BonusAvailable, error) {
	db := db.Instance()
	var bonuses []*models.BonusAvailable
	err := db.Select(&bonuses, `
		SELECT * 
		FROM bonus_availables
		WHERE available_to > NOW()
	`)
	if err != nil {
		return nil, err
	}
	return bonuses, nil
}

func (r *bonusAvailableRepository) GetByID(id uint64) (*models.BonusAvailable, error) {
	db := db.Instance()
	var bonus models.BonusAvailable
	err := db.Get(&bonus, `
		SELECT * 
		FROM bonus_availables
		WHERE id = ?
	`, id)
	if err != nil {
		return nil, err
	}
	return &bonus, nil
}

func (r *bonusAvailableRepository) Update(ba *models.BonusAvailable) error {
	db := db.Instance()
	_, err := db.NamedExec(`
		UPDATE bonus_availables
		SET bonus_type = :bonus_type, currency_id = :currency_id, params = :params, allowed_users = :allowed_users,
		    users_activated = :users_activated, games = :games, available_from = :available_from,
		    available_to = :available_to, updated_at = NOW(), title = :title, img_url = :img_url,
		    descr = :descr, pattern_id = :pattern_id, users_canceled = :users_canceled
		WHERE id = :id
	`, ba)
	return err
}

func (r *bonusAvailableRepository) Delete(id uint64) error {
	db := db.Instance()
	_, err := db.Exec(`
		DELETE FROM bonus_availables
		WHERE id = ?
	`, id)
	return err
}

func (r *bonusAvailableRepository) Insert(ba *models.BonusAvailable) (uint64, error) {
	db := db.Instance()
	result, err := db.NamedExec(`
		INSERT INTO bonus_available (bonus_type, currency_id, params, allowed_users, users_activated, games, available_from, available_to, title, img_url, descr, pattern_id, users_canceled)
		VALUES (:bonus_type, :currency_id, :params, :allowed_users, :users_activated, :games, :available_from, :available_to, :title, :img_url, :descr, :pattern_id, :users_canceled)
	`, ba)
	if err != nil {
		return 0, err
	}
	id, err := result.LastInsertId()
	if err != nil {
		return 0, err
	}
	return uint64(id), nil
}
