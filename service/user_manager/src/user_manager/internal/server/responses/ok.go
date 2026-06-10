package responses

// OkResponse представляет ответ с кодом выполнения и сообщением об ошибке.
// Поле Code содержит числовой код результата.
// Поле Error содержит текст ошибки, если таковая имеется.
type OkResponse struct {
	Code  int    `json:"cd"`
	Error string `json:"err"`
}

// MakeOkResponse создает и возвращает объект OkResponse с кодом ответа 200 и пустым сообщением об ошибке.
func MakeOkResponse() OkResponse {
	return OkResponse{Code: 200, Error: ""}
}
