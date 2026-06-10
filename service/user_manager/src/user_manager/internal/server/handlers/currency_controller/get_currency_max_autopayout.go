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

// GetCurrencyMaxAutopayout обрабатывает HTTP-запрос для получения максимальной автоплатежной суммы валюты.
// Функция извлекает ID валюты из URL параметров, проверяет его корректность,
// находит информацию о валюте и возвращает максимальную сумму автоплатежа в JSON формате.
// В случае ошибки возвращает соответствующий статус ошибки и сообщение.
func GetCurrencyMaxAutopayout(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()
	// Получаем параметр curr из URL
	curr_p := chi.URLParam(r, "curr")
	log := logger.Get()
	// Логируем входящий запрос
	log.Info("currency controller", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	// Преобразуем строковый ID валюты в целое число
	curr_id, err := strconv.Atoi(curr_p)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("currency id error")))
		logger.Get().Error("Balance: currency id error", slog.String("id", curr_p))
		return
	}
	// Поиск информации о валюте в сервисе по её ID
	curr_info, ok := service.Currencies.Find(func(v models.UserCurrency) bool { return v.ID == int64(curr_id) })
	// Если валюта не найдена, возвращаем ошибку
	if !ok {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("currency not found")))
		logger.Get().Error("currency not found", slog.Int("currency", curr_id))
		return
	}

	// Формируем ответ с максимальной суммой автоплатежа
	resp := responses.MakeCurrencyMaxAutopayoutResponse(curr_info.MaxAutoPayout)
	// Логируем отправляемый ответ
	logger.Get().Info("currency controller:", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
