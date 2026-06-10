package natsrequests

import "ktournaments/internal/service/tournament"

// ErrorResponse - структура ответа с ошибкой
type ErrorResponse struct {
	Error string `json:"error"`
}

// BoolResponse - структура ответа с булевым значением
type BoolResponse struct {
	Resp bool `json:"resp"`
}

// TournamentResponse - структура ответа с турниром
type TournamentResponse struct {
	Error      string                 `json:"error"`
	Tournament *tournament.Tournament `json:"tournament,omitempty"`
}

// UserPositionResponse - структура ответа с позицией пользователя в турнире
type UserPositionResponse struct {
	Position int               `json:"position"`
	Winner   tournament.Winner `json:"player"`
}
