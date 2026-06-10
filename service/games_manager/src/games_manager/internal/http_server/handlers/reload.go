package handlers

import (
	"games_manager/internal/games"
	"games_manager/internal/http_server/responses"
	"net/http"
	"time"

	"github.com/go-chi/render"
)

// Reload - обработчик для перезагрузки данных
// Возвращает статус 200 OK и время перезагрузки
// Пример запроса: GET /reload
func Reload(w http.ResponseWriter, r *http.Request) {
	start_time := time.Now()
	games.MustLoad()
	duration := time.Since(start_time)

	resp := responses.MakeReloadReponse(responses.StatusOk, "", duration.String())

	render.JSON(w, r, &resp)
}
