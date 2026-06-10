package handlers

import (
	"net/http"

	"github.com/go-chi/render"
)

// OkHandler - обработчик для проверки работоспособности сервера.
// Он возвращает статус 200 OK и текст "OK" в ответ на запрос.
func OkHandler(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()
	render.PlainText(w, r, string("OK"))
}
