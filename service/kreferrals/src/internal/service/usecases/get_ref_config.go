package usecases

import (
	"kreferrals/internal/service"
)

func GetRefConfig(user_id uint64) service.RefConfig {
	return *service.GetUserConfig(user_id)
}
