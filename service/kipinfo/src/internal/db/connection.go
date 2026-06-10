package db

import (
	"kipinfo/internal/config"
	"sync"
	"time"

	"github.com/jmoiron/sqlx"
)

var (
	DB   *sqlx.DB
	once sync.Once
)

// New создает новое подключение к базе данных
func New() *sqlx.DB {

	cfg := config.Get()

	db, err := sqlx.Connect("mysql", cfg.DB.ConnectionString)
	if err != nil {
		panic(err)
	}

	db.SetConnMaxLifetime(time.Minute * 2)
	db.SetMaxOpenConns(cfg.MaxOpenConns)
	db.SetMaxIdleConns(cfg.MaxIdleConns)

	return db
}

func Instance() *sqlx.DB {
	once.Do(func() {
		DB = New()
	})
	return DB
}

func Close() {
	DB.Close()
}
