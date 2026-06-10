package repositories

import (
	"ktournaments/internal/db"
)

// UserLoginsRepository - интерфейс репозитория для работы с логинами пользователей
// Используется для получения логина пользователя по ID
// чтобы сохранить в таблице турнира
type UserLoginsRepository interface {
	// Получение логина пользователя
	GetUserLogin(id int) (string, error)
}

// NewUserLoginsRepository - создает новый репозиторий для работы с логинами пользователей
func NewUserLoginsRepository() UserLoginsRepository {
	return &UserLoginsRepo{}
}

// UserLoginsRepo - репозиторий для работы с логинами пользователей
type UserLoginsRepo struct{}

// GetUserLogin - получение логина пользователя
func (r *UserLoginsRepo) GetUserLogin(id int) (string, error) {

	var login_data struct {
		Login string `db:"login"`
	}
	query := `SELECT login FROM m_users WHERE id = ?`
	err := db.Instance().Get(&login_data, query, id)
	if err != nil {
		return "", err
	}
	return login_data.Login, nil
}
