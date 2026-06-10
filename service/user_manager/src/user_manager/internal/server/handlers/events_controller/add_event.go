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

// EventsAdd обрабатывает HTTP-запрос для добавления события пользователю
func EventsAdd(w http.ResponseWriter, r *http.Request) {
	// Закрываем тело запроса после завершения обработки
	defer r.Body.Close()
	method := "/events/add"
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

	// Декодируем JSON запрос в структуру
	var req requests.EventRequest
	err = json.Unmarshal(body, &req)
	if err != nil {
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Если пользователь не онлайн - игнорируем
	if !service.IsUserOnline(stypes.UserID(req.UserId)) {
		return
	}

	// Получаем пользователя по идентификатору
	user, err := service.GetUser(stypes.UserID(req.UserId))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNotFound, errors.New("user not found")))
		logger.Get().Error(method, " user not found", slog.Int("id", int(req.UserId)))
		return
	}

	// Добавляем событие пользователю
	user.AddEvent(service.UserEvent{Event: req.Event, Payload: req.Payload})

	// Формируем успешный ответ
	resp := responses.MakeOkResponse()
	logger.Get().Info("AddMoney:", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
