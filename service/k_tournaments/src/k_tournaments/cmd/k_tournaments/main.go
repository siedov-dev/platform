package main

import (
	"context"
	"ktournaments/internal/db"
	"ktournaments/internal/logger"
	"ktournaments/internal/repositories"
	"ktournaments/internal/service"
	"ktournaments/internal/service/container"
	"ktournaments/internal/service/natsconsumer"
	"ktournaments/internal/service/tspinlogger"
	"ktournaments/internal/service/tvaluesaver"
	"os"
	"os/signal"
	"sync"
	"syscall"

	_ "github.com/go-sql-driver/mysql"
)

var GWait *sync.WaitGroup

func main() {
	os.Setenv("TZ", "Europe/Kyiv")
	sloggger := logger.Get()
	defer logger.Close()
	sloggger.Info("Starting app")

	// Инициализация базы данных
	_ = db.Instance()
	defer db.Close()

	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()

	// Инициализация сохранения значений турниров
	container.SetValueSaver(tvaluesaver.NewDeferredSaver(ctx))
	// Инициализация репозиториев
	container.SetRepositories(
		repositories.NewTournamentPrizeRepository(),
		repositories.NewTournamentRepository(),
		repositories.NewTournamentValueRepository(),
		repositories.NewUserLoginsRepository(),
		repositories.NewTournamentLogRepository(),
	)

	container.SetSpinLogger(tspinlogger.NewTSpinLogger(ctx))

	// Инициализация NATS
	natsconsumer.MustInit(ctx)
	sloggger.Info("NATS consumer initialized")
	service.MustInit(ctx)
	sloggger.Info("Service initialized")

	// Завершение работы
	func() {
		exit := make(chan os.Signal, 1)
		signal.Notify(exit, os.Interrupt, syscall.SIGTERM)
		<-exit
		natsconsumer.Stop()
		cancel()
	}()

	// Сохранение оставшихся значений
	sloggger.Info("Saving left values")
	container.GetValueSaver().Flush()

	sloggger.Info("Service stopped")
}
