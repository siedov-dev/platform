package qt

import "fmt"

// ErrorResponse - структура ответа на ошибку
type ErrorResponse struct {
	Code    string `json:"code"`
	Message string `json:"message"`
}

// MakeBalanceResponse - функция для создания ответа на запрос баланса
func MakeBalanceResponse(balance float64, currency string) string {
	return fmt.Sprintf(`{"balance":%.2f,"currency":"%s"}`, balance, currency)
}

// MakeTransactionResponse - функция для создания ответа на запрос транзакции
func MakeTransactionResponse(balance float64, referenceId string) string {
	return fmt.Sprintf(`{"balance":%.2f,"referenceId":"%s"}`, balance, referenceId)
}
