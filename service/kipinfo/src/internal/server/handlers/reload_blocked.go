package handlers

import (
	"kipinfo/internal/server/response"
	"kipinfo/internal/service"
	"net/http"

	"github.com/go-chi/render"
)

// ReloadBlockedCountries - обработчик для перезагрузки списка заблокированных стран.
// Он не принимает никаких параметров и возвращает статус выполнения операции.
func ReloadBlockedCountries(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()
	err := service.ReloadBlockedCountries()

	if err != nil {
		render.JSON(w, r, response.ErrorResponse{
			Error:  err.Error(),
			Status: http.StatusInternalServerError,
		})
		return
	}

	render.JSON(w, r, response.OkResponse{
		Status:  http.StatusOK,
		Message: "Blocked countries reloaded successfully",
	})
}
