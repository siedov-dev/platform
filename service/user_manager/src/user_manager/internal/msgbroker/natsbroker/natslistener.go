package natsbroker

import (
	"context"
	"fmt"
	"log/slog"
	"os"
	"runtime/debug"
	"time"
	"user-manager/internal/config"
	"user-manager/internal/logger"
	"user-manager/internal/msgbroker/natsbroker/callbacks"

	"github.com/nats-io/nats.go"
)

var NatsConn *nats.Conn                        // Соединение с NATS
var UserManagerSubscription *nats.Subscription // Подписка на тему usermanager для обработки запросов. События "usermanager.*".

// MustInit - инициализация NATS обработчика и подписка на тему usermanager.
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

	// Подписываемся на тему usermanager для обработки запросов. События "usermanager.*".
	sub, err := NatsConn.Subscribe("usermanager.*", cbListener)
	if err != nil {
		panic(err)
	}
	UserManagerSubscription = sub

	// Тест соединения с NATS
	resp, err := NatsConn.Request("usermanager.ping", nil, 50*time.Millisecond)
	if err != nil {
		panic(err)
	}
	if string(resp.Data) != "pong" {
		panic("nats ping failed")
	}

}

// Stop - остановка NATS обработчика и закрытие соединения.
// Вызывается при завершении работы приложения.
// Останавливает обработчик спинов турниров и отписывается от темы tournaments.
// Закрывает соединение с NATS.
func Stop() {
	if UserManagerSubscription != nil {
		UserManagerSubscription.Unsubscribe()
	}
	if NatsConn != nil {
		NatsConn.Drain()
	}
}

// cbListener - обработчик входящих сообщений из NATS.
// Обрабатывает входящие сообщения из NATS и отправляет ответы на запросы.
// Вызывается при получении сообщения из NATS.
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
	logger.Get().Debug("request", slog.String("subject", msg.Subject))
	logger.Get().Debug("request", slog.String("data", string(msg.Data)))

	// Обрабатываем запрос
	switch msg.Subject {
	case "usermanager.ping": // Тест соединения с NATS
		callbacks.Ping(msg)
	case "usermanager.change_ref_money": // Изменение реферальных средств
		callbacks.ChangeRefMoney(msg)
	case "usermanager.transfer_ref_money": // Перевод реферальных средств на основной баланс
		callbacks.TransferRefMoney(msg)
	case "usermanager.change_bonus_money": // Изменение накопительных реферальных средств
		callbacks.ChangeBonusMoney(msg)
	case "usermanager.transfer_accum_money": // Перевод накопительных средств на основной баланс
		callbacks.TransferAccumMoney(msg)
	case "usermanager.transfer_all_accum_money": // Перевод всех накопительных средств на основной баланс
		callbacks.TransferAllAccumMoney(msg)
	case "usermanager.get_balance": // Получение баланса пользователя
		callbacks.GetBalance(msg)
	case "usermanager.add_money": // Добавление денег на счет пользователя
		callbacks.AddMoney(msg)
	case "usermanager.rem_money": // Снятие денег со счета пользователя
		callbacks.RemMoney(msg)
	case "usermanager.get_locked_money": // Получение заблокированных средств пользователя
		callbacks.GetLockedMoney(msg)
	case "usermanager.add_locked_money": // Добавление заблокированных средств
		callbacks.AddLockedMoney(msg)
	case "usermanager.rem_locked_money": // Снятие заблокированных средств
		callbacks.RemLockedMoney(msg)
	case "usermanager.set_locked_money": // Установка заблокированных средств
		callbacks.SetLockedMoney(msg)
	case "usermanager.get_total_win": // Получение общей суммы выигрыша
		callbacks.GetTotalWin(msg)
	case "usermanager.get_total_bet": // Получение общей суммы ставок
		callbacks.GetTotalBet(msg)
	case "usermanager.add_total_win": // Добавление общей суммы выигрыша
		callbacks.AddTotalWin(msg)
	case "usermanager.add_total_bet": // Добавление общей суммы ставок
		callbacks.AddTotalBet(msg)
	default:
		msg.Respond([]byte("unknown request"))
	}

}
