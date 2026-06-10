package main

import (
	"context"
	"fmt"
	"kbonus/internal/config"
	dataproviders "kbonus/internal/data_providers"
	"kbonus/internal/db"
	"kbonus/internal/db/repository"
	"kbonus/internal/db/storage"
	"kbonus/internal/factories/activators"
	"kbonus/internal/factories/actualizers"
	betwagerhandlers "kbonus/internal/factories/bet_wager_handlers"
	bonuscreator "kbonus/internal/factories/bonus_creator"
	bonushandlers "kbonus/internal/factories/bonus_handlers"
	bonusinfo "kbonus/internal/factories/bonus_info"
	extfshandlers "kbonus/internal/factories/ext_fs_handlers"
	realmoneywallet "kbonus/internal/factories/real_money_wallet"
	"kbonus/internal/logger"
	"kbonus/internal/natsbroker"
	"kbonus/internal/server/mware"
	"kbonus/internal/server/routes"
	"kbonus/internal/service"
	"kbonus/internal/service/container"
	"net/http"
	"os"
	"os/signal"
	"sync"
	"syscall"
	"time"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/chi/v5/middleware"
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

	// Инициализация репозиториев
	container.SetRepositories(
		repository.NewUserBonusDbRepository(),
		repository.NewBonusCurrencyConfigDbRepository(),
		repository.NewBonusMenuParamsDbRepository(),
		repository.NewBonusMultiDaysParamsDbRepository(),
		repository.NewBonusStatisticDbRepository(),
		repository.NewBonusTransactionDbRepository(),
		repository.NewBonusPatternsRepository(),
		repository.NewBonusAvailableRepository(),
	)

	// Инициализация провайдеров данных
	container.SetDataProviders(
		dataproviders.NewBonusParamsDataProviderImpl(),
		dataproviders.NewCurrencyConfigDataProviderImpl(),
		dataproviders.NewMultiDaysBonusPramsDataProviderImpl(),
		dataproviders.NewBonusPatternsDataProviderImpl(),
	)

	// Инициализация фабик
	container.SetFactories(
		bonuscreator.NewBonusCreatorFactoryImpl(),
		bonushandlers.NewBonusHandlersFactoryImpl(),
		actualizers.NewBonusActualizerFactoryImpl(),
		betwagerhandlers.NewBetWagererFactoryImpl(),
		extfshandlers.NewExtFsHandlersFactoryImpl(),
		// extfshandlers.NewExtFsHandlersMockFactoryImpl(), // <--- Используем мок-фабрику
		realmoneywallet.NewRealMoneyWalletFactory(),
		bonusinfo.NewBonusInfoProviderFactory(),
		activators.NewBonusActivatorFactory(),
	)

	// Инициализация хранилища статистик бонусов
	container.SetBonusStatisticStorage(storage.NewDBBonusStatisticStorage(ctx))
	// Инициализация хранилища отложенных доступных бонусов
	container.SetBonusAvailableDeferredStorage(storage.NewBonusAvailableDeferredStorage(ctx))
	// Инициализация хранилища сегментов бонусов
	container.SetBonusSegmentsStorage(storage.NewBonusSegmentsStorage())

	// Инициализация сервиса
	service.MustInit(ctx)

	// Инициализация NATS
	natsbroker.MustInit(ctx)
	container.SetNatsConnection(natsbroker.NatsConn)
	sloggger.Info("NATS broker initialized")

	fmt.Printf("HTTP Server Address: %s\n", config.Get().HTTPServer.Address)

	r := chi.NewRouter()

	// Стандартный middlware стэк
	//r.Use(middleware.RequestID)
	r.Use(middleware.RealIP)
	r.Use(middleware.Logger)
	r.Use(middleware.Recoverer)
	r.Use(middleware.NoCache)
	// Middlware авторизации запросов
	r.Use(mware.Auth)

	// Таймаут запросов
	r.Use(middleware.Timeout(5 * time.Second))

	routes.SetRoutes(r)

	server := &http.Server{Addr: config.Get().HTTPServer.Address, Handler: r}

	go func() {
		sloggger.Info("Starting HTTP server", "address", config.Get().HTTPServer.Address)
		if err := server.ListenAndServe(); err != nil && err != http.ErrServerClosed {
			sloggger.Error("HTTP server ListenAndServe", "error", err)
			panic(err)
		}
	}()

	// Завершение работы
	func() {
		exit := make(chan os.Signal, 1)
		signal.Notify(exit, os.Interrupt, syscall.SIGTERM)
		<-exit
		cancel()
		server.Shutdown(ctx)
		natsbroker.Stop()
		natsbroker.NatsConn.Close()
		service.Stop()
		db.Close()
	}()

	sloggger.Info("Service stopped")
}
