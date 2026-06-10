package handlers

import (
	"kipinfo/internal/server/response"
	"kipinfo/internal/service"
	"net/http"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// UnBlockHanlder - обработчик для разблокировки IP-адреса.
// Он принимает IP-адрес в качестве параметра и возвращает информацию о разблокировке.
func UnBlockHanlder(w http.ResponseWriter, r *http.Request) {
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

	// Разблокируем IP-адрес
	// Используем сервис для разблокировки IP-адреса
	ip_info, err := service.UnBlock(ip)
	if err != nil {
		render.JSON(w, r, response.ErrorResponse{
			Error:  err.Error(),
			Status: http.StatusInternalServerError,
		})
		return
	}

	// Возвращаем успешный ответ с информацией о разблокировке IP-адреса
	render.JSON(w, r, response.IpInfoResponse{
		Data:   ip_info,
		Status: http.StatusOK,
	})
}
