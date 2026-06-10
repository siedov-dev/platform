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

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// BonusStatus обрабатывает HTTP запрос для получения статуса бонуса пользователя
func BonusStatus(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	// Получаем ID пользователя из URL параметров
	u_id_p := chi.URLParam(r, "user_id")
	log := logger.Get()
	log.Info("BonusStatus request", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	u_id, err := strconv.Atoi(u_id_p)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("user id error")))
		logger.Get().Error("BonusStatus: wrong user id", slog.String("id", u_id_p))
		return
	}

	// Получаем информацию о пользователе из сервиса
	user, err := service.GetUser(stypes.UserID(u_id))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNotFound, errors.New("user not found")))
		logger.Get().Error("BonusStatus: user not found", slog.Int("id", u_id))
		return
	}

	// Получаем игровую сессию пользователя
	gs := user.GetGameSession()
	if gs == nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNotFound, errors.New("game session not found")))
		logger.Get().Error("BonusStatus: game session not found", slog.Int("id", u_id))
		return
	}

	// Получаем активный бонус из игровой сессии
	bonus, err := gs.GetActiveBonus()
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNotFound, errors.New("bonus not found")))
		logger.Get().Error("BonusStatus: bonus not found", slog.Int("id", u_id))
		return
	}

	// Формируем ответ с информацией о статусе бонуса
	// resp := responses.MakeBonusStatusResponse(http.StatusOK, "", int(bonus.GetID()), int(bonus.GetUserBonus().Status), bonus.GetStatus())
	resp := responses.MakeBonusStatusResponse(http.StatusOK, "", int(bonus.GetID()), int(bonus.GetUserBonus().Status))
	logger.Get().Info("BonusStatus:", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
