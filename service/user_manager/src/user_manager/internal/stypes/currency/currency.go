package currency

import "errors"

// ID валют
const (
	CURRENCY_RUB  = 1
	CURRENCY_UAH  = 2
	CURRENCY_USD  = 3
	CURRENCY_EUR  = 4
	CURRENCY_INR  = 5
	CURRENCY_mBTC = 6
	CURRENCY_USDT = 7
	CURRENCY_KZT  = 8
	CURRENCY_UZS  = 9
)

// CurrencyID представляет собой тип идентификатора валюты, используемый для работы с различными валютами в системе.
type CurrencyID int8

// Name возвращает строковое представление валюты, соответствующее идентификатору CurrencyID. Возвращает ошибку, если валюта неизвестна.
func (c CurrencyID) Name() (string, error) {
	return GetName(c)
}

// GetName возвращает строковое представление валюты по заданному идентификатору CurrencyID. Возвращает ошибку для неизвестных.
func GetName(curr_id CurrencyID) (string, error) {
	switch curr_id {
	case CURRENCY_RUB:
		return "RUB", nil
	case CURRENCY_UAH:
		return "UAH", nil
	case CURRENCY_USD:
		return "USD", nil
	case CURRENCY_EUR:
		return "EUR", nil
	case CURRENCY_INR:
		return "INR", nil
	case CURRENCY_mBTC:
		return "mBTC", nil
	case CURRENCY_USDT:
		return "USDT", nil
	case CURRENCY_KZT:
		return "KZT", nil
	case CURRENCY_UZS:
		return "UZS", nil
	}
	return "", errors.New("unknown currency code")
}

// GetCurrencyID возвращает идентификатор валюты CurrencyID по строковому коду валюты или ошибку, если код неизвестен.
func GetCurrencyID(c string) (CurrencyID, error) {
	switch c {
	case "RUB":
		return CURRENCY_RUB, nil
	case "UAH":
		return CURRENCY_UAH, nil
	case "USD":
		return CURRENCY_USD, nil
	case "EUR":
		return CURRENCY_EUR, nil
	case "INR":
		return CURRENCY_INR, nil
	case "mBTC":
		return CURRENCY_INR, nil
	case "USDT":
		return CURRENCY_USDT, nil
	case "KZT":
		return CURRENCY_KZT, nil
	case "UZS":
		return CURRENCY_UZS, nil
	}
	return 0, errors.New("unknown currency name")
}
