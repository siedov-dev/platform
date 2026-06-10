package responses

import (
	"net/http"
)

// BalanceResponse представляет собой структуру ответа, содержащую баланс счета в различных форматах и деталях.
type BalanceResponse struct {
	Code     int     `json:"cd"`  // Code содержит код состояния HTTP ответа.
	Error    string  `json:"err"` // Error описывает текст ошибки, если она произошла.
	Value    float64 `json:"v"`   // Value представляет основное значение баланса в формате float64.
	Cents    int64   `json:"vc"`  // Cents хранит значение баланса в центах как целое число.
	CurrName string  `json:"cn"`  // CurrName содержит строковое название валюты.
	CurrId   int8    `json:"ci"`  // CurrId представляет код валюты в формате int8.
	Active   bool    `json:"a"`   // Active указывает, активен ли данный счет, true или false.
	Bonus    int64   `json:"b"`   // Bonus хранит ид бонуса, модифицирующего отображение основного баланса.
}

// AmountResponse представляет ответ, содержащий информацию о сумме и валюте.
type AmountResponse struct {
	Code     int     `json:"cd"`  // Code определяет код статуса HTTP-ответа.
	Error    string  `json:"err"` // Error содержит сообщение об ошибке, если таковая имеется.
	Value    float64 `json:"v"`   // Value указывает значение суммы.
	CurrName string  `json:"cn"`  // CurrName обозначает название валюты.
	CurrId   int8    `json:"ci"`  // CurrId идентифицирует код валюты в виде числа.
}

// MakeBalanceResponse создает и возвращает структуру BalanceResponse, содержащую информацию о балансе счета.
//
//		Параметры:
//	 - v - значение суммы в формате float64.
//	 - cents - значение баланса в центах как целое число.
//	 - curr - строковое название валюты.
//	 - ci - числовой код валюты в формате int8.
//	 - active - указывает, активен ли данный счет, true или false.
//
// Возвращает: экземпляр BalanceResponse с установленными значениями.
func MakeBalanceResponse(v float64, cents int64, curr string, ci int8, active bool) BalanceResponse {
	return BalanceResponse{
		Code:     http.StatusOK,
		Error:    "",
		Value:    v,
		Cents:    cents,
		CurrName: curr,
		CurrId:   ci,
		Active:   active,
	}
}

// MakeAmountResponse создает объект ответа AmountResponse с указанным значением, валютой и кодом валюты.
//
//		Параметры:
//	 - v - значение суммы в формате float64.
//	 - curr - строковое название валюты.
//	 - ci - числовой код валюты в формате int8.
//
// Возвращает: экземпляр AmountResponse с установленными значениями.
func MakeAmountResponse(v float64, curr string, ci int8) AmountResponse {
	return AmountResponse{
		Code:     http.StatusOK,
		Error:    "",
		Value:    v,
		CurrName: curr,
		CurrId:   ci,
	}
}
