package activators

import (
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
)

type BonusActivatorFactory struct {
}

func NewBonusActivatorFactory() container.BonusActivatorFactory {
	return &BonusActivatorFactory{}
}

func (f *BonusActivatorFactory) GetBonusActivator(bonus *models.UserBonus, wallet stypes.GameWallet) (stypes.BonusActivator, error) {
	switch bonus.Type {
	case stypes.TYPE_CHOOSE_WAGER_FS2:
		return NewChooseWagerFs2Activator(bonus), nil
	case stypes.TYPE_WELCOME_BONUS_PACK:
		return NewWelcomeBonusActivator(bonus), nil
	case stypes.TYPE_SHARED_MONEY_FS:
		return NewShareMoneyFsActivator(bonus), nil
	case stypes.TYPE_SHARED_MONEY:
		return NewShareMoneyActivator(bonus), nil
	case stypes.TYPE_PS_SMART:
		return NewPSSmartFSActivator(bonus), nil
	case stypes.TYPE_PS_SFS_DYNAMIC:
		return NewPSSmartFSDynamicActivator(bonus), nil
	case stypes.TYPE_PS_NON_DEP:
		return NewPSNonDepFSActivator(bonus), nil
	case stypes.TYPE_PS_FS_MENU:
		return NewPSFSMenuActivator(bonus), nil
	case stypes.TYPE_PS_FS:
		return NewPSFreeSpinsActivator(bonus), nil
	case stypes.TYPE_STICKY_FS_MENU:
		return NewStickyFSMenuActivator(bonus), nil

	case stypes.TYPE_SLEG_SMART:
		return NewPSSmartFSActivator(bonus), nil
	case stypes.TYPE_SLEG_SFS_DYNAMIC:
		return NewPSSmartFSDynamicActivator(bonus), nil
	case stypes.TYPE_SLEG_NONDEP:
		return NewPSNonDepFSActivator(bonus), nil
	case stypes.TYPE_SLEG_FS_MENU:
		return NewPSFSMenuActivator(bonus), nil
	case stypes.TYPE_SLEG_FS:
		return NewPSFreeSpinsActivator(bonus), nil

	case stypes.TYPE_PPLAY_SMART_FS:
		return NewPSSmartFSActivator(bonus), nil
	case stypes.TYPE_PPLAY_SFS_DYNAMIC:
		return NewPSSmartFSDynamicActivator(bonus), nil
	case stypes.TYPE_PP_NONDEP:
		return NewPSNonDepFSActivator(bonus), nil
	case stypes.TYPE_PP_FS_MENU:
		return NewPSFSMenuActivator(bonus), nil
	case stypes.TYPE_PPLAY_FS:
		return NewPSFreeSpinsActivator(bonus), nil

	case stypes.TYPE_PLUS_MINUS_DEP:
		return NewPlusMinusDepActivator(bonus), nil
	case stypes.TYPE_PLUS_MINUS:
		return NewPlusMinusActivator(bonus), nil
	case stypes.TYPE_MULTI_DAYS:
		return NewMultidaysActivator(bonus), nil
	case stypes.TYPE_DEPOSIT_PRIZE:
		return NewDepositPrizeActivator(bonus), nil
	case stypes.TYPE_NON_DEPOSIT:
		return NewNonDepositActivator(bonus), nil
	case stypes.TYPE_CASHBACK_MONEY:
		return NewCashbackMoneyActivator(bonus, wallet), nil
	case stypes.TYPE_DEPOSIT_WAGER:
		return NewDepositWagerActivator(bonus), nil
	default:
		return NewDefaultActivator(bonus), nil
	}
}
