package repository

import (
	"kreferrals/internal/db"
	"kreferrals/internal/db/models"
	"kreferrals/internal/service/container"

	"github.com/jmoiron/sqlx"
)

type ReferralEventDbRepository struct {
	// db - база данных
	db *sqlx.DB
}

var _ container.ReferralEventRepository = (*ReferralEventDbRepository)(nil)

// NewReferralEventDbRepository создает новый экземпляр ReferralEventDbRepository с подключением к базе данных.
func NewReferralEventDbRepository() *ReferralEventDbRepository {
	db := db.Instance()
	return &ReferralEventDbRepository{
		db: db,
	}
}

// GetReferralEventByID возвращает событие реферала по его ID.
func (r *ReferralEventDbRepository) GetReferralEventByID(id uint64) (*models.ReferralEvent, error) {
	var event models.ReferralEvent
	query := `SELECT * FROM referral_events WHERE id = ?`
	err := r.db.Get(&event, query, id)
	if err != nil {
		return nil, err
	}
	return &event, nil
}

// CreateReferralEvent создает новое событие реферала в базе данных.
func (r *ReferralEventDbRepository) CreateReferralEvent(event *models.ReferralEvent) error {
	query := `INSERT INTO referral_events (ref_id, user_id, type, data, amount, currency, profit, created_at, updated_at, status, config_id, fingerprint) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), ?, ?, ?)`
	_, err := r.db.Exec(query, event.RefID, event.UserID, event.Type, event.Data, event.Amount, event.Currency, event.Profit, event.Status, event.ConfigID, event.Fingerprint)
	if err != nil {
		return err
	}
	return nil
}

// UpdateReferralEvent обновляет существующее событие реферала в базе данных.
func (r *ReferralEventDbRepository) UpdateReferralEvent(event *models.ReferralEvent) error {
	query := `UPDATE referral_events SET user_id = ?, type = ?, data = ?, amount = ?, currency = ?, profit = ?, config_id = ?, fingerprint = ?, updated_at = NOW() WHERE id = ?`
	_, err := r.db.Exec(query, event.UserID, event.Type, event.Data, event.Amount, event.Currency, event.Profit, event.ConfigID, event.Fingerprint, event.ID)
	if err != nil {
		return err
	}
	return nil
}

// DeleteReferralEvent удаляет событие реферала из базы данных по его ID.
func (r *ReferralEventDbRepository) DeleteReferralEvent(id uint64) error {
	query := `DELETE FROM referral_events WHERE id = ?`
	_, err := r.db.Exec(query, id)
	if err != nil {
		return err
	}
	return nil
}

// GetReferralEvents возвращает список событий реферала по его ID с пагинацией.
// Параметры offset и limit используются для пагинации.
// Исключаются события с типом ReferralEventTypeClick и ReferralEventTypeRegister.
func (r *ReferralEventDbRepository) GetReferralEvents(ref_id uint64, offset uint64, limit uint64) ([]*models.ReferralEvent, error) {
	var events []*models.ReferralEvent
	query := `SELECT * FROM referral_events WHERE ref_id = ? AND type > 1 ORDER BY created_at DESC LIMIT ? OFFSET ?`
	err := r.db.Select(&events, query, ref_id, limit, offset)
	if err != nil {
		return nil, err
	}
	return events, nil
}

func (r *ReferralEventDbRepository) GetReferralEventsCount(ref_id uint64) (uint64, error) {
	var total_rows uint64
	query := `SELECT COUNT(*) FROM referral_events WHERE ref_id = ? AND type > 1`
	err := r.db.Get(&total_rows, query, ref_id)
	if err != nil {
		return 0, err
	}
	return total_rows, nil
}
