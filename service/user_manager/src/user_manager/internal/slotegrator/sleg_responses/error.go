package slegresponses

// ErrorResponse структура ответа на ошибку
type ErrorResponse struct {
	Code     string `json:"error_code"`
	ErrorStr string `json:"error_description"`
}

// GetCode возвращает код ошибки
func (er ErrorResponse) GetCode() string {
	return er.Code
}

// Error возвращает описание ошибки
func (er ErrorResponse) Error() string {
	return er.ErrorStr
}

// MakeErrorResponse создает ответ на ошибку
func MakeErrorResponse(code string, err error) ErrorResponse {
	return ErrorResponse{Code: code, ErrorStr: err.Error()}
}
