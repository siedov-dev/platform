package natsbroker

import (
	"context"
	"fmt"
	"kreferrals/internal/config"
	"kreferrals/internal/logger"
	"kreferrals/internal/natsbroker/callbacks"
	"kreferrals/internal/natsbroker/userevents"
	"log/slog"
	"os"
	"runtime/debug"
	"time"

	"github.com/nats-io/nats.go"
	"github.com/nats-io/nats.go/jetstream"
)

var UserEventsConsumer jetstream.ConsumeContext // Обработчик для получения данных из NATS о событиях пользователей. Евенты "userevent.*".
var NatsConn *nats.Conn                         // Соединение с NATS
var ReferralSubscription *nats.Subscription     // Подписка на тему referrals для обработки запросов. Евенты "referrals.*".

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

	// Подписываемся на тему tournaments для обработки запросов. Евенты "referrals.*".
	sub, err := NatsConn.Subscribe("referrals.*", cbListener)
	if err != nil {
		panic(err)
	}
	ReferralSubscription = sub

	// Тест соединения с NATS
	resp, err := NatsConn.Request("referrals.ping", nil, 50*time.Millisecond)
	if err != nil {
		panic(err)
	}
	if string(resp.Data) != "pong" {
		panic("nats ping failed")
	}

	js, _ := jetstream.New(NatsConn)

	// Создаем поток для событий пользователей
	streamName := "userevents"

	// Создаем jetstream поток для событий пользователей
	stream, err := js.CreateOrUpdateStream(ctx, jetstream.StreamConfig{
		Name: streamName,
		Subjects: []string{
			"userevents.first-dep",
			"userevents.money-spent",
			"userevents.achievement-completed",
			"userevents.registered",
		},
		MaxBytes: 100 * 1024 * 1024, // 100 MB
		MaxAge:   1 * time.Hour,
	})
	if err != nil {
		panic(err)
	}

	// Создаем jetstream потребителя для событий пользователей
	cons, err := stream.CreateOrUpdateConsumer(ctx, jetstream.ConsumerConfig{
		Durable: "referrals",
		AckWait: 5 * time.Second,
	})
	if err != nil {
		panic(err)
	}

	logger.Get().Info("Stream created", slog.String("stream", streamName), slog.String("consumer", "Tournaments"))

	// Подписываемся
	UserEventsConsumer, err = cons.Consume(func(msg jetstream.Msg) {
		defer func() {
			if rec := recover(); rec != nil {
				fmt.Fprintf(os.Stderr, "recover %s", fmt.Sprintf("%v", rec))
				fmt.Fprintf(os.Stderr, "tracelog %s", string(debug.Stack()))
				logger.Get().Error("NATS", slog.String("recover", fmt.Sprintf("%v", rec)))
				logger.Get().Error("NATS", slog.String("tracelog", string(debug.Stack())))
			}
		}()

		defer msg.Ack()
		logger.Get().Info("userevents", slog.String("subject", msg.Subject()))
		logger.Get().Info("userevents", slog.String("data", string(msg.Data())))

		// Обрабатываем входящие сообщения из NATS
		switch msg.Subject() {
		case "userevents.first-dep": // Событие первого депозита пользователя
			userevents.FirstDepUserEvent(msg)
		case "userevents.achievement-completed": // Событие завершения достижения пользователя
			userevents.AchiveDoneUserEvent(msg)
		case "userevents.money-spent": // Событие потраченных денег пользователем
			userevents.MoneySpentUserEvent(msg)
		case "userevents.registered": // Событие регистрации пользователя
			userevents.UserRegistered(msg)
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
	if UserEventsConsumer != nil {
		UserEventsConsumer.Stop()
	}
	if ReferralSubscription != nil {
		ReferralSubscription.Unsubscribe()
	}
	if NatsConn != nil {
		NatsConn.Drain()
	}
}

// cbListener - обработчик входящих сообщений из NATS.
// Обрабатывает входящие сообщения из NATS и отправляет ответы на запросы.
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

	// Логируем запрос
	logger.Get().Info("request", slog.String("subject", msg.Subject))
	logger.Get().Info("request", slog.String("data", string(msg.Data)))

	// Обрабатываем запрос
	switch msg.Subject {
	case "referrals.ping": // Тест соединения с NATS
		callbacks.Ping(msg)
	case "referrals.click": // Обработчик клика по реферальной ссылке
		callbacks.Click(msg)
	case "referrals.transferRefMoney": // Перевод реферальных денег
		callbacks.TransferRefMoney(msg)
	case "referrals.transferAccumMoney": // Перевод накопительных денег
		callbacks.TransferAccumulatedMoney(msg)
	case "referrals.transferAllRefAccumMoney": // Перевод всех накопленных средств всех рефералов
		callbacks.TransferAllRefAccumMoney(msg)
	case "referrals.getRefCode": // Получение реферального кода пользователя
		callbacks.GetRefCode(msg)
	case "referrals.getRefConfig": // Получение реферальных параметров пользователя
		callbacks.GetRefConfig(msg)
	case "referrals.getAchievementRewards": // Получение наград за достижения по валюте
		callbacks.GetAchievementRewards(msg)
	case "referrals.getReferralUsers": // Получение наград за достижения по валюте
		callbacks.GetReferralUsers(msg)
	case "referrals.getReferralUsersCountWithDeps": // Получение количества рефералов c депозитами
		callbacks.GetReferralUsersCountwithDeps(msg)
	case "referrals.getReferralEvents": // Получение событий рефералов
		callbacks.GetReferralEvents(msg)
	case "referrals.reload": // Перезагрузка сервиса
		callbacks.ReloadService(msg)
	default:
		msg.Respond([]byte("unknown request"))
	}

}
