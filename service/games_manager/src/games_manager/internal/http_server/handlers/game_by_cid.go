package handlers

import (
	"games_manager/internal/games"
	"games_manager/internal/http_server/responses"
	"net/http"
	"net/url"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// GameByCid - обработчик для получения игры по ее CID (componment id: уникальный идентификатор игры система/группа(провайдер)/id)
func GameByCid(w http.ResponseWriter, r *http.Request) {
	// Получаем CID из URL
	cid, err := url.QueryUnescape(chi.URLParam(r, "cid"))
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}
	// Получаем in_disabled из URL
	// in_disabled - флаг, который указывает, нужно ли искать игру в отключенных играх
	in_disabled := r.URL.Query().Get("in_disabled")
	// Получаем device и lang из URL
	device := chi.URLParam(r, "device")
	lang := chi.URLParam(r, "lang")

	// Получаем список игр из менеджера игр по устройству и языку
	if manager, found := games.GameManagers[device+"-"+lang]; found {
		// Ищем игру по CID в списке всех игр
		if v, ok := manager.ByCid[cid]; ok {
			resp := responses.MakeGameReponse(responses.StatusOk, "", 1, 0, 0, v)
			render.JSON(w, r, &resp)
			return
		}
		// Если игра не найдена, ищем ее в отключенных играх если in_disabled == "1"
		if in_disabled == "1" {
			if v, ok := manager.DisabledGames.ByCid[cid]; ok {
				resp := responses.MakeGameReponse(responses.StatusOk, "", 1, 0, 0, v)
				render.JSON(w, r, &resp)
				return
			}
		}
	} else {
		resp := responses.MakeGameReponse(responses.StatusError, "Games Manager not found", 0, 0, 0, nil)
		render.JSON(w, r, &resp)
		return
	}

	// Возвращаем ошибку, если игра не найдена
	resp := responses.MakeGameReponse(responses.StatusNotFound, "Game not found", 0, 0, 0, nil)
	render.JSON(w, r, &resp)
}
