package handlers

import (
	"games_manager/internal/games"
	"games_manager/internal/http_server/responses"
	"net/http"
	"net/url"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// GamesByTag - обработчик для получения списка игр по тегу
// принимает параметры:
// device - устройство (desktop или phone)
// lang - язык (например, ru, en и т.д.)
// tag - тег игры (например, "hot", "new")
// Возвращает список игр в формате JSON
// Пример запроса: GET /games/byTag/desktop/ru/hot
func GamesByTag(w http.ResponseWriter, r *http.Request) {
	// Получаем тег из URL
	tag, err := url.QueryUnescape(chi.URLParam(r, "tag"))
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}
	// Получаем device и lang из URL
	device := chi.URLParam(r, "device")
	lang := chi.URLParam(r, "lang")

	// Получаем список игр из менеджера игр по устройству и языку
	manager, found := games.GameManagers[device+"-"+lang]
	if !found {
		resp := responses.MakeGameReponse(responses.StatusError, "Games Manager not found", 0, 0, 0, nil)
		render.JSON(w, r, &resp)
		return
	}

	if v, ok := manager.ByTag[tag]; ok {
		// Если игры найдены, возвращаем их в ответе
		resp := responses.MakeGameReponse(responses.StatusOk, "", len(v), 0, 0, v)
		render.JSON(w, r, &resp)
		return
	}

	// Возвращаем ошибку, если тег не найден
	resp := responses.MakeGameReponse(responses.StatusNotFound, "Provider not found", 0, 0, 0, nil)
	render.JSON(w, r, &resp)
}
