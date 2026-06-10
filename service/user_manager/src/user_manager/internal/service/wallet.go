package service

import (
	"errors"
	"log/slog"
	"strconv"
	"sync"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/service/container"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"
)

var _ stypes.GameWallet = &Wallet{} //interface test

// Wallet Структура кошелька пользователя, содержащая баланс и другие денежные показатели
type Wallet struct {
	DbWallet models.UserWallet   // Модель кошелька в БД
	Currency currency.CurrencyID // ID валюты кошелька
	Cents    int64               // Баланс в центах * 100
	mut      sync.RWMutex        // Мьютекс для синхронизации доступа
}

// LoadFromModel Загружает данные кошелька из модели БД
func (w *Wallet) LoadFromModel(db_wallet *models.UserWallet) {
	if w == nil {
		w = &Wallet{*db_wallet, 1, 0, sync.RWMutex{}}
	}
	w.DbWallet = *db_wallet
	w.Currency = currency.CurrencyID(db_wallet.CurrencyID)
	w.Cents = int64(db_wallet.Amount * 10000)
}

func (w *Wallet) GetID() int64 {
	w.mut.RLock()
	defer w.mut.RUnlock()
	return w.DbWallet.ID
}

func (w *Wallet) GetCurrencyName() string {
	name, err := w.Currency.Name()
	if err != nil {
		logger.Get().Error("wrong currency id", slog.String("currency id", strconv.Itoa(int(w.DbWallet.CurrencyID))))
		panic("wrong currency id")
	}
	return name
}

// GetBalance Получает баланс кошелька в виде числа с плавающей точкой
func (w *Wallet) GetBalance() float64 {
	w.mut.RLock()
	defer w.mut.RUnlock()
	return float64(w.Cents) / 10000
}

// GetCents Получает баланс кошелька в центах
func (w *Wallet) GetCents() int64 {
	w.mut.RLock()
	defer w.mut.RUnlock()
	return w.Cents / 100
}

// AddMoney Добавляет деньги (как число с плавающей точкой) в кошелек
func (w *Wallet) AddMoney(amount float64) float64 {
	w.mut.Lock()
	defer w.mut.Unlock()
	w.Cents += int64(amount * 10000)
	return float64(w.Cents) / 10000
}

// AddCents Добавляет центы в кошелек
func (w *Wallet) AddCents(amount int64) int64 {
	w.mut.Lock()
	defer w.mut.Unlock()
	w.Cents += (amount * 100)
	return w.Cents / 100
}

// RemMoney Снимает деньги (как число с плавающей точкой) с кошелька
func (w *Wallet) RemMoney(amount float64) (float64, error) {
	w.mut.Lock()
	defer w.mut.Unlock()
	if w.Cents < int64(amount*10000) {
		return float64(w.Cents) / 10000, errors.New("not enough money")
	}
	w.Cents -= int64(amount * 10000)
	return float64(w.Cents) / 10000, nil
}

// RemCents Снимает центы с кошелька
func (w *Wallet) RemCents(amount int64) (int64, error) {
	w.mut.Lock()
	defer w.mut.Unlock()
	if w.Cents < (amount * 100) {
		return w.Cents, errors.New("not enough money")
	}
	w.Cents -= (amount * 100)
	return w.Cents / 100, nil
}

// Save Сохраняет кошелек в БД
func (w *Wallet) Save() error {
	w.mut.Lock()
	defer w.mut.Unlock()
	w.DbWallet.Amount = float64(w.Cents) / 10000
	if w.DbWallet.ID == 0 {
		return errors.New("no db wallet loaded")
	}
	return container.GetWalletsRepository().Update(&w.DbWallet)
}

// GetLockedMoney Получает заблокированные средства
func (w *Wallet) GetLockedMoney() float64 {
	w.mut.RLock()
	defer w.mut.RUnlock()
	return w.DbWallet.LockedMoney
}

// AddLockedMoney Добавляет заблокированные средства в кошелек
func (w *Wallet) AddLockedMoney(amount float64) float64 {
	w.mut.Lock()
	defer w.mut.Unlock()
	w.DbWallet.LockedMoney += amount
	return w.DbWallet.LockedMoney
}

// RemLockedMoney Снимает заблокированные средства с кошелька
func (w *Wallet) RemLockedMoney(amount float64) float64 {
	w.mut.Lock()
	defer w.mut.Unlock()
	w.DbWallet.LockedMoney -= amount
	if w.DbWallet.LockedMoney < 0 {
		w.DbWallet.LockedMoney = 0
	}
	return w.DbWallet.LockedMoney
}

// SetLockedMoney Устанавливает значение заблокированных средств
func (w *Wallet) SetLockedMoney(amount float64) {
	w.mut.Lock()
	defer w.mut.Unlock()
	w.DbWallet.LockedMoney = amount
}

// GetBonusMoney Получает бонусные средства
func (w *Wallet) GetBonusMoney() float64 {
	w.mut.RLock()
	defer w.mut.RUnlock()
	return w.DbWallet.BonusMoney
}

// AddBonusMoney Добавляет бонусные средства в кошелек
func (w *Wallet) AddBonusMoney(amount float64) float64 {
	w.mut.Lock()
	defer w.mut.Unlock()
	w.DbWallet.BonusMoney += amount
	return w.DbWallet.BonusMoney
}

// RemBonusMoney Снимает бонусные средства с кошелька
// Баланс может быть отрицательный
func (w *Wallet) RemBonusMoney(amount float64) float64 {
	w.mut.Lock()
	defer w.mut.Unlock()
	w.DbWallet.BonusMoney -= amount
	return w.DbWallet.BonusMoney
}

// SetBonusMoney устанавливает сумму бонусных средств в кошельке.
func (w *Wallet) SetBonusMoney(amount float64) {
	w.mut.Lock()
	defer w.mut.Unlock()
	w.DbWallet.BonusMoney = amount
}

// TransferBonusMoney переводит средства с накопительного на реферальный счет
func (w *Wallet) TransferBonusMoney() float64 {
	w.mut.Lock()
	amount := w.DbWallet.BonusMoney
	w.DbWallet.BonusMoney = 0
	w.mut.Unlock()
	if amount > 0 {
		w.AddReferralMoney(amount)
	}
	return amount
}

// GetReferralMoney Получает реферальные средства
func (w *Wallet) GetReferralMoney() float64 {
	w.mut.RLock()
	defer w.mut.RUnlock()
	return w.DbWallet.ReferralMoney
}

// AddReferralMoney Добавляет реферальные средства в кошелек
func (w *Wallet) AddReferralMoney(amount float64) float64 {
	w.mut.Lock()
	defer w.mut.Unlock()
	w.DbWallet.ReferralMoney += amount
	return w.DbWallet.ReferralMoney
}

// RemReferralMoney Снимает реферальные средства с кошелька
func (w *Wallet) RemReferralMoney(amount float64) float64 {
	w.mut.Lock()
	defer w.mut.Unlock()
	w.DbWallet.ReferralMoney -= amount
	if w.DbWallet.ReferralMoney < 0 {
		w.DbWallet.ReferralMoney = 0
	}
	return w.DbWallet.ReferralMoney
}

// SetReferralMoney Устанавливает значение реферальных средств
func (w *Wallet) SetReferralMoney(amount float64) {
	w.mut.Lock()
	defer w.mut.Unlock()
	w.DbWallet.ReferralMoney = amount
}

// TransferReferralMoney Переводит реферальные средства на основной баланс
func (w *Wallet) TransferReferralMoney() float64 {
	w.mut.Lock()
	amount := w.DbWallet.ReferralMoney
	w.DbWallet.ReferralMoney = 0
	w.mut.Unlock()
	if amount > 0 {
		w.AddMoney(amount)
	}
	return amount
}

// GetTotalBet Получает общую сумму ставок
func (w *Wallet) GetTotalBet() float64 {
	w.mut.RLock()
	defer w.mut.RUnlock()
	return w.DbWallet.TotalBet
}

// AddTotalBet Добавляет сумму к общей сумме ставок
func (w *Wallet) AddTotalBet(amount float64) {
	w.mut.Lock()
	defer w.mut.Unlock()
	w.DbWallet.TotalBet += amount
}

// SetTotalBet Устанавливает общую сумму ставок
func (w *Wallet) SetTotalBet(amount float64) {
	w.mut.RLock()
	defer w.mut.RUnlock()
	w.DbWallet.TotalBet = amount
}

// GetTotalWin Получает общую сумму выигрышей
func (w *Wallet) GetTotalWin() float64 {
	w.mut.RLock()
	defer w.mut.RUnlock()
	return w.DbWallet.TotalWin
}

// SetTotalWin Устанавливает общую сумму выигрышей
func (w *Wallet) SetTotalWin(amount float64) {
	w.mut.RLock()
	defer w.mut.RUnlock()
	w.DbWallet.TotalWin = amount
}

// AddTotalWin Добавляет сумму к общей сумме выигрышей
func (w *Wallet) AddTotalWin(amount float64) {
	w.mut.Lock()
	defer w.mut.Unlock()
	w.DbWallet.TotalWin += amount
}

// SetActive Устанавливает статус активности кошелька
func (w *Wallet) SetActive(active bool) {
	w.mut.Lock()
	defer w.mut.Unlock()
	w.DbWallet.Active = active
}

// SetBonusId Устанавливает ID бонуса
func (w *Wallet) SetBonusId(id int64) {
	w.mut.Lock()
	defer w.mut.Unlock()
	w.DbWallet.BonusID.Int64 = id
	w.DbWallet.BonusID.Valid = true
}

// GetBonusId Получает ID бонуса
func (w *Wallet) GetBonusId() int64 {
	w.mut.Lock()
	defer w.mut.Unlock()
	return w.DbWallet.BonusID.Int64
}
