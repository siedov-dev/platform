// Пакет response содержит структуры для формирования ответов API.
package response

// ErrorResponse представляет собой структуру ответа с ошибкой.
type ErrorResponse struct {
	Status int    `json:"status"` // Код статуса HTTP
	Error  string `json:"error"`  // Сообщение об ошибке
}

// OkResponse представляет собой структуру ответа с успешным статусом.
// Используется для подтверждения успешного выполнения запроса.
type OkResponse struct {
	Status  int    `json:"status"`  // Код статуса HTTP
	Message string `json:"message"` // Сообщение об успешном выполнении
}
