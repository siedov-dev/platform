package handlers

import (
	"net/http"

	"github.com/go-chi/render"
)

// OkHanlder обрабатывает HTTP-запросы и возвращает простой текстовый ответ "OK".
func OkHanlder(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()
	render.PlainText(w, r, string("OK"))
}
