// Package handlers содержит обработчики HTTP запросов для управления игровыми сессиями
package handlers

import (
	"errors"
	"fmt"
	"log/slog"
	"net/http"
	"strconv"
	"user-manager/internal/logger"
	"user-manager/internal/server/responses"
	"user-manager/internal/service"
	"user-manager/internal/stypes"

	"github.com/go-chi/render"
)

// CloseGame обрабатывает HTTP запрос на закрытие игровой сессии.
// Принимает ID пользователя в форме запроса.
// Проверяет подпись запроса, находит пользователя и закрывает его активную игровую сессию.
// Возвращает информацию о закрытой сессии или ошибку, если сессия не найдена.
func CloseGame(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()
	// Имя метода для проверки подписи
	method := "/closeGame"
	// Получаем ID пользователя из формы запроса
	u_id_p := r.PostFormValue("user_id")
	log := logger.Get()
	log.Info("CloseGame request", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	// Конвертируем строковый ID в число
	u_id, err := strconv.Atoi(u_id_p)
	if err != nil {
		// Возвращаем ошибку если ID невалидный
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("user id error")))
		logger.Get().Error("CloseGame: wrong user id", slog.String("id", u_id_p))
		return
	}

	// Проверяем подпись запроса
	if err := service.CheckRequestSign(r, method, fmt.Sprintf(`{"user_id":%s}`, u_id_p)); err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Получаем пользователя
	user, err := service.GetUser(stypes.UserID(u_id))
	if err != nil {
		log.Error("CloseGame", slog.Int("user not found", u_id))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("user not found")))
		return
	}

	// Закрываем игровую сессию пользователя
	gs, err := user.CloseGameSession()
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}
	// Проверяем существование активной сессии
	if gs == nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNoContent, errors.New("no game session")))
		return
	}
	// Отправляем информацию о закрытой сессии
	render.JSON(w, r, responses.MakeGameSessionResponse(gs))
}
