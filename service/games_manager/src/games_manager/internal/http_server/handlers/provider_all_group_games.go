package handlers

import (
	"games_manager/internal/games"
	"games_manager/internal/http_server/responses"
	"net/http"
	"net/url"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// ProviderGroupGames - обработчик для получения списка игр провайдера по группе
// принимает параметры:
// device - устройство (desktop или phone)
// lang - язык (например, ru, en и т.д.)
// provider - провайдер (например, "sleg")
// group - группа (например, "Amatic")
func ProviderGroupGames(w http.ResponseWriter, r *http.Request) {
	providerID, err := url.QueryUnescape(chi.URLParam(r, "provider"))
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}
	groupID, err := url.QueryUnescape(chi.URLParam(r, "groupid"))
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

	if provider, ok := manager.Providers[providerID]; ok {
		if group, found := provider.Groups[groupID]; found {
			resp := responses.MakeGameReponse(responses.StatusOk, "", len(group.AllGames), 0, 0, group.AllGames)
			render.JSON(w, r, &resp)
			return
		}
		resp := responses.MakeGameReponse(responses.StatusNotFound, "Group not found", 0, 0, 0, nil)
		render.JSON(w, r, &resp)
	}

	resp := responses.MakeGameReponse(responses.StatusNotFound, "Provider not found", 0, 0, 0, nil)
	render.JSON(w, r, &resp)
}
