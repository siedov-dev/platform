package betwagerhandlers

import (
	"fmt"
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
	"time"
)

type FilthyBetWagerer struct {
	bonus  *models.UserBonus
	wallet stypes.GameWallet
}

// NewFilthyBetWagerer создает новый экземпляр FilthyBetWagerer.
// Принимает указатель на UserBonus и RealMoneyWallet.
// Возвращает экземпляр FilthyBetWagerer.
// Используется для бонуса Filthy.
func NewFilthyBetWagerer(bonus *models.UserBonus, wallet stypes.GameWallet) stypes.BonusBetWagerer {
	return &FilthyBetWagerer{bonus: bonus, wallet: wallet}
}

// OnUserBet обрабатывает ставку пользователя.
// Принимает сумму ставки.
// Если бонус не активный, ничего не делаем.
// Добавляет сумму ставки к общей сумме ставок.
// Если общая сумма ставок превышает сумму, необходимую для завершения бонуса, устанавливает статус завершен.

func (bw *FilthyBetWagerer) OnUserBet(amount float64) {
	if bw.bonus.Status != stypes.STATUS_FILTHY {
		return
	}
	bw.bonus.TotalBet += amount
	if bw.bonus.TotalBet > bw.bonus.WagerToComplete {
		bw.bonus.Status = stypes.STATUS_DONE
		bw.bonus.MoneyPaidOut = bw.bonus.MoneyAvailable
		bw.bonus.MoneyAvailable = 0
		bw.wallet.AddMoney(bw.bonus.MoneyPaidOut)
		bw.writeTransaction(bw.bonus.MoneyPaidOut)
	}
}

// OnUserWin обрабатывает выигрыш пользователя.
// Принимает сумму выигрыша.
// Ничего не делаем.
func (bw *FilthyBetWagerer) OnUserWin(amount float64) {
}

// writeTransaction записывает транзакцию в базу данных.
func (bw *FilthyBetWagerer) writeTransaction(amount float64) {
	curr, _ := currencyid.CurrencyID(bw.bonus.Currency).Name()
	tr := models.BonusTransaction{
		UserID:    int64(bw.bonus.UserID),
		HallID:    0,
		Date:      time.Now(),
		Sum:       amount,
		StatusID:  35,
		Currency:  curr,
		Operation: fmt.Sprintf("bonus win id# %d", bw.bonus.ID),
	}

	container.GetBonusTransactionRepository().Insert(&tr)

}
