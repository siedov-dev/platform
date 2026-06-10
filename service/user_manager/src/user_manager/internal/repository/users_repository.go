package repository

import (
	"log/slog"
	"time"
	"user-manager/internal/db"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/service/container"
)

// NewUserRepository возвращает реализацию интерфейса UserRepository.
// Используется для работы с данными пользователей в хранилище.
func NewUserRepository() container.UserRepository {
	return &dbUserRepository{}
}

var _ container.UserRepository = &dbUserRepository{}

type dbUserRepository struct{}

// GetByID получает пользователя из базы данных по его уникальному идентификатору.
// Возвращает указатель на структуру M_User и ошибку при возникновении проблемы доступа к данным.
func (r *dbUserRepository) GetByID(id int64) (user *models.M_User, err error) {
	db := db.Instance()
	user = &models.M_User{}
	err = db.Get(user, `
		SELECT id, login, email, currency, phone, token, last_ip
		FROM m_users
		WHERE id=?
	`, id)
	if err != nil {
		logger.Get().Error("DB: Can't find user", slog.Int64("id", id))
		return nil, err
	}
	return user, nil
}

// Update обновляет данные пользователя в базе данных на основе переданных значений структуры M_User.
// Возвращает ошибку при возникновении проблем с выполнением SQL-запроса.
func (r *dbUserRepository) Update(user *models.M_User) (err error) {
	db := db.Instance()
	sql_query := `
		UPDATE m_users
		SET login=:login, email=:email, currency=:currency, phone=:phone, token=:token, last_ip=:last_ip
		WHERE id=:id
	`
	_, err = db.NamedExec(sql_query, user)
	return err
}

// UpdateLastActive обновляет поле last_active пользователя в базе данных по его идентификатору.
func (r *dbUserRepository) UpdateLastActive(user_id int, dt time.Time) (err error) {
	db := db.Instance()
	sql_query := `
		UPDATE m_users
		SET last_active=?
		WHERE id=?
	`
	_, err = db.Exec(sql_query, dt, user_id)
	return err
}
