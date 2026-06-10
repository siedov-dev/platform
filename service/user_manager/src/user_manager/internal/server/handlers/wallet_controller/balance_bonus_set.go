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

// BalanceBonusSet обрабатывает HTTP запрос для установки бонусных средств на счет пользователя
func BalanceBonusSet(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	// Название метода для логирования и проверки подписи
	method := "/balance/bonus/set"
	// Чтение тела запроса
	body, err := io.ReadAll(r.Body)
	if err != nil {
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}
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

	if req.Amount < 0 {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("value error")))
		logger.Get().Error("SetBonusMoney: value < 0", slog.Float64("val", req.Amount))
		return
	}

	// Получение данных пользователя
	user, err := service.GetUserTemp(stypes.UserID(req.UserId))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNotFound, errors.New("user not found")))
		logger.Get().Error("SetBonusMoney: user not found", slog.Int("id", int(req.UserId)))
		return
	}
	curr_name, err := currency.GetName(currency.CurrencyID(req.CurrencyID))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("wrong currency id")))
		logger.Get().Error("SetBonusMoney: wrong currency id", slog.Int("currency", int(req.CurrencyID)))
		return
	}
	wallet, err := user.GetWallet(currency.CurrencyID(req.CurrencyID))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("can't get wallet")))
		logger.Get().Error("SetBonusMoney: can't get wallet", slog.Int("currency", int(req.CurrencyID)))
		return
	}

	// Установка бонусных средств и сохранение изменений
	wallet.SetBonusMoney(req.Amount)
	err = wallet.Save()
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("add money error")))
		logger.Get().Error("SetBonusMoney: add money error", slog.String("err", err.Error()))
		return
	}

	// Возвращаем баланс в виде ответа [responses.AmountResponse]
	resp := responses.MakeAmountResponse(wallet.GetBonusMoney(), curr_name, int8(req.CurrencyID))
	logger.Get().Info("SetBonusMoney:", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
