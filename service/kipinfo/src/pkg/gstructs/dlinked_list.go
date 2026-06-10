package gstructs

import (
	"bytes"
	"fmt"
	"sync"
)

type DLinkedList[T any] struct {
	First     *DLinkedListEl[T]
	Last      *DLinkedListEl[T]
	length    uint64
	sizeLimit uint64
	mut       sync.RWMutex
}

type DLinkedListEl[T any] struct {
	Prev *DLinkedListEl[T]
	Next *DLinkedListEl[T]
	Val  T
}

func (e *DLinkedListEl[T]) Add(v T) *DLinkedListEl[T] {
	if e.Next == nil {
		el := &DLinkedListEl[T]{
			Val:  v,
			Prev: e,
		}
		e.Next = el
		return e.Next
	} else {
		return e.Next.Add(v)
	}
}

// Stringer
func (dl *DLinkedList[T]) String() string {
	dl.mut.RLock()
	defer dl.mut.RUnlock()

	buf := bytes.Buffer{}
	el := dl.First
	for el != nil {
		buf.Write([]byte(fmt.Sprintf("%v->", el.Val)))
		el = el.Next
	}
	return buf.String()
}

// Add values to the end of the list
func (dl *DLinkedList[T]) Push(vl ...T) {
	dl.mut.Lock()
	defer dl.mut.Unlock()

	for _, v := range vl {
		if dl.First == nil {
			el := DLinkedListEl[T]{
				Val: v,
			}
			dl.First = &el
			dl.Last = &el
		} else {
			dl.Last = dl.Last.Add(v)
		}

		dl.length++

		if dl.sizeLimit > 0 && dl.length > dl.sizeLimit {
			dl.fpop()
		}
	}
}

// Add values at the beginning
func (dl *DLinkedList[T]) FPush(vl ...T) {
	dl.mut.Lock()
	defer dl.mut.Unlock()

	for _, v := range vl {
		if dl.First == nil {
			el := DLinkedListEl[T]{
				Val: v,
			}
			dl.First = &el
			dl.Last = &el
		} else {
			el := DLinkedListEl[T]{
				Val: v,
			}
			el.Next = dl.First
			dl.First.Prev = &el
			dl.First = &el
		}

		dl.length++

		if dl.sizeLimit > 0 && dl.length > dl.sizeLimit {
			dl.pop()
		}

	}
}

// Pop last value from the end of the list
func (dl *DLinkedList[T]) Pop() (T, bool) {
	dl.mut.Lock()
	defer dl.mut.Unlock()

	return dl.pop()
}

func (dl *DLinkedList[T]) pop() (T, bool) {

	// if empty return default value
	if dl.length == 0 {
		var df T
		return df, false
	}
	v := dl.Last.Val

	dl.Last = dl.Last.Prev
	if dl.Last != nil {
		dl.Last.Next.Prev = nil
		dl.Last.Next = nil
	}
	dl.length--
	if dl.length == 0 {
		dl.First = nil
		dl.Last = nil
	}
	return v, true
}

// Pop last value from the start of the list
func (dl *DLinkedList[T]) FPop() (T, bool) {
	dl.mut.Lock()
	defer dl.mut.Unlock()

	return dl.fpop()
}

// Pop last value from the start of the list
func (dl *DLinkedList[T]) fpop() (T, bool) {

	// if empty return default value
	if dl.length == 0 {
		var df T
		return df, false
	}
	v := dl.First.Val

	dl.First = dl.First.Next
	if dl.First != nil {
		dl.First.Prev.Next = nil
		dl.First.Prev = nil
	}
	dl.length--
	if dl.length == 0 {
		dl.First = nil
		dl.Last = nil
	}
	return v, true
}

// get first list value
func (dl *DLinkedList[T]) GetFirst() (T, bool) {
	dl.mut.RLock()
	defer dl.mut.RUnlock()

	if dl.length == 0 {
		var df T
		return df, false
	}
	return dl.First.Val, true
}

// get last list value
func (dl *DLinkedList[T]) GetLast() (T, bool) {
	dl.mut.RLock()
	defer dl.mut.RUnlock()

	if dl.length == 0 {
		var df T
		return df, false
	}
	return dl.Last.Val, true
}

// Return all list values in slice
func (dl *DLinkedList[T]) ToSlice() []T {
	dl.mut.RLock()
	defer dl.mut.RUnlock()

	s := make([]T, 0, dl.length)
	el := dl.First
	for el != nil {
		s = append(s, el.Val)
		el = el.Next
	}
	return s
}

// Return all list values in slice and clear the list
func (dl *DLinkedList[T]) PopSlice() []T {
	dl.mut.Lock()
	el := dl.First
	dl.First = nil
	dl.Last = nil
	dl.length = 0
	dl.mut.Unlock()

	s := make([]T, 0, dl.length)
	for el != nil {
		s = append(s, el.Val)
		e := el.Next
		el.Prev = nil
		el.Next = nil
		el = e
	}
	return s
}

// Get value at index
func (dl *DLinkedList[T]) At(idx uint64) (T, bool) {
	dl.mut.RLock()
	defer dl.mut.RUnlock()

	if idx >= dl.length {
		var df T
		return df, false
	}

	el := dl.First
	// if index is closer to the end
	if idx <= dl.length-idx {
		for i := uint64(0); i < idx; i++ {
			el = el.Next
		}
	} else {
		//reverse search
		el = dl.Last
		for i := dl.length - 1; i > idx; i-- {
			el = el.Prev
		}
	}
	return el.Val, true
}

func (dl *DLinkedList[T]) Lenght() uint64 {
	dl.mut.RLock()
	defer dl.mut.RUnlock()

	return dl.length
}

// Set list size limit. 0 - unlimited
func (dl *DLinkedList[T]) SetSizeLimit(l uint64) {
	dl.mut.RLock()
	defer dl.mut.RUnlock()

	dl.sizeLimit = l

	// cut current list
	if l > 0 && l < dl.length {
		el := dl.First
		// if index is closer to the end
		if l <= dl.length-l {
			for i := uint64(0); i < l-1; i++ {
				el = el.Next
			}
		} else {
			//reverse search
			el = dl.Last
			for i := dl.length - 1; i > l-1; i-- {
				el = el.Prev
			}
		}
		dl.Last = el
		if el.Next != nil {
			el.Next.Prev = nil
			el.Next = nil
		}
		dl.length = l
	}
}

// Search list
// returns el when func returns true
func (dl *DLinkedList[T]) Find(filter func(v T) bool) (T, bool) {
	dl.mut.RLock()
	defer dl.mut.RUnlock()

	el := dl.First
	for el != nil {
		if filter(el.Val) {
			return el.Val, true
		}
		el = el.Next
	}

	var nf T
	return nf, false
}

// Search list
// returns all elements that fit func
func (dl *DLinkedList[T]) FindAll(filter func(v T) bool) []T {
	dl.mut.RLock()
	defer dl.mut.RUnlock()

	found := make([]T, 0)

	el := dl.First
	for el != nil {
		if filter(el.Val) {
			found = append(found, el.Val)
		}
		el = el.Next
	}

	return found
}

func (dl *DLinkedList[T]) Clear() {
	dl.mut.Lock()
	el := dl.First
	dl.First = nil
	dl.Last = nil
	dl.length = 0
	dl.mut.Unlock()

	for el != nil {
		e := el.Next
		el.Prev = nil
		el.Next = nil
		el = e
	}
}
