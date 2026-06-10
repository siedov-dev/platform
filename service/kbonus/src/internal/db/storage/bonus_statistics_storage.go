package storage

import (
	"context"
	"kbonus/internal/db/models"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/utils/gstructs"
	"log/slog"
	"time"
)

// NewDBBonusStatisticStorage создает новый экземпляр хранилища статистик бонусов.
// Запускает горутину для периодической записи статистик в БД каждую секунду.
// Горутина завершается при отмене контекста или получении сигнала в канал done.
func NewDBBonusStatisticStorage(ctx context.Context) container.BonusStatisticStorage {
	st := DBBonusStatisticStorage{}
	st.done = make(chan bool)
	st.ticker = time.NewTicker(1 * time.Second)

	go func(ctx context.Context) {
		for {
			select {
			case <-st.done:
				st.ticker.Stop()
				st.Persist()
				close(st.done)
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

// DBBonusStatisticStorage представляет временное хранилище игровых статистик
// Storage - двусвязный список для хранения статистик в памяти
// done - канал для сигнала завершения работы
// ticker - таймер для периодической записи данных
type DBBonusStatisticStorage struct {
	Storage gstructs.DLinkedList[*models.BonusStatistic]
	done    chan bool
	ticker  *time.Ticker
}

// Add добавляет новую статистику в хранилище
func (ts *DBBonusStatisticStorage) Add(tr *models.BonusStatistic) {
	ts.Storage.Push(tr)
}

// Persist сохраняет накопленные статистики в базу данных.
// Извлекает все статистики из хранилища и очищает его
func (ts *DBBonusStatisticStorage) Persist() {
	transactions := ts.Storage.PopSlice()
	if len(transactions) == 0 {
		return
	}
	logger.Get().Info("Persisting transactions")

	err := container.GetBonusStatisticRepository().Insert(transactions...)
	if err != nil {
		logger.Get().Error("Persisting transactions", slog.String("error", err.Error()))
	}
}

// Close завершает работу хранилища, останавливает таймер и закрывает канал done
func (ts *DBBonusStatisticStorage) Close() {
	ts.done <- true
}
