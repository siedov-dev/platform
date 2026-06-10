package handlers

import (
	"games_manager/internal/games"
	"games_manager/internal/http_server/responses"
	"net/http"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// AllGames - обработчик для получения всех игр
// Возвращает список всех игр в формате JSON
// Пример запроса: GET /games/all/desktop/ru
func AllGames(w http.ResponseWriter, r *http.Request) {
	device := chi.URLParam(r, "device")
	lang := chi.URLParam(r, "lang")
	// Получаем список всех игр из менеджера игр для указанного устройства и языка
	// Например, для устройства "desktop" и языка "ru" будет использоваться менеджер "desktop-ru"
	if manager, found := games.GameManagers[device+"-"+lang]; found {
		games := manager.AllGames
		resp := responses.MakeGameReponse(responses.StatusOk, "", len(games), 1, 1, games)
		render.JSON(w, r, &resp)
		return
	} else {
		resp := responses.MakeGameReponse(responses.StatusError, "Games Manager not found", 0, 0, 0, nil)
		render.JSON(w, r, &resp)
		return
	}
}
