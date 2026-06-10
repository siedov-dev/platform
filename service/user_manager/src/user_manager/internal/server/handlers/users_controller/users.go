package userscontroller

import (
	"net/http"
	"user-manager/internal/server/responses"
	"user-manager/internal/service"

	"github.com/go-chi/render"
)

// UsersOnline обрабатывает GET-запрос для получения списка активных пользователей
func UsersOnline(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()
	method := "/users"
	if err := service.CheckRequestSign(r, method, ""); err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	users := service.Users.GetTimeouts()
	render.JSON(w, r, &users)
}
