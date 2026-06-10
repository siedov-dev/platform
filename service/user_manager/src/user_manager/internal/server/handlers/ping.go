// Package handlers содержит обработчики HTTP запросов
package handlers

import (
	"net/http"

	"github.com/go-chi/render"
)

// Ping обрабатывает HTTP запрос для проверки доступности сервера
// и отвечает строкой "pong"
func Ping(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	render.PlainText(w, r, string("pong"))
}
