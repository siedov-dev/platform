package repository

import (
	"kreferrals/internal/db"
	"kreferrals/internal/db/models"
	"kreferrals/internal/service/container"

	"github.com/jmoiron/sqlx"
)

// AchievementRewardsDBRepository представляет собой реализацию репозитория для работы с наградами достижений в базе данных.
type AchievementRewardsDBRepository struct {
	// db - база данных
	db *sqlx.DB
}

var _ container.AchievementRewardsRepository = (*AchievementRewardsDBRepository)(nil)

// NewAchievementRewardsDBRepository создает новый экземпляр AchievementRewardsDBRepository с подключением к базе данных.
func NewAchievementRewardsDBRepository() *AchievementRewardsDBRepository {
	db := db.Instance()
	return &AchievementRewardsDBRepository{
		db: db,
	}
}

// GetAchievementRewards возвращает список наград достижений из базы данных.
func (r *AchievementRewardsDBRepository) GetAchievementRewards() ([]*models.ReferralAchievementReward, error) {
	var rewards []*models.ReferralAchievementReward
	query := `SELECT * FROM referral_achievements_rewards`
	err := r.db.Select(&rewards, query)
	if err != nil {
		return nil, err
	}
	return rewards, nil
}
