package usecases

import (
	"kbonus/internal/service"
	"kbonus/internal/stypes"
)

func ReloadBonusIfCached(id stypes.BonusID, user_id stypes.UserID) error {
	return service.ReloadBonusIfCached(id, user_id)
}
