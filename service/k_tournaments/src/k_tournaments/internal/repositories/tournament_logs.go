package repositories

import (
	"ktournaments/internal/db"
	"ktournaments/internal/db/models"
	"ktournaments/internal/logger"
	"log/slog"

	"github.com/jmoiron/sqlx"
)

// TournamentLogRepository - интерфейс репозитория для работы с логами турниров
type TournamentLogRepository interface {
	// SaveLog - сохраняет лог турнира
	SaveLog(models.TournamentSpinLog) error
	// Insert - добавляет одну или несколько записей TournamentSpinLog в базу данных. Возвращает ошибку при неудачной операции.
	Insert(spins ...*models.TournamentSpinLog) (err error)
}

type tournamentLogRepository struct {
	// db - база данных
	db *sqlx.DB
}

// NewTournamentLogRepository - создает новый репозиторий для работы с логами турниров
func NewTournamentLogRepository() TournamentLogRepository {
	db := db.Instance()
	return &tournamentLogRepository{db: db}
}

// SaveLog - сохраняет лог турнира
func (r *tournamentLogRepository) SaveLog(log models.TournamentSpinLog) error {
	query := `INSERT INTO tournament_spin_logs (tournament_id, user_id, currency, game_system, game_id, bet, win, points, created_at, updated_at) 
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())`
	_, err := r.db.Exec(query, log.TournamentID, log.UserID, log.Currency, log.GameSystem, log.GameID, log.Bet, log.Win, log.Points)
	if err != nil {
		return err
	}
	return nil
}

// Insert добавляет одну или несколько записей TournamentSpinLog в базу данных. Возвращает ошибку при неудачной операции.
func (r *tournamentLogRepository) Insert(spins ...*models.TournamentSpinLog) (err error) {
	db := db.Instance()

	sql_query := `
		INSERT INTO tournament_spin_logs (tournament_id, user_id, currency, game_system, game_id, bet, win, points, created_at, updated_at) 
		VALUES (:tournament_id,:user_id,:currency,:game_system,:game_id,:bet,:win,:points,NOW(),NOW())
	`

	tx := db.MustBegin()

	for _, spin := range spins {
		_, err := tx.NamedExec(sql_query, spin)
		if err != nil {
			logger.Get().Error("transaction repository error", slog.String("err", err.Error()))
		}
	}

	err = tx.Commit()

	return err
}
