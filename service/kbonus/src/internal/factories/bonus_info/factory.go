package bonusinfo

import (
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
)

type bonusInfoProviderFactory struct {
}

func NewBonusInfoProviderFactory() container.BonusInfoProviderFactory {
	return &bonusInfoProviderFactory{}
}

func (f *bonusInfoProviderFactory) GetBonusInfoProvider(bonus *models.UserBonus) stypes.BonusInfoProvider {
	switch bonus.Type {
	case stypes.TYPE_CHOOSE_WAGER_FS2:
		return NewHasMenuBonusProvider(bonus)
	case stypes.TYPE_PP_FS_MENU:
		return NewHasMenuBonusProvider(bonus)
	case stypes.TYPE_SLEG_FS_MENU:
		return NewHasMenuBonusProvider(bonus)
	case stypes.TYPE_PS_FS_MENU:
		return NewHasMenuBonusProvider(bonus)
	case stypes.TYPE_STICKY_FS_MENU:
		return NewHasMenuBonusProvider(bonus)
	default:
		return NewDefaultBonusInfoProvider(bonus) // Возвращаем провайдер по умолчанию
	}
}
