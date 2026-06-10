package responses

type ErrorResponse struct {
	Status int    // статус ответа
	Error  string // ошибка, если есть
}

func MakeErrorResponse(status int, error string) ErrorResponse {
	return ErrorResponse{
		Status: status,
		Error:  error,
	}
}
