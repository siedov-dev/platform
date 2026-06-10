package responses

import "user-manager/internal/service"

// GameSessionResponse представляет собой структуру ответа на запрос о состоянии игровой сессии.
// Содержит код состояния, сообщение об ошибке (если есть) и объект сессии игры.
type GameSessionResponse struct {
	Code        int                  `json:"cd"`
	Error       string               `json:"err"`
	GameSession *service.GameSession `json:"gs"`
}

// MakeGameSessionResponse создает объект GameSessionResponse из игровой сессии GameSession.
// Возвращает успешный ответ с кодом 200, пустой строкой ошибки и указанной игровой сессией.
func MakeGameSessionResponse(gs *service.GameSession) GameSessionResponse {
	return GameSessionResponse{Code: 200, Error: "", GameSession: gs}
}
