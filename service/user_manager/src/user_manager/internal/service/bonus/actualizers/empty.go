package actualizers

import (
	"user-manager/internal/stypes"
)

var _ stypes.BonusActualizer = &EmptyActualizer{}

// NewEmptyActualizer создает новый экземпляр EmptyActualizer.
// Используется для бонусов которые не требуют актуализации.
func NewEmptyActualizer() stypes.BonusActualizer {
	return &EmptyActualizer{}
}

// EmptyActualizer реализует интерфейс BonusActualizer.
type EmptyActualizer struct {
}

// GetStatus возвращает статус бонуса для отображения.
func (a *EmptyActualizer) GetStatus() stypes.BonusStatusPad {
	s := stypes.BonusStatusPad{
		BonusStatus: 0,
		ShowPad:     false,
	}

	return s
}

// GetProgressHint возвращает строку с прогрессом бонуса для отображения.
func (a *EmptyActualizer) GetProgressHint() string {
	return ""
}

// Actualize актуализирует состояние бонуса и возвращает сумму выигрыша.
func (a *EmptyActualizer) Actualize() (win float64) {
	return
}
