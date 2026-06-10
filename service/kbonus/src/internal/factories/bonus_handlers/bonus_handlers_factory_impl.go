package bonushandlers

import (
	"errors"
	"fmt"
	"kbonus/internal/db/models"
	"kbonus/internal/stypes"
)

type BonusHandlersFactoryImpl struct {
}

// NewBonusHandlersFactoryImpl создает новый экземпляр фабрики обработчиков бонусов.
func NewBonusHandlersFactoryImpl() *BonusHandlersFactoryImpl {
	return &BonusHandlersFactoryImpl{}
}

func (f *BonusHandlersFactoryImpl) GetBonusHandler(ub *models.UserBonus, wagerPercent int16, userWallet stypes.RealMoneyWallet) (stypes.BonusHandler, error) {
	if ub == nil {
		return nil, errors.New("user bonus is nil")
	}

	switch ub.Type {
	case stypes.TYPE_NON_DEPOSIT:
		return NewNoBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_FILTHY_FS_WASH_OFF:
		return NewNoBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_SUPER_FREE_SPINS:
		return NewFsTotalWinBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_SUPER_FREE_SPINS_PLAYBACK:
		return NewFsTotalWinBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_FILTHY_FS:
		return NewFsTotalWinBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_BEZDEP:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_DEPOSIT_WAGER:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_DEPOSIT_PRIZE:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_DEPOSIT_FFS_TNT:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_NON_DEPOSIT_WAGER:
		return NewNonDepWagerHandler(ub, wagerPercent), nil
	case stypes.TYPE_CHOOSE_WAGER_FS2:
		return NewFsTotalWinBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_PLUS_MINUS:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_PLUS_MINUS_DEP:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_SHARED_MONEY:
		return NewSharedMoneyBonusHandler(ub, wagerPercent, userWallet), nil
	case stypes.TYPE_SHARED_MONEY_FS:
		return NewSharedMoneyBonusHandler(ub, wagerPercent, userWallet), nil
	case stypes.TYPE_PPLAY_SMART_FS:
		return NewSmartBonusHandler(ub, wagerPercent, userWallet), nil
	case stypes.TYPE_SMART_WAGER:
		return NewSmartBonusHandler(ub, wagerPercent, userWallet), nil
	case stypes.TYPE_SLEG_SFS_DYNAMIC:
		return NewSmartBonusHandler(ub, wagerPercent, userWallet), nil
	case stypes.TYPE_PPLAY_SFS_DYNAMIC:
		return NewSmartBonusHandler(ub, wagerPercent, userWallet), nil
	case stypes.TYPE_PS_SFS_DYNAMIC:
		return NewSmartBonusHandler(ub, wagerPercent, userWallet), nil
	case stypes.TYPE_SLEG_SMART:
		return NewSmartBonusHandler(ub, wagerPercent, userWallet), nil
	case stypes.TYPE_PS_SMART:
		return NewSmartBonusHandler(ub, wagerPercent, userWallet), nil
	case stypes.TYPE_SLEG_FS_MENU:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_PP_FS_MENU:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_PS_FS_MENU:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_SLEG_FS:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_PS_FS:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_SLEG_NONDEP:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_PP_NONDEP:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_PS_NON_DEP:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_NON_DEP:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_QT_FS_WAGER:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_QT_SMART_FS:
		return NewSmartBonusHandler(ub, wagerPercent, userWallet), nil
	case stypes.TYPE_MULTI_DAYS:
		return NewDefaultBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_WELCOME_BONUS_PACK:
		return NewNonPlayableHandler(ub, wagerPercent), nil
	case stypes.TYPE_CASHBACK_MONEY:
		return NewNoBonusHandler(ub, wagerPercent), nil
	case stypes.TYPE_STICKY_FS_MENU:
		return NewSharedMoneyBonusHandler(ub, wagerPercent, userWallet), nil
	}
	return nil, fmt.Errorf("no bonus handler for type %v", ub.Type)
}
