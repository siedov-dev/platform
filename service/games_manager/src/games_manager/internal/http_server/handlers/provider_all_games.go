package handlers

import (
	"games_manager/internal/games"
	"games_manager/internal/http_server/responses"
	"net/http"
	"net/url"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// ProviderAllGames - обработчик для получения списка игр по провайдеру
// принимает параметры:
// device - устройство (например, "desktop" или "phone")
// lang - язык (например, "ru", "en" и т.д.)
// provider - провайдер (например, "sleg")
// Возвращает список игр в формате JSON
// Пример запроса: GET /provider/desktop/ru/sleg/allGames
func ProviderAllGames(w http.ResponseWriter, r *http.Request) {
	providerID, err := url.QueryUnescape(chi.URLParam(r, "provider"))
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}
	device := chi.URLParam(r, "device")
	lang := chi.URLParam(r, "lang")

	manager, found := games.GameManagers[device+"-"+lang]
	if !found {
		resp := responses.MakeGameReponse(responses.StatusError, "Games Manager not found", 0, 0, 0, nil)
		render.JSON(w, r, &resp)
		return
	}

	if v, ok := manager.ByProviders[providerID]; ok {
		resp := responses.MakeGameReponse(responses.StatusOk, "", len(v), 0, 0, v)
		render.JSON(w, r, &resp)
		return
	}

	resp := responses.MakeGameReponse(responses.StatusNotFound, "Provider not found", 0, 0, 0, nil)
	render.JSON(w, r, &resp)
}
