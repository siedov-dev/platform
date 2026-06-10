package test

import (
	"sync"
	"testing"
	"user-manager/internal/service"
)

func TestWallet(t *testing.T) {
	//init balace
	wallet := service.Wallet{}
	balance := wallet.GetBalance()
	cents := wallet.GetCents()
	if balance != 0 {
		t.Error("init balance error")
	}
	if cents != 0 {
		t.Error("init cent balance error")
	}
	// add 100 cents
	wallet.AddCents(100)
	balance = wallet.GetBalance()
	cents = wallet.GetCents()
	if balance != 1 {
		t.Errorf("add balance error expected 1 got %v", balance)
	}
	if cents != 100 {
		t.Errorf("cents balance error expected 100 got %v", cents)
	}
	//add 10 money
	wallet.AddMoney(10)
	balance = wallet.GetBalance()
	cents = wallet.GetCents()
	if balance != 11 {
		t.Errorf("add balance error expected 11 got %v", balance)
	}
	if cents != 1100 {
		t.Errorf("cents balance error expected 1100 got %v", cents)
	}

	//add 1 cent
	cents = wallet.AddCents(1)
	balance = wallet.GetBalance()
	if balance != 11.01 {
		t.Errorf("add balance error expected 11.01 got %v", balance)
	}
	if cents != 1101 {
		t.Errorf("cents balance error expected 1101 got %v", cents)
	}

	//rem 2 cents
	cents, err := wallet.RemCents(2)
	if err != nil {
		t.Error(err)
	}
	balance = wallet.GetBalance()
	if balance != 10.99 {
		t.Errorf("add balance error expected 10.99 got %v", balance)
	}
	if cents != 1099 {
		t.Errorf("cents balance error expected 1099 got %v", cents)
	}

	//rem 5 money
	balance, err = wallet.RemMoney(5)
	if err != nil {
		t.Error(err)
	}
	cents = wallet.GetCents()
	if balance != 5.99 {
		t.Errorf("add balance error expected 5.99 got %v", balance)
	}
	if cents != 599 {
		t.Errorf("cents balance error expected 599 got %v", cents)
	}

	//test err removing money
	cents, err = wallet.RemCents(600)
	if err == nil {
		t.Errorf("expected not enough balance error got %v", cents)
	}
	balance = wallet.GetBalance()
	cents = wallet.GetCents()
	if balance != 5.99 {
		t.Errorf("add balance error expected 5.99 got %v", balance)
	}
	if cents != 599 {
		t.Errorf("cents balance error expected 599 got %v", cents)
	}

	balance, err = wallet.RemMoney(6)
	if err == nil {
		t.Errorf("expected not enough balance error got %v", cents)
	}
	cents = wallet.GetCents()
	if balance != 5.99 {
		t.Errorf("add balance error expected 5.99 got %v", balance)
	}
	if cents != 599 {
		t.Errorf("cents balance error expected 599 got %v", cents)
	}

}

func TestWalletConcurrent(t *testing.T) {
	wg := sync.WaitGroup{}
	wg.Add(1000)
	wallet := service.Wallet{}
	balance := wallet.GetBalance()
	cents := wallet.GetCents()
	if balance != 0 {
		t.Error("init balance error")
	}
	if cents != 0 {
		t.Error("init cent balance error")
	}

	//add remove balancce
	for i := 0; i < 1000; i++ {
		go func() {
			defer wg.Done()
			wallet.AddMoney(10)
			wallet.RemMoney(10)
		}()
	}
	wg.Wait()

	balance = wallet.GetBalance()
	cents = wallet.GetCents()
	if balance != 0 {
		t.Errorf("expected balance 0, got %v", balance)
	}
	if cents != 0 {
		t.Errorf("expected balance 0, got %v", cents)
	}

	wg.Add(1000)
	//add balancce
	for i := 0; i < 1000; i++ {
		go func() {
			defer wg.Done()
			wallet.AddMoney(10)
		}()
	}
	wg.Wait()

	balance = wallet.GetBalance()
	cents = wallet.GetCents()
	if balance != 10000 {
		t.Errorf("expected balance 10000, got %v", balance)
	}
	if cents != 1000000 {
		t.Errorf("expected balance 1000000, got %v", cents)
	}

	wg.Add(1000)
	//add balancce
	for i := 0; i < 1000; i++ {
		go func() {
			defer wg.Done()
			wallet.RemMoney(10)
		}()
	}
	wg.Wait()

	balance = wallet.GetBalance()
	cents = wallet.GetCents()
	if balance != 0 {
		t.Errorf("expected balance 0, got %v", balance)
	}
	if cents != 0 {
		t.Errorf("expected balance 0, got %v", cents)
	}

	wg.Add(1000)

	for i := 0; i < 1000; i++ {
		go func() {
			defer wg.Done()
			wallet.AddCents(10)
			wallet.RemCents(10)
		}()
	}
	wg.Wait()

	balance = wallet.GetBalance()
	cents = wallet.GetCents()
	if balance != 0 {
		t.Errorf("expected balance 0, got %v", balance)
	}
	if cents != 0 {
		t.Errorf("expected balance 0, got %v", cents)
	}
}
