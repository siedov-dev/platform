package test

import (
	"context"
	"kbonus/internal/natsbroker"
	"kbonus/internal/natsbroker/natsuserwallet"
	"kbonus/internal/service/container"
	"kbonus/internal/utils/currencyid"
	"testing"
)

func TestNatsWallet(t *testing.T) {
	// Initialize the NATS connection
	ctx := context.Background()
	natsbroker.MustInit(ctx)
	container.SetNatsConnection(natsbroker.NatsConn)
	natsConn := container.GetNatsConnection()
	if natsConn == nil {
		t.Fatal("NATS connection is not initialized")
	}

	// Create a new NATS user wallet
	wallet := natsuserwallet.NewNatsUserWallet(12, currencyid.CurrencyID(1), natsConn)

	if wallet.GetCurrencyName() != "RUB" {
		t.Errorf("Expected currency name 'RUB', got '%s'", wallet.GetCurrencyName())
	}

	balance := wallet.GetBalance()
	wallet.AddMoney(100.0)
	if wallet.GetBalance() != balance+100.0 {
		t.Errorf("Expected balance to be %f after adding 100.0, got %f", balance+100.0, wallet.GetBalance())
	}
	wallet.RemMoney(50.0)
	if wallet.GetBalance() != balance+50.0 {
		t.Errorf("Expected balance to be %f after removing 50.0, got %f", balance+50.0, wallet.GetBalance())
	}
	wallet.RemMoney(50.0)
	if wallet.GetBalance() != balance {
		t.Errorf("Expected balance to be %f after removing 50.0, got %f", balance+50.0, wallet.GetBalance())
	}

	wallet.SetLockedMoney(200.0)
	if wallet.GetLockedMoney() != 200.0 {
		t.Errorf("Expected locked money to be 200.0, got %f", wallet.GetLockedMoney())
	}
	wallet.AddLockedMoney(100.0)
	if wallet.GetLockedMoney() != 300.0 {
		t.Errorf("Expected locked money to be 300.0 after adding 100.0, got %f", wallet.GetLockedMoney())
	}
	wallet.RemLockedMoney(50.0)
	if wallet.GetLockedMoney() != 250.0 {
		t.Errorf("Expected locked money to be 250.0 after removing 50.0, got %f", wallet.GetLockedMoney())
	}
	wallet.RemLockedMoney(300.0)
	if wallet.GetLockedMoney() != 0.0 {
		t.Errorf("Expected locked money to be 0.0 after removing 300.0, got %f", wallet.GetLockedMoney())
	}

	totalBet := wallet.GetTotalBet()
	wallet.AddTotalBet(100.0)
	if wallet.GetTotalBet() != totalBet+100.0 {
		t.Errorf("Expected total bet to be %f after adding 100.0, got %f", totalBet+100.0, wallet.GetTotalBet())
	}
	totalWin := wallet.GetTotalWin()
	wallet.AddTotalWin(50.0)
	if wallet.GetTotalWin() != totalWin+50.0 {
		t.Errorf("Expected total win to be %f after adding 50.0, got %f", totalWin+50.0, wallet.GetTotalWin())
	}

}
