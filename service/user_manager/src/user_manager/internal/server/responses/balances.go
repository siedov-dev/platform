package responses

import (
	"net/http"
	"user-manager/internal/service"
	"user-manager/internal/stypes/currency"
)

// WalletBalance представляет собой структуру для хранения информации о балансе кошелька пользователя.
// Содержит значение баланса в основной валюте, значения в центах, данные о валюте, состояние активности и бонусы.
type WalletBalance struct {
	Value    float64 `json:"v"`  // Value представляет собой числовое значение баланса кошелька в основной валюте в формате с плавающей точкой.
	Cents    int64   `json:"vc"` // Cents представляет собой числовое значение баланса в центах.
	CurrName string  `json:"cn"` // CurrName представляет собой строковое значение, содержащее название валюты, связанной с балансом кошелька.
	CurrId   int8    `json:"ci"` // CurrId представляет собой идентификатор валюты, связанный с балансом кошелька, хранимый в виде 8-битного целого числа.
	Active   bool    `json:"a"`  // Active указывает на состояние активности кошелька: true - активен, false - неактивен.
	Bonus    int64   `json:"b"`  // Bonus представляет собой целочисленное значение бонусов, связанных с балансом кошелька, выраженное в 64-битном формате.
}

// BalancesResponse представляет собой ответ, содержащий информацию о балансах кошельков пользователя.
// Поле Code указывает на статус-код ответа.
// Поле Error предоставляет текст ошибки в случае неудачи операции.
// Поле Active указывает текущую активную валюту.
// Поле Values содержит отображение балансов кошелька, где ключом выступает идентификатор валюты CurrencyID.
type BalancesResponse struct {
	Code   int    `json:"cd"`
	Error  string `json:"err"`
	Active int    `json:"a"`
	Values map[currency.CurrencyID]WalletBalance
}

// MakeBalancesResponse создает и возвращает BalancesResponse на основе переданной карты кошельков.
// Использует данные из каждого Wallet для наполнения структуры ответа.
// Код ответа всегда http.StatusOK, а сообщение об ошибке пустое.
func MakeBalancesResponse(v map[currency.CurrencyID]*service.Wallet) BalancesResponse {
	b := BalancesResponse{}
	b.Code = http.StatusOK
	b.Error = ""
	b.Values = make(map[currency.CurrencyID]WalletBalance, len(v))
	for ci, w := range v {
		b.Values[ci] = WalletBalance{
			Value:    w.GetBalance(),
			Cents:    w.GetCents(),
			CurrId:   int8(ci),
			CurrName: w.GetCurrencyName(),
			Active:   w.DbWallet.Active,
			Bonus:    w.GetBonusId(),
		}
	}
	return b
}
