package natsbroker

import (
	"context"
	"encoding/json"
	"log/slog"
	"time"
	"user-manager/internal/config"
	"user-manager/internal/logger"
	"user-manager/internal/msgbroker"

	"github.com/nats-io/nats.go"
	"github.com/nats-io/nats.go/jetstream"
)

// natsbroker представляет структуру для работы с брокером сообщений NATS.
// Содержит контекст выполнения, канал для сообщений, соединение NATS и интерфейс JetStream.
type natsbroker struct {
	ctx context.Context
	ch  chan msgbroker.Message
	nc  *nats.Conn
	js  jetstream.JetStream
}

// NewNatsBroker создаёт новый экземпляр брокера сообщений NATS с использованием JetStream и возвращает его.
// Принимает контекст выполнения для управления жизненным циклом брокера.
// В случае ошибки подключения или настройки стрима возвращает ошибку.
// Публикует сообщения в асинхронном режиме через внутренний канал.
func NewNatsBroker(ctx context.Context) (msgbroker.MsgBroker, error) {
	b := &natsbroker{
		ctx: ctx,
		ch:  make(chan msgbroker.Message, 100),
	}

	nc, err := nats.Connect(config.Get().NATS.URL, nats.Token(config.Get().NATS.Token))
	if err != nil {
		return nil, err
	}
	b.nc = nc

	js, _ := jetstream.New(nc)
	b.js = js

	// Публикуем в канал spins
	streamName := "spins"

	ctx_timeout, cancel := context.WithTimeout(context.Background(), 10*time.Second)
	defer cancel()

	//Создаем JetStream
	_, err = js.CreateOrUpdateStream(ctx_timeout, jetstream.StreamConfig{
		Name:     streamName,
		Subjects: []string{"spins"},
		MaxBytes: 10 * 1024 * 1024, // 100 MB
		MaxAge:   1 * time.Hour,
	})

	if err != nil {
		return nil, err
	}

	//Воркер собирает сообщения из канала и публикует в NATS
	go func(b *natsbroker) {
		for {
			select {
			case <-b.ctx.Done():
				return
			case m := <-b.ch:
				_, err = b.js.Publish(b.ctx, m.Subject(), []byte(m.Payload()))
				if err != nil {
					logger.Get().Error("NATS", slog.String("Error publishing data", err.Error()))
				}
			}
		}
	}(b)

	return b, nil
}

// Send отправляет сообщение типа msgbroker.Message в канал сообщений natsbroker.
// Возвращает ошибку в случае сбоя при отправке.
func (b *natsbroker) Send(m msgbroker.Message) error {
	b.ch <- m
	return nil
}

// Request отправляет запрос с заданной темой и данными, ожидая ответа в течение 5 секунд.
// Возвращает полученные данные и ошибку, если операция завершилась неудачно.
func (b *natsbroker) Request(subject string, data any) (any, error) {
	jsonData, err := json.Marshal(data)
	if err != nil {
		return nil, err
	}
	resp, err := b.nc.Request(subject, jsonData, 5*time.Second)
	if err != nil {
		return nil, err
	}
	return resp.Data, nil
}

// Close завершает работу брокера: закрывает соединение NATS и канал сообщений, если они существуют.
func (b *natsbroker) Close() {
	if b.ch != nil {
		b.nc.Close()
		close(b.ch)
	}
}
