package mware

import (
	"kbonus/internal/config"
	"kbonus/internal/server/response"
	"net/http"
	"strings"

	"github.com/go-chi/render"
)

// Auth мидлвар для проверки авторизации
// Проверяет наличие токена в заголовке Authorization
// Если токен отсутствует или неверный, возвращает 401 Unauthorized
func Auth(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		// Проверка токена
		reqToken := r.Header.Get("Authorization")
		splitToken := strings.Split(reqToken, "Bearer")
		if len(splitToken) != 2 {
			w.WriteHeader(http.StatusUnauthorized)
			render.JSON(w, r, response.ErrorResponse{
				Error:  "invalid token",
				Status: http.StatusUnauthorized,
			})
			return
		}
		reqToken = strings.TrimSpace(splitToken[1])
		if reqToken != config.Get().HTTPServer.Token {
			w.WriteHeader(http.StatusUnauthorized)
			render.JSON(w, r, response.ErrorResponse{
				Error:  "invalid token",
				Status: http.StatusUnauthorized,
			})
			return
		}

		next.ServeHTTP(w, r)
	})
}
