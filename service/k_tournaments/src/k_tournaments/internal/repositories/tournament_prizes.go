package repositories

import (
	"ktournaments/internal/db"
	"ktournaments/internal/db/models"

	"github.com/jmoiron/sqlx"
)

// TournamentPrizeRepository - интерфейс репозитория для работы с призами турнира
type TournamentPrizeRepository interface {
	// Получение приза по ID
	GetByID(id uint64) (models.TournamentPrizeModel, error)
	// Получение призов по ID группы
	GetByGroupID(groupId uint64) ([]models.TournamentPrizeModel, error)
	// Получение приза по ID группы и места
	GetByGroupIDPlace(groupId uint64, place int) (models.TournamentPrizeModel, error)
	// Вставка приза
	Insert(tournamentPrize models.TournamentPrizeModel) (uint64, error)
	// Обновление приза
	Update(tournamentPrize models.TournamentPrizeModel) error
	// Удаление приза
	Delete(id uint64) error
}

type tournamentPrizeRepository struct {
	db *sqlx.DB
}

// NewTournamentPrizeRepository - создает новый репозиторий для работы с призами турнира
func NewTournamentPrizeRepository() TournamentPrizeRepository {
	db := db.Instance()
	return &tournamentPrizeRepository{db: db}
}

// GetByID retrieves a tournament prize by its ID
func (r *tournamentPrizeRepository) GetByID(id uint64) (models.TournamentPrizeModel, error) {
	var tournamentPrize models.TournamentPrizeModel
	query := `SELECT * FROM tournament_prizes WHERE id = ?`
	err := r.db.Get(&tournamentPrize, query, id)
	if err != nil {
		return models.TournamentPrizeModel{}, err
	}
	return tournamentPrize, nil
}

// GetByGroupID retrieves all tournament prizes for a given group ID
func (r *tournamentPrizeRepository) GetByGroupID(groupId uint64) ([]models.TournamentPrizeModel, error) {
	var tournamentPrizes []models.TournamentPrizeModel
	query := `SELECT * FROM tournament_prizes WHERE group_id = ?`
	err := r.db.Select(&tournamentPrizes, query, groupId)
	if err != nil {
		return nil, err
	}
	return tournamentPrizes, nil
}

// GetByGroupIDPlace retrieves a tournament prize by group ID and place
func (r *tournamentPrizeRepository) GetByGroupIDPlace(groupId uint64, place int) (models.TournamentPrizeModel, error) {
	var tournamentPrize models.TournamentPrizeModel
	query := `SELECT * FROM tournament_prizes WHERE group_id = ? AND place = ?`
	err := r.db.Get(&tournamentPrize, query, groupId, place)
	if err != nil {
		return models.TournamentPrizeModel{}, err
	}
	return tournamentPrize, nil
}

// Insert creates a new tournament prize
func (r *tournamentPrizeRepository) Insert(tournamentPrize models.TournamentPrizeModel) (uint64, error) {
	query := `INSERT INTO tournament_prizes (group_id, place, name, img, currency_amounts, item_type, item_eval, amount, bonus_type, bonus_params, bonus_games, texts, container_img, container_texts) 
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`
	result, err := r.db.Exec(query,
		tournamentPrize.GroupID,
		tournamentPrize.Place,
		tournamentPrize.Name,
		tournamentPrize.Img,
		tournamentPrize.CurrencyAmounts,
		tournamentPrize.ItemType,
		tournamentPrize.ItemEval,
		tournamentPrize.Amount,
		tournamentPrize.BonusType,
		tournamentPrize.BonusParams,
		tournamentPrize.BonusGames,
		tournamentPrize.Texts,
		tournamentPrize.ContainerImg,
		tournamentPrize.ContainerTexts,
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

// Update updates an existing tournament prize
func (r *tournamentPrizeRepository) Update(tournamentPrize models.TournamentPrizeModel) error {
	query := `UPDATE tournament_prizes SET group_id = ?, place = ?, name = ?, img = ?, currency_amounts = ?, 
				item_type = ?, item_evail = ?, amount = ?, bonus_type = ?, bonus_params = ?, bonus_games = ?, texts = ? 
				container_img = ?, container_texts = ?
				WHERE id = ?`
	_, err := r.db.Exec(query,
		tournamentPrize.GroupID,
		tournamentPrize.Place,
		tournamentPrize.Name,
		tournamentPrize.Img,
		tournamentPrize.CurrencyAmounts,
		tournamentPrize.ItemType,
		tournamentPrize.ItemEval,
		tournamentPrize.Amount,
		tournamentPrize.BonusType,
		tournamentPrize.BonusParams,
		tournamentPrize.BonusGames,
		tournamentPrize.Texts,
		tournamentPrize.ContainerImg,
		tournamentPrize.ContainerTexts,
		tournamentPrize.ID)
	if err != nil {
		return err
	}
	return nil
}

// Delete removes a tournament prize by its ID
func (r *tournamentPrizeRepository) Delete(id uint64) error {
	query := `DELETE FROM tournament_prizes WHERE id = ?`
	_, err := r.db.Exec(query, id)
	if err != nil {
		return err
	}
	return nil
}
