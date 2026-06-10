package eventscontroller

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

// EventsPull обрабатывает HTTP-запрос для получения событий пользователя.
// Функция проверяет подпись запроса, извлекает ID пользователя и возвращает список его событий.
func EventsPull(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()
	method := "/events/pull"
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

	// Разбираем JSON запроса
	var req requests.UserIDRequest
	err = json.Unmarshal(body, &req)
	if err != nil {
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Получаем информацию о пользователе
	user, err := service.GetUser(stypes.UserID(req.UserId))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNotFound, errors.New("user not found")))
		logger.Get().Error(method, " user not found", slog.Int("id", int(req.UserId)))
		return
	}

	// Получаем и возвращаем события пользователя
	resp := user.PullEvents()
	logger.Get().Info(method, slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
