package usecases

import "kbonus/internal/service"

func ReloadBonusAvailable(id int64) error {
	return service.ReloadBonusAvailable(id)
}
