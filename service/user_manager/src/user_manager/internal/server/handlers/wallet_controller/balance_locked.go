package walletcontroller

import (
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"log/slog"
	"net/http"
	"user-manager/internal/logger"
	"user-manager/internal/server/requests"
	"user-manager/internal/server/responses"
	"user-manager/internal/service"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"

	"github.com/go-chi/render"
)

// BalanceLocked обрабатывает HTTP запрос для получения заблокированных для отыгрыша средств пользователя
func BalanceLocked(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	// Название метода для логирования и проверки подписи
	method := "/balance/locked"
	// Чтение тела запроса
	body, err := io.ReadAll(r.Body)
	if err != nil {
		logger.Get().Error("balance request error", slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}
	//Проверка подписи
	if err := service.CheckRequestSign(r, method, string(body)); err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Разбор JSON запроса в структуру
	var req requests.BalanceRequest
	err = json.Unmarshal(body, &req)
	if err != nil {
		logger.Get().Error("balance request error", slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Получение данных пользователя
	user, err := service.GetUserTemp(stypes.UserID(req.UserId))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNotFound, errors.New("user not found")))
		logger.Get().Error("Balance: user not found", slog.Int("id", int(req.UserId)))
		return
	}
	// Получение названия валюты по ID
	curr_name, err := currency.GetName(currency.CurrencyID(req.CurrencyID))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("wrong currency id")))
		logger.Get().Error("Balance: wrong currency id", slog.Int("currency", int(req.CurrencyID)))
		return
	}
	// Получение кошелька
	wallet, err := user.GetWallet(currency.CurrencyID(req.CurrencyID))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("can't get wallet")))
		logger.Get().Error("Balance: can't get wallet", slog.Int("currency", int(req.CurrencyID)))
		return
	}
	// Возвращаем баланс в виде ответа [responses.AmountResponse]
	resp := responses.MakeAmountResponse(wallet.GetLockedMoney(), curr_name, int8(req.CurrencyID))
	logger.Get().Info("Balance:", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
