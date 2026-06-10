package routes

import (
	"kipinfo/internal/server/handlers"

	"github.com/go-chi/chi/v5"
)

// SetRoutes устанавливает маршруты для сервера
func SetRoutes(r *chi.Mux) {
	r.Get("/", handlers.OkHanlder)                                     // Проверка работоспособности сервера
	r.Get("/ip/{ip}", handlers.IpHanlder)                              // Получение информации по IP
	r.Get("/ipnocache/{ip}", handlers.IpNoCacheHanlder)                // Получение информации по IP без кеша
	r.Get("/isblocked/{ip}", handlers.IsBlockedHanlder)                // Проверка, заблокирован ли IP
	r.Get("/block/{ip}/{block_to}", handlers.BlockHanlder)             // Блокировка IP
	r.Get("/whitelist/{ip}/{isWhiteList}", handlers.WhitelistHanlder)  // Установка IP в белый список
	r.Get("/unblock/{ip}", handlers.UnBlockHanlder)                    // Разблокировка IP
	r.Get("/reload/BlockedCountries", handlers.ReloadBlockedCountries) // Перезагрузка заблокированных стран
}
