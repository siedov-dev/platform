package games

// GameProvider содержит информацию о провайдере игр
// и предоставляет методы для работы с играми
// в этом провайдере
type GameProvider struct {
	ID            string                 // ID провайдера игр
	AllGames      GamePtrList            // Список всех игр
	DisabledGames GamePtrList            // Список отключенных игр
	ByCid         map[string]*Game       // Индекс по CID игры
	ByID          map[string]*Game       // Индекс по ID игры
	DisabledByID  map[string]*Game       // Индекс по ID отключенной игры
	ByCategory    map[string]GamePtrList // Индекс по категориям
	ByGroup       map[string]GamePtrList // Индекс по группам
	LandingGroups map[string]GamePtrList // Индекс по группам для лендинга
	Groups        map[string]*GameGroup  // Список групп игр
}

// CreateGamesProvider создает новую группу игр
// id - ID провайдера игр
// Возвращает указатель на созданную группу игр
// инициализирует все необходимые структуры для хранения игр
// и их категорий
func CreateGamesProvider(id string) *GameProvider {
	gp := GameProvider{}
	gp.ID = id
	gp.AllGames = make(GamePtrList, 0, 10)
	gp.DisabledGames = make(GamePtrList, 0, 10)
	gp.ByCid = make(map[string]*Game, 10)
	gp.ByID = make(map[string]*Game, 10)
	gp.DisabledByID = make(map[string]*Game, 10)
	gp.ByCategory = make(map[string]GamePtrList, 10)
	gp.ByGroup = make(map[string]GamePtrList, 10)
	gp.LandingGroups = make(map[string]GamePtrList, 10)
	gp.Groups = make(map[string]*GameGroup, 5)
	return &gp
}

// AddGame добавляет игру в провайдер игр
// game - указатель на игру, которую нужно добавить
func (gp *GameProvider) AddGame(game *Game) {
	gp.AllGames = append(gp.AllGames, game)
	gp.ByCid[game.CID] = game
	gp.ByID[game.GameID] = game
	gp.ByCategory[game.Category] = append(gp.ByCategory[game.Category], game)
	gp.ByGroup[game.Group] = append(gp.ByGroup[game.Group], game)
	gp.LandingGroups[game.LandingGroup] = append(gp.LandingGroups[game.LandingGroup], game)

	// add to group
	if group, exists := gp.Groups[game.Group]; exists {
		group.AddGame(game)
	} else {
		gp.Groups[game.Group] = CreateGamesGroup(game.Group)
		gp.Groups[game.Group].AddGame(game)
	}
}

// AddDisabledGame добавляет отключенную игру в провайдер игр
// game - указатель на игру, которую нужно добавить
func (gp *GameProvider) AddDisabledGame(game *Game) {
	gp.DisabledGames = append(gp.AllGames, game)
	gp.DisabledByID[game.GameID] = game
}

// SearchGames ищет игры в провайдере игр
// groups - список групп, в которых нужно искать игры
// categories - список категорий, в которых нужно искать игры
// tag - тег, по которому нужно искать игры
// name - имя игры, по которому нужно искать игры
// result - список игр, в который нужно добавить найденные игры
// Возвращает список указателей на игры, соответствующие критериям поиска
// Если groups не указаны, то ищет по всем играм в провайдере
// Если categories не указаны, то ищет по всем играм в провайдере
// Если tag и name не указаны, то возвращает все игры в провайдере
func (gp *GameProvider) SearchGames(groups []string, categories []string, tag int, name string, result GamePtrList) GamePtrList {
	if len(groups) > 0 {
		for _, gn := range groups {
			if g, ok := gp.Groups[gn]; ok {
				result = g.SearchGames(categories, tag, name, result)
			}
		}
	} else if len(categories) > 0 {
		for _, cn := range categories {
			if c, ok := gp.ByCategory[cn]; ok {
				result = append(result, c.SearchByNameTag(name, tag)...)
			}
		}
	} else {
		result = append(result, gp.AllGames.SearchByNameTag(name, tag)...)
	}
	return result
}
