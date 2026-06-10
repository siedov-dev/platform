package usecases

import "kbonus/internal/service"

func ReloadService() error {
	return service.Reload()
}
