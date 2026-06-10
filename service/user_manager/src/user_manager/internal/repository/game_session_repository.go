package repository

import (
	"log/slog"
	"user-manager/internal/db"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/service/container"
)

type dbUserGameStastRepository struct{}

// GetGameSessionRepository возвращает реализацию интерфейса GameSessionRepository для работы с игровой статистикой пользователя.
func NewGameSessionRepository() container.GameSessionRepository {
	return dbUserGameStastRepository{}
}

// GetByID возвращает объект UserGameStats по указанному идентификатору id.
// Если запись не найдена, возвращает ошибку.
func (r dbUserGameStastRepository) GetByID(id int64) (*models.UserGameStats, error) {
	db := db.Instance()

	data := models.UserGameStats{}

	err := db.Get(&data,
		`SELECT 
			id, user_id, currency, session_id,`+
			"`system,`"+
			"`group,`"+`, 
			game_id, login_balance, logout_balance, balance_diff, 
			full_total_bet, full_total_win, real_total_bet, real_total_win, 
			total_bet, total_win, login_date, logout_date, last_active_date, 
			created_at, updated_at, outer_table, outer_id, status, bonus_id
		FROM user_game_stats
		WHERE id=?`, id)
	if err != nil {
		return nil, err
	}

	return &data, nil
}

// Insert добавляет новую запись в таблицу user_game_stats и возвращает ID вставленной записи или ошибку.
func (r dbUserGameStastRepository) Insert(sess *models.UserGameStats) (int64, error) {
	db := db.Instance()

	sql_query := `
			INSERT INTO user_game_stats (
				id, 
				user_id, 
				currency, 
				session_id, 
				` + "`system`" + `, 
				` + "`group`" + `, 
				game_id, 
				login_balance, 
				logout_balance, 
				balance_diff, 
				full_total_bet, 
				full_total_win, 
				real_total_bet, 
				real_total_win, 
				total_bet, 
				total_win, 
				login_date, 
				logout_date, 
				last_active_date, 
				created_at, 
				updated_at, 
				outer_table, 
				outer_id, 
				status,
				bonus_id
			) VALUES (
				:id, 
				:user_id, 
				:currency, 
				:session_id, 
				:system, 
				:group, 
				:game_id, 
				:login_balance, 
				:logout_balance, 
				:balance_diff, 
				:full_total_bet, 
				:full_total_win, 
				:real_total_bet, 
				:real_total_win, 
				:total_bet, 
				:total_win, 
				:login_date, 
				:logout_date, 
				:last_active_date, 
				:created_at, 
				:updated_at, 
				:outer_table, 
				:outer_id, 
				:status,
				:bonus_id
			);
		`

	result, err := db.NamedExec(sql_query, sess)
	if err != nil {
		logger.Get().Error("transaction repository error", slog.String("err", err.Error()))
		return 0, err
	}

	// inserted ID
	insertedID, err := result.LastInsertId()
	if err != nil {
		return 0, err
	}

	sess.ID = insertedID
	return insertedID, nil
}

// Update обновляет запись в таблице user_game_stats на основе данных из структуры UserGameStats.
// Возвращает ошибку, если обновление не удалось.
func (r dbUserGameStastRepository) Update(sess *models.UserGameStats) error {
	db := db.Instance()

	sql_query := `
       UPDATE user_game_stats 
       SET 
           user_id = :user_id, 
           currency = :currency, 
           session_id = :session_id, 
           ` + "`system`" + ` = :system, 
           ` + "`group`" + ` = :group, 
           game_id = :game_id, 
           login_balance = :login_balance, 
           logout_balance = :logout_balance, 
           balance_diff = :balance_diff, 
           full_total_bet = :full_total_bet, 
           full_total_win = :full_total_win, 
           real_total_bet = :real_total_bet, 
           real_total_win = :real_total_win, 
           total_bet = :total_bet, 
           total_win = :total_win, 
           login_date = :login_date, 
           logout_date = :logout_date, 
           last_active_date = :last_active_date, 
           created_at = :created_at, 
           updated_at = :updated_at, 
           outer_table = :outer_table, 
           outer_id = :outer_id, 
           status = :status,
		   bonus_id = :bonus_id
       WHERE id = :id;
   `
	_, err := db.NamedExec(sql_query, sess)
	return err
}
