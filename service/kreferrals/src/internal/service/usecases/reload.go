package usecases

import "kreferrals/internal/service"

func Reload() error {
	return service.LoadConfigs()
}
