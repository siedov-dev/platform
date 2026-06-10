package db

import (
	"kbonus/internal/config"
	"kbonus/internal/logger"
	"sync"
	"time"

	"github.com/jmoiron/sqlx"
)

var (
	DB   *sqlx.DB
	once sync.Once
)

// New - создает новое соединение с базой данных
func New() *sqlx.DB {

	cfg := config.Get()

	db, err := sqlx.Connect("mysql", cfg.DB.ConnectionString)
	if err != nil {
		logger.Get().Error(err.Error())
		panic(err)
		//log.Fatalln(err) wont write logs
	}

	db.SetConnMaxLifetime(time.Minute * 2)
	db.SetMaxOpenConns(cfg.MaxOpenConns)
	db.SetMaxIdleConns(cfg.MaxIdleConns)

	return db
}

// Instance - возвращает экземпляр соединения с базой данных
func Instance() *sqlx.DB {
	once.Do(func() {
		DB = New()
	})
	return DB
}

func Close() {
	DB.Close()
}
