package slegresponses

type RollbackResponse struct {
	Balance              float64  `json:"balance"`               // баланс после отката
	TransactionID        string   `json:"transaction_id"`        // ID транзакции отката
	RollbackTransactions []string `json:"rollback_transactions"` // транзакции, которые были откатаны
}

func MakeRollbackResponse(balance float64, transactionID string, rollbackTransactions []string) RollbackResponse {
	return RollbackResponse{
		Balance:              balance,
		TransactionID:        transactionID,
		RollbackTransactions: rollbackTransactions,
	}
}
