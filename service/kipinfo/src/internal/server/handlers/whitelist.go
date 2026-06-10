package handlers

import (
	"kipinfo/internal/server/response"
	"kipinfo/internal/service"
	"net/http"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// WhitelistHanlder обрабатывает запросы на добавление IP-адреса в белый список.
// Принимает IP-адрес и флаг, указывающий, является ли IP-адрес в белом списке.
func WhitelistHanlder(w http.ResponseWriter, r *http.Request) {
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
	// Получаем флаг белого списка из URL-параметра
	isWhiteList := chi.URLParam(r, "isWhiteList")
	if isWhiteList == "" {
		render.JSON(w, r, response.ErrorResponse{
			Error:  "isWhiteList is empty",
			Status: http.StatusBadRequest,
		})
		return
	}
	// Преобразуем флаг белого списка в булево значение
	var whitelist bool
	if isWhiteList == "1" || isWhiteList == "true" {
		whitelist = true
	} else if isWhiteList == "0" || isWhiteList == "false" {
		whitelist = false
	} else {
		render.JSON(w, r, response.ErrorResponse{
			Error:  "isWhiteList must be true or false or 1 or 0",
			Status: http.StatusBadRequest,
		})
		return
	}
	// Устанавливаем флаг белого списка для IP-адреса
	ip_info, err := service.SetIPWhitelist(ip, whitelist)
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
