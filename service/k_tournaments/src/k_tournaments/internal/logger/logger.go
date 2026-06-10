package logger

import (
	"io"
	"ktournaments/internal/config"
	"log"
	"log/slog"
	"sync"
)

var (
	sloggger *slog.Logger
	once     sync.Once
	outFile  io.WriteCloser
	DailyLog *DailyLogFile
)

const (
	envDev  = "dev"
	envProd = "prod"
)

// Get - возвращает экземпляр логгера
func Get() *slog.Logger {
	once.Do(func() {
		cfg := config.Get()

		file, err := NewDailyLogFile()
		if err != nil {
			panic(err)
		}
		DailyLog = file
		outFile = file
		//outFile = os.Stdout
		sloggger = setupLogger(cfg.Env, outFile)
		log.SetOutput(outFile)
	})

	return sloggger
}

// setupLogger - настраивает логгер
func setupLogger(env string, w io.Writer) *slog.Logger {

	switch env {
	case envDev:
		sloggger = slog.New(slog.NewTextHandler(w, &slog.HandlerOptions{Level: slog.LevelDebug}))
	case envProd:
		sloggger = slog.New(slog.NewJSONHandler(w, &slog.HandlerOptions{Level: slog.LevelInfo}))
	}

	return sloggger
}

// Close - закрывает файл лога
func Close() {
	if outFile != nil {
		outFile.Close()
	}
}
