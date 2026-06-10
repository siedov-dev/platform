package repository

import (
	"kbonus/internal/db"
	"kbonus/internal/db/models"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"log/slog"
)

type dbBonusTransactionRepository struct{}

// GetBonusTransactionRepository возвращает реализацию интерфейса BonusTransactionRepository для работы с транзакциями бонусов.
func NewBonusTransactionDbRepository() container.BonusTransactionRepository {
	return &dbBonusTransactionRepository{}
}

// Insert выполняет добавление новой записи о бонусной транзакции в базу данных и возвращает её идентификатор или ошибку.
func (r *dbBonusTransactionRepository) Insert(tr *models.BonusTransaction) (int64, error) {
	db := db.Instance()

	sql_query := `
			INSERT INTO payout_transactions (
				id, 
				user_id, 
				hall_id,
				date,
				` + "`sum`" + `, 
				status_id, 
				currency, 
				operation
			) VALUES (
				NULL, 
				:user_id, 
				:hall_id,
				:date,
				:sum, 
				:status_id, 
				:currency, 
				:operation
			);
		`

	result, err := db.NamedExec(sql_query, tr)
	if err != nil {
		logger.Get().Error("bonus transaction repository error", slog.String("err", err.Error()))
		return 0, err
	}

	// inserted ID
	insertedID, err := result.LastInsertId()
	if err != nil {
		return 0, err
	}

	return insertedID, nil
}
