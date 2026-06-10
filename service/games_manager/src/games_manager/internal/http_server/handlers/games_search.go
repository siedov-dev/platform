package handlers

import (
	"games_manager/internal/games"
	"games_manager/internal/games/filter"
	"games_manager/internal/http_server/responses"
	"net/http"
	"net/url"
	"slices"
	"strconv"
	"strings"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// GamesSearch - обработчик для получения списка игр по тегу
// принимает параметры:
// device - устройство (desktop или phone)
// lang - язык (например, ru, en и т.д.)
// groups - группы игр (например, "Endorphina,Amatic")
// categories - категории игр (например, "slots,table")
// tags - теги игр (например, "top,new")
// per_page - количество игр на странице
// page - номер страницы
// name - имя игры (например, "book of ra")
// Опционально принимает параметр in_disabled - флаг, который указывает, нужно ли искать игру в отключенных играх
// Возвращает список игр в формате JSON
// Пример запроса: GET /games/search/desktop/ru/Endorphina,Amatic/slots,table/top,new/10/1/book%20of%20ra
func GamesSearch(w http.ResponseWriter, r *http.Request) {
	// Получаем device и lang из URL
	device := chi.URLParam(r, "device")
	lang := chi.URLParam(r, "lang")
	// Получаем in_disabled из URL
	// in_disabled - флаг, который указывает, нужно ли искать игру в отключенных играх
	in_disabled := r.URL.Query().Get("in_disabled")
	// for_bonus_type - флаг, который указывает, нужно ли искать игры для типа определенного бонуса
	// В этом случае он используется для фильтрации игр по типу бонуса
	// например, для бонуса 47.
	// Используеться для бонусов, у которых не прописаны игры
	// и используется список игр по умолчанию
	// В данный момент используеться только тип бонуса 47 для всех типов бонусов!
	for_bonus_type := r.URL.Query().Get("for_bonus_type")

	// Получаем группы, категории и теги из URL
	groups_str, err := url.QueryUnescape(chi.URLParam(r, "groups"))
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}
	groups := make([]string, 0)
	if groups_str != "" {
		groups = strings.Split(groups_str, ",")
	}

	categories_str, err := url.QueryUnescape(chi.URLParam(r, "categories"))
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}
	categories := make([]string, 0)
	if categories_str != "" {
		categories = strings.Split(categories_str, ",")
	}

	tags_str, err := url.QueryUnescape(chi.URLParam(r, "tags"))
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}
	tags := make([]string, 0)
	if tags_str != "" {
		tags = strings.Split(tags_str, ",")
	}

	tag := games.CreateTagFlags(tags)

	// Параметры для пагинации
	per_page_str := chi.URLParam(r, "per_page")
	per_page, err := strconv.Atoi(per_page_str)
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}

	page_str := chi.URLParam(r, "page")
	page, err := strconv.Atoi(page_str)
	if err != nil {
		page = 0
	}

	// Получаем имя игры из URL
	name, err := url.QueryUnescape(chi.URLParam(r, "name"))
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}
	name = strings.Trim(name, " \"")

	// Получаем менеджер игр по устройству и языку
	manager, found := games.GameManagers[device+"-"+lang]
	if !found {
		resp := responses.MakeGameReponse(responses.StatusError, "Games Manager not found", 0, 0, 0, nil)
		render.JSON(w, r, &resp)
		return
	}

	var search_result games.GamePtrList
	var sort_games bool = true
	var sort_by_lnd_cat = false

	if len(categories) == 1 && (categories_str == "live" || categories_str == "table") {
		// Если указана только одна категория "live" или "table", то ищем только в них
		// и отключаем сортировку по приоритету
		sort_by_lnd_cat = true
	}

	// Оптимизация для поиска.
	// Если указаны только группы или только категории, то ищем только в них
	if len(groups) == 0 && len(categories) == 0 {
		if len(tags) == 1 {
			search_result = manager.ByTag[tags_str]
			search_result = search_result.SearchByNameTag(name, tag)
			sort_games = false
		} else {
			search_result = manager.AllGames.SearchByNameTag(name, tag)
		}
	} else if len(groups) == 1 && len(categories) == 0 {
		search_result, found = manager.ByGroup[groups[0]]
		if found && (tag > 0 || name != "") {
			search_result = search_result.SearchByNameTag(name, tag)
		}
	} else if len(groups) == 0 && len(categories) == 1 {
		search_result, found = manager.ByCategory[categories[0]]
		if found && (tag > 0 || name != "") {
			search_result = search_result.SearchByNameTag(name, tag)
		}
	} else if len(groups) == 1 && len(categories) == 1 {
		g, found := manager.Groups[groups[0]]
		if found {
			search_result, found = g.ByCategory[categories[0]]
			if found && (tag > 0 || name != "") {
				search_result = search_result.SearchByNameTag(name, tag)
			}
		}
	} else {
		search_result = make(games.GamePtrList, 0)
		for _, p := range manager.Providers {
			search_result = p.SearchGames(groups, categories, tag, name, search_result)
		}
	}

	// Если установлен флаг in_disabled == "1", то ищем в отключенных играх
	if in_disabled == "1" {
		filter := filter.New(filter.Groups(groups), filter.Categories(categories), filter.Tags(tag), filter.Name(name))
		disabled_result := filter.FilterAllGames(manager.DisabledGames.AllGames)
		search_result = append(search_result, disabled_result...)
	}

	// Если поиск по типу бонуса
	if for_bonus_type != "" {
		bt, err := strconv.ParseInt(for_bonus_type, 10, 16)
		if err != nil {
			resp := responses.MakeGameReponse(responses.StatusError, err.Error(), 0, 0, 0, nil)
			render.JSON(w, r, &resp)
			return
		}
		// Важно! Установлен тип бонуса 47 для всех игр!
		// настройки по усолчанюи для всех бонусов устанавляваються в этом типе бонуса
		// Если необходимо использовать другой тип бонуса закоментируйте эти строки
		// if использован для подавления предупреждений компилятора
		if bt != 47 {
			bt = 47
		}

		if len(games.DisabledByBonusType[int16(bt)]) > 0 {
			ci := 0
			for i, game_ptr := range search_result {
				if games.DisabledByBonusType[int16(bt)][game_ptr.CID] ||
					games.DisabledByBonusType[int16(bt)][game_ptr.System+"/*"] ||
					games.DisabledByBonusType[int16(bt)][game_ptr.System+"/"+game_ptr.Group+"/*"] {
					search_result[ci] = search_result[i]
					ci++
				}
			}
			search_result = search_result[:ci]
		}

	}

	total_games := len(search_result)
	last_idx := total_games
	pages := 1

	// Сортировка
	if sort_games && total_games > 0 {
		slices.SortFunc(search_result, func(g1, g2 *games.Game) int {
			// сортировка по приоритету лендинга
			if sort_by_lnd_cat {
				if g1.LandPriority == 0 && g2.LandPriority > 0 {
					return 1
				}
				if g1.LandPriority > 0 && g2.LandPriority == 0 {
					return -1
				}
				if g1.LandPriority != g2.LandPriority {
					return int(g1.LandPriority - g2.LandPriority)
				}
			}
			// сортируем по приоритету (Топ игры всегда в начале)
			if g1.Priority == 0 && g2.Priority > 0 {
				return 1
			}
			if g1.Priority > 0 && g2.Priority == 0 {
				return -1
			}
			if g1.Priority != g2.Priority {
				return int(g1.Priority - g2.Priority)
			}
			// если приоритет одинаковый, сортируем по имени
			return strings.Compare(g1.Name, g2.Name)
		})
	}

	// Пагинация
	if per_page > 0 && page > 0 && total_games > 0 {

		pages = total_games / per_page
		if pages*per_page < total_games {
			pages++
		}

		i_st := per_page * (page - 1)
		i_e := per_page * page

		if i_st > last_idx {
			i_st = last_idx
		}
		if i_e > last_idx {
			i_e = last_idx
		}
		search_result = search_result[i_st:i_e]
	}
	resp := responses.MakeSearchResponse(responses.StatusOk, "", total_games, pages, page, search_result.GetGroups(), search_result)
	render.JSON(w, r, &resp)
}
