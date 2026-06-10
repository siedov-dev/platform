package logger

import (
	"fmt"
	"io"
	"log"
	"log/slog"
	"sync"
	"user-manager/internal/config"
)

var (
	sloggger *slog.Logger
	once     sync.Once
	outFile  io.WriteCloser
)

const (
	envLocal = "local"
	envDev   = "dev"
	envProd  = "prod"
)

// Get возвращает глобальный экземпляр логгера slog.Logger. Создание и настройка выполняются только один раз.
func Get() *slog.Logger {
	once.Do(func() {
		cfg := config.Get()

		file, err := NewDailyLogFile()
		if err != nil {
			panic(err)
		}
		outFile = file
		//outFile = os.Stdout
		sloggger = setupLogger(cfg.Env, outFile)
		log.SetOutput(outFile)
		//sloggger = sloggger.With(slog.String("env", cfg.Env)) // add env type to logs
	})

	return sloggger
}

// setupLogger настраивает и возвращает новый экземпляр slog.Logger в зависимости от окружения (env).
// Для local окружения используются текстовые логи с уровнем отладки.
// Для dev и prod окружений используются JSON-логи с уровнями отладки и информации соответственно.
// Параметры: env (строка, определяет окружение), w (io.Writer, куда писать логи).
// Возвращает указатель на настроенный slog.Logger.
func setupLogger(env string, w io.Writer) *slog.Logger {

	switch env {
	case envLocal:
		sloggger = slog.New(slog.NewTextHandler(w, &slog.HandlerOptions{Level: slog.LevelDebug}))
	case envDev:
		sloggger = slog.New(slog.NewJSONHandler(w, &slog.HandlerOptions{Level: slog.LevelDebug}))
	case envProd:
		sloggger = slog.New(slog.NewJSONHandler(w, &slog.HandlerOptions{Level: slog.LevelInfo}))
	}

	return sloggger
}

// Close закрывает файл outFile, если он был открыт, и выводит сообщение об ошибке в случае неудачи.
func Close() {
	if outFile != nil {
		err := outFile.Close()
		if err != nil {
			fmt.Println("Error closing log file", err)
		}
	}
}
