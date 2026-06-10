package db

import (
	"log/slog"
	"sync"
	"time"
	"user-manager/internal/config"
	"user-manager/internal/logger"

	"github.com/jmoiron/sqlx"
)

var (
	DB   *sqlx.DB
	once sync.Once
)

// New создает и возвращает новый экземпляр *sqlx.DB с настройками подключения из глобальной конфигурации.
// Устанавливаются параметры соединений: максимальное время жизни соединения, максимальное количество открытых и простаивающих соединений.
// В случае ошибки подключения вызывает panic.
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

// Instance возвращает глобальный экземпляр sqlx.DB, инициализируя его только один раз с использованием функции New.
func Instance() *sqlx.DB {
	once.Do(func() {
		DB = New()
	})
	return DB
}

// Close завершает соединение с базой данных через вызов метода Close для объекта DB.
func Close() {
	err := DB.Close()
	if err != nil {
		logger.Get().Error("DB: Can't close connection", slog.String("error", err.Error()))
	}
}
