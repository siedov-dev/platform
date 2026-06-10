package main

import (
	"context"
	"games_manager/internal/config"
	"games_manager/internal/games"
	"games_manager/internal/http_server/routes"
	"log/slog"
	"net/http"
	"os"
	"os/signal"
	"syscall"
	"time"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/chi/v5/middleware"
	_ "github.com/go-sql-driver/mysql"
)

const (
	envLocal = "local"
	envDev   = "dev"
	envProd  = "prod"
)

func main() {
	cfg := config.Get()

	log := setupLogger(cfg.Env)
	log = log.With(slog.String("env", cfg.Env)) // тип логирования

	log.Info("Loading games...")

	// создаем контекст с отменой
	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()

	// загрузка игр
	games.MustLoad()

	log.Info("initializing server", slog.String("address", cfg.Address))

	r := chi.NewRouter()

	// Основные middleware
	r.Use(middleware.RequestID)
	r.Use(middleware.RealIP)
	r.Use(middleware.Logger)
	r.Use(middleware.Recoverer)
	r.Use(middleware.NoCache)

	// таймауты для запросов
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
		server.Shutdown(ctx)
	}()

	// Запуск сервера
	err := server.ListenAndServe()
	if err != nil {
		log.Error(err.Error())
	}
}

// setupLogger настраивает логгер в зависимости от окружения
func setupLogger(env string) *slog.Logger {
	var log *slog.Logger

	switch env {
	case envLocal:
		log = slog.New(slog.NewTextHandler(os.Stdout, &slog.HandlerOptions{Level: slog.LevelDebug}))
	case envDev:
		log = slog.New(slog.NewJSONHandler(os.Stdout, &slog.HandlerOptions{Level: slog.LevelDebug}))
	case envProd:
		log = slog.New(slog.NewJSONHandler(os.Stdout, &slog.HandlerOptions{Level: slog.LevelInfo}))
	}

	return log
}
