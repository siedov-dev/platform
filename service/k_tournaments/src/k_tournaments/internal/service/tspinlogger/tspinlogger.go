package tspinlogger

import (
	"context"
	"ktournaments/internal/db/models"
	"ktournaments/internal/logger"
	"ktournaments/internal/service/container"
	"ktournaments/pkg/gstructs"
	"log/slog"
	"time"
)

// SpinLogger - интерфейс для логирования спинов турнира
var _ container.SpinLogger = (*TSpinLogger)(nil)

// NewTSpinLogger - создает новый логгер спинов турнира
// Запускает горутину, которая будет сохранять логи в базу данных каждые 1 секунду
func NewTSpinLogger(ctx context.Context) *TSpinLogger {
	st := TSpinLogger{}
	st.done = make(chan bool)
	st.ticker = time.NewTicker(1 * time.Second)

	go func(ctx context.Context) {
		for {
			select {
			case <-st.done:
				return
			case <-ctx.Done():
				st.ticker.Stop()
				st.Persist()
				close(st.done)
				return
			case t := <-st.ticker.C:
				_ = t
				//logger.Get().Info("transaction storage", slog.String("tick", t.String()))
				st.Persist()
			}
		}
	}(ctx)

	return &st
}

type TSpinLogger struct {
	Storage gstructs.DLinkedList[*models.TournamentSpinLog] // Список для хранения логов спинов турнира
	done    chan bool
	ticker  *time.Ticker
}

func (tsl *TSpinLogger) LogSpin(log models.TournamentSpinLog) error {
	// Добавляем лог в хранилище
	tsl.Storage.Push(&log)
	return nil
}

// Persist сохраняет накопленные транзакции в базу данных.
// Извлекает все транзакции из хранилища и очищает его
func (tsl *TSpinLogger) Persist() {
	transactions := tsl.Storage.PopSlice()
	if len(transactions) == 0 {
		return
	}
	logger.Get().Info("Persisting spin logs")

	err := container.GetTournamentLogsRepository().Insert(transactions...)
	if err != nil {
		logger.Get().Error("Persisting spin logs", slog.String("error", err.Error()))
	}
}
