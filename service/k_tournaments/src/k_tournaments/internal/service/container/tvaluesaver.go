package container

import (
	"ktournaments/internal/db/models"
)

// TValueSaver - интерфейс сохранения значений турниров
type TValueSaver interface {
	// Сохранение значения
	Save(*models.TournamentValueModel) (uint64, error)
	// Сохранение всех значений и сброс буфера
	Flush()
}

var valuesaver TValueSaver

// SetValueSaver - установка сохранения значений турниров
func SetValueSaver(s TValueSaver) {
	valuesaver = s
}

// GetValueSaver - получение сохранения значений турниров
func GetValueSaver() TValueSaver {
	return valuesaver
}
