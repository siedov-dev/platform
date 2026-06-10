package responses

// BonusStatusResponse представляет структуру для ответа по статусу бонуса.
// Включает код ответа, описание ошибок, ID бонуса, статус бонуса и данные о статусной панели.
type BonusStatusResponse struct {
	Code    int    `json:"cd"`
	Error   string `json:"err"`
	BonusID int    `json:"bid"`
	Status  int    `json:"st"`
}

// MakeBonusStatusResponse создает объект BonusStatusResponse из входных данных: кода, ошибки, ID бонуса, статуса бонуса и StatusPad.
func MakeBonusStatusResponse(cd int, err string, bid int, st int) BonusStatusResponse {
	return BonusStatusResponse{
		Code:    cd,
		Error:   err,
		BonusID: bid,
		Status:  st,
	}
}
