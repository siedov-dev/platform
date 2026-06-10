package repository

import (
	"database/sql"
	"kreferrals/internal/db"
	"kreferrals/internal/db/models"
	"kreferrals/internal/service/container"

	"github.com/jmoiron/sqlx"
)

type UserReferralsDbRepository struct {
	// db - база данных
	db *sqlx.DB
}

var _ container.UserReferralRepository = (*UserReferralsDbRepository)(nil)

// NewUserReferralsDbRepository создает новый экземпляр UserReferralsDbRepository с подключением к базе данных.
func NewUserReferralsDbRepository() *UserReferralsDbRepository {
	db := db.Instance()
	return &UserReferralsDbRepository{
		db: db,
	}
}

func (r *UserReferralsDbRepository) GetUserReferralByID(id uint64) (*models.UserReferral, error) {
	var userReferral models.UserReferral
	query := `SELECT * FROM user_referrals WHERE id = ?`
	err := r.db.Get(&userReferral, query, id)
	if err != nil {
		return nil, err
	}
	return &userReferral, nil
}

func (r *UserReferralsDbRepository) CreateUserReferral(userReferral *models.UserReferral) error {
	query := `INSERT INTO user_referrals (user_id, ref_code, created_at, updated_at) VALUES (?, ?, NOW(), NOW())`
	_, err := r.db.Exec(query, userReferral.UserID, userReferral.RefCode)
	if err != nil {
		return err
	}
	return nil
}

func (r *UserReferralsDbRepository) UpdateUserReferral(userReferral *models.UserReferral) error {
	query := `UPDATE user_referrals SET user_id = ?, ref_code = ?, updated_at = NOW() WHERE id = ?`
	_, err := r.db.Exec(query, userReferral.UserID, userReferral.RefCode, userReferral.ID)
	if err != nil {
		return err
	}
	return nil
}

func (r *UserReferralsDbRepository) DeleteUserReferral(id uint64) error {
	query := `DELETE FROM user_referrals WHERE id = ?`
	_, err := r.db.Exec(query, id)
	if err != nil {
		return err
	}
	return nil
}

func (r *UserReferralsDbRepository) GetUserReferralByUserID(userID uint64) (*models.UserReferral, error) {
	var userReferral models.UserReferral
	query := `SELECT * FROM user_referrals WHERE user_id = ?`
	err := r.db.Get(&userReferral, query, userID)
	if err != nil {
		if err == sql.ErrNoRows {
			return nil, nil
		}
		return nil, err
	}
	return &userReferral, nil
}

func (r *UserReferralsDbRepository) GetUserReferralByRefCode(refCode string) (*models.UserReferral, error) {
	var userReferral models.UserReferral
	query := `SELECT * FROM user_referrals WHERE ref_code = ?`
	err := r.db.Get(&userReferral, query, refCode)
	if err != nil {
		return nil, err
	}
	return &userReferral, nil
}

func (r *UserReferralsDbRepository) GetUserReferrals(start int64, limit int64) ([]*models.UserReferral, error) {
	var userReferrals []*models.UserReferral
	query := `SELECT * FROM user_referrals LIMIT ? OFFSET ?`
	err := r.db.Select(&userReferrals, query, limit, start)
	if err != nil {
		return nil, err
	}
	return userReferrals, nil
}

func (r *UserReferralsDbRepository) UserReferralExists(userID uint64) (bool, error) {
	var exists bool
	query := `SELECT EXISTS(SELECT 1 FROM user_referrals WHERE user_id = ?)`
	err := r.db.Get(&exists, query, userID)
	if err != nil {
		return false, err
	}
	return exists, nil
}
