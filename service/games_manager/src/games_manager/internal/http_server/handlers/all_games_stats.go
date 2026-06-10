package handlers

import (
	"games_manager/internal/games"
	"games_manager/internal/http_server/responses"
	"net/http"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// AllGamesStats - обработчик для количества игр и групп
// Возвращает количество игр и групп в формате JSON
// Пример запроса: GET /games/stats/desktop/ru
func AllGamesStats(w http.ResponseWriter, r *http.Request) {
	device := chi.URLParam(r, "device")
	lang := chi.URLParam(r, "lang")
	// Получаем список всех игр из менеджера игр для указанного устройства и языка
	// Например, для устройства "desktop" и языка "ru" будет использоваться менеджер "desktop-ru"
	manager, found := games.GameManagers[device+"-"+lang]
	if !found {
		resp := responses.GamesStatsResponse{
			Status: responses.StatusError,
			Error:  "Games Manager not found",
		}
		render.JSON(w, r, &resp)
		return
	}

	resp := responses.GamesStatsResponse{
		Status: responses.StatusOk,
		Error:  "",
		Stats:  manager.Stats,
	}
	render.JSON(w, r, &resp)
}
