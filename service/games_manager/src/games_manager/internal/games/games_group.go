package games

// GameGroup содержит информацию о группе игр
// и предоставляет методы для работы с играми в этой группе
type GameGroup struct {
	Name          string
	AllGames      GamePtrList
	ByCid         map[string]*Game
	ByID          map[string]*Game
	ByCategory    map[string]GamePtrList
	LandingGroups map[string]GamePtrList
}

// GroupInfo содержит информацию о группе игр
// и используется для передачи данных о группе в ответах API
type GroupInfo struct {
	Name        string   `json:"name"`
	GamesCount  int      `json:"games_count"`
	Catergories []string `json:"categories"`
	Priority    int      `json:"pr"`
}

// CreateGamesGroup создает новую группу игр
// name - имя группы игр
// Возвращает указатель на созданную группу игр
// инициализирует все необходимые структуры для хранения игр
// и их категорий
func CreateGamesGroup(name string) *GameGroup {
	gg := GameGroup{}
	gg.Name = name
	gg.AllGames = make(GamePtrList, 0, 10)
	gg.ByCid = make(map[string]*Game, 10)
	gg.ByID = make(map[string]*Game, 10)
	gg.ByCategory = make(map[string]GamePtrList, 10)
	gg.LandingGroups = make(map[string]GamePtrList, 10)
	return &gg
}

// AddGame добавляет игру в группу игр
// game - указатель на игру, которую нужно добавить
func (gg *GameGroup) AddGame(game *Game) {
	gg.AllGames = append(gg.AllGames, game)
	gg.ByCid[game.GameID] = game
	gg.ByID[game.GameID] = game
	gg.ByCategory[game.Category] = append(gg.ByCategory[game.Category], game)
	gg.LandingGroups[game.LandingGroup] = append(gg.LandingGroups[game.LandingGroup], game)
}

// GetInfo возвращает информацию о группе игр
// GameSystem - провайдер(игровая система), для которой запрашивается информация
// Возвращает структуру GroupInfo с информацией о группе игр
// и ее приоритете
func (gg *GameGroup) GetInfo(GameSystem string) GroupInfo {
	gi := GroupInfo{}
	gi.Name = gg.Name
	gi.GamesCount = len(gg.AllGames)
	gi.Catergories = make([]string, 0, len(gg.ByCategory))
	for cat := range gg.ByCategory {
		gi.Catergories = append(gi.Catergories, cat)
	}
	gi.Priority = GameGroupPriorities[GameSystem+"_"+gg.Name]
	return gi
}

// SearchGames ищет игры в группе по заданным критериям
// categories - список категорий, по которым нужно искать игры
// tag - тег игры (например, "hot", "new", "top", "jackpot", "fs", "megaways", "crash")
// name - имя игры, по которому нужно искать
// result - список игр, в который будут добавлены найденные игры
// Возвращает список указателей на игры, соответствующие критериям поиска
// Если categories не указаны, то ищет по всем играм в группе
// Если tag и name не указаны, то возвращает все игры в группе
// Если tag и name указаны, то ищет по всем играм в группе
func (gg *GameGroup) SearchGames(categories []string, tag int, name string, result GamePtrList) GamePtrList {
	if len(categories) > 0 {
		for _, cn := range categories {
			if c, ok := gg.ByCategory[cn]; ok {
				if name != "" || tag > 0 {
					result = append(result, c.SearchByNameTag(name, tag)...)
				} else {
					result = append(result, c...)
				}
			}
		}

		return result
	}

	if name != "" || tag > 0 {
		result = append(result, gg.AllGames.SearchByNameTag(name, tag)...)
	} else {
		result = append(result, gg.AllGames...)
	}

	return result
}
