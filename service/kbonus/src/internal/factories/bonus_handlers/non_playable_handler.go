package bonushandlers

import (
	"kbonus/internal/db/models"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
)

var _ stypes.BonusHandler = &NonPlayableHandler{}

// NonPlayableHandler структура для обработки бонусов.
type NonPlayableHandler struct {
	bonus    *models.UserBonus
	WagerPrc int16
}

// NewNonPlayableHandler создает новый экземпляр NonPlayableHandler.
// Принимает указатель на UserBonus и процент отыгрыша ставки.
// Возвращает экземпляр NonPlayableHandler.
func NewNonPlayableHandler(b *models.UserBonus, wp int16) stypes.BonusHandler {
	return &NonPlayableHandler{bonus: b, WagerPrc: wp}
}

// AddTotalBet добавляет сумму ставки к общей сумме ставок.
func (n *NonPlayableHandler) AddTotalBet(amount float64) {
}

// AddTotalWin добавляет сумму выигрыша к общей сумме выигрышей.
func (n *NonPlayableHandler) AddTotalWin(amount float64) {
}

// GetTotalBet возвращает общую сумму ставок.
func (n *NonPlayableHandler) GetTotalBet() float64 {
	return 0
}

// GetTotalWin возвращает общую сумму выигрышей.
func (n *NonPlayableHandler) GetTotalWin() float64 {
	return 0
}

// GetBalance возвращает текущий баланс бонуса.
func (n *NonPlayableHandler) GetBalance() float64 {
	return n.bonus.MoneyAvailable
}

// GetCents возвращает текущий баланс бонуса в центах.
func (n *NonPlayableHandler) GetCents() int64 {
	return int64(n.bonus.MoneyAvailable * 100)
}

// GetCurrencyName возвращает название валюты бонуса.
func (n *NonPlayableHandler) GetCurrencyName() string {
	name, _ := currencyid.CurrencyID(n.bonus.Currency).Name()
	return name
}

// RemCents удаляет сумму в центах из баланса бонуса.
func (n *NonPlayableHandler) RemCents(cents int64) (balance int64, real_amount int64, bonus_amount int64, err error) {
	return n.GetCents(), 0, 0, nil
}

// RemMoney удаляет сумму из баланса бонуса.
func (n *NonPlayableHandler) RemMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64, err error) {
	return n.GetBalance(), 0, 0, nil
}

// Save сохраняет бонус в базу данных.
func (n *NonPlayableHandler) Save() error {
	err := container.GetUserBonusRepository().Update(n.bonus)
	if err != nil {
		logger.Get().Error("Save bonus", "err", err.Error())
	}
	return err
}

// AddCents добавляет сумму в центах к балансу бонуса.
func (n *NonPlayableHandler) AddCents(cents int64) (balance int64, real_amount int64, bonus_amount int64) {
	return n.GetCents(), 0, 0
}

// AddMoney добавляет сумму к балансу бонуса.
func (n *NonPlayableHandler) AddMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64) {
	return n.GetBalance(), 0, 0
}
