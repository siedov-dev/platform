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

	"github.com/go-chi/render"
)

// JsonBalances обрабатывает запрос для получения балансов пользователя и возвращает их в формате JSON.
// В случае ошибки проверки подписи, некорректного тела запроса или отсутствия пользователя возвращает ошибку.
func JsonBalances(w http.ResponseWriter, r *http.Request) {
	// Закрываем тело запроса после завершения обработки
	defer r.Body.Close()
	method := "/balances"

	// Читаем тело запроса
	body, err := io.ReadAll(r.Body)
	if err != nil {
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}
	// Проверяем подпись запроса
	if err := service.CheckRequestSign(r, method, string(body)); err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Декодируем JSON-запрос в структуру
	var req requests.BalancesRequest
	err = json.Unmarshal(body, &req)
	if err != nil {
		logger.Get().Error("balances request error", slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Получаем информацию о пользователе
	user, err := service.GetUser(stypes.UserID(req.UserId))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNotFound, errors.New("user not found")))
		logger.Get().Error("Balances: user not found", slog.Int("id", int(req.UserId)))
		return
	}

	// Формируем и отправляем ответ с балансами пользователя
	wallets := user.GetWallets()
	resp := responses.MakeBalancesResponse(wallets)
	resp.Active = user.GetDbUser().CurrencyId

	logger.Get().Info("Balances:", slog.String("resp", fmt.Sprintf("%v", resp)))
	render.JSON(w, r, resp)
}
