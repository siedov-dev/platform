package natsbroker

import (
	"context"
	"fmt"
	"kbonus/internal/config"
	"kbonus/internal/logger"
	"log/slog"
	"time"

	"github.com/nats-io/nats.go"
	"github.com/nats-io/nats.go/jetstream"
)

var UserEventsConsumer jetstream.ConsumeContext // Обработчик для получения данных из NATS о событиях пользователей. Евенты "userevent.*".
var NatsConn *nats.Conn                         // Соединение с NATS
var BonusSubscription *nats.Subscription        // Подписка на тему bonus для обработки запросов. Евенты "bonus.*".

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

	// Подписываемся на тему bonus для обработки запросов. Евенты "bonus.*".
	sub, err := NatsConn.Subscribe("bonus.*", cbListener)
	if err != nil {
		panic(err)
	}
	BonusSubscription = sub

	// Тест соединения с NATS
	startTime := time.Now()
	resp, err := NatsConn.Request("bonus.ping", nil, 50*time.Millisecond)
	duration := time.Since(startTime)
	logger.Get().Info("ping request duration", slog.Duration("duration", duration))

	if err != nil {
		panic(err)
	}
	if string(resp.Data) != "pong" {
		panic("nats ping failed")
	}

	// js, _ := jetstream.New(NatsConn)

	// // Создаем поток для событий пользователей
	// streamName := "userevents"

	// // Создаем jetstream поток для событий пользователей
	// stream, err := js.CreateOrUpdateStream(ctx, jetstream.StreamConfig{
	// 	Name: streamName,
	// 	Subjects: []string{
	// 		"userevents.first-dep",
	// 		"userevents.registered",
	// 	},
	// 	MaxBytes: 100 * 1024 * 1024, // 100 MB
	// 	MaxAge:   1 * time.Hour,
	// })
	// if err != nil {
	// 	panic(err)
	// }

	// // Создаем jetstream потребителя для событий пользователей
	// cons, err := stream.CreateOrUpdateConsumer(ctx, jetstream.ConsumerConfig{
	// 	Durable: "bonus",
	// 	AckWait: 5 * time.Second,
	// })
	// if err != nil {
	// 	panic(err)
	// }

	// logger.Get().Info("Stream created", slog.String("stream", streamName), slog.String("consumer", "bonus"))

	// // Подписываемся
	// UserEventsConsumer, err = cons.Consume(func(msg jetstream.Msg) {
	// 	defer func() {
	// 		if rec := recover(); rec != nil {
	// 			fmt.Fprintf(os.Stderr, "recover %s", fmt.Sprintf("%v", rec))
	// 			fmt.Fprintf(os.Stderr, "tracelog %s", string(debug.Stack()))
	// 			logger.Get().Error("NATS", slog.String("recover", fmt.Sprintf("%v", rec)))
	// 			logger.Get().Error("NATS", slog.String("tracelog", string(debug.Stack())))
	// 		}
	// 	}()

	// 	defer msg.Ack()
	// 	logger.Get().Info("userevents", slog.String("subject", msg.Subject()))
	// 	logger.Get().Info("userevents", slog.String("data", string(msg.Data())))

	// 	// Обрабатываем входящие сообщения из NATS
	// 	switch msg.Subject() {
	// 	case "userevents.first-dep": // Событие первого депозита пользователя
	// 		userevents.FirstDepUserEvent(msg)
	// 	case "userevents.registered": // Событие регистрации пользователя
	// 		userevents.UserRegistered(msg)
	// 	}

	// })
	// if err != nil {
	// 	panic(err)
	// }
}

// Stop - остановка NATS обработчика и закрытие соединения.
// Вызывается при завершении работы приложения.
// Останавливает обработчик спинов турниров и отписывается от темы tournaments.
// Закрывает соединение с NATS.
func Stop() {
	if UserEventsConsumer != nil {
		UserEventsConsumer.Stop()
	}
	if BonusSubscription != nil {
		BonusSubscription.Unsubscribe()
	}
	if NatsConn != nil {
		NatsConn.Drain()
	}
}
