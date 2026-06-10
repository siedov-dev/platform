package container

type RefWallet interface {
	ChangeRefBalance(userID uint64, amount float64, currency string) error              // Изменение баланса реферала
	ChangeAccumulativeBalance(userID uint64, amount float64, currency string) error     // Изменение накопительного баланса реферала
	TransferRefMoney(userID uint64, currency string) (transferred float64, err error)   // Перевод реферальных денег
	TransferAccumMoney(userID uint64, currency string) (transferred float64, err error) // Перевод накопительных денег
	TransferAllAccumulatedMoney(userID uint64) (TransferAllAccumMoneyResponse, error)   // Перевод всех накопленных средств
}

type TransferAllAccumMoneyResponse struct {
	Error     string          `json:"err"`     // Ошибка, если есть
	ErrorCode int             `json:"cd"`      // Код ошибки, если есть
	UserID    int64           `json:"user_id"` // ID пользователя, которому переводятся все накопленные средства
	Amounts   map[int]float64 `json:"amounts"` // Сумма переведенных средств по валютам
}
