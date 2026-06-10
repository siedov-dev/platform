package gamehandlers

import (
	"encoding/json"
	"math"
	"strconv"
)

// BetX - обработчик игры "По сумме ставки"
type BetX struct {
	step map[string]float64 // Шаг игры по валюте
}

// WinPoints - обработка игры "По сумме ставки"
// Вычисляет количество очков за игру по сумме ставки / установленному шагу
// Например:
// - ставка 100, шаг 10, то очки = 10
// - ставка 100, шаг 0.1, то очки = 1000
//
// Параметры:
// - user_Id: id пользователя
// - currency: валюта
// - bet: ставка
// - win: выигрыш
//
// Возвращает:
// - очки пользователя
func (b *BetX) WinPoints(user_Id int64, currency string, bet float64, win float64) float32 {
	// Если шаг игры не установлен, то возвращаем 0
	if b.step[currency] == 0 {
		return 0
	}
	return float32(math.Floor(bet / b.step[currency]))
}

// NewBetX - создание новой игры "По сумме ставки"
//
// Параметры:
// - params: параметры игры (json)
func NewBetX(params string) (*BetX, error) {
	b := &BetX{}
	// Инициализация шага игры
	b.step = make(map[string]float64)
	// Если параметры игры не пустые, то парсим их
	if params != "" {
		// Инициализация мапы параметров
		p := make(map[string]any)
		err := json.Unmarshal([]byte(params), &p)
		if err != nil {
			return nil, err
		}
		// Парсим параметры игры
		for k, v := range p {
			// Если параметр является строкой, то парсим его как float64
			if val, ok := v.(string); ok {
				b.step[k], _ = strconv.ParseFloat(val, 64)
			}
			// Если параметр является float64, то используем его
			if val, ok := v.(float64); ok {
				b.step[k] = val
			}
		}
	}
	return b, nil
}
