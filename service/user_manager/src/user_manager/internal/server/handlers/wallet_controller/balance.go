package walletcontroller

import (
	"errors"
	"fmt"
	"log/slog"
	"net/http"
	"strconv"
	"user-manager/internal/logger"
	"user-manager/internal/server/responses"
	"user-manager/internal/service/usecases"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// Balance обрабатывает HTTP запрос на получение баланса кошелька пользователя
// для указанной валюты.
func Balance(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()
	// Получаем ID пользователя из URL параметров
	u_id_p := chi.URLParam(r, "user_id")
	// Получаем ID валюты из URL параметров
	curr_p := chi.URLParam(r, "curr")
	// Инициализируем логгер и записываем информацию о запросе
	log := logger.Get()
	log.Info("balance request", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	u_id, err := strconv.Atoi(u_id_p)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("user id error")))
		logger.Get().Error("Balance: wrong user id", slog.String("id", u_id_p))
		return
	}

	// Конвертируем ID валюты из строки в число
	curr_id, err := strconv.Atoi(curr_p)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("currency id error")))
		logger.Get().Error("Balance: currency id error", slog.String("id", curr_p))
		return
	}

	// Формируем и отправляем ответ с информацией о балансе
	resp, err := usecases.GetBalance(int64(u_id), curr_id)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, fmt.Errorf("error: %s", err.Error())))
		logger.Get().Error("Balance: error getting balance", slog.Int64("user_id", int64(u_id)), slog.Int("currency_id", curr_id))
		return
	}
	// Логируем успешный ответ
	logger.Get().Info("Balance:", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
