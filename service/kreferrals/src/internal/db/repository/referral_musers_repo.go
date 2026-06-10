package repository

import (
	"fmt"
	"kreferrals/internal/db"
	"kreferrals/internal/db/models"
	"kreferrals/internal/service/container"

	"github.com/jmoiron/sqlx"
)

type ReferralMUsersDBRepository struct {
	// db - база данных
	db *sqlx.DB
}

var _ container.ReferralMUsersRepository = (*ReferralMUsersDBRepository)(nil)

func NewReferralMUsersRepository() *ReferralMUsersDBRepository {
	db := db.Instance()
	return &ReferralMUsersDBRepository{
		db: db,
	}
}

// GetReferralUsersCount возвращает общее количество пользователей, приглашенных рефералом с указанным ref_id.
func (r *ReferralMUsersDBRepository) GetReferralUsersCount(ref_id uint64) (uint64, error) {
	var users_count uint64
	query := `SELECT count(id) FROM m_users WHERE ref_id = ?`
	err := r.db.Get(&users_count, query, ref_id)
	if err != nil {
		return 0, err
	}
	return users_count, nil
}

// GetReferralUsers возвращает пользователей, которые были приглашены рефералом с указанным ref_id.
// Возвращает список пользователей, их валютуб сумму потраченых денег и прибыль, если она есть.
// Параметры offset и limit используются для пагинации результатов.
func (r *ReferralMUsersDBRepository) GetReferralUsers(ref_id uint64, offset uint64, limit uint64) ([]*container.ReferralMUser, error) {
	var users []*container.ReferralMUser
	query := `
		SELECT id, login, m_users.currency as currency_id, created_at as reg_date,
			COALESCE(profits.profit, 0) as profit, 
			COALESCE(profits.spent, 0) as money_spent, 
			COALESCE(profits.currency, m_users.currency) as profit_currency
		FROM m_users 
		LEFT JOIN (
    		SELECT user_id, 
				sum(profit) as profit, 
				sum(IF(type = ` + fmt.Sprintf("%d", models.ReferralEventTypeMoneySpent) + `, amount, 0)) as spent, 
				currency
    		FROM referral_events
    		WHERE profit > 0
    		GROUP BY user_id, currency
		) as profits on (profits.user_id = m_users.id)
		WHERE m_users.ref_id = ? 
		LIMIT ? OFFSET ?
	`
	err := r.db.Select(&users, query, ref_id, limit, offset)
	if err != nil {
		return nil, err
	}
	return users, nil
}

// GetReferralUsersCountWithProfit возвращает общее количество пользователей, подходящих под условия выборки GetReferralUsers.
func (r *ReferralMUsersDBRepository) GetReferralUsersCountWithProfit(ref_id uint64) (uint64, error) {
	var count uint64
	query := `
        SELECT COUNT(*)
        FROM m_users 
        LEFT JOIN (
            SELECT user_id, sum(profit) as profit, currency
            FROM referral_events
            WHERE profit > 0
            GROUP BY user_id, currency
        ) as profits on (profits.user_id = m_users.id)
        WHERE m_users.ref_id = ? 
    `
	err := r.db.Get(&count, query, ref_id)
	if err != nil {
		return 0, err
	}
	return count, nil
}

func (r *ReferralMUsersDBRepository) GetReferralUsersCountwithDeps(ref_id uint64) (uint64, error) {
	var users_count uint64
	query := `
		SELECT count(DISTINCT m_users.id) 
		FROM m_users 
		inner JOIN payout_transactions on (payout_transactions.user_id = m_users.id)
		WHERE ref_id = ?
		AND payout_transactions.user_dep_n = 1;
	`
	err := r.db.Get(&users_count, query, ref_id)
	if err != nil {
		return 0, err
	}
	return users_count, nil
}
