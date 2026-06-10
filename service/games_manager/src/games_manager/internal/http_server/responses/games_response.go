package responses

// GamesResponse - структура для ответа на запрос игр
// содержит статус ответа, ошибку, общее количество игр, общее количество страниц,
// номер текущей страницы и данные
// данные могут быть разными в зависимости от типа запроса
// например, для получения всех игр это будет список игр,
// а для получения игры по CID - информация об игре
// Статус ответа - 200, если все хорошо, 400, если ошибка
// 404, если игра не найдена и 500, если произошла ошибка сервера
type GamesResponse struct {
	Status     int         // статус ответа
	Error      string      // ошибка, если есть
	GamesTotal int         // общее количество игр
	PagesTotal int         // общее количество страниц
	Page       int         // номер текущей страницы
	Data       interface{} // данные, которые нужно вернуть
}

// MakeGameReponse - функция для создания ответа на запрос игр
// принимает статус ответа, ошибку, общее количество игр, общее количество страниц,
// номер текущей страницы и данные
func MakeGameReponse(status int, error string, games_total, pages_total, page int, data interface{}) GamesResponse {
	return GamesResponse{
		Status:     status,
		Error:      error,
		GamesTotal: games_total,
		PagesTotal: pages_total,
		Page:       page,
		Data:       data,
	}
}
