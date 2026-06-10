package test

import (
	"context"
	"fmt"
	"testing"
	"time"
	"user-manager/pkg/gstructs"
)

func TestTimeoutMap(t *testing.T) {
	mp := gstructs.NewTimeoutMap[int, int](2, context.Background())
	mp.SetClearFunc(func(k int, v int) {
		fmt.Println("Clearing", k, v)
	})
	mp.Set(1, 1)
	mp.Set(2, 2)
	fmt.Println(mp)
	fmt.Println(mp.Length())
	mp.Set(1, 1)
	fmt.Println(mp)
	fmt.Println(mp.Length())
	mp.Set(1, 1)
	time.Sleep(2 * time.Second)
	mp.Set(3, 3)
	fmt.Println(mp)
	fmt.Println(mp.Length())
	mp.Get(2)
	fmt.Println(mp)
	fmt.Println(mp.Length())
	time.Sleep(2 * time.Second)
	mp.Get(2)
	//mp.Update()
	fmt.Println(mp)
	fmt.Println(mp.Length())
	time.Sleep(2 * time.Second)
	//mp.Update()
	fmt.Println(mp)
	fmt.Println(mp.Length())
	mp.Set(3, 3)
	fmt.Println(mp)
	fmt.Println(mp.Length())
	mp.Clear()
}
