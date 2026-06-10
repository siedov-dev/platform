package betwagerhandlers

import (
	"user-manager/internal/db/models"
	"user-manager/internal/stypes"
)

// GetBetWagerer возвращает соответствующий тип бонуса для обработки ставок.
// Принимает указатель на UserBonus и RealMoneyWallet.
// Возвращает экземпляр BonusBetWagerer.
// Используется для бонусов различных типов.
// (на данный момент не используется)
func GetBetWagerer(bonus *models.UserBonus, wallet stypes.RealMoneyWallet) stypes.BonusBetWagerer {
	switch bonus.Type {
	case stypes.TYPE_NON_DEPOSIT:
		return NewNonDepWagerer(bonus, wallet)
	case stypes.TYPE_FILTHY_FS_WASH_OFF:
		return NewFilthyBetWagerer(bonus, wallet)
	}
	return NewNoBetWagerer()
}
