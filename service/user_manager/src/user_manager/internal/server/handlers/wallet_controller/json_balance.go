package walletcontroller

import (
	"encoding/json"
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

// JsonBalance обрабатывает запрос на получение баланса кошелька пользователя
func JsonBalance(w http.ResponseWriter, r *http.Request) {
	// Закрываем тело запроса после завершения обработки
	defer r.Body.Close()
	method := "/balance"
	// Читаем тело запроса
	body, err := io.ReadAll(r.Body)
	if err != nil {
		logger.Get().Error("balance request error", slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}
	if err := service.CheckRequestSign(r, method, string(body)); err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Десериализуем JSON запрос в структуру
	var req requests.BalanceRequest
	err = json.Unmarshal(body, &req)
	if err != nil {
		logger.Get().Error("balance request error", slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Формируем и отправляем ответ с информацией о балансе
	resp, err := usecases.GetBalance(int64(req.UserId), int(req.CurrencyID))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, fmt.Errorf("error: %s", err.Error())))
		logger.Get().Error("Balance: error getting balance	", slog.Int64("user_id", int64(req.UserId)), slog.Int("currency_id", int(req.CurrencyID)))
		return
	}
	// Логируем успешный ответ
	logger.Get().Info("Balance:", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
