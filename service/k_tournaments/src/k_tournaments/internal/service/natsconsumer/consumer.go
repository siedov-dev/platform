// consumer.go - файл для инициализации NATS обработчика
// и подписки на темы NATS
// Выполняется при запуске приложения
// Инициализирует соединение с NATS, подписывается на темы и создает обработчик для получения данных
// и отправки ответов на запросы
//
// SpinsConsumer - обработчик для получения данных из NATS о спинах турниров. Евенты "spins.*".
// TournamentSubscription - подписка на тему tournaments для обработки запросов. Евенты "tournaments.*".
package natsconsumer

import (
	"context"
	"encoding/json"
	"fmt"
	"ktournaments/internal/config"
	"ktournaments/internal/logger"
	"ktournaments/internal/service"
	"ktournaments/internal/service/natsconsumer/callbacks"
	"ktournaments/internal/service/natsconsumer/natsmsg"
	"log/slog"
	"os"
	"runtime/debug"
	"time"

	"github.com/nats-io/nats.go"
	"github.com/nats-io/nats.go/jetstream"
)

var SpinsConsumer jetstream.ConsumeContext    // Обработчик для получения данных из NATS о спинах турниров. Евенты "spins.*".
var NatsConn *nats.Conn                       // Соединение с NATS
var TournamentSubscription *nats.Subscription // Подписка на тему tournaments для обработки запросов. Евенты "tournaments.*".

// MustInit - инициализация NATS обработчика и подписка на тему tournaments.
func MustInit(ctx context.Context) {
	// Создаем соединение с NATS
	conn, err := nats.Connect(
		config.Get().NATS.URL,
		nats.Token(config.Get().NATS.Token),
		nats.MaxReconnects(-1), // always retry
		nats.ReconnectHandler(func(nc *nats.Conn) {
			fmt.Println("Reconnected to NATS server:", nc.ConnectedUrl())
			logger.Get().Info(fmt.Sprintln("Reconnected to NATS server:", nc.ConnectedUrl()))
		}),
		nats.DisconnectHandler(func(nc *nats.Conn) {
			fmt.Println("Disconnected from NATS server")
			logger.Get().Info(fmt.Sprintln("Disconnected from NATS server"))
		}),
	)
	if err != nil {
		panic(err)
	}
	NatsConn = conn

	// Подписываемся на тему tournaments для обработки запросов. Евенты "tournaments.*".
	sub, err := NatsConn.Subscribe("tournaments.*", cbListener)
	if err != nil {
		panic(err)
	}
	TournamentSubscription = sub

	// Тест соединения с NATS
	resp, err := NatsConn.Request("tournaments.ping", nil, 50*time.Millisecond)
	if err != nil {
		panic(err)
	}
	if string(resp.Data) != "pong" {
		panic("nats ping failed")
	}

	js, _ := jetstream.New(NatsConn)

	// Создаем поток для спинов турниров
	streamName := "spins"

	// Создаем jetstream поток для спинов турниров
	stream, err := js.CreateOrUpdateStream(ctx, jetstream.StreamConfig{
		Name:     streamName,
		Subjects: []string{"spins"},
		MaxBytes: 10 * 1024 * 1024, // 100 MB
		MaxAge:   1 * time.Hour,
	})
	if err != nil {
		panic(err)
	}

	// Создаем jetstream потребителя для спинов турниров
	cons, err := stream.CreateOrUpdateConsumer(ctx, jetstream.ConsumerConfig{
		Durable: "Tournaments",
		AckWait: 5 * time.Second,
	})
	if err != nil {
		panic(err)
	}

	logger.Get().Info("Stream created", slog.String("stream", streamName), slog.String("consumer", "Tournaments"))

	// Подписываемся на тему spins для обработки спинов турниров
	SpinsConsumer, err = cons.Consume(func(msg jetstream.Msg) {
		defer func() {
			if rec := recover(); rec != nil {
				fmt.Fprintf(os.Stderr, "recover %s", fmt.Sprintf("%v", rec))
				fmt.Fprintf(os.Stderr, "tracelog %s", string(debug.Stack()))
				logger.Get().Error("NATS", slog.String("recover", fmt.Sprintf("%v", rec)))
				logger.Get().Error("NATS", slog.String("tracelog", string(debug.Stack())))
			}
		}()

		defer msg.Ack()
		spin := natsmsg.SpinMessage{}
		logger.Get().Info("spin", slog.String("subject", msg.Subject()))
		logger.Get().Info("spin", slog.String("data", string(msg.Data())))
		err := json.Unmarshal(msg.Data(), &spin)
		if err != nil {
			logger.Get().Error("Error unmarshalling message", slog.String("error", err.Error()))
			return
		}
		for _, t := range service.ActiveTournaments {
			if t.ProcessSpin(spin.UserID, spin.Currency, spin.GameSystem, spin.GameID, spin.Bet, spin.Win, spin.SpinTime) {
				logger.Get().Info("Spin processed")
				break
			}
		}
	})
	if err != nil {
		panic(err)
	}
}

// Stop - остановка NATS обработчика и закрытие соединения.
// Вызывается при завершении работы приложения.
// Останавливает обработчик спинов турниров и отписывается от темы tournaments.
// Закрывает соединение с NATS.
func Stop() {
	if SpinsConsumer != nil {
		SpinsConsumer.Stop()
	}
	if TournamentSubscription != nil {
		TournamentSubscription.Unsubscribe()
	}
	if NatsConn != nil {
		NatsConn.Drain()
	}
}

// cbListener - обработчик входящих сообщений из NATS.
// Обрабатывает входящие сообщения из NATS и отправляет ответы на запросы.
// Вызывается при получении сообщения из NATS.
// Обрабатывает запросы на получиние информации о турнирах, модификации турниров и.т.д.
func cbListener(msg *nats.Msg) {

	// Обработка паники
	defer func() {
		if rec := recover(); rec != nil {
			fmt.Fprintf(os.Stderr, "recover %s", fmt.Sprintf("%v", rec))
			fmt.Fprintf(os.Stderr, "tracelog %s", string(debug.Stack()))
			logger.Get().Error("NATS", slog.String("recover", fmt.Sprintf("%v", rec)))
			logger.Get().Error("NATS", slog.String("tracelog", string(debug.Stack())))
		}
	}()

	// Получаем запрос из темы
	request := msg.Subject[12:]

	// Логируем запрос
	logger.Get().Info("request", slog.String("subject", msg.Subject))
	logger.Get().Info("request", slog.String("data", string(msg.Data)))

	// Обрабатываем запрос
	switch request {
	case "ping": // Тест соединения с NATS
		msg.Respond([]byte("pong"))
	case "active-tournaments": // Получение активных турниров
		callbacks.ActiveTournaments(msg)
	case "pending-tournaments": // Получение ожидающих наград турниров
		callbacks.PendingTournaments(msg)
	case "completed-tournaments": // Получение завершенных турниров
		callbacks.CompletedTournaments(msg)
	case "get-tournament": // Получение информации о турнире
		callbacks.GetTournament(msg)
	case "get-values": // Получение таблицы лидеров турнира
		callbacks.GetWinners(msg)
	case "get-prizes": // Получение таблицы призов турнира
		callbacks.GetPrizes(msg)
	case "get-description": // Получение описания турнира
		callbacks.GetDescription(msg)
	case "reload-tournaments": // Перезагрузка всех турниров
		callbacks.ReloadTournaments(msg)
	case "reload-tournament": // Перезагрузка турнира
		callbacks.ReloadTournament(msg)
	case "unload-tournament": // Выгрузка турнира
		callbacks.UnloadTournament(msg)
	case "set-state": // Установка состояния турнира
		callbacks.TournamentSetState(msg)
	case "pending-award": // Получение ожидающих наград турниров
		callbacks.PendingAwardTournaments(msg)
	case "user-participates": // Проверка участия пользователя в турнире
		callbacks.UserParticipates(msg)
	case "add-participant": // Добавление участника в турнир
		callbacks.AddParticipant(msg)
	case "game-tournament": // Получение турнира для игры
		callbacks.GetGameTournament(msg)
	case "user-position": // Получение позиции пользователя в турнире
		callbacks.GetUserPosition(msg)
	default:
		msg.Respond([]byte("unknown request"))
	}

}
