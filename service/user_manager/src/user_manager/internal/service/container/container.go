// Package container предоставляет контейнер зависимостей для брокера сообщений
package container

import (
	"sync"
	"user-manager/internal/msgbroker"
)

var mx sync.RWMutex

// broker хранит экземпляр брокера сообщений для всего приложения
var broker msgbroker.MsgBroker

// SetMsgBroker устанавливает экземпляр брокера сообщений
func SetMsgBroker(b msgbroker.MsgBroker) {
	mx.Lock()
	defer mx.Unlock()
	broker = b
}

// GetMsgBroker возвращает текущий экземпляр брокера сообщений
func GetMsgBroker() msgbroker.MsgBroker {
	mx.RLock()
	defer mx.RUnlock()
	if broker == nil {
		panic("message broker is not set")
	}
	return broker
}
