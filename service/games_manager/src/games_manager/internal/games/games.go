// Package games содержит функции и структуры для работы с играми
package games

import (
	"games_manager/internal/config"
	"games_manager/internal/db/models"
	"games_manager/internal/games/games_t"
	"games_manager/internal/repositories"
	"log"
	"strings"
	"sync"
)

// Devices - список устройств, на которых доступны игры
var Devices = [2]string{"desktop", "phone"}

// EgLangs - список основных языков для игр
var EgLangs games_t.StringSet

// Langs - список языков, на которых доступны игры
var Langs games_t.StringSet

// AllGames - глобальный список всех игр
var AllGames = make(GameList, 0, 1000)

// DisabledGames - глобальный список отключенных игр
var DisabledGames = make([]models.DisabledGame, 0, 100)

// DisabledByBonusType - глобальный список игр по умолчанию по типу бонуса
// раньше использовался для запрета игр по типу бонуса
// ключ - тип бонуса, значение - список игр
var DisabledByBonusType = make(map[int16]games_t.StringSet)

// LandingGames - глобальный список игр для лендинга
var LandingGames = make([]models.LandingGames, 100)

// GameManagers - глобальный список менеджеров игр
// ключ - устройство и язык, значение - менеджер игр
// например, "desktop-ru" - менеджер игр для настольного устройства на русском языке
// "phone-en" - менеджер игр для мобильного устройства на английском языке
var GameManagers = make(map[string]*GamesManager, 20)

// GameGroupPriorities - глобальный список приоритетов групп игр
// ключ - система и группа, значение - приоритет
var GameGroupPriorities = make(map[string]int, 0)

// providerNames - глобальный список новых имен провайдеров
// ключ - система, значение - карта старых имен провайдеров и новых имен провайдеров
// используется для замены старых имен провайдеров на новые
// при выводе игр пользователю
var providerNames map[string]map[string]string
var providerNamesMx sync.RWMutex

var gameSegments map[uint64][]string
var gameSegmentsMx sync.RWMutex

// Запускает загрузку всех игр и их менеджеров
// Запускается при старте приложения
// и при перезагрузке игр
func MustLoad() {
	// инициализация языков
	EgLangs = make(games_t.StringSet, len(config.Get().MainLangs))
	Langs = make(games_t.StringSet, len(config.Get().AllLangs))
	for _, k := range config.Get().MainLangs {
		EgLangs[k] = true
	}
	for _, k := range config.Get().AllLangs {
		Langs[k] = true
	}
	loadGames()
	loadLandingGames()
	LoadDisabledGames()
	loadGamesManagers()
	LoadDisabledBonusGames()
	loadGameGroupPriorities()
	LoadProviderNames()
	loadGameSegments()
	// free resources
	AllGames = AllGames[:0]
	DisabledGames = nil
	LandingGames = nil
}

// LoadProviderNames загружает новые имена провайдеров из базы данных
// и заполняет глобальный список providerNames
// ключ - система, значение - карта старых имен провайдеров и новых имен провайдеров
// используется для замены старых имен провайдеров на новые
// при выводе игр пользователю
func LoadProviderNames() {
	providerNamesMx.Lock()
	defer providerNamesMx.Unlock()
	providerNames = make(map[string]map[string]string)
	providers, err := repositories.GetProviderNamesRepository().GetAll()
	if err != nil {
		log.Printf("LoadProviderNames: %v", err)
		return
	}

	for _, p := range providers {
		if providerNames[p.GameSystem] == nil {
			providerNames[p.GameSystem] = make(map[string]string)
		}
		providerNames[p.GameSystem][p.Provider] = p.NewName
	}
}

// GetSystemProvidersNewNames возвращает карту новых имен провайдеров для заданной системы
// если для системы нет новых имен провайдеров, возвращает nil
// используется для замены старых имен провайдеров на новые
// при выводе игр пользователю
func GetSystemProvidersNewNames(system string) map[string]string {
	providerNamesMx.RLock()
	defer providerNamesMx.RUnlock()
	if providerNames == nil {
		return nil
	}
	return providerNames[system]
}

// loadGames загружает все игры из базы данных
// и заполняет глобальный список AllGames
func loadGames() {
	games, err := repositories.GetGameConfigsRepository().GetGameConfigs()
	if err != nil {
		panic(err)
	}

	for _, v := range games {
		game := Game{}
		game.ID = v.ID
		game.CID = v.CID
		game.System = v.System
		game.GameID = v.GameID
		if v.Group.Valid {
			game.Group = v.Group.String
		}
		if v.Name.Valid {
			game.Name = v.Name.String
		}
		if v.Img.Valid {
			game.Img = v.Img.String
		}
		if v.Category.Valid {
			game.Category = v.Category.String
		}
		if v.LandingGroup.Valid {
			game.LandingGroup = v.LandingGroup.String
		}
		if v.Title.Valid && v.Title.String != "" {
			game.Name = v.Title.String
		}
		if v.DeviceType.Valid {
			game.Device = v.DeviceType.String
		}
		if v.CustomImg.Valid {
			img := strings.Clone(v.CustomImg.String)
			game.CustomImg = &img
		}

		AllGames = append(AllGames, game)
	}
}

// loadLandingGames загружает игры для лендинга из базы данных
// и заполняет глобальный список LandingGames
func loadLandingGames() {
	lg, err := repositories.GetLanfingGamesRepository().GetLandingGames()
	if err != nil {
		panic(err)
	}

	LandingGames = lg
}

// LoadDisabledGames загружает отключенные игры из базы данных
func LoadDisabledGames() {
	dg, err := repositories.GetDisabledGamesRepository().GetDisabledGames()
	if err != nil {
		panic(err)
	}

	DisabledGames = dg
}

// loadGamesManagers создает менеджеров игр для каждого устройства и языка
// и заполняет глобальный список GameManagers
// ключ - устройство и язык, значение - менеджер игр
func loadGamesManagers() {
	for _, device := range Devices {
		GameManagers[device+"-test"] = NewGamesManager(device, "test")

		for lang := range Langs {
			GameManagers[device+"-"+lang] = NewGamesManager(device, lang)
		}
	}
}

// LoadDisabledBonusGames загружает отключенные игры по типу бонуса из базы данных
// и заполняет глобальный список DisabledByBonusType
// ключ - тип бонуса, значение - список отключенных игр
func LoadDisabledBonusGames() {
	DisabledByBonusType = make(map[int16]games_t.StringSet)
	dcids, err := repositories.GetBonusDisabledCIDsRepository().GetDisabledGamesCIDs()
	if err != nil {
		log.Fatal(err)
	}
	for _, disabled_cid := range dcids {
		if DisabledByBonusType[disabled_cid.BonusType] == nil {
			DisabledByBonusType[disabled_cid.BonusType] = make(games_t.StringSet)
		}
		DisabledByBonusType[disabled_cid.BonusType][disabled_cid.CID] = true
	}
}

// loadGameGroupPriorities загружает приоритеты групп игр из базы данных
// и заполняет глобальный список GameGroupPriorities
// ключ - система и группа, значение - приоритет
func loadGameGroupPriorities() {
	GameGroupPriorities = make(map[string]int, 0)
	dbPriorities, err := repositories.GetGropPriorityRepository().GetPriorities()
	if err != nil {
		log.Fatal(err)
	}
	for _, pr := range dbPriorities {
		GameGroupPriorities[pr.SystemId+"_"+pr.GroupId] = pr.Priority
	}
}

func loadGameSegments() {
	gameSegmentsMx.Lock()
	defer gameSegmentsMx.Unlock()
	gameSegments = make(map[uint64][]string)
	segments, err := repositories.GetGameSegmentRepository().GetGameSegments()
	if err != nil {
		log.Printf("loadGameSegments: %v", err)
		return
	}
	gameSegments = segments
}
