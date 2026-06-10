package gamehandlers

import (
	"math"
	"sync"
)

// Multiplier - обработчик игры "Множитель выигрыша"
// Не требует параметров
type Multiplier struct {
	lastPoints map[int64]userLastPoints // Последние очки пользователей
	mux        sync.Mutex               // Мьютекс для синхронизации доступа к последним очкам
}

type userLastPoints struct {
	UserId int64   // ID пользователя
	Points float32 // Очки пользователя
	Bet    float64 // Ставка пользователя
	Win    float64 // Выигрыш пользователя
}

// WinPoints - обработка игры "Множитель выигрыша"
//
// Параметры:
// - user_Id: id пользователя
// - currency: валюта
// - bet: ставка
// - win: выигрыш
//
// Возвращает:
// - очки пользователя
func (m *Multiplier) WinPoints(user_Id int64, currency string, bet float64, win float64) float32 {
	m.mux.Lock()
	defer m.mux.Unlock()
	lp := m.lastPoints[user_Id]
	if lp.UserId == 0 {
		// Если пользователь еще не играл, создаем новую запись
		lp = userLastPoints{
			UserId: user_Id,
			Points: 0,
			Bet:    bet,
			Win:    win,
		}
	}
	points := float32(0)
	// Если ставка равна 0, то это следующий выигрыш на призовой игре
	if bet == 0 {
		// Если становленная ставка равна 0, то просто возвращаем 0
		if lp.Bet == 0 {
			return 0
		}
		// Добавляем выигрыш призовой к общему выигрышу
		lp.Win += win
		// Вычисляем новое количество очков за игру по формуле: win / bet
		points = float32(math.Floor(lp.Win / lp.Bet))
		// Вычисляем разницу очков корорые необходимо добавить к текущим очкам
		pointsDiff := points - lp.Points
		lp.Points = points
		m.lastPoints[user_Id] = lp
		return pointsDiff
	} else {
		lp.Win = win
		lp.Bet = bet
		// Вычисляем количество очков за игру по формуле: win / bet
		lp.Points = float32(math.Floor(win / bet))
		m.lastPoints[user_Id] = lp
		return lp.Points
	}

}

// NewMultiplier - создание новой игры "Множитель выигрыша"
//
// Возвращает:
// - обработчик игры "Множитель выигрыша"
func NewMultiplier() *Multiplier {
	return &Multiplier{
		lastPoints: make(map[int64]userLastPoints),
	}
}
