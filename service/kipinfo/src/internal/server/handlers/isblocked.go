package handlers

import (
	"kipinfo/internal/server/response"
	"kipinfo/internal/service"
	"net/http"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// IsBlockedHanlder - обработчик для проверки, заблокирован ли IP-адрес
// Он принимает IP-адрес в качестве параметра и возвращает информацию о блокировке.
func IsBlockedHanlder(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()
	ip := chi.URLParam(r, "ip")
	if ip == "" {
		render.JSON(w, r, response.ErrorResponse{
			Error:  "ip is empty",
			Status: http.StatusBadRequest,
		})
		return
	}

	// Проверяем, является ли IP-адрес в белом списке
	// Используем сервис для проверки белого списка
	isWhiteList, err := service.IsIPWhitelisted(ip)
	if err != nil {
		render.JSON(w, r, response.ErrorResponse{
			Error:  err.Error(),
			Status: http.StatusInternalServerError,
		})
		return
	}
	// Если IP-адрес в белом списке, возвращаем успешный ответ
	if isWhiteList {
		render.JSON(w, r, response.IsBlockedResponse{
			Blocked: false,
			Status:  http.StatusOK,
			Reason:  "Whitelist",
		})
		return
	}

	// Проверяем, заблокирован ли IP-адрес
	// Используем сервис для проверки блокировки IP-адреса
	blocked, err := service.IsBlocked(ip)
	if err != nil {
		render.JSON(w, r, response.ErrorResponse{
			Error:  err.Error(),
			Status: http.StatusInternalServerError,
		})
		return
	}

	// Если IP-адрес заблокирован, возвращаем информацию о блокировке
	if blocked {
		render.JSON(w, r, response.IsBlockedResponse{
			Blocked: blocked,
			Status:  http.StatusOK,
			Reason:  "IP",
		})
		return
	}

	// Проверяем, заблокирована ли страна, к которой принадлежит IP-адрес
	// Используем сервис для проверки блокировки по стране
	// Если IP-адрес не заблокирован, проверяем блокировку по стране
	blocked, err = service.IsCountryBlocked(ip)
	if err != nil {
		render.JSON(w, r, response.ErrorResponse{
			Error:  err.Error(),
			Status: http.StatusInternalServerError,
		})
		return
	}
	if blocked {
		render.JSON(w, r, response.IsBlockedResponse{
			Blocked: blocked,
			Status:  http.StatusOK,
			Reason:  "Country",
		})
		return
	}

	// Если IP-адрес и страна не заблокированы, возвращаем успешный ответ
	// с информацией о том, что IP-адрес не заблокирован
	render.JSON(w, r, response.IsBlockedResponse{
		Blocked: false,
		Status:  http.StatusOK,
		Reason:  "",
	})

}
