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

func GetBalance(userID int64, currencyID int) (responses.BalanceResponse, error) {
	// Получаем информацию о пользователе по его ID
	user, err := service.GetUser(stypes.UserID(userID))
	if err != nil {
		logger.Get().Error("Balance: user not found", slog.Int64("id", userID))
		return responses.BalanceResponse{}, errors.New("user not found")
	}
	// Получаем название валюты по её ID
	curr_name, err := currency.GetName(currency.CurrencyID(currencyID))
	if err != nil {
		logger.Get().Error("Balance: wrong currency id", slog.Int("currency", currencyID))
		return responses.BalanceResponse{}, errors.New("wrong currency id")
	}
	// Получаем кошелек пользователя для указанной валюты
	wallet, err := user.GetWallet(currency.CurrencyID(currencyID))
	if err != nil {
		logger.Get().Error("Balance: can't get wallet", slog.Int("currency", currencyID))
		return responses.BalanceResponse{}, errors.New("can't get wallet")
	}
	// Формируем и отправляем ответ с информацией о балансе
	resp := responses.MakeBalanceResponse(wallet.GetBalance(), wallet.GetCents(), curr_name, int8(currencyID), wallet.DbWallet.Active)
	resp.Bonus = wallet.GetBonusId() // Добавляем бонусный ID в ответ
	return resp, nil
}
