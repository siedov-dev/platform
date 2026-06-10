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

// BalanceReferralRem обрабатывает запрос на снятие реферальных денег с баланса пользователя
func BalanceReferralRem(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	// Путь метода для логирования и подписи
	method := "/balance/referral/rem"
	// Читаем тело запроса
	body, err := io.ReadAll(r.Body)
	if err != nil {
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Проверяем подпись API запроса
	if err := service.CheckRequestSign(r, method, string(body)); err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Декодируем JSON запрос в структуру
	var req requests.BalanceAddRequest
	err = json.Unmarshal(body, &req)
	if err != nil {
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Проверяем что сумма положительная
	if req.Amount < 0 {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("value error")))
		logger.Get().Error("RemReferralMoney: value < 0", slog.Float64("val", req.Amount))
		return
	}

	// Получаем информацию о пользователе
	user, err := service.GetUserTemp(stypes.UserID(req.UserId))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNotFound, errors.New("user not found")))
		logger.Get().Error("RemReferralMoney: user not found", slog.Int("id", int(req.UserId)))
		return
	}
	curr_name, err := currency.GetName(currency.CurrencyID(req.CurrencyID))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("wrong currency id")))
		logger.Get().Error("RemReferralMoney: wrong currency id", slog.Int("currency", int(req.CurrencyID)))
		return
	}
	wallet, err := user.GetWallet(currency.CurrencyID(req.CurrencyID))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("can't get wallet")))
		logger.Get().Error("RemReferralMoney: can't get wallet", slog.Int("currency", int(req.CurrencyID)))
		return
	}

	// Снимаем реферальные деньги с кошелька пользователя
	wallet.RemReferralMoney(req.Amount)
	// Сохраняем изменения в кошельке
	err = wallet.Save()
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("add money error")))
		logger.Get().Error("RemReferralMoney: add money error", slog.String("err", err.Error()))
		return
	}
	
	// Формирование и отправка ответа с обновленной информацией о реферальных средствах
	resp := responses.MakeAmountResponse(wallet.GetReferralMoney(), curr_name, int8(req.CurrencyID))
	logger.Get().Info("RemReferralMoney:", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
