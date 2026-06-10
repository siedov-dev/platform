package natsuserwallet

import (
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
)

// BalanceRequest представляет собой структуру для запроса баланса.
type BalanceRequest struct {
	UserId     stypes.UserID         `json:"user_id"`  // UserID представляет идентификатор пользователя.
	CurrencyID currencyid.CurrencyID `json:"currency"` // CurrencyID обозначает идентификатор валюты, используемый для указания типа валюты в запросе.
}

// AmountRequest используется для установки или изменения значений в кошельках.
type AmountRequest struct {
	UserId     stypes.UserID         `json:"user_id"`  // UserID представляет идентификатор пользователя.
	CurrencyID currencyid.CurrencyID `json:"currency"` // CurrencyID обозначает идентификатор валюты, используемый для указания типа валюты в запросе.
	Amount     float64               `json:"amount"`
}

// BalanceResponse представляет собой структуру ответа, содержащую баланс счета в различных форматах и деталях.
type BalanceResponse struct {
	Code     int     `json:"cd"`  // Code содержит код состояния HTTP ответа.
	Error    string  `json:"err"` // Error описывает текст ошибки, если она произошла.
	Value    float64 `json:"v"`   // Value представляет основное значение баланса в формате float64.
	Cents    int64   `json:"vc"`  // Cents хранит значение баланса в центах как целое число.
	CurrName string  `json:"cn"`  // CurrName содержит строковое название валюты.
	CurrId   int8    `json:"ci"`  // CurrId представляет код валюты в формате int8.
	Active   bool    `json:"a"`   // Active указывает, активен ли данный счет, true или false.
	Bonus    int64   `json:"b"`   // Bonus хранит ид бонуса, модифицирующего отображение основного баланса.
}

// AmountResponse представляет ответ, содержащий информацию о сумме и валюте.
type AmountResponse struct {
	Code     int     `json:"cd"`  // Code определяет код статуса HTTP-ответа.
	Error    string  `json:"err"` // Error содержит сообщение об ошибке, если таковая имеется.
	Value    float64 `json:"v"`   // Value указывает значение суммы.
	CurrName string  `json:"cn"`  // CurrName обозначает название валюты.
	CurrId   int8    `json:"ci"`  // CurrId идентифицирует код валюты в виде числа.
}
