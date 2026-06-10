package service

import (
	"context"
	"log/slog"
	"time"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/service/container"
	"user-manager/pkg/gstructs"
)

// NewTransactionStorage создает новый экземпляр хранилища транзакций.
// Запускает горутину для периодической записи транзакций в БД каждую секунду.
// Горутина завершается при отмене контекста или получении сигнала в канал done.
func NewTransactionStorage(ctx context.Context) *TransactionStorage {
	st := TransactionStorage{}
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

// TransactionStorage представляет временное хранилище игровых транзакций
// Storage - двусвязный список для хранения транзакций в памяти
// done - канал для сигнала завершения работы
// ticker - таймер для периодической записи данных
type TransactionStorage struct {
	Storage gstructs.DLinkedList[*models.GameTransaction]
	done    chan bool
	ticker  *time.Ticker
}

// Add добавляет новую транзакцию в хранилище
func (ts *TransactionStorage) Add(tr *models.GameTransaction) {
	ts.Storage.Push(tr)
}

// Persist сохраняет накопленные транзакции в базу данных.
// Извлекает все транзакции из хранилища и очищает его
func (ts *TransactionStorage) Persist() {
	transactions := ts.Storage.PopSlice()
	if len(transactions) == 0 {
		return
	}
	logger.Get().Info("Persisting transactions")

	err := container.GetTransactionRepository().Insert(transactions...)
	if err != nil {
		logger.Get().Error("Persisting transactions", slog.String("error", err.Error()))
	}
}
