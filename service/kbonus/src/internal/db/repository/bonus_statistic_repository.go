package repository

import (
	"kbonus/internal/db"
	"kbonus/internal/db/models"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"log/slog"
)

var _ container.BonusStatisticRepository = &dbBonusStatisticRepository{}

// GetBonusStatisticRepository возвращает экземпляр реализации интерфейса BonusStatisticRepository.
func NewBonusStatisticDbRepository() container.BonusStatisticRepository {
	return &dbBonusStatisticRepository{}
}

type dbBonusStatisticRepository struct {
}

// Insert добавляет новую запись в таблицу bonus_statistics и возвращает ID вставленной записи или ошибку.
func (r *dbBonusStatisticRepository) Insert(bss ...*models.BonusStatistic) error {
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

	tx := db.MustBegin()

	for _, bs := range bss {
		_, err := tx.NamedExec(sql_query, bs)
		if err != nil {
			logger.Get().Error("transaction repository error", slog.String("err", err.Error()))
		}
	}
	err := tx.Commit()

	return err
}
