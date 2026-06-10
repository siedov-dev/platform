package handlers

import (
	"fmt"
	"log/slog"
	"net/http"
	"user-manager/internal/gamesystems/pragplay"
	pplayresponses "user-manager/internal/gamesystems/pragplay/pplay_responses"
	"user-manager/internal/logger"

	"github.com/go-chi/render"
)

// PPlaySessionExpired обрабатывает запрос об истечении сессии от PragmaticPlay.
// Проверяет hash для валидации запроса и вызывает обработчик истечения сессии.
func PPlaySessionExpired(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	log := logger.Get()
	pplay := pragplay.Make()

	// Получаем hash из POST параметров
	hash := r.PostFormValue("hash")

	// Логируем детали запроса
	log.Info("pplay", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	log.Info("pplay", slog.String("PostForm", fmt.Sprintf("%v", r.PostForm)))

	// Формируем мапу из POST параметров для проверки hash
	postData := make(map[string]string)
	for key, values := range r.PostForm {
		postData[key] = values[0]
	}

	// Проверяем валидность hash
	if hash != pplay.CalcHash(postData) {
		log.Error("pplay", slog.String("wrong hash", hash))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(5, "Invalid hash code"))
		return
	}

	// Обрабатываем истечение сессии
	pplay.SessionExpired(w, r)
}
