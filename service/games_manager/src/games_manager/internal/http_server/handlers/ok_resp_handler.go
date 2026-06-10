package handlers

import (
	"net/http"

	"github.com/go-chi/render"
)

// OkHanlder - обработчик для проверки работоспособности сервера
func OkHanlder(w http.ResponseWriter, r *http.Request) {
	render.PlainText(w, r, string("OK"))
}
