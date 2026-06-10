package gamehandlers

import (
	"encoding/json"
	"math"
	"strconv"
)

// WinX - обработчик игры "По сумме выигрыша"
type WinX struct {
	step map[string]float64 // Шаг игры по валюте
}

// WinPoints - обработка игры "По сумме выигрыша"
//
// Параметры:
// - user_Id: id пользователя
// - currency: валюта
// - bet: ставка
// - win: выигрыш
//
// Возвращает:
// - очки пользователя
func (b *WinX) WinPoints(user_Id int64, currency string, bet float64, win float64) float32 {
	// Если шаг игры не установлен, то возвращаем 0
	if b.step[currency] == 0 {
		return 0
	}
	return float32(math.Floor(win / b.step[currency]))
}

// NewWinX - создание новой игры "По сумме выигрыша"
//
// Параметры:
// - params: параметры игры (json)
func NewWinX(params string) (*WinX, error) {
	b := &WinX{}
	b.step = make(map[string]float64)
	if params != "" {
		p := make(map[string]any)
		err := json.Unmarshal([]byte(params), &p)
		if err != nil {
			return nil, err
		}
		for k, v := range p {
			if val, ok := v.(string); ok {
				b.step[k], _ = strconv.ParseFloat(val, 64)
			}
			if val, ok := v.(float64); ok {
				b.step[k] = val
			}
		}
	}
	return b, nil
}
