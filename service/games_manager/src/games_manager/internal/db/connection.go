package db

import (
	"games_manager/internal/config"
	"log"
	"time"

	"github.com/jmoiron/sqlx"
)

// Создает новое подключение к БД и возвращает его
func New() *sqlx.DB {

	cfg := config.Get()

	db, err := sqlx.Connect("mysql", cfg.DB.ConnectionString)
	if err != nil {
		log.Fatalln(err)
	}

	db.SetConnMaxLifetime(time.Minute * 2)
	db.SetMaxOpenConns(10)
	db.SetMaxIdleConns(10)

	return db
}
