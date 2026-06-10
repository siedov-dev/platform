package handlers

import (
	"net/http"

	"github.com/go-chi/render"
)

// OkHanlder - обработчик для проверки работоспособности сервера
// Возвращает статус 200 OK и текст "pong"
func Ping(w http.ResponseWriter, r *http.Request) {
	render.PlainText(w, r, string("pong"))
}
