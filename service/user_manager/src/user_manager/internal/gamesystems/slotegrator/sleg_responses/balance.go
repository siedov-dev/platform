package slegresponses

// BalanceResponse структура ответа на запрос баланса
type BalanceResponse struct {
	Balance float64 `json:"balance"`
}

// MakeBalanceResponse создает ответ на запрос баланса
func MakeBalanceResponse(balance float64) BalanceResponse {
	return BalanceResponse{Balance: balance}
}
