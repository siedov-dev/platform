package responses

import "games_manager/internal/games/games_t"

// SearchResponse - структура для ответа на запрос поиска игр
// содержит статус ответа, ошибку, общее количество игр, общее количество страниц,
// номер текущей страницы, группы игр и данные
// данные могут быть разными в зависимости от типа запроса
// например, для поиска игр по тегу это будет список игр,
// а для поиска игр по имени - информация об игре
type SearchResponse struct {
	Status     int                    // статус ответа
	Error      string                 // ошибка, если есть
	GamesTotal int                    // общее количество игр
	PagesTotal int                    // общее количество страниц
	Page       int                    // номер текущей страницы
	Groups     games_t.GamesGroupsSet // группы игр
	Data       interface{}            // данные, которые нужно вернуть
}

// MakeSearchResponse - функция для создания ответа на запрос поиска игр
// принимает статус ответа, ошибку, общее количество игр, общее количество страниц,
// номер текущей страницы, группы игр и данные
// возвращает структуру SearchResponse
func MakeSearchResponse(status int, error string, games_total, pages_total, page int, groups games_t.GamesGroupsSet, data interface{}) SearchResponse {
	return SearchResponse{
		Status:     status,
		Error:      error,
		GamesTotal: games_total,
		PagesTotal: pages_total,
		Page:       page,
		Groups:     groups,
		Data:       data,
	}
}
