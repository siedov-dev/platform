package container

import "ktournaments/internal/db/models"

// SpinLogger - интерфейс для логирования спинов турнира
// Сохраняет информацию о спинах турнира в базу данных
type SpinLogger interface {
	// LogSpin  - сохраняет лог спина турнира
	LogSpin(models.TournamentSpinLog) error
}

var spinLogger SpinLogger

// SetSpinLogger - установка логгера спинов турнира
func SetSpinLogger(logger SpinLogger) {
	spinLogger = logger
}

// GetSpinLogger - получение логгера спинов турнира
func GetSpinLogger() SpinLogger {
	return spinLogger
}
