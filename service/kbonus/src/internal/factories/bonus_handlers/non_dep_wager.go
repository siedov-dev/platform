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

var _ stypes.BonusHandler = &NonDepWagerHandler{}

type NonDepWagerHandler struct {
	bonus    *models.UserBonus
	WagerPrc int16
	mut      sync.RWMutex
}

// NewNonDepWagerHandler создает новый обработчик бонуса.
// Принимает указатель на UserBonus и процент отыгрыша ставки.
// Возвращает экземпляр NonDepWagerHandler.
func NewNonDepWagerHandler(b *models.UserBonus, wp int16) stypes.BonusHandler {
	return &NonDepWagerHandler{bonus: b, WagerPrc: wp}
}

// AddTotalBet добавляет сумму ставки к общей сумме ставок.
func (n *NonDepWagerHandler) AddTotalBet(amount float64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	n.bonus.TotalBet += amount
}

// AddTotalWin добавляет сумму выигрыша к общей сумме выигрышей.
func (n *NonDepWagerHandler) AddTotalWin(amount float64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	n.bonus.TotalWin += amount
}

// GetTotalBet возвращает общую сумму ставок.
func (n *NonDepWagerHandler) GetTotalBet() float64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return n.bonus.TotalBet
}

// GetTotalWin возвращает общую сумму выигрышей.
func (n *NonDepWagerHandler) GetTotalWin() float64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return n.bonus.TotalWin
}

// GetBalance возвращает текущий баланс бонуса.
func (n *NonDepWagerHandler) GetBalance() float64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return n.bonus.MoneyAvailable
}

// GetCents возвращает текущий баланс бонуса в центах.
func (n *NonDepWagerHandler) GetCents() int64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return int64(n.bonus.MoneyAvailable * 100)
}

// GetCurrencyName возвращает название валюты бонуса.
func (n *NonDepWagerHandler) GetCurrencyName() string {
	n.mut.RLock()
	defer n.mut.RUnlock()
	name, _ := currencyid.CurrencyID(n.bonus.Currency).Name()
	return name
}

// RemCents удаляет сумму в центах из баланса бонуса.
func (n *NonDepWagerHandler) RemCents(cents int64) (balance int64, real_amount int64, bonus_amount int64, err error) {
	n.mut.Lock()
	defer n.mut.Unlock()
	amount := float64(cents / 100)
	r, ra, ba, err := n.remMoney(amount)
	return int64(r * 100), int64(ra), int64(ba), err
}

// RemMoney удаляет сумму из баланса бонуса.
func (n *NonDepWagerHandler) RemMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64, err error) {
	n.mut.Lock()
	defer n.mut.Unlock()
	return n.remMoney(amount)
}

func (n *NonDepWagerHandler) remMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64, err error) {
	// если бонус имеет фриспины
	if n.bonus.SpinsAvailable > 0 {
		n.bonus.SpinsAvailable--

		if n.bonus.SpinsAvailable == 0 {
			n.bonus.SpinsAvailable = -1
			n.bonus.MoneyAvailable = n.bonus.TotalWin
			// если установлен max win coefficient
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
func (n *NonDepWagerHandler) Save() error {
	err := container.GetUserBonusRepository().Update(n.bonus)
	if err != nil {
		logger.Get().Error("Save bonus", "err", err.Error())
	}
	return err
}

// AddCents добавляет сумму в центах к балансу бонуса.
func (n *NonDepWagerHandler) AddCents(cents int64) (balance int64, real_amount int64, bonus_amount int64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	amount := float64(cents / 100)
	b, rt, bt := n.addMoney(amount)
	return int64(b * 100), int64(rt * 100), int64(bt * 100)
}

// AddMoney добавляет сумму к балансу бонуса.
func (n *NonDepWagerHandler) AddMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	return n.addMoney(amount)
}

func (n *NonDepWagerHandler) addMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64) {
	if n.bonus.SpinsAvailable > 0 {
		n.bonus.TotalWin += amount
		return n.bonus.MoneyAvailable, 0, amount
	}
	n.bonus.MoneyAvailable += amount
	n.bonus.TotalWin += amount * (float64(n.WagerPrc) / 100)
	return n.bonus.MoneyAvailable, 0, amount
}
