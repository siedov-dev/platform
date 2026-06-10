package main

import (
	"context"
	"kreferrals/internal/db"
	dbeventssaver "kreferrals/internal/db/db_events_saver"
	"kreferrals/internal/db/repository"
	"kreferrals/internal/logger"
	"kreferrals/internal/natsbroker"
	"kreferrals/internal/natsbroker/natsrefwallet"
	"kreferrals/internal/service"
	"kreferrals/internal/service/container"
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
	_ = ctx

	// Инициализация репозиториев
	container.SetRepositories(
		repository.NewReferralConfigDbRepository(),
		repository.NewAchievementRewardsDBRepository(),
		repository.NewReferralEventDbRepository(),
		repository.NewUserReferralsDbRepository(),
		repository.NewReferralMUsersRepository(),
	)

	// Инициализация репозитория событий
	container.SetEventsSaver(
		dbeventssaver.NewDBEventsSaver(),
	)

	// Инициализация NATS
	natsbroker.MustInit(ctx)
	sloggger.Info("NATS broker initialized")

	// Инициализация кошелька рефералов
	container.SetRefWallet(
		natsrefwallet.NewNatsRefWallet(natsbroker.NatsConn),
	)

	// Инициализация сервиса
	service.MustInit(ctx)
	sloggger.Info("Service initialized")

	// Завершение работы
	func() {
		exit := make(chan os.Signal, 1)
		signal.Notify(exit, os.Interrupt, syscall.SIGTERM)
		<-exit
		cancel()
		natsbroker.Stop()
		natsbroker.NatsConn.Close()
		service.Stop()
		db.Close()
	}()

	sloggger.Info("Service stopped")
}
