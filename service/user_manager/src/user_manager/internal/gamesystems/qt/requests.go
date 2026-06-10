package qt

// transaction_request - структура запроса на транзакцию
type transaction_request struct {
	TxnType   string  `json:"txnType"`
	TxnId     string  `json:"txnId"`
	PlayerId  string  `json:"playerId"`
	RoundId   string  `json:"roundId"`
	Amount    float64 `json:"amount"`
	Currency  string  `json:"currency"`
	GameId    string  `json:"gameId"`
	BonusType string  `json:"bonusType"`
	BonusCode string  `json:"bonusCode"`
}

// rollback_request - структура запроса на возврат ставки
type rollback_request struct {
	BetId    string  `json:"betId"`
	TxnId    string  `json:"txnId"`
	PlayerId string  `json:"playerId"`
	RoundId  string  `json:"roundId"`
	Amount   float64 `json:"amount"`
	Currency string  `json:"currency"`
	GameId   string  `json:"gameId"`
}

// promotion_status_request - структура запроса на статус бонуса
type promotion_status_request struct {
	BonusId     string  `json:"bonusId"`
	PlayerId    string  `json:"playerId"`
	Status      string  `json:"status"`
	TotalPayout float64 `json:"totalPayout"`
}

// rewards_request - структура запроса на награду
type rewards_request struct {
	RewardType  string  `json:"rewardType"`
	RewardTitle string  `json:"rewardTitle"`
	TxnId       string  `json:"txnId"`
	PlayerId    string  `json:"playerId"`
	Amount      float64 `json:"amount"`
	Currency    string  `json:"currency"`
}
