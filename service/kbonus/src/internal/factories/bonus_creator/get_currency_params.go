package bonuscreator

import (
	"encoding/json"
	"errors"
	"kbonus/internal/service/container"
	"kbonus/internal/utils/currencyid"
	"strconv"
)

// GetBonusParamsFromJson извлекает параметры бонуса из JSON-строки и возвращает их в виде карты.
// Если currency равен 0, то устанавливает валюту по умолчанию (EUR).
// Если jsonParams пустой, возвращает ошибку.
// Если в jsonParams есть ключ "currency", то проверяет его значение и устанавливает currency.
// Если ключ "currency" не найден и keepParams равно false, то извлекает параметры для заданной валюты из конфигурации бонусов.
// Параметры, связанные с деньгами, такие как "money", "max_bet", "money_to_activate" и т.д., умножаются на коэффициент MoneyK из конфигурации валюты.
// Возвращает карту параметров для заданной валюты или ошибку, если что-то пошло не так.
func GetBonusParamsFromJson(jsonParams string, currency int8, keepParams bool) (map[string]any, error) {
	if currency == 0 {
		defaultCurrency, err := currencyid.GetCurrencyID("EUR") // Установка валюты по умолчанию, если не указана
		if err != nil {
			return nil, errors.New("invalid default currency")
		}
		currency = int8(defaultCurrency)
	}

	if jsonParams == "" {
		return nil, errors.New("jsonParams cannot be empty")
	}
	params := make(map[string]any)
	err := json.Unmarshal([]byte(jsonParams), &params)
	if err != nil {
		return nil, errors.New("invalid jsonParams format")
	}

	if val, ok := params["currency"]; ok {
		if currencyStr, ok := val.(string); ok {
			currencyID, err := currencyid.GetCurrencyID(currencyStr)
			if err != nil {
				return nil, errors.New("invalid currency in jsonParams")
			}
			currency = int8(currencyID)
		} else {
			return nil, errors.New("currency in jsonParams must be a string")
		}
	}
	if !keepParams {
		params, err = GetBonusParamsForCurrency(params, currency)
		if err != nil {
			return nil, err
		}
	}
	return params, nil
}

// GetBonusParamsForCurrency извлекает параметры бонуса для заданной валюты из карты параметров.
// Если параметры для этой валюты не найдены, то загружает конфигурацию бонусов для валюты из репозитория.
// Параметры, связанные с деньгами, такими как "money", "max_bet", "money_to_activate" и т.д., умножаются на коэффициент MoneyK из конфигурации валюты.
// Возвращает карту параметров для заданной валюты или ошибку, если что-то пошло не так.
func GetBonusParamsForCurrency(params map[string]any, currency int8) (map[string]any, error) {
	currName, err := currencyid.CurrencyID(currency).Name()
	if err != nil {
		return nil, err
	}
	// Проверяем, есть ли в параметрах ключ для текущей валюты
	currency_params_key := "params_" + currName
	if val, ok := params[currency_params_key]; ok {
		// Если есть, то проверяем, что это карта
		if currencyParams, ok := val.(map[string]any); ok {
			return currencyParams, nil
		}
		return nil, errors.New("currency params must be a map")
	}

	// Если нет, то загружаем конфигурацию бонусов для валюты
	bonus_params_coeffs, err := container.GetBonusCurrencyConfigDataProvider().GetBonusCurrencyConfig(currency)
	if err != nil {
		return nil, err
	}
	if bonus_params_coeffs == nil {
		return nil, errors.New("no bonus params found for currency " + currName)
	}
	// Если коэффициент MoneyK не задан, то используем 1.0 по умолчанию
	moneyK := 1.0
	if bonus_params_coeffs.MoneyK.Valid {
		moneyK = bonus_params_coeffs.MoneyK.Float64
	}

	// Преобразуем параметры, связанные с деньгами, умножая их на коэффициент MoneyK
	paramsToConvert := []string{"money", "min_sum", "max_sum", "win_limit", "max_win_sum", "max_win", "max_bet", "activation_sum"}
	for _, key := range paramsToConvert {
		if val, ok := params[key]; ok {
			if moneyVal, ok := val.(float64); ok {
				params[key] = moneyVal * moneyK
			} else if moneyVal, ok := val.(int64); ok {
				params[key] = float64(moneyVal) * moneyK
			} else if moneyVal, ok := val.(int); ok {
				params[key] = float64(moneyVal) * moneyK
			} else if moneyVal, ok := val.(float32); ok {
				params[key] = float64(moneyVal) * moneyK
			} else if moneyVal, ok := val.(string); ok {
				var moneyFloat float64
				moneyFloat, err = strconv.ParseFloat(moneyVal, 64) // Пробуем преобразовать строку в float64
				// Пробуем преобразовать строку в float64
				if err == nil {
					params[key] = moneyFloat * moneyK
				} else {
					return nil, errors.New(key + " in params must be a float64 or numeric type")
				}
			} else {
				return nil, errors.New(key + " in params must be a float64")
			}
		}
	}

	return params, nil
}

// GetBonusParamOfTypeInt извлекает параметр из карты параметров по ключу и преобразует его в int.
// Если параметр не найден или не может быть преобразован, возвращает ошибку.
// Поддерживаются типы int, int64, float64, float32 и string (строка должна быть числом).
func GetBonusParamOfTypeInt(params map[string]any, key string) (int, error) {
	if val, ok := params[key]; ok {
		if intVal, ok := val.(int); ok {
			return intVal, nil
		} else if intVal, ok := val.(int64); ok {
			return int(intVal), nil
		} else if intVal, ok := val.(float64); ok {
			return int(intVal), nil
		} else if intVal, ok := val.(float32); ok {
			return int(intVal), nil
		} else if strVal, ok := val.(string); ok {
			intVal, err := strconv.Atoi(strVal)
			if err != nil {
				return 0, errors.New(key + " in params must be an integer")
			}
			return intVal, nil
		}
	}
	return 0, errors.New(key + " not found in params or is not an integer")
}

// GetBonusParamOfTypeFloat извлекает параметр из карты параметров по ключу и преобразует его в float64.
// Если параметр не найден или не может быть преобразован, возвращает ошибку.
// Поддерживаются типы float64, float32, int, int64 и string (строка должна быть числом).
func GetBonusParamOfTypeFloat(params map[string]any, key string) (float64, error) {
	if val, ok := params[key]; ok {
		if floatVal, ok := val.(float64); ok {
			return floatVal, nil
		} else if floatVal, ok := val.(float32); ok {
			return float64(floatVal), nil
		} else if intVal, ok := val.(int); ok {
			return float64(intVal), nil
		} else if intVal, ok := val.(int64); ok {
			return float64(intVal), nil
		} else if strVal, ok := val.(string); ok {
			floatVal, err := strconv.ParseFloat(strVal, 64)
			if err != nil {
				return 0, errors.New(key + " in params must be a float64 or numeric type")
			}
			return floatVal, nil
		}
	}
	return 0, errors.New(key + " not found in params or is not a float")
}
