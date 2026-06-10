package slegresponses

// BetResponse структура ответа на запрос ставки
type BetResponse struct {
	Balance       float64 `json:"balance"`
	TransactionID string  `json:"transaction_id"`
}

// MakeBetResponse создает ответ на запрос ставки
func MakeBetResponse(balance float64, tr_id string) BetResponse {
	return BetResponse{Balance: balance, TransactionID: tr_id}
}
