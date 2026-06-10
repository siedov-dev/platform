package responses

import (
	"net/http"
)

// WalletBonusResponse представляет ответ с информацией о бонусе для модификации отображаемого баланса кошелька.
// Code содержит код ответа.
// Error содержит текст ошибки, если она произошла.
// BonusId идентифицирует бонус.
type WalletBonusResponse struct {
	Code    int    `json:"cd"`
	Error   string `json:"err"`
	BonusId int64  `json:"bid"`
}

// MakeWalletBonusResponseResponse создает объект WalletBonusResponse с успешным кодом ответа и переданным идентификатором бонуса.
func MakeWalletBonusResponseResponse(id int64) WalletBonusResponse {
	return WalletBonusResponse{
		Code:    http.StatusOK,
		Error:   "",
		BonusId: id,
	}
}
