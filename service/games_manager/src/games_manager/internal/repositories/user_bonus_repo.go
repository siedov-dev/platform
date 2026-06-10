package repositories

import (
	"games_manager/internal/db"
	"games_manager/internal/db/models"
)

type UserBonusRepo struct {
}

func (ub UserBonusRepo) ById(id int) (*models.UserBonus, error) {
	db := db.New()
	defer db.Close()

	data := models.UserBonus{}
	err := db.Get(&data, "SELECT id, user_id, type, allowed_games, game_provider, game_id"+
		" FROM users_bonuses"+
		" WHERE id=?", id)
	if err != nil {
		return nil, err
	}

	return &data, nil
}
