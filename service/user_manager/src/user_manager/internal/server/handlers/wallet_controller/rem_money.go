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

// RemMoney обрабатывает запрос на снятие денег с кошелька пользователя
func RemMoney(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	// Получаем параметры из URL: ID пользователя, валюту и сумму
	u_id_p := chi.URLParam(r, "user_id")
	curr_p := chi.URLParam(r, "curr")
	val_p := chi.URLParam(r, "val")
	log := logger.Get()
	log.Info("RemMoney request", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	// Преобразуем ID пользователя из строки в число
	u_id, err := strconv.Atoi(u_id_p)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("user id error")))
		logger.Get().Error("RemMoney: wrong user id", slog.String("id", u_id_p))
		return
	}
	// Преобразуем ID валюты из строки в число
	curr_id, err := strconv.Atoi(curr_p)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("currency id error")))
		logger.Get().Error("RemMoney: currency id error", slog.String("id", curr_p))
		return
	}
	// Преобразуем сумму из строки в число с плавающей точкой
	val, err := strconv.ParseFloat(val_p, 64)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("value error")))
		logger.Get().Error("RemMoney: value id error", slog.String("id", curr_p))
		return
	}

	if val < 0 {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("value error")))
		logger.Get().Error("RemMoney: value < 0", slog.Float64("val", val))
		return
	}

	// Формируем ответ
	resp, err := usecases.RemMoney(int64(u_id), curr_id, val)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, fmt.Errorf("error: %s", err.Error())))
		logger.Get().Error("RemMoney: error removing money", slog.Int64("user_id", int64(u_id)), slog.Int("currency_id", curr_id), slog.Float64("value", val))
		return
	}
	logger.Get().Info("RemMoney:", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
