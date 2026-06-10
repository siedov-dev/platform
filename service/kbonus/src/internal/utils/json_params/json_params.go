package jsonparams

import (
	"encoding/json"
	"errors"
	"fmt"
	"strconv"
)

func GetParamOfTypeInt(params string, key string) (int, error) {
	var jsonParams map[string]any
	if err := json.Unmarshal([]byte(params), &jsonParams); err != nil {
		return 0, errors.New("invalid json")
	}
	if val, ok := jsonParams[key]; ok {
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

func GetParamOfTypeFloat(params string, key string) (float64, error) {
	var jsonParams map[string]any
	if err := json.Unmarshal([]byte(params), &jsonParams); err != nil {
		return 0, errors.New("invalid json")
	}
	if val, ok := jsonParams[key]; ok {
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

func GetParamOfTypeString(params string, key string) (string, error) {
	var jsonParams map[string]any
	if err := json.Unmarshal([]byte(params), &jsonParams); err != nil {
		return "", errors.New("invalid json")
	}
	if val, ok := jsonParams[key]; ok {
		if strVal, ok := val.(string); ok {
			return strVal, nil
		} else if strVal, ok := val.(fmt.Stringer); ok {
			return strVal.String(), nil
		} else if numVal, ok := val.(int); ok {
			return strconv.Itoa(numVal), nil
		} else if numVal, ok := val.(int64); ok {
			return strconv.FormatInt(numVal, 10), nil
		} else if numVal, ok := val.(float64); ok {
			return strconv.FormatFloat(numVal, 'f', -1, 64), nil
		} else if numVal, ok := val.(float32); ok {
			return strconv.FormatFloat(float64(numVal), 'f', -1, 32), nil
		} else {
			return "", errors.New(key + " in params must be a string or convertible to string")
		}
	}
	return "", errors.New(key + " not found in params or is not a string")
}
