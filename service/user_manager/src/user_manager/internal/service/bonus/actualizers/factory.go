package actualizers

import (
	"fmt"
	"user-manager/internal/db/models"
	"user-manager/internal/stypes"
)

// GetActualizer возвращает актуализатор бонуса в зависимости от его типа.
// Принимает указатель на UserBonus и GameWallet.
// Возвращает актуализатор бонуса и ошибку.
func GetActualizer(bonus *models.UserBonus, wallet stypes.RealMoneyWallet) (stypes.BonusActualizer, error) {
	switch bonus.Type {
	case stypes.TYPE_SUPER_FREE_SPINS_PLAYBACK:
		return NewSuperFsPbActualizer(wallet, bonus), nil
	case stypes.TYPE_SUPER_FREE_SPINS:
		return NewSuperFsActualizer(wallet, bonus), nil
	case stypes.TYPE_FILTHY_FS:
		return NewFilthyFsActualizer(wallet, bonus), nil
	case stypes.TYPE_NON_DEPOSIT:
		return NewEmptyActualizer(), nil
	case stypes.TYPE_FILTHY_FS_WASH_OFF:
		return NewEmptyActualizer(), nil
	case stypes.TYPE_BEZDEP:
		return NewDefaultActualizer(wallet, bonus), nil
	case stypes.TYPE_DEPOSIT_WAGER:
		return NewDefaultActualizer(wallet, bonus), nil
	case stypes.TYPE_DEPOSIT_PRIZE:
		return NewDefaultActualizer(wallet, bonus), nil
	case stypes.TYPE_DEPOSIT_FFS_TNT:
		return NewDefaultActualizer(wallet, bonus), nil
	case stypes.TYPE_NON_DEPOSIT_WAGER:
		return NewNonDepWagerAcualizer(wallet, bonus), nil
	case stypes.TYPE_CHOOSE_WAGER_FS2:
		return NewExtFsActualizer(wallet, bonus), nil
	case stypes.TYPE_PLUS_MINUS:
		return NewPlusMinusActualizer(wallet, bonus), nil
	case stypes.TYPE_PLUS_MINUS_DEP:
		return NewPlusMinusDepActualizer(wallet, bonus), nil
	case stypes.TYPE_SHARED_MONEY:
		return NewSharedMoneyAcualizer(wallet, bonus), nil
	case stypes.TYPE_SHARED_MONEY_FS:
		return NewSharedMoneyAcualizer(wallet, bonus), nil
	case stypes.TYPE_SLEG_FS:
		return NewExtFsActualizer(wallet, bonus), nil
	case stypes.TYPE_PS_FS:
		return NewExtFsActualizer(wallet, bonus), nil
	case stypes.TYPE_PP_NONDEP:
		return NewExtFsActualizer(wallet, bonus), nil
	case stypes.TYPE_SLEG_NONDEP:
		return NewExtFsActualizer(wallet, bonus), nil
	case stypes.TYPE_PS_NON_DEP:
		return NewExtFsActualizer(wallet, bonus), nil
	case stypes.TYPE_PPLAY_SMART_FS:
		return NewExtFsActualizer(wallet, bonus), nil
	case stypes.TYPE_SLEG_SMART:
		return NewExtFsActualizer(wallet, bonus), nil
	case stypes.TYPE_PS_SMART:
		return NewExtFsActualizer(wallet, bonus), nil
	case stypes.TYPE_PPLAY_SFS_DYNAMIC:
		return NewExtFsActualizer(wallet, bonus), nil
	case stypes.TYPE_PS_SFS_DYNAMIC:
		return NewExtFsActualizer(wallet, bonus), nil
	case stypes.TYPE_SLEG_FS_MENU:
		return NewExtFsActualizer(wallet, bonus), nil
	case stypes.TYPE_PP_FS_MENU:
		return NewExtFsActualizer(wallet, bonus), nil
	case stypes.TYPE_PS_FS_MENU:
		return NewExtFsActualizer(wallet, bonus), nil
	case stypes.TYPE_SMART_WAGER:
		return NewDefaultActualizer(wallet, bonus), nil
	case stypes.TYPE_NON_DEP:
		return NewDefaultActualizer(wallet, bonus), nil
	case stypes.TYPE_QT_FS_WAGER:
		return NewDefaultActualizer(wallet, bonus), nil
	case stypes.TYPE_QT_SMART_FS:
		return NewDefaultActualizer(wallet, bonus), nil
	case stypes.TYPE_MULTI_DAYS:
		return NewMultidaysActualizer(wallet, bonus), nil
	}
	return nil, fmt.Errorf("no bonus actualizer for type %v", bonus.Type)
}
