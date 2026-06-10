package repository

import (
	"log/slog"
	"user-manager/internal/db"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/service/container"
)

var _ container.BonusStatisticRepository = &dbBonusStatisticRepository{}

// GetBonusStatisticRepository возвращает экземпляр реализации интерфейса BonusStatisticRepository.
func NewBonusStatisticRepository() container.BonusStatisticRepository {
	return &dbBonusStatisticRepository{}
}

type dbBonusStatisticRepository struct {
}

// Insert добавляет новую запись в таблицу bonus_statistics и возвращает ID вставленной записи или ошибку.
func (r *dbBonusStatisticRepository) Insert(bs *models.BonusStatistic) (int64, error) {
	db := db.Instance()

	sql_query := `
			INSERT INTO bonus_statistics (
				id,
				bonus_id,
				pattern_id,
				user_id,
				action,
				amount,
				currency,
				status_before,
				status,
				created_at,
				updated_at,
				data,
				promocode_id,
				bonus_type
			) VALUES (
				NULL, 
				:bonus_id,
				:pattern_id,
				:user_id,
				:action,
				:amount,
				:currency,
				:status_before,
				:status,
				:created_at,
				:updated_at,
				:data,
				:promocode_id,
				:bonus_type
			);
		`

	result, err := db.NamedExec(sql_query, bs)
	if err != nil {
		logger.Get().Error("bonus statistc repository error", slog.String("err", err.Error()))
		return 0, err
	}

	// inserted ID
	insertedID, err := result.LastInsertId()
	if err != nil {
		return 0, err
	}

	return insertedID, nil
}
