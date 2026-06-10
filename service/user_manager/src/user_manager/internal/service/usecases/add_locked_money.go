package usecases

import (
	"errors"
	"log/slog"
	"user-manager/internal/logger"
	"user-manager/internal/server/responses"
	"user-manager/internal/service"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"
)

func AddLockedMoney(userID int64, currencyID int, amount float64) (responses.AmountResponse, error) {
	// Получаем информацию о пользователе по его ID
	user, err := service.GetUser(stypes.UserID(userID))
	if err != nil {
		logger.Get().Error("Balance: user not found", slog.Int64("id", userID))
		return responses.AmountResponse{}, errors.New("user not found")
	}
	// Получаем название валюты по её ID
	curr_name, err := currency.GetName(currency.CurrencyID(currencyID))
	if err != nil {
		logger.Get().Error("Balance: wrong currency id", slog.Int("currency", currencyID))
		return responses.AmountResponse{}, errors.New("wrong currency id")
	}
	// Получаем кошелек пользователя для указанной валюты
	wallet, err := user.GetWallet(currency.CurrencyID(currencyID))
	if err != nil {
		logger.Get().Error("Balance: can't get wallet", slog.Int("currency", currencyID))
		return responses.AmountResponse{}, errors.New("can't get wallet")
	}

	// Формируем и отправляем ответ с информацией о балансе
	resp := responses.MakeAmountResponse(wallet.AddLockedMoney(amount), curr_name, int8(currencyID))
	return resp, nil
}
