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
	"user-manager/internal/service/usecases"

	"github.com/go-chi/render"
)

// JsonBalanceRem обрабатывает HTTP запрос на снятие средств с баланса кошелька пользователя
func JsonBalanceRem(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	// Путь метода API
	method := "/balance/rem"
	// Читаем тело запроса
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

	// Десериализуем JSON запрос в структуру
	var req requests.BalanceAddRequest
	err = json.Unmarshal(body, &req)
	if err != nil {
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	if req.Amount < 0 {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("value error")))
		logger.Get().Error("AddMoney: value < 0", slog.Float64("val", req.Amount))
		return
	}

	// Формируем ответ
	resp, err := usecases.RemMoney(int64(req.UserId), int(req.CurrencyID), req.Amount)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, fmt.Errorf("error: %s", err.Error())))
		logger.Get().Error("AddMoney: error adding money", slog.Int64("user_id", int64(req.UserId)), slog.Int("currency_id", int(req.CurrencyID)), slog.Float64("value", req.Amount))
		return
	}
	// Логируем успешный ответ
	logger.Get().Info("AddMoney:", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
