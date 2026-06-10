package handlers

import (
	"fmt"
	"log/slog"
	"net/http"
	"user-manager/internal/logger"
	"user-manager/internal/pragplay"
	pplayresponses "user-manager/internal/pragplay/pplay_responses"

	"github.com/go-chi/render"
)

func PPlaySessionExpired(w http.ResponseWriter, r *http.Request) {
	log := logger.Get()
	pplay := pragplay.Make()

	hash := r.PostFormValue("hash")

	log.Info("pplay", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	log.Info("pplay", slog.String("PostForm", fmt.Sprintf("%v", r.PostForm)))

	postData := make(map[string]string)
	for key, values := range r.PostForm {
		postData[key] = values[0]
	}

	//check hash
	if hash != pplay.CalcHash(postData) {
		log.Error("pplay", slog.String("wrong hash", hash))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(5, "Invalid hash code"))
		return
	}

	pplay.SessionExpired(w, r)
}
