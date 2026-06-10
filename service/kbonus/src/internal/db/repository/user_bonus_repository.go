package repository

import (
	"kbonus/internal/db"
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"strconv"
	"strings"
)

func NewUserBonusDbRepository() container.UserBonusRepository {
	return dbUserBonusRepository{}
}

type dbUserBonusRepository struct {
}

// dbUserBonusRepository реализует интерфейс UserBonusRepository для работы с бонусами пользователей в базе данных.
// GetByID извлекает запись UserBonus из базы данных по заданному идентификатору id.
// Возвращает указатель на структуру UserBonus и ошибку, если операция завершилась неудачно.
func (ub dbUserBonusRepository) GetByID(id stypes.BonusID) (*models.UserBonus, error) {
	db := db.Instance()

	data := models.UserBonus{}
	err := db.Get(&data,
		`SELECT id, user_id, type, money_available, money_paid_out, total_bet, total_bet_for_payout, 
		total_win, wager_to_complete, wager_to_payout, multiplier_to_complete, multiplier_to_payout, 
		real_money_spent, status, spins_total, spins_available, created_at, updated_at, active_to, total_bet_after, 
		completed_at, money_to_activate, multiplier, hours_active, max_win, tries, hall_id, currency, 
		pattern_id, pattern_status, bonus_prizes, given_prizes, enable_after, games_limited, allowed_games, 
		json_params, creator_type, creator_id, game_provider, game_id, title, img_url, marker, wager_games_segment_id
		FROM users_bonuses
		WHERE id=?`, id)
	if err != nil {
		return nil, err
	}

	return &data, nil
}

// GetByUserId извлекает все бонусы пользователя по его идентификатору.
// Возвращает срез структур UserBonus и ошибку, если операция завершилась неудачно.
func (ub dbUserBonusRepository) GetByUserId(id stypes.UserID) ([]models.UserBonus, error) {
	db := db.Instance()

	data := []models.UserBonus{}
	err := db.Select(&data,
		`SELECT id, user_id, type, money_available, money_paid_out, total_bet, total_bet_for_payout, 
		total_win, wager_to_complete, wager_to_payout, multiplier_to_complete, multiplier_to_payout, 
		real_money_spent, status, spins_total, spins_available, created_at, updated_at, active_to, total_bet_after, 
		completed_at, money_to_activate, multiplier, hours_active, max_win, tries, hall_id, currency, 
		pattern_id, pattern_status, bonus_prizes, given_prizes, enable_after, games_limited, allowed_games, 
		json_params,
		creator_type, creator_id, game_provider, game_id, title, img_url,
		marker, wager_games_segment_id
		FROM users_bonuses
		WHERE user_id=?`, id)
	if err != nil {
		return nil, err
	}
	return data, nil
}

func (ub dbUserBonusRepository) GetActiveByUserId(id stypes.UserID) ([]models.UserBonus, error) {
	db := db.Instance()

	completedStatuses := []string{
		strconv.FormatInt(stypes.STATUS_CANCELED, 10),
		strconv.FormatInt(stypes.STATUS_DONE, 10),
		strconv.FormatInt(stypes.STATUS_EXPIRED, 10),
		strconv.FormatInt(stypes.STATUS_WIN, 10),
	}
	st_str := strings.Join(completedStatuses, ",")
	// Извлекаем только активные бонусы пользователя, исключая завершенные
	// Используем оператор NOT IN для фильтрации по статусам
	// Возвращаем только активные бонусы
	data := []models.UserBonus{}
	err := db.Select(&data,
		`SELECT id, user_id, type, money_available, money_paid_out, total_bet, total_bet_for_payout, 
		total_win, wager_to_complete, wager_to_payout, multiplier_to_complete, multiplier_to_payout, 
		real_money_spent, status, spins_total, spins_available, created_at, updated_at, active_to, total_bet_after, 
		completed_at, money_to_activate, multiplier, hours_active, max_win, tries, hall_id, currency, 
		pattern_id, pattern_status, bonus_prizes, given_prizes, enable_after, games_limited, allowed_games, 
		json_params, creator_type, creator_id, game_provider, game_id, title, img_url, marker, wager_games_segment_id
		FROM users_bonuses
		WHERE user_id=?
		AND status not in (`+st_str+`)
		`, id)
	if err != nil {
		return nil, err
	}
	return data, nil
}

// GetCompletedByUserId извлекает все завершенные бонусы пользователя по его идентификатору.
func (ub dbUserBonusRepository) GetCompletedByUserId(id stypes.UserID, limit int) ([]models.UserBonus, error) {
	db := db.Instance()

	completedStatuses := []string{
		strconv.FormatInt(stypes.STATUS_CANCELED, 10),
		strconv.FormatInt(stypes.STATUS_DONE, 10),
		strconv.FormatInt(stypes.STATUS_EXPIRED, 10),
		strconv.FormatInt(stypes.STATUS_WIN, 10),
	}
	st_str := strings.Join(completedStatuses, ",")
	// Извлекаем только активные бонусы пользователя, исключая завершенные
	// Используем оператор NOT IN для фильтрации по статусам
	// Возвращаем только активные бонусы
	data := []models.UserBonus{}
	err := db.Select(&data,
		`SELECT id, user_id, type, money_available, money_paid_out, total_bet, total_bet_for_payout, 
		total_win, wager_to_complete, wager_to_payout, multiplier_to_complete, multiplier_to_payout, 
		real_money_spent, status, spins_total, spins_available, created_at, updated_at, active_to, total_bet_after, 
		completed_at, money_to_activate, multiplier, hours_active, max_win, tries, hall_id, currency, 
		pattern_id, pattern_status, bonus_prizes, given_prizes, enable_after, games_limited, allowed_games, 
		json_params, creator_type, creator_id, game_provider, game_id, title, img_url, marker, wager_games_segment_id
		FROM users_bonuses
		WHERE user_id=?
		AND status in (`+st_str+`)
		ORDER BY id DESC
		LIMIT ?
		`, id, limit)
	if err != nil {
		return nil, err
	}
	return data, nil
}

// Update обновляет запись UserBonus в базе данных, используя данные из переданной структуры.
// Возвращает ошибку, если операция завершилась неудачно.
func (ub dbUserBonusRepository) Update(bonus *models.UserBonus) error {
	db := db.Instance()

	sql_query := `
	UPDATE users_bonuses
	SET
		user_id = :user_id,
		type = :type,
		money_available = :money_available,
		money_paid_out = :money_paid_out,
		total_bet = :total_bet,
		total_bet_for_payout = :total_bet_for_payout,
		total_win = :total_win,
		wager_to_complete = :wager_to_complete,
		wager_to_payout = :wager_to_payout,
		multiplier_to_complete = :multiplier_to_complete,
		multiplier_to_payout = :multiplier_to_payout,
		real_money_spent = :real_money_spent,
		status = :status,
		spins_total = :spins_total,
		spins_available = :spins_available,
		created_at = :created_at,
		updated_at = :updated_at,
		active_to = :active_to,
		total_bet_after = :total_bet_after,
		completed_at = :completed_at,
		money_to_activate = :money_to_activate,
		multiplier = :multiplier,
		hours_active = :hours_active,
		max_win = :max_win,
		tries = :tries,
		hall_id = :hall_id,
		currency = :currency,
		pattern_id = :pattern_id,
		pattern_status = :pattern_status,
		bonus_prizes = :bonus_prizes,
		given_prizes = :given_prizes,
		enable_after = :enable_after,
		games_limited = :games_limited,
		allowed_games = :allowed_games,
		json_params = :json_params,
		creator_type = :creator_type,
		creator_id = :creator_id,
		game_provider = :game_provider,
		game_id = :game_id,
		title = :title,
		img_url = :img_url,
		marker = :marker,
		wager_games_segment_id = :wager_games_segment_id
		WHERE id = :id;
	`
	_, err := db.NamedExec(sql_query, bonus)
	return err
}

// Insert добавляет новую запись UserBonus в базу данных и возвращает ID добавленной записи или ошибку, если операция не удалась.
func (ub dbUserBonusRepository) Insert(bonus *models.UserBonus) (stypes.BonusID, error) {
	db := db.Instance()

	sql_query := `
		INSERT INTO users_bonuses (
			user_id, type, money_available, money_paid_out, total_bet, total_bet_for_payout, total_win, wager_to_complete, 
			wager_to_payout, multiplier_to_complete, multiplier_to_payout, real_money_spent, status, spins_total, spins_available, 
			created_at, updated_at, active_to, total_bet_after, completed_at, money_to_activate, multiplier, hours_active, 
			max_win, tries, hall_id, currency, pattern_id, pattern_status, bonus_prizes, given_prizes, enable_after, 
			games_limited, allowed_games, json_params, creator_type, creator_id, game_provider, game_id, title, img_url, marker, wager_games_segment_id
		) VALUES (
			:user_id, :type, :money_available, :money_paid_out, :total_bet, :total_bet_for_payout, :total_win, :wager_to_complete, 
			:wager_to_payout, :multiplier_to_complete, :multiplier_to_payout, :real_money_spent, :status, :spins_total, :spins_available, 
			:created_at, :updated_at, :active_to, :total_bet_after, :completed_at, :money_to_activate, :multiplier, :hours_active, 
			:max_win, :tries, :hall_id, :currency, :pattern_id, :pattern_status, :bonus_prizes, :given_prizes, :enable_after, 
			:games_limited, :allowed_games, :json_params, :creator_type, :creator_id, :game_provider, :game_id, :title, :img_url, :marker, :wager_games_segment_id
		)
	`

	result, err := db.NamedExec(
		sql_query,
		bonus,
	)

	if err != nil {
		return 0, err
	}

	// inserted ID
	insertedID, err := result.LastInsertId()
	if err != nil {
		return 0, err
	}

	bonus.ID = insertedID
	return insertedID, nil
}
