package bonuscreator

import (
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
)

type BonusCreatorFactoryImpl struct {
	creators map[int8]container.BonusCreator
}

func NewBonusCreatorFactoryImpl() container.BonusCreatorFactory {
	return &BonusCreatorFactoryImpl{
		creators: make(map[int8]container.BonusCreator),
	}
}

func (f *BonusCreatorFactoryImpl) GetBonusCreator(bonusType int8) container.BonusCreator {
	if creator, exists := f.creators[bonusType]; exists {
		return creator
	}

	var creator container.BonusCreator
	switch bonusType {
	case stypes.TYPE_PS_NON_DEP:
		creator = NewPSNonDepFSCreator()
	case stypes.TYPE_NON_DEP:
		creator = NewNonDepCreator()
	case stypes.TYPE_PS_SMART:
		creator = NewPSSmartFSCreator()
	case stypes.TYPE_PS_FS_MENU:
		creator = NewPSFsMenuCreator()
	case stypes.TYPE_PS_FS:
		creator = NewPSFreeSpinsCreator()
	case stypes.TYPE_WELCOME_BONUS_PACK:
		creator = NewWelcomeBonusCreator()
	case stypes.TYPE_SHARED_MONEY_FS:
		creator = NewSharedMoneyFsCreator()
	case stypes.TYPE_SHARED_MONEY:
		creator = NewSharedMoneyCreator()
	case stypes.TYPE_PLUS_MINUS_DEP:
		creator = NewPlusMinusDepCreator()
	case stypes.TYPE_MULTI_DAYS:
		creator = NewMultiDaysBonusCreator()
	case stypes.TYPE_SLEG_SFS_DYNAMIC:
		creator = NewSlegSfsDynamicCreator()
	case stypes.TYPE_PPLAY_SFS_DYNAMIC:
		creator = NewPPlaySfsDynamicCreator()
	case stypes.TYPE_CASHBACK_MONEY:
		creator = NewCashBackMoneyCreator()
	case stypes.TYPE_SLEG_FS_MENU:
		creator = NewSlegFsMenuCreator()
	case stypes.TYPE_PP_FS_MENU:
		creator = NewPPlayFsMenuCreator()
	case stypes.TYPE_SLEG_NONDEP:
		creator = NewSlegNonDepFSCreator()
	case stypes.TYPE_PP_NONDEP:
		creator = NewPPlayNonDepFSCreator()
	case stypes.TYPE_SLEG_SMART:
		creator = NewSlegSmartFSCreator()
	case stypes.TYPE_PPLAY_SMART_FS:
		creator = NewPPlaySmartFSCreator()
	case stypes.TYPE_QT_SMART_FS:
		creator = NewQTSmartFSCreator()
	case stypes.TYPE_SLEG_FS:
		creator = NewSlegFreeSpinsCreator()
	case stypes.TYPE_QT_FS_WAGER:
		creator = NewQTFreeSpinsCreator()
	case stypes.TYPE_PPLAY_FS:
		creator = NewPPlayFreeSpinsCreator()
	case stypes.TYPE_SMART_WAGER:
		creator = NewSmartWagerCreator()
	case stypes.TYPE_CHOOSE_WAGER_FS2:
		creator = NewChooseWagerFs2Creator()
	case stypes.TYPE_PS_SFS_DYNAMIC:
		creator = NewPSSfsDynamicCreator()
	case stypes.TYPE_DEPOSIT_PRIZE:
		creator = NewDepositPrizeBonusCreator()
	case stypes.TYPE_NON_DEPOSIT:
		creator = NewNonDepositBonusCreator()
	case stypes.TYPE_DEPOSIT_WAGER:
		creator = NewDepositWagerBonusCreator()
	case stypes.TYPE_STICKY_FS_MENU:
		creator = NewStickyFsMenuCreator()
	default:
		return nil // Неизвестный тип бонуса
	}

	f.creators[bonusType] = creator
	return creator
}
