package factories

import (
	"log/slog"
	restbonus "user-manager/internal/factories/rest_bonus"
	"user-manager/internal/logger"
	"user-manager/internal/service/container"
	"user-manager/internal/stypes"
)

type restUserBonusFactory struct {
}

func NewUserBonusRestFactory() container.BonusFactory {
	return restUserBonusFactory{}
}

func (f restUserBonusFactory) LoadBonus(bonusID int64, w stypes.RealMoneyWallet, WagerPrc int16) (stypes.IBonus, error) {
	b := &restbonus.Bonus{}
	ub, err := container.GetUserBonusRepository().GetByID(bonusID)
	if err != nil {
		logger.Get().Error("Load bonus", slog.String("err", err.Error()))
		return nil, err
	}
	b.UserBonus = ub
	b.GWallet = w
	b.WagerPrc = WagerPrc

	return b, err
}
