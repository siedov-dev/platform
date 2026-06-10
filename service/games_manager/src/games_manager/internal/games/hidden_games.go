package games

// HiddenGames - структура для хранения скрытых игр
type HiddenGames struct {
	AllGames         GameList         // список всех скрытых игр
	ByCid            map[string]*Game // ключ - CID игры, значение - указатель на игру
	ByID             map[string]*Game // ключ - ID игры, значение - указатель на игру
	ByProviderGameID map[string]*Game // ключ - система:ID игры, значение - указатель на игру
}

// NewHiddenGames - конструктор для создания новой структуры HiddenGames
// и инициализации ее полей
// Возвращает указатель на созданную структуру
func NewHiddenGames() *HiddenGames {
	hg := new(HiddenGames)
	hg.ByCid = make(map[string]*Game)
	hg.ByID = make(map[string]*Game)
	hg.ByProviderGameID = make(map[string]*Game)
	return hg
}

// MakeHiddenGames - функция для создания новой структуры HiddenGames
// и инициализации ее полей
// Возвращает структуру HiddenGames
func MakeHiddenGames() HiddenGames {
	hg := HiddenGames{}
	hg.ByCid = make(map[string]*Game)
	hg.ByID = make(map[string]*Game)
	hg.ByProviderGameID = make(map[string]*Game)
	return hg
}

// AddGame - функция для добавления игры в список скрытых игр
// Принимает указатель на игру и добавляет ее в список
func (hg *HiddenGames) AddGame(g Game) {
	hg.AllGames = append(hg.AllGames, g)
	last_idx := len(hg.AllGames) - 1
	ig := &hg.AllGames[last_idx]
	hg.ByCid[g.CID] = ig
	hg.ByID[g.GameID] = ig
	hg.ByProviderGameID[g.System+":"+g.GameID] = ig
}
