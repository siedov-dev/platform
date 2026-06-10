package games_t

// Package games_t содержит структуры для работы с играми

// StringSet - структура для хранения уникальных строк
type StringSet map[string]bool

// GamesGroupsSet - структура для хранения уникальный групп игр
type GamesGroupsSet map[string]int

// GamesStats - структура для хранения статистики игр
type GamesStats struct {
	TotalGames    int            // количество игр
	Categories    map[string]int // количество игр в категории
	Tags          map[string]int // количество игр в теге
	Providers     map[string]int // количество игр в провайдере
	LandingGroups map[string]int // количество игр в лендинге
}
