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

// BalanceLockedSet обрабатывает HTTP запрос для установки заблокированных для отыгрыша средств пользователя
func BalanceLockedSet(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	// Название метода для логирования и проверки подписи
	method := "/balance/locked/set"
	// Чтение тела запроса
	body, err := io.ReadAll(r.Body)
	if err != nil {
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}
	// Проверка подписи
	if err := service.CheckRequestSign(r, method, string(body)); err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Разбор JSON запроса в структуру
	var req requests.BalanceAddRequest
	err = json.Unmarshal(body, &req)
	if err != nil {
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Проверка корректности суммы
	if req.Amount < 0 {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("value error")))
		logger.Get().Error("AddMoney: value < 0", slog.Float64("val", req.Amount))
		return
	}

	// Получение данных пользователя
	user, err := service.GetUserTemp(stypes.UserID(req.UserId))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNotFound, errors.New("user not found")))
		logger.Get().Error("AddMoney: user not found", slog.Int("id", int(req.UserId)))
		return
	}
	// Получение названия валюты по ID
	curr_name, err := currency.GetName(currency.CurrencyID(req.CurrencyID))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("wrong currency id")))
		logger.Get().Error("AddMoney: wrong currency id", slog.Int("currency", int(req.CurrencyID)))
		return
	}
	// Получение кошелька
	wallet, err := user.GetWallet(currency.CurrencyID(req.CurrencyID))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("can't get wallet")))
		logger.Get().Error("AddMoney: can't get wallet", slog.Int("currency", int(req.CurrencyID)))
		return
	}

	// Установка заблокированных средств кошелька
	wallet.SetLockedMoney(req.Amount)
	// Сохранение изменений в кошельке
	err = wallet.Save()
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("add money error")))
		logger.Get().Error("AddMoney: add money error", slog.String("err", err.Error()))
		return
	}

	// Формирование ответа с обновленной информацией о заблокированных средствах в виде [responses.AmountResponse]
	resp := responses.MakeAmountResponse(wallet.GetLockedMoney(), curr_name, int8(req.CurrencyID))
	logger.Get().Info("AddMoney:", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
