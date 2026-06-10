package handlers

import (
	"kipinfo/internal/server/response"
	"kipinfo/internal/service"
	"net/http"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// IpHanlder - обработчик для получения информации о IP-адресе.
// Он принимает IP-адрес в качестве параметра и возвращает информацию о нем.
func IpHanlder(w http.ResponseWriter, r *http.Request) {
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
	// Получаем информацию о IP-адресе
	// Используем сервис для получения информации о IP-адресе
	ip_info, err := service.GetIPInfo(ip)
	if err != nil {
		render.JSON(w, r, response.ErrorResponse{
			Error:  err.Error(),
			Status: http.StatusInternalServerError,
		})
		return
	}

	// Если информация о IP-адресе не найдена, возвращаем 404
	render.JSON(w, r, response.IpInfoResponse{
		Data:   ip_info,
		Status: http.StatusOK,
	})
}
