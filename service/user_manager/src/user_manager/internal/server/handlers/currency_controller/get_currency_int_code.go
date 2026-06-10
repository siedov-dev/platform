package currencycontroller

import (
	"errors"
	"fmt"
	"log/slog"
	"net/http"
	"strconv"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/server/responses"
	"user-manager/internal/service"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// GetCurrencyIntCode обрабатывает HTTP-запрос для получения международного кода валюты по её идентификатору.
// В случае успешного поиска возвращает JSON с международным кодом валюты.
func GetCurrencyIntCode(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()
	curr_p := chi.URLParam(r, "curr")
	log := logger.Get()
	log.Info("currency controller", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	// Читаем ид валюты из запроса
	curr_id, err := strconv.Atoi(curr_p)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("currency id error")))
		logger.Get().Error("Balance: currency id error", slog.String("id", curr_p))
		return
	}
	// Получение информации о валюте
	curr_info, ok := service.Currencies.Find(func(v models.UserCurrency) bool { return v.ID == int64(curr_id) })
	if !ok {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("currency not found")))
		logger.Get().Error("currency not found", slog.Int("currency", curr_id))
		return
	}
	// Ответ responses.CurrencyIntCodeResponse
	resp := responses.MakeCurrencyIntCodeResponse(curr_info.Code)
	logger.Get().Info("currency controller:", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
