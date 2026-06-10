package gstructs

import (
	"bytes"
	"context"
	"fmt"
	"sync"
	"time"
)

// TimeoutMap Интерфейс карты с таймаутом для автоматического удаления устаревших элементов.
// Метод Set добавляет или обновляет значение по ключу.
// Метод Get возвращает значение по ключу и обновляет таймаут ключа.
// Метод Length возвращает количество элементов в карте.
// Метод Update удаляет элементы с истекшим таймаутом из карты.
// Метод SetClearFunc устанавливает функцию, вызываемую при удалении элементов.
// Метод SetUpdateValueFunc задает функцию для вызова при обновлении значения.
// Метод Clear очищает карту, удаляя все элементы.
// Метод SetPostponeLimit задает лимит откладываний обновления таймаута элемента.
// Метод GetMap возвращает карту элементов с текущими значениями.
// Метод GetTimeouts возвращает карту таймаутов всех ключей.
type TimeoutMap[K comparable, V any] interface {
	// Set Добавляет новый элемент в карту
	// Если ключ уже существует, обновляет значение и возвращает false.
	// В противном случае добавляет новый элемент и возвращает true
	// Время последнего обновления устанавливается в текущее время
	Set(key K, val V) bool

	// Get Возвращает значение по ключу и булево значение,
	// указывающее существует ли ключ.
	// Обновляет время последнего обновления ключа
	Get(key K) (V, bool)

	// Length Возвращает количество элементов в карте
	Length() uint64

	// Update Удаляет все элементы, время последнего обновления которых
	// старше периода очистки
	Update()

	// SetClearFunc Устанавливает функцию, которая будет вызвана
	// при удалении элемента из карты
	SetClearFunc(func(K, V))

	// SetUpdateValueFunc Устанавливает функцию, которая будет вызвана
	// при обновлении элемента в карте
	SetUpdateValueFunc(func(K, V))

	// Clear Удаляет все элементы из карты
	Clear()

	// SetPostponeLimit Устанавливает максимальное количество раз,
	// которое значение может быть отложено при обновлении
	// перед удалением
	SetPostponeLimit(int16)

	// GetMap Возвращает карту со значениями ключей
	GetMap() map[K]V

	// GetTimeouts Возвращает ключи с временем их последнего обновления
	GetTimeouts() map[K]int64
}

// NewTimeoutMap создает новую таймаут карту с указанным периодом очистки и контекстом для управления жизненным циклом.
func NewTimeoutMap[K comparable, V any](clear_period int64, ctx context.Context) TimeoutMap[K, V] {

	tm := &timeoutMap[K, V]{
		mp:          make(map[K]*timeoutMapEl[K, V]),
		clearPeriod: clear_period,
	}
	tm.ticker = time.NewTicker(time.Second * time.Duration(clear_period))

	go func(ctx context.Context) {
		for {
			select {
			case <-ctx.Done():
				tm.ticker.Stop()
				return
			case t := <-tm.ticker.C:
				_ = t
				//logger.Get().Info("transaction storage", slog.String("tick", t.String()))
				tm.Update()
			}
		}
	}(ctx)

	return tm
}

// Структура таймаут карты, реализующая интерфейс TimeoutMap
type timeoutMap[K comparable, V any] struct {
	first         *timeoutMapEl[K, V]       // Первый элемент в списке
	last          *timeoutMapEl[K, V]       // Последний элемент в списке
	length        uint64                    // Количество элементов
	mut           sync.RWMutex              // Мьютекс для синхронизации
	mp            map[K]*timeoutMapEl[K, V] // Карта для быстрого доступа по ключу
	clearPeriod   int64                     // Период очистки в секундах
	postponeLimit int16                     // Лимит откладывания элемента
	onClear       func(K, V)                // Функция, вызываемая при удалении элемента
	onUpdateValue func(K, V)                // Функция, вызываемая при обновлении элемента
	ticker        *time.Ticker              // Таймер для периодической очистки
}

// Элемент таймаут карты, представляющий узел в двусвязном списке
type timeoutMapEl[K comparable, T any] struct {
	Prev          *timeoutMapEl[K, T] // Ссылка на предыдущий элемент
	Next          *timeoutMapEl[K, T] // Ссылка на следующий элемент
	Key           K                   // Ключ элемента
	Val           T                   // Значение элемента
	timeout       int64               // Время последнего обновления
	postponeCount int16               // Счетчик откладываний
}

// Stringer
func (dl *timeoutMap[K, V]) String() string {
	dl.mut.RLock()
	defer dl.mut.RUnlock()

	buf := bytes.Buffer{}
	el := dl.first
	for el != nil {
		buf.Write([]byte(fmt.Sprintf("%v->", el.Val)))
		el = el.Next
	}
	return buf.String()
}

// Set Add adds a new element to the map.
// If the key already exists, it will update the value and return false.
// Otherwise, it will add the new element and return true
// The timeout is set to the current time
func (dl *timeoutMap[K, V]) Set(key K, val V) bool {
	dl.mut.Lock()
	defer dl.mut.Unlock()

	now := time.Now().Unix()
	if el, ok := dl.mp[key]; ok {
		// call the update value function
		if dl.onUpdateValue != nil {
			defer dl.onUpdateValue(el.Key, el.Val)
		}

		el.Val = val
		el.Key = key

		if dl.postponeLimit > 0 {
			if el.postponeCount > dl.postponeLimit {
				return false
			}
			el.postponeCount++
		}

		el.timeout = now

		if dl.length == 1 || dl.last == el {
			return false
		}

		//move the element to the end of the list
		if el.Prev != nil {
			el.Prev.Next = el.Next
		}
		if el.Next != nil {
			el.Next.Prev = el.Prev
		}
		if dl.last == el {
			dl.last = el.Prev
		}
		if dl.first == el {
			dl.first = el.Next
		}
		el.Next = nil
		el.Prev = nil
		dl.length--
		if dl.length == 0 {
			dl.first = nil
			dl.last = nil
		}
		dl.push(el)

		return false
	}

	newEl := &timeoutMapEl[K, V]{}
	newEl.Val = val
	newEl.Key = key
	newEl.timeout = now
	dl.mp[key] = newEl
	dl.push(newEl)

	// call the update value function
	if dl.onUpdateValue != nil {
		dl.onUpdateValue(newEl.Key, newEl.Val)
	}

	return true
}

func (dl *timeoutMap[K, V]) Get(key K) (V, bool) {
	dl.mut.Lock()
	defer dl.mut.Unlock()

	now := time.Now().Unix()
	if el, ok := dl.mp[key]; ok {

		if dl.onUpdateValue != nil {
			defer dl.onUpdateValue(el.Key, el.Val)
		}

		if dl.postponeLimit > 0 {
			if el.postponeCount > dl.postponeLimit {
				return el.Val, true
			}
			el.postponeCount++
		}

		el.timeout = now

		if dl.length == 1 || dl.last == el {
			return el.Val, true
		}

		//move the element to the end of the list
		if el.Prev != nil {
			el.Prev.Next = el.Next
		}
		if el.Next != nil {
			el.Next.Prev = el.Prev
		}
		if dl.last == el {
			dl.last = el.Prev
		}
		if dl.first == el {
			dl.first = el.Next
		}
		el.Next = nil
		el.Prev = nil
		dl.length--
		if dl.length == 0 {
			dl.first = nil
			dl.last = nil
		}
		dl.push(el)

		return el.Val, true
	}

	var d V
	return d, false
}

// Add elements to the end of the list
func (dl *timeoutMap[K, V]) push(el *timeoutMapEl[K, V]) {
	if dl.first == nil {
		dl.first = el
		dl.last = el
	} else {
		el.Prev = dl.last
		el.Prev.Next = el
		dl.last = el
	}
	dl.length++
}

func (dl *timeoutMap[K, V]) Length() uint64 {
	dl.mut.RLock()
	defer dl.mut.RUnlock()

	return dl.length
}

func (dl *timeoutMap[K, V]) SetClearFunc(f func(K, V)) {
	dl.onClear = f
}

func (dl *timeoutMap[K, V]) SetUpdateValueFunc(f func(K, V)) {
	dl.onUpdateValue = f
}

func (dl *timeoutMap[K, V]) Update() {
	dl.mut.Lock()
	defer dl.mut.Unlock()

	now := time.Now().Unix()
	timeThreshold := now - dl.clearPeriod
	for el := dl.first; el != nil && el.timeout < timeThreshold; {
		//delete from map
		delete(dl.mp, el.Key)
		//delete el from the list
		dl.first = el.Next
		if el.Next != nil {
			el.Next.Prev = nil
		}
		el.Next = nil
		el.Prev = nil

		if dl.last == el {
			dl.last = nil
		}
		dl.length--

		if dl.onClear != nil {
			dl.onClear(el.Key, el.Val)
		}
		el = dl.first
	}
}

func (dl *timeoutMap[K, V]) Clear() {
	dl.mut.Lock()
	oldm := dl.mp
	dl.mp = make(map[K]*timeoutMapEl[K, V])
	dl.first = nil
	dl.last = nil
	dl.length = 0
	dl.mut.Unlock()

	for _, el := range oldm {
		el.Next = nil
		el.Prev = nil
		if dl.onClear != nil {
			dl.onClear(el.Key, el.Val)
		}
	}
}

func (dl *timeoutMap[K, V]) GetMap() map[K]V {
	dl.mut.RLock()
	defer dl.mut.RUnlock()
	m := make(map[K]V, len(dl.mp))
	for k, v := range dl.mp {
		m[k] = v.Val
	}
	return m
}

func (dl *timeoutMap[K, V]) GetTimeouts() map[K]int64 {
	dl.mut.RLock()
	defer dl.mut.RUnlock()
	m := make(map[K]int64, len(dl.mp))
	for k, v := range dl.mp {
		m[k] = v.timeout
	}
	return m
}

func (dl *timeoutMap[K, V]) SetPostponeLimit(limit int16) {
	dl.mut.Lock()
	defer dl.mut.Unlock()

	dl.postponeLimit = limit
}
