package currencycontroller

import (
	"errors"
	"fmt"
	"log/slog"
	"net/http"
	"strconv"
	"user-manager/internal/logger"
	"user-manager/internal/server/responses"
	"user-manager/internal/stypes/currency"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// GetCurrencyName обрабатывает HTTP-запрос для получения название валюты (ISO 4217) по её идентификатору.
// В случае успешного поиска возвращает JSON с наименованием валюты.
func GetCurrencyName(w http.ResponseWriter, r *http.Request) {
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
	// Получение название валюты
	curr_name, err := currency.GetName(currency.CurrencyID(curr_id))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("wrong currency id")))
		logger.Get().Error("Balance: wrong currency id", slog.Int("currency", curr_id))
		return
	}
	// Ответ responses.CurrencyNameResponse
	resp := responses.MakeCurrencyNameResponse(curr_name)
	logger.Get().Info("currency controller:", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
