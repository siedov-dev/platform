package routes

import (
	"kbonus/internal/server/handlers"

	"github.com/go-chi/chi/v5"
)

// SetRoutes устанавливает маршруты для сервера
func SetRoutes(r *chi.Mux) {
	r.Get("/", handlers.OkHandler)               // Проверка работоспособности сервера
	r.Post("/api/{method}", handlers.APIHandler) // API хендлер
}
