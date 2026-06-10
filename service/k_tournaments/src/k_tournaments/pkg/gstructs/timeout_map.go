package gstructs

import (
	"bytes"
	"context"
	"fmt"
	"sync"
	"time"
)

type TimeoutMap[K comparable, V any] interface {
	// Add adds a new element to the map
	// If the key already exists, it will update the value
	// and return false
	// Otherwise, it will add the new element and return true
	// The timeout is set to the current time
	Set(key K, val V) bool

	// Get returns the value of the key and a boolean
	// indicating if the key exists
	// Updates the timeout of the key to the current time
	Get(key K) (V, bool)

	// Lenght returns the number of elements in the map
	Lenght() uint64

	// Update removes all elements that have a timeout
	// older than the clear period
	Update()

	// SetClearFunc sets the function that will be called
	// when an element is removed from the map
	SetClearFunc(func(K, V))

	// Removes all elements from the map
	Clear()

	// Set how many times a value could be posponed when update
	// before remove
	SetPostponeLimit(int16)

	// Get map with key values
	GetMap() map[K]V

	// get keys with last updated values
	GetTimeouts() map[K]int64
}

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
				tm.Clear()
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

type timeoutMap[K comparable, V any] struct {
	first         *timeoutMapEl[K, V]
	last          *timeoutMapEl[K, V]
	length        uint64
	mut           sync.RWMutex
	mp            map[K]*timeoutMapEl[K, V]
	clearPeriod   int64
	postponeLimit int16
	onClear       func(K, V)
	ticker        *time.Ticker
}

type timeoutMapEl[K comparable, T any] struct {
	Prev          *timeoutMapEl[K, T]
	Next          *timeoutMapEl[K, T]
	Key           K
	Val           T
	timeout       int64
	postponeCount int16
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

// Add adds a new element to the map
// If the key already exists, it will update the value
// and return false
// Otherwise, it will add the new element and return true
// The timeout is set to the current time
func (dl *timeoutMap[K, V]) Set(key K, val V) bool {
	dl.mut.Lock()
	defer dl.mut.Unlock()

	now := time.Now().Unix()
	if el, ok := dl.mp[key]; ok {
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

	return true
}

func (dl *timeoutMap[K, V]) Get(key K) (V, bool) {
	dl.mut.Lock()
	defer dl.mut.Unlock()

	now := time.Now().Unix()
	if el, ok := dl.mp[key]; ok {

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

// Add elemets to the end of the list
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

func (dl *timeoutMap[K, V]) Lenght() uint64 {
	dl.mut.RLock()
	defer dl.mut.RUnlock()

	return dl.length
}

func (dl *timeoutMap[K, V]) SetClearFunc(f func(K, V)) {
	dl.onClear = f
}

func (dl *timeoutMap[K, V]) Update() {
	dl.mut.Lock()
	defer dl.mut.Unlock()

	now := time.Now().Unix()
	timeThreshold := now - dl.clearPeriod
	for el := dl.first; el != nil && el.timeout < timeThreshold; {
		//delete from map
		delete(dl.mp, el.Key)
		//delete from list
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
