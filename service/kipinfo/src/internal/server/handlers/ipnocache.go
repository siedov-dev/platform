package handlers

import (
	"kipinfo/internal/server/response"
	"kipinfo/internal/service"
	"net/http"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// IpHanlder - обработчик для получения информации о IP-адресе без использовангия кеша.
func IpNoCacheHanlder(w http.ResponseWriter, r *http.Request) {
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
	// Получаем информацию о IP-адресе без кеша
	ip_info := service.GetIpInfoNoCache(ip)
	render.JSON(w, r, response.IpInfoResponse{
		Data:   ip_info,
		Status: http.StatusOK,
	})
}
