package bonushandlers

import (
	"errors"
	"kbonus/internal/db/models"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
	"sync"
)

var _ stypes.BonusHandler = &SmartBonusHandler{}

// SmartBonusHandler обработчик бонуса.
// Обрабатывает бонусы, которые имеют общий баланс с реальными деньгами.
// Снимает деньги 50/50 между бонусом и реальными деньгами.
// Если нет реальных денег, то снимает только бонус.
type SmartBonusHandler struct {
	bonus    *models.UserBonus
	WagerPrc int16
	mut      sync.RWMutex
	wallet   stypes.GameWallet
}

// NewSmartBonusHandler создает новый обработчик бонуса.
// Принимает указатель на UserBonus, процент отыгрыша ставки и реальный денежный кошелек.
// Возвращает экземпляр SmartBonusHandler.
func NewSmartBonusHandler(b *models.UserBonus, wp int16, w stypes.GameWallet) stypes.BonusHandler {
	return &SmartBonusHandler{bonus: b, WagerPrc: wp, wallet: w}
}

// AddTotalBet добавляет сумму ставки к общей сумме ставок.
func (n *SmartBonusHandler) AddTotalBet(amount float64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	n.bonus.TotalBet += amount
}

// AddTotalWin добавляет сумму выигрыша к общей сумме выигрышей.
func (n *SmartBonusHandler) AddTotalWin(amount float64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	n.bonus.TotalWin += amount
}

// GetTotalBet возвращает общую сумму ставок.
func (n *SmartBonusHandler) GetTotalBet() float64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return n.bonus.TotalBet
}

// GetTotalWin возвращает общую сумму выигрышей.
func (n *SmartBonusHandler) GetTotalWin() float64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return n.bonus.TotalWin
}

// GetBalance возвращает текущий баланс бонуса.
func (n *SmartBonusHandler) GetBalance() float64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return n.getBalance()
}

// GetCents возвращает текущий баланс бонуса в центах.
func (n *SmartBonusHandler) GetCents() int64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return int64(n.getBalance() * 100)
}

func (n *SmartBonusHandler) getBalance() float64 {
	return n.bonus.MoneyAvailable + n.wallet.GetBalance()
}

// GetCurrencyName возвращает название валюты бонуса.
func (n *SmartBonusHandler) GetCurrencyName() string {
	n.mut.RLock()
	defer n.mut.RUnlock()
	name, _ := currencyid.CurrencyID(n.bonus.Currency).Name()
	return name
}

// RemCents удаляет сумму в центах из баланса бонуса.
func (n *SmartBonusHandler) RemCents(cents int64) (balance int64, real_amount int64, bonus_amount int64, err error) {
	n.mut.Lock()
	defer n.mut.Unlock()
	amount := float64(cents / 100)
	r, ra, ba, err := n.remMoney(amount)
	return int64(r * 100), int64(ra), int64(ba), err
}

// RemMoney удаляет сумму из баланса бонуса.
func (n *SmartBonusHandler) RemMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64, err error) {
	n.mut.Lock()
	defer n.mut.Unlock()
	return n.remMoney(amount)
}

func (n *SmartBonusHandler) remMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64, err error) {
	// если бонус имеет фриспины
	if n.bonus.SpinsAvailable > 0 {
		n.bonus.SpinsAvailable--
		return n.bonus.MoneyAvailable, 0, 0, nil
	}

	k := 0.5 // половина бонуса и половина реальных денег
	bonus_amount = float64(int(amount*100*k)) / 100
	real_amount = amount - float64(bonus_amount)

	// если нет достаточно реальных денег, используем только бонус
	if n.wallet.GetBalance() < real_amount {
		bonus_amount = amount
		real_amount = 0
		k = 0
	}

	n.bonus.RealMoneySpent = k

	if n.bonus.MoneyAvailable < bonus_amount {
		logger.Get().Error("Not enought bonus money", "bonus", n.bonus.MoneyAvailable, "amount", bonus_amount)
		return n.bonus.MoneyAvailable, 0, 0, errors.New("not enought bonus money")
	}

	if n.getBalance() < amount {
		logger.Get().Error("Not enought money", "balance", n.getBalance(), "amount", amount)
		return n.getBalance(), 0, 0, errors.New("not enought money")
	}

	n.wallet.RemMoney(real_amount)
	n.bonus.MoneyAvailable -= bonus_amount

	n.bonus.TotalBet += amount * (float64(n.WagerPrc) / 100)
	return n.bonus.MoneyAvailable, real_amount, bonus_amount, nil
}

// Save сохраняет бонус в базу данных.
func (n *SmartBonusHandler) Save() error {
	err := container.GetUserBonusRepository().Update(n.bonus)
	if err != nil {
		logger.Get().Error("Save bonus", "err", err.Error())
	}
	return err
}

// AddCents добавляет сумму в центах к балансу бонуса.
func (n *SmartBonusHandler) AddCents(cents int64) (balance int64, real_amount int64, bonus_amount int64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	amount := float64(cents / 100)
	b, rt, bt := n.addMoney(amount)
	return int64(b * 100), int64(rt * 100), int64(bt * 100)
}

// AddMoney добавляет сумму к балансу бонуса.
func (n *SmartBonusHandler) AddMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	return n.addMoney(amount)
}

func (n *SmartBonusHandler) addMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64) {
	k := 0.5 // half bonus half real money
	bonus_amount = float64(int(amount*100*k)) / 100
	real_amount = amount - float64(bonus_amount)

	if n.bonus.RealMoneySpent == 0 {
		bonus_amount = amount
		real_amount = 0
	}

	n.bonus.MoneyAvailable += bonus_amount
	n.wallet.AddMoney(real_amount)

	n.bonus.TotalWin += amount * (float64(n.WagerPrc) / 100)
	return n.bonus.MoneyAvailable, real_amount, bonus_amount
}
