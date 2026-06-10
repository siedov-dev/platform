package gamehandlers

import "fmt"

// TournamentGame - интерфейс для обработки турнирной игры
type TournamentGame interface {
	WinPoints(user_Id int64, currency string, bet float64, win float64) float32
}

// GameType - тип игры
type GameType = int

// GameType - типы игр
const (
	GameMultiplier GameType = iota + 1 // Множитель выигрыша
	GameBetX                           // По сумме ставки
	GameWinX                           // По сумме выигрыша
	GameWinK                           // Коэффициент выигрыша
)

// NewTournamentGame - создание новой турнирной игры
//
// Параметры:
// - gameType: тип игры
// - params: параметры игры (json)
func NewTournamentGame(gameType GameType, params string) (TournamentGame, error) {
	switch gameType {
	case GameMultiplier:
		return NewMultiplier(), nil
	case GameBetX:
		return NewBetX(params)
	case GameWinX:
		return NewWinX(params)
	case GameWinK:
		return NewWinK(params)
	default:
		return nil, fmt.Errorf("unknown game type: %d", gameType)
	}
}
