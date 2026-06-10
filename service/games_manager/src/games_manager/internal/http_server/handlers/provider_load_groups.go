package handlers

import (
	"games_manager/internal/games"
	"games_manager/internal/http_server/responses"
	"net/http"
	"net/url"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// ProviderLoadGroups - обработчик для получения списка групп провайдера
// принимает параметры:
// device - устройство (например, "desktop" или "phone")
// lang - язык (например, "ru", "en" и т.д.)
// provider - провайдер (например, "sleg")
// Возвращает список групп в формате JSON
// Пример запроса: GET /provider/desktop/ru/sleg/loadGroups
func ProviderLoadGroups(w http.ResponseWriter, r *http.Request) {
	providerID, err := url.QueryUnescape(chi.URLParam(r, "provider"))
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}
	device := chi.URLParam(r, "device")
	lang := chi.URLParam(r, "lang")

	manager, found := games.GameManagers[device+"-"+lang]
	if !found {
		resp := responses.MakeGroupReponse(responses.StatusError, "Games Manager not found")
		render.JSON(w, r, &resp)
		return
	}

	provider, found := manager.Providers[providerID]

	if !found {
		resp := responses.MakeGroupReponse(responses.StatusError, "Provider not found")
		render.JSON(w, r, &resp)
		return
	}
	resp := responses.MakeGroupReponse(responses.StatusOk, "")

	for _, gi := range provider.Groups {
		resp.Data = append(resp.Data, gi.GetInfo(providerID))
	}

	render.JSON(w, r, &resp)
}
