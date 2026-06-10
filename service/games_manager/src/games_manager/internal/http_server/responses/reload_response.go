package responses

// ReloadResponse - структура для ответа на запрос перезагрузки данных
// Status - статус ответа
// Error - сообщение об ошибке
// Time - время перезагрузки
// Пример ответа: {"status":200,"error":"","time":"0.123456789"}
// Пример ошибки: {"status":400,"error":"ошибка перезагрузки","time":""}
type ReloadResponse struct {
	Status int
	Error  string
	Time   string
}

func MakeReloadReponse(status int, error string, time string) ReloadResponse {
	return ReloadResponse{
		Status: status,
		Error:  error,
		Time:   time,
	}
}
