package main

import (
	"context"
	"fmt"
	"kipinfo/internal/config"
	"kipinfo/internal/dataproviders/ipdataprovider"
	"kipinfo/internal/db"
	"kipinfo/internal/repositories"
	"kipinfo/internal/server/mware"
	"kipinfo/internal/server/routes"
	"kipinfo/internal/service"
	"kipinfo/internal/service/container"
	"net/http"
	"os"
	"os/signal"
	"syscall"
	"time"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/chi/v5/middleware"
	_ "github.com/go-sql-driver/mysql"
)

func main() {
	os.Setenv("TZ", "Europe/Kyiv")
	ctx, cancel := context.WithCancel(context.Background())
	defer cancel()

	// Загружаем конфигурацию
	cfg := config.Get()
	// Инициализация базы данных
	_ = db.Instance()
	defer db.Close()

	// Инициализация репозиториев
	container.SetIPInfoRepository(repositories.NewDBIpInfoRepository())
	container.SetCountryBlockedRepository(repositories.NewCountryBlockedRepository())

	// Инициализация IP провайдеров данных
	// Основные провайдеры данных IP
	container.SetIPDataProviders(
		ipdataprovider.NewIpApiCoProvider(),     // выдает больше информации включая валюту страны
		ipdataprovider.NewIpApiIsDataProvider(), // выдает больше информации включая валюту страны
	)
	// Провайдеры данных IP для более точной/расширенной информации
	container.SetIPDataEnrichmentProviders(
		ipdataprovider.NewIpInfoProvider(), // более правильно выдает местоположения
	)

	service.MustInit(ctx)

	fmt.Println("Config loaded successfully")
	fmt.Printf("HTTP Server Address: %s\n", cfg.HTTPServer.Address)

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

	server := &http.Server{Addr: cfg.Address, Handler: r}

	// Правильная остановка сервиса
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
		fmt.Println("Server error:", err)
	}

}
