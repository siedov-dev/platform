package routes

import (
	"games_manager/internal/http_server/handlers"

	"github.com/go-chi/chi/v5"
)

func SetRoutes(r *chi.Mux) {
	// ответ "ok"
	r.Get("/", handlers.OkHanlder)
	// ответ "pong"
	r.Get("/ping", handlers.Ping)
	// Перезагрузка игр
	r.Get("/reload", handlers.Reload)

	// Поиск игры по CID (componment id: уникальный идентификатор игры система/группа(провайдер)/id)
	r.Get("/byCid/{device}/{lang}/{cid}", handlers.GameByCid)
	// Игры по тегу (hot, new, etc)
	r.Get("/byTag/{device}/{lang}/{tag}", handlers.GamesByTag)
	// Игры по Лендинг группе
	r.Get("/byLndGroup/{device}/{lang}/{group}", handlers.GamesByLndGroup)

	// Все игры для устройства/языка
	r.Get("/games/{device}/{lang}", handlers.AllGames)
	// Количество игр и групп для устройства/языка
	r.Get("/games/stats/{device}/{lang}", handlers.AllGamesStats)
	// Поиск игры по группе, категории, тегу, имени.
	// peg_page - количество игр на странице, page - номер страницы
	r.Get("/search/{device}/{lang}/{groups}/{categories}/{tags}/{name}/{per_page}/{page}", handlers.GamesSearch)
	// Игры бонуса
	r.Get("/bonusGames/{device}/{lang}/{bonus_id}/{groups}/{categories}/{tags}/{name}/{per_page}/{page}", handlers.BonusGames)

	// Все игры провайдера(игровой системы) для устройства/языка
	r.Get("/provider/{device}/{lang}/{provider}/allGames", handlers.ProviderAllGames)
	// Игра провайдера(игровой системы) по id
	r.Get("/provider/{device}/{lang}/{provider}/getGame/{id}", handlers.ProviderGetGame)

	// Группы игры(провайдеры) игровой системы (провайдера)
	r.Get("/provider/{device}/{lang}/{provider}/getGroups", handlers.ProviderLoadGroups)
	// Игры группы(провайдера) игровой системы (провайдера)
	r.Get("/provider/{device}/{lang}/{provider}/groupGames/{groupid}", handlers.ProviderGroupGames)
	// Игра группы(провайдера) игровой системы (провайдера) по ид
	r.Get("/provider/{device}/{lang}/{provider}/getGroupGame/{groupid}/{id}", handlers.ProviderGetGroupGame)
	// Поиск игры в играх игровой системы (провайдера) по группе, категории, тегу, имени.
	r.Get("/provider/{device}/{lang}/{provider}/searchGames/{groups}/{categories}/{tags}/{name}/{per_page}/{page}", handlers.ProviderSearchGames)
	// Новые имена провайдеров для игровой системы
	r.Get("/providerNames/{system}", handlers.ProviderNames)
	// Игры по сегменту
	r.Get("/games/segment/{device}/{lang}/{segment}/{per_page}/{page}", handlers.ProviderGamesInSegment)
}
