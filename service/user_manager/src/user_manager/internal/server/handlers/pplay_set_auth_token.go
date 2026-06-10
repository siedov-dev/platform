package handlers

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

// PPlaySetAuthToken обрабатывает запрос на установку токена авторизации для PragmaticPlay.
// Принимает идентификатор пользователя и токен, проверяет подпись запроса
// и сохраняет токен в хранилище.
func PPlaySetAuthToken(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	// Путь метода для логирования
	method := "/pplay/token"
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
	var req requests.SetPPlayToken
	err = json.Unmarshal(body, &req)
	if err != nil {
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Проверяем существование пользователя
	_, err = service.GetUser(stypes.UserID(req.UserId))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNotFound, errors.New("user not found")))
		logger.Get().Error(method+": user not found", slog.Int("id", int(req.UserId)))
		return
	}

	// Сохраняем токен авторизации в хранилище
	service.PPlayAuthTokens.Set(req.Token, req.UserId)

	// Формируем успешный ответ
	resp := responses.MakeOkResponse()
	logger.Get().Info(method, slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
