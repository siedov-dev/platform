package repositories

import (
	"ktournaments/internal/db"
	"ktournaments/internal/db/models"

	"github.com/jmoiron/sqlx"
)

// TournamentRepository - интерфейс репозитория для работы с турнирами
type TournamentRepository interface {
	// Создание турнира
	CreateTournament(t *models.TournamentModel) error
	// Получение турнира по ID
	GetTournamentByID(id uint64) (*models.TournamentModel, error)
	// Обновление турнира
	UpdateTournament(t *models.TournamentModel) error
	// Удаление турнира
	DeleteTournament(id uint64) error
	// Получение всех турниров
	GetAllTournaments() ([]models.TournamentModel, error)
	// Получение турниров по статусу
	GetTournamentsByStatus(status int) ([]models.TournamentModel, error)
	// Получение завершенных турниров
	GetCompletedTournaments(limit int) ([]models.TournamentModel, error)
}

type tournamentRepository struct {
	db *sqlx.DB
}

// NewTournamentRepository - создает новый репозиторий для работы с турнирами
func NewTournamentRepository() TournamentRepository {
	db := db.Instance()
	return &tournamentRepository{db: db}
}

// CreateTournament - создание турнира
func (r *tournamentRepository) CreateTournament(t *models.TournamentModel) error {
	query := `
		INSERT INTO tournaments (
			type, name, status, start_time, end_time, prize_places, win_prc, game_type, game_params, bank,
			buyin_cost, rebuy_cost, rebuy_count, buyin_bank_prc, rebuy_bank_prc, player_list, allowed_levels,
			game_list, params, tournament_bonus_group_id, created_at, updated_at, ed_rebuys, penalty_points,
			free_rebuys, hall_id, currency, is_test, tile_img, mobile_img, desktop_img, cabinet_d_img,
			cabinet_m_img, req_confirm, conditions
		) VALUES (
			:type, :name, :status, :start_time, :end_time, :prize_places, :win_prc, :game_type, :game_params, :bank,
			:buyin_cost, :rebuy_cost, :rebuy_count, :buyin_bank_prc, :rebuy_bank_prc, :player_list, :allowed_levels,
			:game_list, :params, :tournament_bonus_group_id, NOW(), NOW(), :ed_rebuys, :penalty_points,
			:free_rebuys, :hall_id, :currency, :is_test, :tile_img, :mobile_img, :desktop_img,
			:cabinet_d_img,:cabinet_m_img,:req_confirm, :conditions
		)
	`
	_, err := r.db.NamedExec(query, t)
	return err
}

// GetTournamentByID - получение турнира по ID
func (r *tournamentRepository) GetTournamentByID(id uint64) (*models.TournamentModel, error) {
	var t models.TournamentModel
	query := `SELECT * FROM tournaments WHERE id = ?`
	err := r.db.Get(&t, query, id)
	return &t, err
}

// UpdateTournament - обновление турнира
func (r *tournamentRepository) UpdateTournament(t *models.TournamentModel) error {
	query := `
		UPDATE tournaments SET
			type = :type, name = :name, status = :status, start_time = :start_time, end_time = :end_time,
			prize_places = :prize_places, win_prc = :win_prc, game_type = :game_type, game_params = :game_params,
			bank = :bank, buyin_cost = :buyin_cost, rebuy_cost = :rebuy_cost, rebuy_count = :rebuy_count,
			buyin_bank_prc = :buyin_bank_prc, rebuy_bank_prc = :rebuy_bank_prc, player_list = :player_list,
			allowed_levels = :allowed_levels, game_list = :game_list, params = :params,
			tournament_bonus_group_id = :tournament_bonus_group_id, updated_at = NOW(), ed_rebuys = :ed_rebuys,
			penalty_points = :penalty_points, free_rebuys = :free_rebuys, hall_id = :hall_id, currency = :currency,
			is_test = :is_test, tile_img = :tile_img, mobile_img = :mobile_img, desktop_img = :desktop_img,
			cabinet_d_img = :cabinet_d_img,cabinet_m_img=:cabinet_m_img, req_confirm=:req_confirm, conditions=:conditions
		WHERE id = :id
	`
	_, err := r.db.NamedExec(query, t)
	return err
}

// DeleteTournament - удаление турнира
func (r *tournamentRepository) DeleteTournament(id uint64) error {
	query := `DELETE FROM tournaments WHERE id = ?`
	_, err := r.db.Exec(query, id)
	return err
}

// GetAllTournaments - получение всех турниров
func (r *tournamentRepository) GetAllTournaments() ([]models.TournamentModel, error) {
	var tournaments []models.TournamentModel
	query := `SELECT * FROM tournaments`
	err := r.db.Select(&tournaments, query)
	return tournaments, err
}

// GetTournamentsByStatus - получение турниров по статусу
func (r *tournamentRepository) GetTournamentsByStatus(status int) ([]models.TournamentModel, error) {
	var tournaments []models.TournamentModel
	query := `SELECT * FROM tournaments WHERE status = ?`
	err := r.db.Select(&tournaments, query, status)
	return tournaments, err
}

// GetCompletedTournaments - получение завершенных турниров
func (r *tournamentRepository) GetCompletedTournaments(limit int) ([]models.TournamentModel, error) {
	var tournaments []models.TournamentModel
	query := `SELECT * FROM tournaments WHERE status in (?, ?) ORDER BY end_time DESC LIMIT ?`
	err := r.db.Select(&tournaments, query, models.STATUS_DONE, models.STATUS_COMPLETED, limit)
	return tournaments, err
}
