package container

import "kreferrals/internal/db/models"

type ReferralConfigRepository interface {
	GetReferralUsersConfigs() ([]*models.ReferralConfig, error)
	GetReferralMainConfig() (*models.ReferralConfig, error)
	GetReferralPromoConfigs() ([]*models.ReferralConfig, error)
	GetReferralActivePromoConfigs() ([]*models.ReferralConfig, error)
}

type AchievementRewardsRepository interface {
	GetAchievementRewards() ([]*models.ReferralAchievementReward, error)
}

type ReferralEventRepository interface {
	GetReferralEventByID(id uint64) (*models.ReferralEvent, error)
	CreateReferralEvent(event *models.ReferralEvent) error
	UpdateReferralEvent(event *models.ReferralEvent) error
	DeleteReferralEvent(id uint64) error
	GetReferralEvents(ref_id uint64, offset uint64, limit uint64) ([]*models.ReferralEvent, error)
	GetReferralEventsCount(ref_id uint64) (uint64, error)
}

type UserReferralRepository interface {
	GetUserReferralByID(id uint64) (*models.UserReferral, error)
	CreateUserReferral(userReferral *models.UserReferral) error
	UpdateUserReferral(userReferral *models.UserReferral) error
	DeleteUserReferral(id uint64) error
	GetUserReferralByUserID(userID uint64) (*models.UserReferral, error)
	GetUserReferralByRefCode(refCode string) (*models.UserReferral, error)
	GetUserReferrals(start int64, limit int64) ([]*models.UserReferral, error)
	UserReferralExists(userID uint64) (bool, error)
}

type ReferralMUsersRepository interface {
	GetReferralUsersCount(ref_id uint64) (uint64, error)
	GetReferralUsers(ref_id uint64, offset uint64, limit uint64) ([]*ReferralMUser, error)
	GetReferralUsersCountWithProfit(ref_id uint64) (uint64, error)
	GetReferralUsersCountwithDeps(ref_id uint64) (uint64, error)
}

// ReferralMUser представляет собой сокращенную версию модели пользователя для реферальных систем
type ReferralMUser struct {
	ID             uint64  `db:"id" json:"id"`                           // ID пользователя
	Login          string  `db:"login" json:"login"`                     // Логин пользователя
	RegDate        string  `db:"reg_date" json:"reg_date"`               // Дата регистрации пользователя
	MoneySpent     float64 `db:"money_spent" json:"money_spent"`         // Сумма денег, потраченная пользователем
	Profit         float64 `db:"profit" json:"profit"`                   // Прибыль пользователя
	CurrencyID     int     `db:"currency_id" json:"currency_id"`         // ID валюты пользователя
	ProfitCurrency int     `db:"profit_currency" json:"profit_currency"` // Валюта прибыли
}
