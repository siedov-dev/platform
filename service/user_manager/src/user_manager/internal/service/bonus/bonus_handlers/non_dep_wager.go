package bonushandlers

import (
	"errors"
	"sync"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/repository"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"
)

var _ stypes.BonusHandler = &NonDepWagerHanlder{}

type NonDepWagerHanlder struct {
	bonus    *models.UserBonus
	WagerPrc int16
	mut      sync.RWMutex
}

// NewNonDepWagerHanlder создает новый обработчик бонуса.
// Принимает указатель на UserBonus и процент отыгрыша ставки.
// Возвращает экземпляр NonDepWagerHanlder.
func NewNonDepWagerHanlder(b *models.UserBonus, wp int16) stypes.BonusHandler {
	return &NonDepWagerHanlder{bonus: b, WagerPrc: wp}
}

// AddTotalBet добавляет сумму ставки к общей сумме ставок.
func (n *NonDepWagerHanlder) AddTotalBet(amount float64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	n.bonus.TotalBet += amount
}

// AddTotalWin добавляет сумму выигрыша к общей сумме выигрышей.
func (n *NonDepWagerHanlder) AddTotalWin(amount float64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	n.bonus.TotalWin += amount
}

// GetTotalBet возвращает общую сумму ставок.
func (n *NonDepWagerHanlder) GetTotalBet() float64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return n.bonus.TotalBet
}

// GetTotalWin возвращает общую сумму выигрышей.
func (n *NonDepWagerHanlder) GetTotalWin() float64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return n.bonus.TotalWin
}

// GetBalance возвращает текущий баланс бонуса.
func (n *NonDepWagerHanlder) GetBalance() float64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return n.bonus.MoneyAvailable
}

// GetCents возвращает текущий баланс бонуса в центах.
func (n *NonDepWagerHanlder) GetCents() int64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return int64(n.bonus.MoneyAvailable * 100)
}

// GetCurrencyName возвращает название валюты бонуса.
func (n *NonDepWagerHanlder) GetCurrencyName() string {
	n.mut.RLock()
	defer n.mut.RUnlock()
	name, _ := currency.CurrencyID(n.bonus.Currency).Name()
	return name
}

// RemCents удаляет сумму в центах из баланса бонуса.
func (n *NonDepWagerHanlder) RemCents(cents int64) (balance int64, real_amount int64, bonus_amount int64, err error) {
	n.mut.Lock()
	defer n.mut.Unlock()
	amount := float64(cents / 100)
	r, ra, ba, err := n.remMoney(amount)
	return int64(r * 100), int64(ra), int64(ba), err
}

// RemMoney удаляет сумму из баланса бонуса.
func (n *NonDepWagerHanlder) RemMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64, err error) {
	n.mut.Lock()
	defer n.mut.Unlock()
	return n.remMoney(amount)
}

func (n *NonDepWagerHanlder) remMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64, err error) {
	// если бонус имеет фриспины
	if n.bonus.SpinsAvailable > 0 {
		n.bonus.SpinsAvailable--

		if n.bonus.SpinsAvailable == 0 {
			n.bonus.SpinsAvailable = -1
			n.bonus.MoneyAvailable = n.bonus.TotalWin
			// если установлен max win koef
			if n.bonus.MultiplierToComplete > 0 {
				n.bonus.WagerToPayout = n.bonus.MoneyAvailable * n.bonus.MultiplierToComplete
			}
			n.bonus.WagerToComplete = float64(n.bonus.Multiplier) * n.bonus.MoneyAvailable
		}

		return n.bonus.MoneyAvailable, 0, 0, nil
	}

	if n.bonus.MoneyAvailable < amount {
		return 0, 0, 0, errors.New("not enough money")
	}

	n.bonus.MoneyAvailable -= amount
	n.bonus.TotalBet += amount * (float64(n.WagerPrc) / 100)
	return n.bonus.MoneyAvailable, 0, amount, nil
}

// Save сохраняет бонус в базу данных.
func (n *NonDepWagerHanlder) Save() error {
	err := repository.GetUserBonusRepository().Update(n.bonus)
	if err != nil {
		logger.Get().Error("Save bonus", "err", err.Error())
	}
	return err
}

// AddCents добавляет сумму в центах к балансу бонуса.
func (n *NonDepWagerHanlder) AddCents(cents int64) (balance int64, real_amount int64, bonus_amount int64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	amount := float64(cents / 100)
	b, rt, bt := n.addMoney(amount)
	return int64(b * 100), int64(rt * 100), int64(bt * 100)
}

// AddMoney добавляет сумму к балансу бонуса.
func (n *NonDepWagerHanlder) AddMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	return n.addMoney(amount)
}

func (n *NonDepWagerHanlder) addMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64) {
	if n.bonus.SpinsAvailable > 0 {
		n.bonus.TotalWin += amount
		return n.bonus.MoneyAvailable, 0, amount
	}
	n.bonus.MoneyAvailable += amount
	n.bonus.TotalWin += amount * (float64(n.WagerPrc) / 100)
	return n.bonus.MoneyAvailable, 0, amount
}
