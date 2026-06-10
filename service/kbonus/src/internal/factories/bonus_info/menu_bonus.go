package bonusinfo

import (
	"kbonus/internal/db/models"
	"kbonus/internal/stypes"
)

type hasMenuBonusProvider struct {
	userBonus *models.UserBonus
}

func NewHasMenuBonusProvider(ub *models.UserBonus) stypes.BonusInfoProvider {
	return &hasMenuBonusProvider{
		userBonus: ub,
	}
}

func (d *hasMenuBonusProvider) GetBonusInfo() stypes.BonusInfo {
	info := createDefaultBonusInfo(d.userBonus)
	info.IsActivatedByMenu = true
	return info
}
