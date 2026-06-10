package responses

import (
	"net/http"
)

// CurrencyNameResponse представляет собой структуру для передачи ответа с информацией о названии валюты.
// Поле Code содержит HTTP-код ответа.
// Поле Error содержит сообщение об ошибке, если таковая произошла.
// Поле CurrName содержит строковое название валюты.
type CurrencyNameResponse struct {
	Code     int    `json:"cd"`
	Error    string `json:"err"`
	CurrName string `json:"cn"`
}

// MakeCurrencyNameResponse создает и возвращает структуру CurrencyNameResponse с указанным названием валюты.
func MakeCurrencyNameResponse(name string) CurrencyNameResponse {
	return CurrencyNameResponse{
		Code:     http.StatusOK,
		Error:    "",
		CurrName: name,
	}
}

// CurrencyIntCodeResponse предоставляет структуру для хранения кода ответа, текста ошибки и числового международного кода валюты.
type CurrencyIntCodeResponse struct {
	Code    int    `json:"cd"`
	Error   string `json:"err"`
	IntCode int64  `json:"ic"`
}

// MakeCurrencyIntCodeResponse создает объект CurrencyIntCodeResponse с заданным числовым международным кодом валюты и кодом успешного ответа.
func MakeCurrencyIntCodeResponse(code int64) CurrencyIntCodeResponse {
	return CurrencyIntCodeResponse{
		Code:    http.StatusOK,
		Error:   "",
		IntCode: code,
	}
}

// CurrencyHallIdResponse представляет данные ответа о зале валют с идентификатором.
// Code указывает код выполнения операции.
// Error содержит текст сообщения об ошибке, если она возникла.
// HallID представляет собой идентификатор зала.
type CurrencyHallIdResponse struct {
	Code   int    `json:"cd"`
	Error  string `json:"err"`
	HallID int64  `json:"hid"`
}

// MakeCurrencyHallIdResponse создает и возвращает объект CurrencyHallIdResponse с указанным идентификатором зала.
func MakeCurrencyHallIdResponse(hid int64) CurrencyHallIdResponse {
	return CurrencyHallIdResponse{
		Code:   http.StatusOK,
		Error:  "",
		HallID: hid,
	}
}

// CurrencyMaxAutopayoutResponse представляет ответ с информацией о максимальной сумме для автоматической выплаты.
// Code содержит код ответа HTTP.
// Error содержит текст ошибки, если она возникла.
// Max представляет максимальную сумму выплаты.
type CurrencyMaxAutopayoutResponse struct {
	Code  int     `json:"cd"`
	Error string  `json:"err"`
	Max   float64 `json:"max"`
}

// MakeCurrencyMaxAutopayoutResponse создаёт и возвращает объект CurrencyMaxAutopayoutResponse с указанной максимальной суммой.
func MakeCurrencyMaxAutopayoutResponse(max float64) CurrencyMaxAutopayoutResponse {
	return CurrencyMaxAutopayoutResponse{
		Code:  http.StatusOK,
		Error: "",
		Max:   max,
	}
}
