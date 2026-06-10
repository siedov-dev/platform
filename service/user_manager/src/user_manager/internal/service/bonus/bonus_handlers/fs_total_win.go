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

var _ stypes.BonusHandler = &FsTotalWinBonusHanlder{}

// FsTotalWinBonusHanlder структура для обработки бонуса TotalWin.
type FsTotalWinBonusHanlder struct {
	bonus    *models.UserBonus // бонус
	WagerPrc int16             // процент отыгрыша ставки
	mut      sync.RWMutex      // мьютекс для безопасной работы с бонусом
}

// NewFsTotalWinBonusHanlder создает новый экземпляр FsTotalWinBonusHanlder.
// Принимает указатель на UserBonus и процент отыгрыша ставки.
// Возвращает экземпляр FsTotalWinBonusHanlder.
func NewFsTotalWinBonusHanlder(b *models.UserBonus, wp int16) stypes.BonusHandler {
	return &FsTotalWinBonusHanlder{bonus: b, WagerPrc: wp}
}

// AddTotalBet добавляет сумму ставки к общей сумме ставок.
func (n *FsTotalWinBonusHanlder) AddTotalBet(amount float64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	n.bonus.TotalBet += amount
}

// AddTotalWin добавляет сумму выигрыша к общей сумме выигрышей.
func (n *FsTotalWinBonusHanlder) AddTotalWin(amount float64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	n.bonus.TotalWin += amount
}

// GetTotalBet возвращает общую сумму ставок.
func (n *FsTotalWinBonusHanlder) GetTotalBet() float64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return n.bonus.TotalBet
}

// GetTotalWin возвращает общую сумму выигрышей.
func (n *FsTotalWinBonusHanlder) GetTotalWin() float64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return n.bonus.TotalWin
}

// GetBalance возвращает текущий баланс бонуса.
func (n *FsTotalWinBonusHanlder) GetBalance() float64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return n.bonus.MoneyAvailable
}

// GetCents возвращает текущий баланс бонуса в центах.
func (n *FsTotalWinBonusHanlder) GetCents() int64 {
	n.mut.RLock()
	defer n.mut.RUnlock()
	return int64(n.bonus.MoneyAvailable * 100)
}

// GetCurrencyName возвращает название валюты бонуса.
func (n *FsTotalWinBonusHanlder) GetCurrencyName() string {
	n.mut.RLock()
	defer n.mut.RUnlock()
	name, _ := currency.CurrencyID(n.bonus.Currency).Name()
	return name
}

// RemCents удаляет сумму в центах из баланса бонуса.
func (n *FsTotalWinBonusHanlder) RemCents(cents int64) (balance int64, real_amount int64, bonus_amount int64, err error) {
	n.mut.Lock()
	defer n.mut.Unlock()
	amount := float64(cents / 100)
	r, ra, ba, err := n.remMoney(amount)
	return int64(r * 100), int64(ra), int64(ba), err
}

// RemMoney удаляет сумму из баланса бонуса.
func (n *FsTotalWinBonusHanlder) RemMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64, err error) {
	n.mut.Lock()
	defer n.mut.Unlock()
	return n.remMoney(amount)
}

func (n *FsTotalWinBonusHanlder) remMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64, err error) {
	if n.bonus.MoneyAvailable < amount {
		return 0, 0, 0, errors.New("not enough money")
	}

	//process freespins
	if n.bonus.SpinsAvailable > 0 {
		n.bonus.SpinsAvailable--
		n.bonus.TotalBet += amount
		return n.bonus.MoneyAvailable, 0, 0, nil
	}

	n.bonus.MoneyAvailable -= amount
	n.bonus.TotalBet += amount * (float64(n.WagerPrc) / 100)
	return n.bonus.MoneyAvailable, 0, amount, nil
}

// Save сохраняет бонус в базу данных.
func (n *FsTotalWinBonusHanlder) Save() error {
	err := repository.GetUserBonusRepository().Update(n.bonus)
	if err != nil {
		logger.Get().Error("Save bonus", "err", err.Error())
	}
	return err
}

// AddCents добавляет сумму в центах к балансу бонуса.
func (n *FsTotalWinBonusHanlder) AddCents(cents int64) (balance int64, real_amount int64, bonus_amount int64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	amount := float64(cents / 100)
	b, rt, bt := n.addMoney(amount)
	return int64(b * 100), int64(rt * 100), int64(bt * 100)
}

// AddMoney добавляет сумму к балансу бонуса.
func (n *FsTotalWinBonusHanlder) AddMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64) {
	n.mut.Lock()
	defer n.mut.Unlock()
	return n.addMoney(amount)
}

func (n *FsTotalWinBonusHanlder) addMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64) {
	if n.bonus.SpinsAvailable > 0 {
		n.bonus.TotalWin += amount
		return n.bonus.MoneyAvailable, 0, amount
	}
	n.bonus.MoneyAvailable += amount
	n.bonus.TotalWin += amount * (float64(n.WagerPrc) / 100)
	return n.bonus.MoneyAvailable, 0, amount
}
