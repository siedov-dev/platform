package handlers

import (
	"games_manager/internal/games"
	"games_manager/internal/http_server/responses"
	"net/http"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

func ProviderNames(w http.ResponseWriter, r *http.Request) {
	system := chi.URLParam(r, "system")
	if system == "" {
		resp := responses.MakeErrorResponse(responses.StatusError, "System is required")
		render.JSON(w, r, &resp)
		return
	}
	if names := games.GetSystemProvidersNewNames(system); names != nil {
		render.JSON(w, r, &names)
		return
	} else {
		resp := responses.MakeErrorResponse(responses.StatusError, "No provider names found for system "+system)
		render.JSON(w, r, &resp)
		return
	}
}
