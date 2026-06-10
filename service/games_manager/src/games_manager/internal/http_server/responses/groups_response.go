package responses

import "games_manager/internal/games"

// GroupResponse - структура для ответа с группами
type GroupResponse struct {
	Status int               // статус ответа
	Error  string            // сообщение об ошибке
	Data   []games.GroupInfo // данные - список групп
}

func MakeGroupReponse(status int, error string) GroupResponse {
	gr := GroupResponse{
		Status: status,
		Error:  error,
		Data:   make([]games.GroupInfo, 0),
	}
	return gr
}
