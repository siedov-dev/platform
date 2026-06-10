package repositories

import (
	"ktournaments/internal/db"
	"ktournaments/internal/db/models"
	"time"

	"github.com/jmoiron/sqlx"
)

// TournamentValueRepository - интерфейс репозитория для работы со значениями турниров
type TournamentValueRepository interface {
	// Получение значения по ID
	GetByID(id uint64) (models.TournamentValueModel, error)
	// Получение значений по ID турнира
	GetTournamentValues(tournament_id uint64) ([]models.TournamentValueModel, error)
	// Получение значения по ID турнира и ID пользователя
	GetByTournamentIdUserId(tournamentId, userId uint64) (models.TournamentValueModel, error)
	// Вставка значения
	Insert(tournamentValue models.TournamentValueModel) (uint64, error)
	// Обновление значения
	Update(tournamentValue models.TournamentValueModel) error
	// Удаление значения
	Delete(id uint64) error
}

type tournamentValueRepository struct {
	db *sqlx.DB
}

// NewTournamentValueRepository - создает новый репозиторий для работы со значениями турниров
func NewTournamentValueRepository() TournamentValueRepository {
	db := db.Instance()
	return &tournamentValueRepository{db: db}
}

// GetByID - получение значения по ID
func (r *tournamentValueRepository) GetByID(id uint64) (models.TournamentValueModel, error) {
	var tournamentValue models.TournamentValueModel
	query := `SELECT * FROM tournament_values WHERE id = ?`
	err := r.db.Get(&tournamentValue, query, id)
	if err != nil {
		return models.TournamentValueModel{}, err
	}
	return tournamentValue, nil
}

// GetTournamentValues - получение всех значений по ID турнира
func (r *tournamentValueRepository) GetTournamentValues(tournament_id uint64) ([]models.TournamentValueModel, error) {
	var tournamentValues []models.TournamentValueModel
	query := `SELECT * FROM tournament_values WHERE tournament_id = ?`
	err := r.db.Select(&tournamentValues, query, tournament_id)
	if err != nil {
		return nil, err
	}
	return tournamentValues, nil
}

// GetByTournamentIdUserId - получение значения по ID турнира и ID пользователя
func (r *tournamentValueRepository) GetByTournamentIdUserId(tournamentId, userId uint64) (models.TournamentValueModel, error) {
	var tournamentValue models.TournamentValueModel
	query := `SELECT * FROM tournament_values WHERE tournament_id = ? AND user_id = ?`
	err := r.db.Get(&tournamentValue, query, tournamentId, userId)
	if err != nil {
		return models.TournamentValueModel{}, err
	}
	return tournamentValue, nil
}

// Insert - вставка значения
func (r *tournamentValueRepository) Insert(tournamentValue models.TournamentValueModel) (uint64, error) {
	query := `INSERT INTO tournament_values (tournament_id, user_id, value, spins_count, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)`
	result, err := r.db.Exec(
		query,
		tournamentValue.TournamentID,
		tournamentValue.UserID,
		tournamentValue.Value,
		tournamentValue.SpinsCount,
		time.Now().Format("2006-01-02 15:04:05"),
		time.Now().Format("2006-01-02 15:04:05"),
	)
	if err != nil {
		return 0, err
	}
	id, err := result.LastInsertId()
	if err != nil {
		return 0, err
	}
	return uint64(id), nil
}

// Update - обновление значения
func (r *tournamentValueRepository) Update(tournamentValue models.TournamentValueModel) error {
	query := `UPDATE tournament_values SET value = ?, spins_count = ?, updated_at = ? WHERE id = ?`
	_, err := r.db.Exec(query, tournamentValue.Value, tournamentValue.SpinsCount, time.Now().Format("2006-01-02 15:04:05"), tournamentValue.ID)
	if err != nil {
		return err
	}
	return nil
}

// Delete - удаление значения
func (r *tournamentValueRepository) Delete(id uint64) error {
	query := `DELETE FROM tournament_values WHERE id = ?`
	_, err := r.db.Exec(query, id)
	if err != nil {
		return err
	}
	return nil
}
