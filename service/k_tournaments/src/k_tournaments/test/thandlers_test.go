package test

import (
	"fmt"
	"ktournaments/internal/service/tournament/gamehandlers"
	"testing"
)

func TestMultiplierHander(t *testing.T) {
	// Create a new instance of the Multiplier game handler
	multiplierHandler := gamehandlers.NewMultiplier()

	// Define test cases
	tests := []struct {
		userID   int64
		currency string
		bet      float64
		win      float64
		expected float32
	}{
		{12, "USD", 100, 200, 2},
		{12, "EUR", 50, 150, 3},
		{12, "USD", 200, 300, 1},
		{12, "RUB", 10, 100, 10},
		{12, "RUB", 10, 85, 8},
		{12, "RUB", 10, 0, 0},
		{12, "RUB", 100, 500, 5},
	}

	for _, test := range tests {
		t.Run(fmt.Sprintf("UserID: %d, Currency: %s", test.userID, test.currency), func(t *testing.T) {
			result := multiplierHandler.WinPoints(test.userID, test.currency, test.bet, test.win)
			if result != test.expected {
				t.Errorf("Expected %f but got %f", test.expected, result)
			}
		})
	}
}

func TestWinKHander(t *testing.T) {
	// Create a new instance of the Multiplier game handler
	params := `{
	"RUB":[
	{"amount":"20","mult":"1"},
	{"amount":"50","mult":"1.5"},
	{"amount":"100","mult":"2"},
	{"amount":"300","mult":"3"},
	{"amount":"500","mult":"4"}],
	"UAH":[
	{"amount":"10","mult":"1"},
	{"amount":"20","mult":"1.5"},
	{"amount":"100","mult":"2"}],
	"USD":[{"amount":"1","mult":"1"}],
	"EUR":[{"amount":"1","mult":"1"}],
	"INR":[
	{"amount":"20","mult":"1"},
	{"amount":"100","mult":"2"}],
	"USDT":[
	{"amount":"1","mult":"1"},
	{"amount":"20","mult":"2"}],
	"KZT":[{"amount":"50","mult":"1"}]}`

	multiplierHandler, err := gamehandlers.NewWinK(params)
	if err != nil {
		t.Fatalf("Failed to create BetX handler: %v", err)
	}

	// Define test cases
	tests := []struct {
		userID   int64
		currency string
		bet      float64
		win      float64
		expected float32
	}{
		{12, "RUB", 10, 100, 2},
		{12, "RUB", 10, 85, 1.5},
		{12, "RUB", 10, 0, 0},
		{12, "RUB", 10, 10, 0},
		{12, "RUB", 10, 25, 1},
		{12, "RUB", 10, 20, 1},
		{12, "RUB", 10, 300, 3},
		{12, "RUB", 10, 355, 3},
		{12, "RUB", 10, 500, 4},
		{12, "RUB", 10, 1500, 4},
		{12, "UAH", 10, 20, 1.5},
		{12, "UAH", 10, 5, 0},
	}

	for _, test := range tests {
		t.Run(fmt.Sprintf("UserID: %d, Currency: %s", test.userID, test.currency), func(t *testing.T) {
			result := multiplierHandler.WinPoints(test.userID, test.currency, test.bet, test.win)
			if result != test.expected {
				t.Errorf("Expected %f but got %f", test.expected, result)
			}
		})
	}
}

func TestWinXHander(t *testing.T) {
	// Create a new instance of the game handler
	params := `{
	"RUB":"100",
	"UAH":"50",
	"USD":"10",
	"EUR":"10",
	"INR":"100",
	"USDT":"5",
	"KZT":"500"}`

	multiplierHandler, err := gamehandlers.NewWinX(params)
	if err != nil {
		t.Fatalf("Failed to create BetX handler: %v", err)
	}

	// Define test cases
	tests := []struct {
		userID   int64
		currency string
		bet      float64
		win      float64
		expected float32
	}{
		{12, "RUB", 10, 100, 1},
		{12, "RUB", 10, 85, 0},
		{12, "RUB", 10, 0, 0},
		{12, "RUB", 10, 10, 0},
		{12, "RUB", 10, 25, 0},
		{12, "RUB", 10, 20, 0},
		{12, "RUB", 10, 300, 3},
		{12, "RUB", 10, 355, 3},
		{12, "RUB", 10, 500, 5},
		{12, "RUB", 10, 1500, 15},
		{12, "UAH", 10, 20, 0},
		{12, "UAH", 10, 5, 0},
		{12, "UAH", 10, 55, 1},
		{12, "USDT", 10, 55, 11},
		{12, "USDT", 10, 54, 10},
		{12, "KZT", 10, 550, 1},
		{12, "KKK", 10, 550, 0},
	}

	for _, test := range tests {
		t.Run(fmt.Sprintf("UserID: %d, Currency: %s", test.userID, test.currency), func(t *testing.T) {
			result := multiplierHandler.WinPoints(test.userID, test.currency, test.bet, test.win)
			if result != test.expected {
				t.Errorf("Expected %f but got %f", test.expected, result)
			}
		})
	}
}

func TestBetXHander(t *testing.T) {
	// Create a new instance of the game handler
	params := `{
	"RUB":"100",
	"UAH":"50",
	"USD":"10",
	"EUR":"10",
	"INR":"100",
	"USDT":"5",
	"KZT":"500"}`

	multiplierHandler, err := gamehandlers.NewBetX(params)
	if err != nil {
		t.Fatalf("Failed to create BetX handler: %v", err)
	}

	// Define test cases
	tests := []struct {
		userID   int64
		currency string
		bet      float64
		win      float64
		expected float32
	}{
		{12, "RUB", 100, 10, 1},
		{12, "RUB", 85, 185, 0},
		{12, "RUB", 10, 0, 0},
		{12, "RUB", 10, 10, 0},
		{12, "RUB", 25, 25, 0},
		{12, "RUB", 20, 20, 0},
		{12, "RUB", 3000, 30, 30},
		{12, "RUB", 355, 55, 3},
		{12, "RUB", 500, 5500, 5},
		{12, "RUB", 1500, 2500, 15},
		{12, "UAH", 20, 20, 0},
		{12, "UAH", 5, 55, 0},
		{12, "UAH", 55, 555, 1},
		{12, "USDT", 55, 5, 11},
		{12, "USDT", 54, 154, 10},
		{12, "KZT", 550, 1150, 1},
		{12, "KKK", 555, 550, 0},
	}

	for _, test := range tests {
		t.Run(fmt.Sprintf("UserID: %d, Currency: %s", test.userID, test.currency), func(t *testing.T) {
			result := multiplierHandler.WinPoints(test.userID, test.currency, test.bet, test.win)
			if result != test.expected {
				t.Errorf("Expected %f but got %f", test.expected, result)
			}
		})
	}
}
