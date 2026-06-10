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

var _ stypes.BonusHandler = &SharedMoneyBonusHandler{}

// SharedMoneyBonusHandler обработчик бонуса.
// Обрабатывает бонусы, которые имеют общий баланс с реальными деньгами.
type SharedMoneyBonusHandler struct {
	bonus    *models.UserBonus
	WagerPrc int16
	mut      sync.RWMutex
	wallet   stypes.RealMoneyWallet
}

// NewSharedMoneyBonusHandler создает новый обработчик бонуса.
// Принимает указатель на UserBonus, процент отыгрыша ставки и реальный денежный кошелек.
// Возвращает экземпляр SharedMoneyBonusHandler.
func NewSharedMoneyBonusHandler(b *models.UserBonus, wp int16, w stypes.RealMoneyWallet) stypes.BonusHandler {
	return &SharedMoneyBonusHandler{bonus: b, WagerPrc: wp, wallet: w}
}

// AddTotalBet добавляет сумму ставки к общей сумме ставок.
func (n *SharedMoneyBonusHandler) AddTotalBet(amount float64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	n.bonus.TotalBet += amount
}

// AddTotalWin добавляет сумму выигрыша к общей сумме выигрышей.
func (n *SharedMoneyBonusHandler) AddTotalWin(amount float64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	n.bonus.TotalWin += amount
}

// GetTotalBet возвращает общую сумму ставок.
func (n *SharedMoneyBonusHandler) GetTotalBet() float64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return n.bonus.TotalBet
}

// GetTotalWin возвращает общую сумму выигрышей.
func (n *SharedMoneyBonusHandler) GetTotalWin() float64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return n.bonus.TotalWin
}

// GetBalance возвращает текущий баланс бонуса.
func (n *SharedMoneyBonusHandler) GetBalance() float64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return n.getBalance()
}

// GetCents возвращает текущий баланс бонуса в центах.
func (n *SharedMoneyBonusHandler) GetCents() int64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return int64(n.getBalance() * 100)
}

func (n *SharedMoneyBonusHandler) getBalance() float64 {
	return n.bonus.MoneyAvailable + n.wallet.GetBalance()
}

// GetCurrencyName возвращает название валюты бонуса.
func (n *SharedMoneyBonusHandler) GetCurrencyName() string {
	n.mut.RLock()
	defer n.mut.RUnlock()
	name, _ := currencyid.CurrencyID(n.bonus.Currency).Name()
	return name
}

// RemCents удаляет сумму в центах из баланса бонуса.
func (n *SharedMoneyBonusHandler) RemCents(cents int64) (balance int64, real_amount int64, bonus_amount int64, err error) {
	n.mut.Lock()
	defer n.mut.Unlock()
	amount := float64(cents / 100)
	r, ra, ba, err := n.remMoney(amount)
	return int64(r * 100), int64(ra), int64(ba), err
}

// RemMoney удаляет сумму из баланса бонуса.
func (n *SharedMoneyBonusHandler) RemMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64, err error) {
	n.mut.Lock()
	defer n.mut.Unlock()
	return n.remMoney(amount)
}

func (n *SharedMoneyBonusHandler) remMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64, err error) {
	// если бонус имеет фриспины
	if n.bonus.SpinsAvailable > 0 {
		n.bonus.SpinsAvailable--
		return n.bonus.MoneyAvailable, 0, 0, nil
	}

	// сначала используем реальные деньги
	real_amount = amount

	// если нет достаточно реальных денег, используем баланс бонуса
	if n.wallet.GetBalance() < real_amount {
		real_amount = n.wallet.GetBalance()
		bonus_amount = amount - real_amount
	}

	// если нет достаточно денег, возвращаем ошибку
	if n.getBalance() < amount {
		logger.Get().Error("Not enough money", "balance", n.getBalance(), "amount", amount)
		return n.getBalance(), 0, 0, errors.New("not enough money")
	}

	// снимаем реальные деньги из кошелька
	_, err = n.wallet.RemMoney(real_amount)
	if err != nil {
		return 0, 0, 0, err
	}

	// если не достаточно денег, возвращаем ошибку
	if n.bonus.MoneyAvailable < bonus_amount {
		logger.Get().Error("Not enough bonus money", "balance", n.getBalance(), "amount", amount)
		return n.getBalance(), 0, 0, errors.New("not enough money")
	}

	// снимаем деньги из баланса бонуса
	n.bonus.MoneyAvailable -= bonus_amount

	// если есть заблокированные деньги, снимаем их
	if n.wallet.GetLockedMoney() > 0 {
		n.wallet.RemLockedMoney(real_amount * (float64(n.WagerPrc) / 100))
	}

	// добавляем сумму ставки к общей сумме ставок
	n.bonus.TotalBet += amount * (float64(n.WagerPrc) / 100)

	// возвращаем сумму, которая осталась
	return n.bonus.MoneyAvailable, real_amount, bonus_amount, nil
}

// Save сохраняет бонус в базу данных.
func (n *SharedMoneyBonusHandler) Save() error {
	err := container.GetUserBonusRepository().Update(n.bonus)
	if err != nil {
		logger.Get().Error("Save bonus", "err", err.Error())
	}
	return err
}

// AddCents добавляет сумму в центах к балансу бонуса.
func (n *SharedMoneyBonusHandler) AddCents(cents int64) (balance int64, real_amount int64, bonus_amount int64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	amount := float64(cents / 100)
	b, rt, bt := n.addMoney(amount)
	return int64(b * 100), int64(rt * 100), int64(bt * 100)
}

// AddMoney добавляет сумму к балансу бонуса.
func (n *SharedMoneyBonusHandler) AddMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	return n.addMoney(amount)
}

func (n *SharedMoneyBonusHandler) addMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64) {

	n.bonus.MoneyAvailable += amount
	n.bonus.TotalWin += amount * (float64(n.WagerPrc) / 100)
	return n.bonus.MoneyAvailable, 0, amount
}
