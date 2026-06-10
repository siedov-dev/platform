package handlers

import (
	"games_manager/internal/games"
	"games_manager/internal/http_server/responses"
	"net/http"
	"net/url"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// GamesByLndGroup - обработчик для получения списка игр по группе
// принимает параметры:
// device - устройство (desktop или phone)
// lang - язык (например, ru, en и т.д.)
// group - лендинг группа игр (например, "topTableGames", "topLiveGames")
// Возвращает список игр в формате JSON
func GamesByLndGroup(w http.ResponseWriter, r *http.Request) {
	// Получаем группу из URL
	group, err := url.QueryUnescape(chi.URLParam(r, "group"))
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

	// Ищем игры по лендинг группе в списке всех игр
	if v, ok := manager.LandingGroups[group]; ok {
		// Если игры найдены, возвращаем их в ответе
		// v - это срез игр, которые соответствуют лендинг группе
		resp := responses.MakeGameReponse(responses.StatusOk, "", len(v), 0, 0, v)
		render.JSON(w, r, &resp)
		return
	}

	// Возвращаем ошибку, если группа не найдена
	resp := responses.MakeGameReponse(responses.StatusNotFound, "Landing group not found "+group, 0, 0, 0, nil)
	render.JSON(w, r, &resp)
}
