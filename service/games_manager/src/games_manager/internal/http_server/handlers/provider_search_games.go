package handlers

import (
	"games_manager/internal/games"
	"games_manager/internal/games/filter"
	"games_manager/internal/http_server/responses"
	"log/slog"
	"net/http"
	"net/url"
	"slices"
	"strconv"
	"strings"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// ProviderSearchGames - обработчик для поиска игр провайдера
// принимает параметры:
// device - устройство (например, "desktop" или "phone")
// lang - язык (например, "ru", "en" и т.д.)
// provider - провайдер (например, "sleg")
// groups - группы игр (например, "Endorphina,Amatic")
// categories - категории игр (например, "slots,table")
// tags - теги игр (например, "top,new")
// per_page - количество игр на странице
// page - номер страницы
// name - имя игры (например, "book of ra")
// Опционально принимает параметр in_disabled - флаг, который указывает, нужно ли искать игру в отключенных играх
// Возвращает список игр в формате JSON
// Работает аналогично GamesSearch, но ищет только по провайдеру без приоритетной сортировки
func ProviderSearchGames(w http.ResponseWriter, r *http.Request) {
	device := chi.URLParam(r, "device")
	lang := chi.URLParam(r, "lang")
	in_disabled := r.URL.Query().Get("in_disabled")

	providerID, err := url.QueryUnescape(chi.URLParam(r, "provider"))
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}

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

	name, err := url.QueryUnescape(chi.URLParam(r, "name"))
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}
	name = strings.Trim(name, " \"")

	manager, found := games.GameManagers[device+"-"+lang]
	if !found {
		resp := responses.MakeGameReponse(responses.StatusError, "Games Manager not found", 0, 0, 0, nil)
		render.JSON(w, r, &resp)
		return
	}

	provider, found := manager.Providers[providerID]

	if !found {
		resp := responses.MakeGroupReponse(responses.StatusError, "Provider not found")
		render.JSON(w, r, &resp)
		return
	}

	var search_result games.GamePtrList

	//optimize search source
	if len(groups) == 0 && len(categories) == 0 {
		slog.Info("all games search")
		search_result = provider.AllGames.SearchByNameTag(name, tag)
	} else if len(groups) == 1 && len(categories) == 0 {
		slog.Info("groups search")
		search_result, found = provider.ByGroup[groups[0]]
		if found && (tag > 0 || name != "") {
			search_result = search_result.SearchByNameTag(name, tag)
		}
	} else if len(groups) == 0 && len(categories) == 1 {
		slog.Info("categories search")
		search_result, found = provider.ByCategory[categories[0]]
		if found && (tag > 0 || name != "") {
			search_result = search_result.SearchByNameTag(name, tag)
		}
	} else if len(groups) == 1 && len(categories) == 1 {
		slog.Info("categories and group search")
		g, found := provider.Groups[groups[0]]
		if found {
			search_result, found = g.ByCategory[categories[0]]
			if found && (tag > 0 || name != "") {
				search_result = search_result.SearchByNameTag(name, tag)
			}
		}
	} else {
		slog.Info("main search")
		search_result = make(games.GamePtrList, 0)
		for _, p := range provider.Groups {
			search_result = p.SearchGames(categories, tag, name, search_result)
		}
	}

	if in_disabled == "1" {
		filter := filter.New(filter.Provider(providerID), filter.Groups(groups), filter.Categories(categories), filter.Tags(tag), filter.Name(name))
		disabled_result := filter.FilterAllGames(manager.DisabledGames.AllGames)
		search_result = append(search_result, disabled_result...)
	}

	total_games := len(search_result)
	last_idx := total_games
	pages := 1

	// Сортировка по именам
	if total_games > 0 {
		slices.SortFunc(search_result, func(g1, g2 *games.Game) int { return strings.Compare(g1.Name, g2.Name) })
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
