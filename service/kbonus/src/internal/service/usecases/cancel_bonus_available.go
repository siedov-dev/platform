package usecases

import (
	"fmt"
	"kbonus/internal/service"
	"kbonus/internal/stypes"
)

func CancelBonusAvailable(avBonusID int64, userID stypes.UserID) error {
	av_bonus, err := service.GetBonusAvailable(avBonusID)
	if err != nil {
		return err
	}
	if av_bonus == nil {
		return fmt.Errorf("bonus not found")
	}

	av_bonus.AddCanceledUser(userID)

	return nil
}
