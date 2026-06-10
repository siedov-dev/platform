// Package natsrequests содержит структуры запросов для NATS
// Одна структура запроса может использоваться для нескольких обработчиков
// В зависимости от того, какие данные нужны для обработки запроса
package natsrequests

// ActiveTournamentsRequest - запрос на получение активных турниров
type ActiveTournamentsRequest struct {
	UserID     int  `json:"user_id"`     // ID пользователя
	CurrencyID int  `json:"currency_id"` // ID валюты
	Test       bool `json:"test"`        // Флаг теста
}

// GetTournamentRequest - запрос на получение турнира
// Используется для получения турнира по ID
type GetTournamentRequest struct {
	TournamentId uint64 `json:"id"` // ID турнира
}

// TournamentWithCurrencyRequest - запрос на получение турнира
// с данными для указанной валюты
type TournamentWithCurrencyRequest struct {
	TournamentId uint64 `json:"id"`       // ID турнира
	Currency     string `json:"currency"` // Валюта
}

// TournamentDescriptionRequest - запрос на получение описания турнира
// Используется для получения описания турнира на разных языках
type TournamentDescriptionRequest struct {
	TournamentId uint64 `json:"id"`   // ID турнира
	Lang         string `json:"lang"` // Язык
}

// TournamentStateRequest - запрос на изменение статуса турнира
type TournamentStateRequest struct {
	TournamentId uint64 `json:"id"`    // ID турнира
	State        int    `json:"state"` // Статус турнира
}

// TournamentUserRequest - запрос на добавление участника в турнир
type TournamentUserRequest struct {
	TournamentId uint64 `json:"id"`      // ID турнира
	UserID       int    `json:"user_id"` // ID пользователя
}

// ActiveGameTournamentRequest - запрос на получение турнира для игры
type ActiveGameTournamentRequest struct {
	UserID     int    `json:"user_id"`     // ID пользователя
	Currency   string `json:"currency"`    // Валюта
	Test       bool   `json:"test"`        // Флаг теста
	GameSystem string `json:"game_system"` // Система игры
	GameID     string `json:"game_id"`     // ID игры
}

// GetWinnersRequest - запрос на получение победителей турнира
// Используется для получения турнирной таблицы
type GetWinnersRequest struct {
	TournamentId uint64 `json:"id"`              // ID турнира
	Limit        int    `json:"limit,omitempty"` // Лимит таблицы
}
