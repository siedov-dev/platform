package responses

// ErrorResponse представляет структуру для передачи информации об ошибке в формате JSON.
// Code содержит числовой код ошибки.
// Error содержит текстовое описание или сообщение об ошибке.
type ErrorResponse struct {
	Code  int    `json:"cd"`
	Error string `json:"err"`
}

// MakeErrorResponse создает объект ErrorResponse на основе кода ошибки и объекта ошибки.
// code - числовой код ошибки.
// err - объект ошибки, из которого извлекается сообщение.
// Возвращает ErrorResponse с заданным кодом и сообщением об ошибке.
func MakeErrorResponse(code int, err error) ErrorResponse {
	return ErrorResponse{Code: code, Error: err.Error()}
}
