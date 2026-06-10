package repository

import (
	"log/slog"
	"user-manager/internal/db"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/service/container"
)

func NewTransactionRepository() container.GameTransactionRepository {
	return &dbTransactionRepository{}
}

var _ container.GameTransactionRepository = &dbTransactionRepository{}

type dbTransactionRepository struct{}

// GetByID возвращает объект GameTransaction по его уникальному идентификатору.
// В случае ошибки поиска или выполнения SQL-запроса возвращает nil и ошибку.
func (r *dbTransactionRepository) GetByID(id int64) (transaction *models.GameTransaction, err error) {
	db := db.Instance()
	transaction = &models.GameTransaction{}
	err = db.Get(transaction, `
		SELECT id, gen_id, `+"`system`"+`, user_id, bonus, action, amount, currency, game_id,
				session_id, transaction_id, type, date, refunded, bet_transaction_id, last_bet_value, last_bet_id
		FROM game_transactions
		WHERE id=?
	`, id)
	if err != nil {
		logger.Get().Error("DB: Can't find user", slog.Int64("id", id))
		return nil, err
	}
	return transaction, nil
}

// GetByTrID выполняет поиск записи GameTransaction в базе данных по идентификатору транзакции transaction_id.
// Возвращает найденную запись и возможную ошибку при выполнении запроса или если запись не найдена.
func (r *dbTransactionRepository) GetByTrID(transaction_id int64) (transaction *models.GameTransaction, err error) {
	db := db.Instance()
	transaction = &models.GameTransaction{}
	err = db.Get(transaction, `
		SELECT id, gen_id, `+"`system`"+`, user_id, bonus, action, amount, currency, game_id,
				session_id, transaction_id, type, date, refunded, bet_transaction_id, last_bet_value, last_bet_id
		FROM game_transactions
		WHERE transaction_id=?
	`, transaction_id)
	if err != nil {
		logger.Get().Error("DB: Can't find user", slog.Int64("id", transaction_id))
		return nil, err
	}
	return transaction, nil
}

// Insert добавляет одну или несколько записей GameTransaction в базу данных. Возвращает ошибку при неудачной операции.
func (r *dbTransactionRepository) Insert(trs ...*models.GameTransaction) (err error) {
	db := db.Instance()

	sql_query := `
		INSERT INTO game_transactions (id, gen_id, ` + "`system`" + `, user_id, bonus, action, amount, currency, game_id,
		session_id, transaction_id, type, date, refunded, bet_transaction_id, last_bet_value, last_bet_id)
		VALUES (NULL,:gen_id,:system,:user_id,:bonus,:action,:amount,:currency,:game_id,
		:session_id,:transaction_id,:type,:date,:refunded,:bet_transaction_id,:last_bet_value,:last_bet_id)
	`

	tx := db.MustBegin()

	for _, tr := range trs {
		_, err := tx.NamedExec(sql_query, tr)
		if err != nil {
			logger.Get().Error("transaction repository error", slog.String("err", err.Error()))
		}
	}

	err = tx.Commit()

	return err
}
