package filter

import (
	"games_manager/internal/games"
	"games_manager/internal/games/games_t"
	"strings"
)

// GamesFilter - структура для фильтрации игр
// Используется для фильтрации игр по различным критериям
// Например, по провайдеру, группе, категории, тегам и имени
// Внутри структуры используются флаги для определения, какие критерии фильтрации применяются
// и соответствующие наборы значений для хранения выбранных значений
// Функции Provider, Providers, Group, Groups, Category, Categories, Tags и Name
// используются для создания фильтров и добавления их в структуру GamesFilter
// Функция New создает новый экземпляр GamesFilter и применяет к нему переданные фильтры
// Функция Add позволяет добавлять новые фильтры к уже существующему экземпляру GamesFilter
// Функция FilterGame проверяет, подходит ли игра под заданные фильтры
// Функция FilterGames фильтрует список указателей игр по заданным фильтрам и возвращает отфильтрованный список
// Функция FilterAllGames фильтрует список игр по заданным фильтрам и возвращает отфильтрованный список
type GamesFilter struct {
	Providers    games_t.StringSet
	ByPrividers  bool
	Groups       games_t.StringSet
	ByGroups     bool
	Categories   games_t.StringSet
	ByCategories bool
	Tags         int
	Name         string
	ByName       bool
}

type Filter func(*GamesFilter)

func Provider(provider string) Filter {
	return func(gf *GamesFilter) {
		if provider != "" {
			gf.ByPrividers = true
			gf.Providers[provider] = true
		}
	}
}

func Providers(providers []string) Filter {
	return func(gf *GamesFilter) {
		for _, pn := range providers {
			gf.ByPrividers = true
			gf.Providers[pn] = true
		}
	}
}

func Group(group string) Filter {
	return func(gf *GamesFilter) {
		if group != "" {
			gf.ByGroups = true
			gf.Groups[group] = true
		}
	}
}

func Groups(groups []string) Filter {
	return func(gf *GamesFilter) {
		for _, gn := range groups {
			gf.ByGroups = true
			gf.Groups[gn] = true
		}
	}
}

func Category(category string) Filter {
	return func(gf *GamesFilter) {
		if category != "" {
			gf.ByCategories = true
			gf.Categories[category] = true
		}
	}
}

func Categories(catrgories []string) Filter {
	return func(gf *GamesFilter) {
		for _, cn := range catrgories {
			gf.ByCategories = true
			gf.Categories[cn] = true
		}
	}
}

func Tags(tags int) Filter {
	return func(gf *GamesFilter) {
		if tags != 0 {
			gf.Tags = tags
		}
	}
}

func Name(name string) Filter {
	return func(gf *GamesFilter) {
		if name != "" {
			gf.ByName = true
			gf.Name = name
		}
	}
}

func New(filters ...Filter) GamesFilter {
	filter := GamesFilter{}
	filter.Providers = make(games_t.StringSet, 0)
	filter.Groups = make(games_t.StringSet, 0)
	filter.Categories = make(games_t.StringSet, 0)
	for _, f := range filters {
		f(&filter)
	}

	return filter
}

func (gf *GamesFilter) Add(f Filter) {
	f(gf)
}

func (gf *GamesFilter) FilterGame(game *games.Game) bool {
	if gf.ByPrividers {
		if !gf.Providers[game.System] {
			return false
		}
	}

	if gf.ByCategories {
		if !gf.Categories[game.Category] {
			return false
		}
	}

	if gf.ByGroups {
		if !gf.Groups[game.Group] {
			return false
		}
	}

	if (!gf.ByName || strings.Contains(strings.ToLower(game.Name), strings.ToLower(gf.Name))) && (gf.Tags == 0 || game.Tags&gf.Tags > 0) {
		return true
	}
	return false
}

func (gf *GamesFilter) FilterGames(gl games.GamePtrList) games.GamePtrList {
	games := make(games.GamePtrList, 0)
	for _, game := range gl {
		if gf.FilterGame(game) {
			games = append(games, game)
		}
	}
	return games
}

func (gf *GamesFilter) FilterAllGames(gl games.GameList) games.GamePtrList {
	games := make(games.GamePtrList, 0)
	for _, game := range gl {
		if gf.FilterGame(&game) {
			games = append(games, &game)
		}
	}
	return games
}
