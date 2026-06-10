package repository

import (
	"user-manager/internal/db"
	"user-manager/internal/db/models"
	"user-manager/internal/service/container"
)

func NewUserBonusRepository() container.UserBonusRepository {
	return dbUserBonusRepository{}
}

type dbUserBonusRepository struct {
}

// GetByID извлекает запись UserBonus из базы данных по заданному идентификатору id.
// Возвращает указатель на структуру UserBonus и ошибку, если операция завершилась неудачно.
func (ub dbUserBonusRepository) GetByID(id int64) (*models.UserBonus, error) {
	db := db.Instance()

	data := models.UserBonus{}
	err := db.Get(&data,
		`SELECT id, user_id, type, money_available, money_paid_out, total_bet, total_bet_for_payout, 
		total_win, wager_to_complete, wager_to_payout, multiplier_to_complete, multiplier_to_payout, 
		real_money_spent, status, spins_total, spins_available, created_at, updated_at, active_to, total_bet_after, 
		completed_at, money_to_activate, multiplier, hours_active, max_win, tries, hall_id, currency, 
		pattern_id, pattern_status, bonus_prizes, given_prizes, enable_after, games_limited, allowed_games, 
		json_params, creator_type, creator_id, game_provider, game_id, title, img_url, marker
		FROM users_bonuses
		WHERE id=?`, id)
	if err != nil {
		return nil, err
	}

	return &data, nil
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
		marker = :marker
		WHERE id = :id;
	`
	_, err := db.NamedExec(sql_query, bonus)
	return err
}

// Insert добавляет новую запись UserBonus в базу данных и возвращает ID добавленной записи или ошибку, если операция не удалась.
func (ub dbUserBonusRepository) Insert(bonus *models.UserBonus) (int64, error) {
	db := db.Instance()

	sql_query := `
		INSERT INTO users_bonuses (
			user_id, type, money_available, money_paid_out, total_bet, total_bet_for_payout, total_win, wager_to_complete, 
			wager_to_payout, multiplier_to_complete, multiplier_to_payout, real_money_spent, status, spins_total, spins_available, 
			created_at, updated_at, active_to, total_bet_after, completed_at, money_to_activate, multiplier, hours_active, 
			max_win, tries, hall_id, currency, pattern_id, pattern_status, bonus_prizes, given_prizes, enable_after, 
			games_limited, allowed_games, json_params, creator_type, creator_id, game_provider, game_id, title, img_url, marker
		) VALUES (
			?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
		)
	`

	result, err := db.Exec(
		sql_query,
		bonus.UserID,
		bonus.Type,
		bonus.MoneyAvailable,
		bonus.MoneyPaidOut,
		bonus.TotalBet,
		bonus.TotalBetForPayout,
		bonus.TotalWin,
		bonus.WagerToComplete,
		bonus.WagerToPayout,
		bonus.MultiplierToComplete,
		bonus.MultiplierToPayout,
		bonus.RealMoneySpent,
		bonus.Status,
		bonus.SpinsTotal,
		bonus.SpinsAvailable,
		bonus.CreatedAt,
		bonus.UpdatedAt,
		bonus.ActiveTo,
		bonus.TotalBetAfter,
		bonus.CompletedAt,
		bonus.MoneyToActivate,
		bonus.Multiplier,
		bonus.HoursActive,
		bonus.MaxWin,
		bonus.Tries,
		bonus.HallID,
		bonus.Currency,
		bonus.PatternID,
		bonus.PatternStatus,
		bonus.BonusPrizes,
		bonus.GivenPrizes,
		bonus.EnableAfter,
		bonus.GamesLimited,
		bonus.AllowedGames,
		bonus.JSONParams,
		bonus.CreatorType,
		bonus.CreatorID,
		bonus.GameProvider,
		bonus.GameID,
		bonus.Title,
		bonus.ImgURL,
		bonus.Marker,
	)

	if err != nil {
		return 0, err
	}

	// inserted ID
	insertedID, err := result.LastInsertId()
	if err != nil {
		return 0, err
	}

	bonus.ID = uint(insertedID)
	return insertedID, nil
}
