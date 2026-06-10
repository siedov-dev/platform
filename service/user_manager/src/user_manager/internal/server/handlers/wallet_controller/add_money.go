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

// AddMoney Обработчик для добавления денежных средств на счет пользователя
func AddMoney(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()
	// Получаем параметры из URL запроса
	u_id_p := chi.URLParam(r, "user_id") // ID пользователя
	curr_p := chi.URLParam(r, "curr")    // ID валюты
	val_p := chi.URLParam(r, "val")      // Сумма пополнения
	log := logger.Get()
	log.Info("AddMoney request", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	// Преобразуем ID пользователя в число
	u_id, err := strconv.Atoi(u_id_p)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("user id error")))
		logger.Get().Error("AddMoney: wrong user id", slog.String("id", u_id_p))
		return
	}
	// Преобразуем ID валюты в число
	curr_id, err := strconv.Atoi(curr_p)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("currency id error")))
		logger.Get().Error("AddMoney: currency id error", slog.String("id", curr_p))
		return
	}
	// Преобразуем сумму пополнения в число с плавающей точкой
	val, err := strconv.ParseFloat(val_p, 64)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("value error")))
		logger.Get().Error("AddMoney: value id error", slog.String("id", curr_p))
		return
	}

	if val < 0 {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("value error")))
		logger.Get().Error("AddMoney: value < 0", slog.Float64("val", val))
		return
	}

	// Возвращаем текущий баланс в ответ
	resp, err := usecases.AddMoney(int64(u_id), curr_id, val)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, fmt.Errorf("error: %s", err.Error())))
		logger.Get().Error("AddMoney: error adding money", slog.Int64("user_id", int64(u_id)), slog.Int("currency_id", curr_id), slog.Float64("value", val))
		return
	}
	// Логируем успешный ответ
	logger.Get().Info("AddMoney:", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
