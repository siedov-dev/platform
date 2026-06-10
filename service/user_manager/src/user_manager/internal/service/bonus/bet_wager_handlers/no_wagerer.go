package betwagerhandlers

import (
	"user-manager/internal/stypes"
)

type NoBetWagerer struct {
}

// NewNoBetWagerer создает новый экземпляр NoBetWagerer.
// Возвращает экземпляр NoBetWagerer.
func NewNoBetWagerer() stypes.BonusBetWagerer {
	return &NoBetWagerer{}
}

// OnUserBet обрабатывает ставку пользователя.
// Принимает сумму ставки.
// Ничего не делаем.
func (bw *NoBetWagerer) OnUserBet(amount float64) {
}

// OnUserWin обрабатывает выигрыш пользователя.
// Принимает сумму выигрыша.
// Ничего не делаем.
func (bw *NoBetWagerer) OnUserWin(amount float64) {
}
