package handlers

import (
	"kipinfo/internal/server/response"
	"kipinfo/internal/service"
	"net/http"
	"time"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// BlockHanlder - обработчик для блокировки IP-адреса
func BlockHanlder(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	// Получаем IP-адрес из URL-параметра
	ip := chi.URLParam(r, "ip")
	if ip == "" {
		render.JSON(w, r, response.ErrorResponse{
			Error:  "ip is empty",
			Status: http.StatusBadRequest,
		})
		return
	}
	// Получаем дату блокировки из URL-параметра
	// Пример формата даты: 2023-10-01 15:04:05
	block_to_param := chi.URLParam(r, "block_to")
	if block_to_param == "" {
		render.JSON(w, r, response.ErrorResponse{
			Error:  "block_to is empty",
			Status: http.StatusBadRequest,
		})
		return
	}
	// Преобразуем строку в тип time.Time
	block_to, err := time.Parse(time.DateTime, block_to_param)
	if err != nil {
		render.JSON(w, r, response.ErrorResponse{
			Error:  err.Error(),
			Status: http.StatusInternalServerError,
		})
		return
	}
	// Блокируем IP-адрес до указанной даты
	// Если IP-адрес уже заблокирован, обновляем дату блокировки
	ip_info, err := service.BlockTo(ip, block_to)
	if err != nil {
		render.JSON(w, r, response.ErrorResponse{
			Error:  err.Error(),
			Status: http.StatusInternalServerError,
		})
		return
	}

	// Возвращаем успешный ответ с информацией о блокировке IP-адреса
	render.JSON(w, r, response.IpInfoResponse{
		Data:   ip_info,
		Status: http.StatusOK,
	})
}
