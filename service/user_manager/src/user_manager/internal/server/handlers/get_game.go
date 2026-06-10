package handlers

import (
	"errors"
	"log/slog"
	"net/http"
	"strconv"
	"user-manager/internal/logger"
	"user-manager/internal/server/responses"
	"user-manager/internal/service"
	"user-manager/internal/stypes"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// GetGame обрабатывает HTTP-запрос на получение информации об игровой сессии пользователя
// Параметры:
//   - w: объект http.ResponseWriter для записи HTTP-ответа
//   - r: объект http.Request, содержащий данные HTTP-запроса
func GetGame(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	// Инициализация основных параметров
	method := "/getGame"
	u_id_p := chi.URLParam(r, "user_id")
	log := logger.Get()
	log.Info("GetGame request", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	// Преобразование ID пользователя из строки в число
	u_id, err := strconv.Atoi(u_id_p)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("user id error")))
		logger.Get().Error("GetGame: wrong user id", slog.String("id", u_id_p))
		return
	}

	// Проверка подписи запроса
	if err := service.CheckRequestSign(r, method, ""); err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Получение информации о пользователе
	user, err := service.GetUser(stypes.UserID(u_id))
	if err != nil {
		log.Error("GetGame", slog.Int("user not found", u_id))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("user not found")))
		return
	}

	// Получение и проверка игровой сессии
	gs := user.GetGameSession()
	if gs == nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNoContent, errors.New("no game session")))
		return
	}
	// Отправка ответа с данными игровой сессии
	render.JSON(w, r, responses.MakeGameSessionResponse(gs))
}
