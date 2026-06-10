package main

import (
	"context"
	"log/slog"
	"net/http"
	"os"
	"os/signal"
	"syscall"
	"time"
	"user-manager/internal/config"
	"user-manager/internal/db"
	"user-manager/internal/factories"
	"user-manager/internal/logger"
	"user-manager/internal/msgbroker/natsbroker"
	"user-manager/internal/repository"
	"user-manager/internal/server/routes"
	"user-manager/internal/service"
	"user-manager/internal/service/container"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/chi/v5/middleware"
	_ "github.com/go-sql-driver/mysql"
)

func main() {
	os.Setenv("TZ", "Europe/Kyiv")

	// Создание логера
	sloggger := logger.Get()
	defer logger.Close()
	sloggger.Info("starting app")

	// Глобальный контекст
	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()

	// Загрузка конфигурации
	cfg := config.Get()
	_ = db.Instance()
	defer db.Close()

	// Инициализация брокера сообщений
	broker, err := natsbroker.NewNatsBroker(ctx)
	if err != nil {
		panic(err)
	}
	container.SetMsgBroker(broker)

	// Установка репозиториев
	// container.SetUserBonusRepository(repository.NewUserBonusRepository())
	container.SetUserBonusRepository(repository.NewUserBonusRestRepository())
	container.SetBonusStatisticRepository(repository.NewBonusStatisticRepository())
	container.SetBonusTransactionRepository(repository.NewBonusTransactionRepository())
	container.SetGameSessionRepository(repository.NewGameSessionRepository())
	container.SetGameTransactionRepository(repository.NewTransactionRepository())
	container.SetUserRepository(repository.NewUserRepository())
	container.SetWalletsRepository(repository.NewWalletsRepository())

	// Установка фабрик
	container.SetBonusFactory(factories.NewUserBonusRestFactory())

	// Инициализация обработчика команд NATS
	natsbroker.MustInit(ctx)

	// Инициализация сервиса
	service.MustInit(ctx)
	defer service.Shutdown()

	sloggger.Info("initializing server", slog.String("address", cfg.Address))

	r := chi.NewRouter()

	// Стандартный мидл стек
	r.Use(middleware.RequestID)
	r.Use(middleware.RealIP)
	r.Use(middleware.Logger)
	r.Use(middleware.Recoverer)
	r.Use(middleware.NoCache)

	// Таймаут запроса
	r.Use(middleware.Timeout(10 * time.Second))

	// Установка маршрутов
	routes.SetRoutes(r)

	server := &http.Server{Addr: cfg.Address, Handler: r}

	// Graceful shutdown
	go func() {
		exit := make(chan os.Signal, 1)
		signal.Notify(exit, os.Interrupt, syscall.SIGTERM)
		<-exit
		cancel()
		sloggger.Info("shutting down server")
		server.Shutdown(ctx)
		natsbroker.Stop() // Остановка NATS брокера
	}()

	// Запуск сервера
	err = server.ListenAndServe()
	if err != nil {
		sloggger.Error(err.Error())
	}
}
