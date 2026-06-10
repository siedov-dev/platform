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

func GetTotalWin(userID int64, currencyID int) (responses.AmountResponse, error) {
	// Получаем информацию о пользователе по его ID
	user, err := service.GetUser(stypes.UserID(userID))
	if err != nil {
		logger.Get().Error("GetTotalWin: user not found", slog.Int64("id", userID))
		return responses.AmountResponse{}, errors.New("user not found")
	}
	// Получаем название валюты по её ID
	curr_name, err := currency.GetName(currency.CurrencyID(currencyID))
	if err != nil {
		logger.Get().Error("GetTotalWin: wrong currency id", slog.Int("currency", currencyID))
		return responses.AmountResponse{}, errors.New("wrong currency id")
	}
	// Получаем кошелек пользователя для указанной валюты
	wallet, err := user.GetWallet(currency.CurrencyID(currencyID))
	if err != nil {
		logger.Get().Error("GetTotalWin: can't get wallet", slog.Int("currency", currencyID))
		return responses.AmountResponse{}, errors.New("can't get wallet")
	}
	// Формируем и отправляем ответ
	resp := responses.MakeAmountResponse(wallet.GetTotalWin(), curr_name, int8(currencyID))
	return resp, nil
}
