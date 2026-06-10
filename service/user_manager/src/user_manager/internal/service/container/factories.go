package container

import (
	"user-manager/internal/stypes"
)

var (
	bonusFactory BonusFactory
)

type BonusFactory interface {
	LoadBonus(bonusID int64, w stypes.RealMoneyWallet, WagerPrc int16) (stypes.IBonus, error)
}

func SetBonusFactory(factory BonusFactory) {
	mx.Lock()
	defer mx.Unlock()
	bonusFactory = factory
}

func GetBonusFactory() BonusFactory {
	mx.RLock()
	defer mx.RUnlock()
	if bonusFactory == nil {
		panic("bonus factory is not set")
	}
	return bonusFactory
}
