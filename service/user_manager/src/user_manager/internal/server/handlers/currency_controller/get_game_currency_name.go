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
	"user-manager/internal/stypes/currency"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// GetGameCurrencyName обрабатывает HTTP-запрос для получения имени игровой валюты по её идентификатору.
// В случае успешного поиска возвращает JSON с именем валюты из GameCurrency, если оно задано,
// в противном случае возвращает стандартное имя валюты.
func GetGameCurrencyName(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()
	// Читаем ид валюты из запроса
	curr_p := chi.URLParam(r, "curr")
	log := logger.Get()
	log.Info("currency controller", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	curr_id, err := strconv.Atoi(curr_p)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("currency id error")))
		logger.Get().Error("currency controller: currency id error", slog.String("id", curr_p))
		return
	}
	// Получение стандартного имени валюты
	curr_name, err := currency.GetName(currency.CurrencyID(curr_id))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("wrong currency id")))
		logger.Get().Error("currency controller: wrong currency id", slog.Int("currency", curr_id))
		return
	}
	// Получение информации о валюте из сервиса
	curr_info, ok := service.Currencies.Find(func(v models.UserCurrency) bool { return v.ID == int64(curr_id) })
	if !ok {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("currency not found")))
		logger.Get().Error("currency not found", slog.Int("currency", curr_id))
		return
	}

	// Формирование ответа responses.CurrencyNameResponse
	var resp responses.CurrencyNameResponse
	if curr_info.GameCurrency.Valid {
		resp = responses.MakeCurrencyNameResponse(curr_info.GameCurrency.String)
	} else {
		resp = responses.MakeCurrencyNameResponse(curr_name)
	}
	logger.Get().Info("currency controller:", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
