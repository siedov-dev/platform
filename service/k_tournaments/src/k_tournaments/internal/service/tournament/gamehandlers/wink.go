package gamehandlers

import (
	"encoding/json"
	"strconv"
)

// WinK - обработчик игры "Коэффициент выигрыша"
type WinK struct {
	params map[string]map[float64]float32
}

// WinPoints - обработка игры "Коэффициент выигрыша"
//
// Параметры:
// - user_Id: id пользователя
// - currency: валюта
// - bet: ставка
// - win: выигрыш
//
// Возвращает:
// - очки пользователя
func (b *WinK) WinPoints(user_Id int64, currency string, bet float64, win float64) float32 {
	// Если коэффициенты не установлены, то возвращаем 0
	if b.params[currency] == nil {
		return 0
	}
	// Ищем максимальный коэффициент выигрыша
	winPoints := float32(0)
	for val, points := range b.params[currency] {
		if win >= val && winPoints < points {
			winPoints = points
		}
	}
	return winPoints
}

// NewWinK - создание новой игры "Коэффициент выигрыша"
//
// Параметры:
// - params: параметры игры (json)
func NewWinK(params string) (*WinK, error) {
	// Инициализация мапы параметров
	var temp map[string][]struct {
		Amount string `json:"amount"`
		Mult   string `json:"mult"`
	}
	// Парсим параметры игры
	err := json.Unmarshal([]byte(params), &temp)
	if err != nil {
		return nil, err
	}

	result := make(map[string]map[float64]float32)
	// Парсим параметры игры
	for currency, values := range temp {
		// Инициализация мапы коэффициентов
		result[currency] = make(map[float64]float32)
		for _, value := range values {
			// Парсим сумму выигрыша
			amount, err := strconv.ParseFloat(value.Amount, 64)
			if err != nil {
				return nil, err
			}
			// Парсим коэффициент выигрыша
			mult, err := strconv.ParseFloat(value.Mult, 32)
			if err != nil {
				return nil, err
			}
			result[currency][amount] = float32(mult)
		}
	}

	return &WinK{params: result}, nil
}
