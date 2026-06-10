package handlers

import (
	"games_manager/internal/games"
	"games_manager/internal/http_server/responses"
	"net/http"
	"net/url"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// ProviderGetGroupGame - обработчик для получения игры группы провайдера по ID игры
// принимает параметры:
// device - устройство (например, "desktop" или "phone")
// lang - язык (например, "ru", "en" и т.д.)
// provider - провайдер (например, "sleg")
// groupid - ID группы (например, "Amatic")
// id - ID игры провайдера (например, "book_of_ra")
func ProviderGetGroupGame(w http.ResponseWriter, r *http.Request) {
	id, err := url.QueryUnescape(chi.URLParam(r, "id"))
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}

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
	in_disabled := r.URL.Query().Get("in_disabled")

	manager, found := games.GameManagers[device+"-"+lang]
	if !found {
		resp := responses.MakeGameReponse(responses.StatusError, "Games Manager not found", 0, 0, 0, nil)
		render.JSON(w, r, &resp)
		return
	}

	provider, found := manager.Providers[providerID]

	if !found {
		resp := responses.MakeGameReponse(responses.StatusError, "Provider not found", 0, 0, 0, nil)
		render.JSON(w, r, &resp)
		return
	}

	group, found := provider.Groups[groupID]

	if !found {
		resp := responses.MakeGameReponse(responses.StatusError, "Group not found", 0, 0, 0, nil)
		render.JSON(w, r, &resp)
		return
	}

	if v, ok := group.ByID[id]; ok {
		resp := responses.MakeGameReponse(responses.StatusOk, "", 1, 0, 0, v)
		render.JSON(w, r, &resp)
		return
	}

	if in_disabled == "1" {
		if v, ok := manager.DisabledGames.ByProviderGameID[providerID+":"+id]; ok {
			resp := responses.MakeGameReponse(responses.StatusOk, "", 1, 0, 0, v)
			render.JSON(w, r, &resp)
			return
		}
	}

	resp := responses.MakeGameReponse(responses.StatusNotFound, "Game not found", 0, 0, 0, nil)
	render.JSON(w, r, &resp)
}
