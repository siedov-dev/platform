package games

import (
	"games_manager/internal/db/models"
	"games_manager/internal/games/games_t"
	"strings"
)

// GamesManager - структура для управления играми
// Для каждого устройства и языка создается свой менеджер игр
// Так же создается менеджер для тестовых игр
// В нем хранятся все игры, а также индексы по группам, тегам и провайдерам
type GamesManager struct {
	Device        string                   // Устройство, на котором игра доступна (например, "desktop", "mobile")
	Lang          string                   // Язык, на котором игра доступна (например, "en", "ru")
	AllGames      GameList                 // Список всех игр
	ByCid         map[string]*Game         // Индекс по CID игры
	DisabledGames HiddenGames              // Список отключенных игр
	ByGroup       map[string]GamePtrList   // Индекс по группам
	ByTag         map[string]GamePtrList   // Индекс по тегам
	ByProviders   map[string]GamePtrList   // Индекс по провайдерам
	ByCategory    map[string]GamePtrList   // Индекс по категориям
	LandingGroups map[string]GamePtrList   // Индекс по группам для лендинга
	TestGamesCIDs games_t.StringSet        // Список тестовых игр
	IsTest        bool                     // Флаг, указывающий, что это тестовый менеджер игр
	Groups        map[string]*GameGroup    // Список групп игр
	Providers     map[string]*GameProvider // Список провайдеров игр
	Stats         games_t.GamesStats       // Статистика игр
}

// GetBonusGames возвращает список бонусных игр для пользователя.
// b - указатель на структуру UserBonus.
// Возвращает список игр, которые доступны для бонуса.
func (gm *GamesManager) GetBonusGames(b *models.UserBonus) (GamePtrList, error) {
	b_games, err := b.GetGames()
	if err != nil {
		return nil, err
	}
	games := make(GamePtrList, 0)
	for pn, game_ids := range b_games {
		if p, ok := gm.Providers[pn]; ok {
			for _, game_id := range game_ids {
				if game, ok := p.ByID[game_id]; ok {
					games = append(games, game)
				}
				if game, ok := gm.DisabledGames.ByProviderGameID[pn+":"+game_id]; ok {
					games = append(games, game)
				}
			}
		}
	}

	return games, nil
}

// GetGamesBySegmentID возвращает список игр по ID сегмента.
// segment_id - ID сегмента.
// Возвращает список игр, которые принадлежат к этому сегменту.
func (gm *GamesManager) GetGamesBySegmentID(segment_id uint64) GamePtrList {
	gameSegmentsMx.RLock()
	defer gameSegmentsMx.RUnlock()
	games := make(GamePtrList, 0)
	if cids, ok := gameSegments[segment_id]; ok {
		for _, cid := range cids {
			if game, ok := gm.ByCid[cid]; ok {
				games = append(games, game)
			}
		}
	}
	return games
}

// NewGamesManager создает новый менеджер игр
// device - устройство, на котором игра доступна (например, "desktop", "mobile")
// lang - язык, на котором игра доступна (например, "en", "ru")
// Возвращает указатель на созданный менеджер игр
// инициализирует все необходимые структуры для хранения игр
// и их категорий
// Если lang == "test", то создается тестовый менеджер игр
func NewGamesManager(device string, lang string) *GamesManager {

	gm := GamesManager{}
	gm.Device = device
	gm.Lang = lang
	gm.ByCid = make(map[string]*Game)
	gm.DisabledGames = MakeHiddenGames()
	gm.ByGroup = make(map[string]GamePtrList, 10)
	gm.ByTag = make(map[string]GamePtrList, 10)
	gm.ByProviders = make(map[string]GamePtrList, 10)
	gm.ByCategory = make(map[string]GamePtrList, 10)
	gm.LandingGroups = make(map[string]GamePtrList, 10)
	gm.TestGamesCIDs = make(games_t.StringSet, 0)
	gm.Groups = make(map[string]*GameGroup, 5)
	gm.Providers = make(map[string]*GameProvider, 2)
	gm.Stats = games_t.GamesStats{
		TotalGames:    0,
		Categories:    make(map[string]int, 10),
		Tags:          make(map[string]int, 10),
		Providers:     make(map[string]int, 10),
		LandingGroups: make(map[string]int, 10),
	}

	if lang == "test" {
		gm.IsTest = true
		gm.Lang = "ru"
	}

	device_id := "1"
	if device == "phone" {
		device_id = "2"
	}

next_game:
	for _, game := range AllGames {
		// Если Менеджер не тестовый, то проверяем, что игра не тестовая
		// и не находится в тестовом менеджере
		if !gm.IsTest {
			if testgm, ok := GameManagers[device+"-test"]; ok {
				if testgm.TestGamesCIDs[game.CID] {
					// Добавляем в отключенные игры
					gm.DisabledGames.AddGame(game)
					continue
				}
			}
		}
		// Проверяем, что игра доступна на устройстве
		if game.Device != "0" && game.Device != device_id {
			// Добавляем в отключенные игры
			gm.DisabledGames.AddGame(game)
			continue
		}
		// Проверяем, что игра не отключена для языка
		for _, dcid := range DisabledGames {
			// Если язык не в списке EgLangs, то ставим язык по умолчанию
			// и проверяем, что игра доступна на устройстве
			// Язык "test" - используется для тестовых игр
			if lang != "test" && !EgLangs[lang] {
				lang = "en"
			}
			if dcid.Device != device || dcid.Type != lang {
				continue
			}

			// Если CID игры подходит под шаблон, то отключаем игру
			// Например, sleg/* - отключаем все игры системы sleg
			if strings.Contains(dcid.CID, "*") && strings.HasPrefix(game.CID, strings.Replace(dcid.CID, "*", "", -1)) {
				// Пометка как тестовая
				if lang == "test" {
					game.IsTest = new(bool)
					*game.IsTest = true
					gm.TestGamesCIDs[game.CID] = true
				} else {
					// добавляем в отключенные игры
					gm.DisabledGames.AddGame(game)
					continue next_game
				}
			}
			if dcid.CID == game.CID {
				// Пометка как тестовая
				if lang == "test" {
					game.IsTest = new(bool)
					*game.IsTest = true
					gm.TestGamesCIDs[game.CID] = true
				} else {
					// добавляем в отключенные игры
					gm.DisabledGames.AddGame(game)
					continue next_game
				}
			}
		}
		// Добавляем игру в список всех игр
		gm.AllGames = append(gm.AllGames, game)
		gm.Stats.TotalGames++
	}

	// Индекс по группам
	for i, game := range gm.AllGames {
		gm.ByGroup[game.Group] = append(gm.ByGroup[game.Group], &gm.AllGames[i])
	}

	// Индекс по ID
	for i, game := range gm.AllGames {
		gm.ByCid[game.CID] = &gm.AllGames[i]
	}

	// Индекс по тегам и лендинг группам
	for _, lg := range LandingGames {
		tag := lg.Type
		// Проверяем соответствие языка
		if lg.Lang != gm.Lang {
			continue
		}
		if game, ok := gm.ByCid[lg.CID]; ok {
			// Пропускаем plustop, и plushot
			// так как они дублируються в top, hot
			if tag == "plustop" {
				continue
			}
			if tag == "plushot" {
				continue
			}
			if tag == "topSlotGames" {
				game.LandingGroup = tag
				game.SetLndFlag(tag)
				gm.LandingGroups[tag] = append(gm.LandingGroups[tag], game)
				gm.Stats.LandingGroups[tag]++
				continue
			}
			if tag == "topLiveGames" {
				game.LandingGroup = tag
				game.SetLndFlag(tag)
				gm.LandingGroups[tag] = append(gm.LandingGroups[tag], game)
				game.LandPriority = lg.ID
				gm.Stats.LandingGroups[tag]++
				continue
			}
			if tag == "topTableGames" {
				game.LandingGroup = tag
				game.SetLndFlag(tag)
				gm.LandingGroups[tag] = append(gm.LandingGroups[tag], game)
				game.LandPriority = lg.ID
				gm.Stats.LandingGroups[tag]++
				continue
			}
			gm.ByTag[tag] = append(gm.ByTag[tag], game)
			game.SetTag(tag)
			gm.Stats.Tags[tag]++
			if tag == "top" {
				// top games should be shown first in order by id
				game.Priority = lg.ID
			}
		}
	}

	// Индекс по игровым системам(провайдерам)
	for i, game := range gm.AllGames {
		if game.System != "" {
			gm.ByProviders[game.System] = append(gm.ByProviders[game.System], &gm.AllGames[i])
			gm.Stats.Providers[game.System]++
		}
	}

	// Индекс по категориям
	for i, game := range gm.AllGames {
		if game.Category != "" {
			gm.ByCategory[game.Category] = append(gm.ByCategory[game.Category], &gm.AllGames[i])
			gm.Stats.Categories[game.Category]++
		}
	}

	// Создаем группы игр
	// и добавляем в них игры
	for gn, g := range gm.ByGroup {
		gm.Groups[gn] = CreateGamesGroup(gn)
		for _, game_ptr := range g {
			gm.Groups[gn].AddGame(game_ptr)
		}
	}

	// Создаем провайдеры игр
	// и добавляем в них игры
	for pn, g := range gm.ByProviders {
		gm.Providers[pn] = CreateGamesProvider(pn)
		for _, game_ptr := range g {
			gm.Providers[pn].AddGame(game_ptr)
		}
	}

	return &gm
}
