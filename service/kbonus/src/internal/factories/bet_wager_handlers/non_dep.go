package betwagerhandlers

import (
	"kbonus/internal/db/models"
	"kbonus/internal/stypes"
)

type NonDepWagerer struct {
	bonus  *models.UserBonus
	wallet stypes.GameWallet
}

// NewNonDepWagerer создает новый экземпляр NonDepWagerer.
// Принимает указатель на UserBonus и RealMoneyWallet.
// Возвращает экземпляр NonDepWagerer.
// Использовался для бонуса NonDeposit(2).
// (на данный момент не используется)
func NewNonDepWagerer(bonus *models.UserBonus, wallet stypes.GameWallet) stypes.BonusBetWagerer {
	return &NonDepWagerer{bonus: bonus, wallet: wallet}
}

// OnUserBet обрабатывает ставку пользователя.
// Принимает сумму ставки.
// Добавляет сумму ставки к общей сумме ставок.
// Если общая сумма ставок превышает сумму, необходимую для завершения бонуса, устанавливает статус завершен.
func (bw *NonDepWagerer) OnUserBet(amount float64) {
	accumulation := amount * float64(bw.bonus.Multiplier)
	bw.bonus.TotalBetForPayout += accumulation
	if bw.bonus.TotalBetForPayout >= bw.bonus.MoneyAvailable {
		bw.bonus.TotalBetForPayout = bw.bonus.MoneyAvailable
	}
}

// OnUserWin обрабатывает выигрыш пользователя.
// Принимает сумму выигрыша.
// Ничего не делаем.
func (bw *NonDepWagerer) OnUserWin(amount float64) {
}
