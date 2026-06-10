package games

import (
	"games_manager/internal/games/games_t"
	"strings"
)

// GameList содержит список игр
type GameList []Game

// GamePtrList содержит список указателей на игры
type GamePtrList []*Game

// GroupList содержит список групп игр
type GroupList []GameGroup

// SearchByNameTag ищет игры по имени и тегу
// name - имя игры
// tag - тег игры
func (gpl *GamePtrList) SearchByNameTag(name string, tag int) GamePtrList {
	found := make(GamePtrList, 0)
	for _, game := range *gpl {
		if (name == "" || strings.Contains(strings.ToLower(game.Name), strings.ToLower(name))) && (tag == 0 || game.Tags&tag > 0) {
			found = append(found, game)
		}
	}
	return found
}

// SearchById ищет игру по ID
// id - ID игры
// Возвращает указатель на игру или nil, если игра не найдена
func (gpl *GamePtrList) SearchById(id string) *Game {
	for _, game := range *gpl {
		if game.GameID == id {
			return game
		}
	}
	return nil
}

// SearchByNameTag ищет игры по имени и тегу
// name - имя игры
// tag - тег игры
// Возвращает список указателей на игры, соответствующие критериям поиска
func (gpl *GameList) SearchByNameTag(name string, tag int) GamePtrList {
	found := make(GamePtrList, 0, 10)
	for i, game := range *gpl {
		if (name == "" || strings.Contains(strings.ToLower(game.Name), strings.ToLower(name))) && (tag == 0 || game.Tags&tag > 0) {
			found = append(found, &(*gpl)[i])
		}
	}
	return found
}

// SearchById ищет игру по ID
// id - ID игры
// Возвращает указатель на игру или nil, если игра не найдена
// Важно: метод работает только с указателями на игры
func (gpl *GameList) SearchById(id string) *Game {
	for _, game := range *gpl {
		if game.GameID == id {
			return &game
		}
	}
	return nil
}

// GetGroups возвращает список групп игр
// Возвращает множество групп игр, в которых находятся игры из списка
func (gpl *GamePtrList) GetGroups() games_t.GamesGroupsSet {
	gg := make(games_t.GamesGroupsSet)
	for _, game := range *gpl {
		gg[game.Group]++
	}
	return gg
}
