// Пакет response содержит структуры для формирования ответов API.
package response

import "kipinfo/internal/db/models"

// ErrorResponse представляет собой структуру ответа с ошибкой.
type ErrorResponse struct {
	Status int    `json:"status"` // Код статуса HTTP
	Error  string `json:"error"`  // Сообщение об ошибке
}

// IpInfoResponse представляет собой структуру ответа с информацией о IP-адресе.
type IpInfoResponse struct {
	Status int                  `json:"status"` // Код статуса HTTP
	Data   models.IpInfoDBModel `json:"data"`   // Информация о IP-адресе
}

// OkResponse представляет собой структуру ответа с успешным статусом.
// Используется для подтверждения успешного выполнения запроса.
type OkResponse struct {
	Status  int    `json:"status"`  // Код статуса HTTP
	Message string `json:"message"` // Сообщение об успешном выполнении
}

// IsBlockedResponse представляет собой структуру ответа с информацией о блокировке IP-адреса.
type IsBlockedResponse struct {
	Status  int    `json:"status"`  // Код статуса HTTP
	Blocked bool   `json:"blocked"` // Флаг блокировки IP-адреса
	Reason  string `json:"reason"`  // Причина блокировки (ip - заблокирован по ip, country - заблокирована страна)
}
