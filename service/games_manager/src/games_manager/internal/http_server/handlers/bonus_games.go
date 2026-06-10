package handlers

import (
	"games_manager/internal/games"
	"games_manager/internal/games/filter"
	"games_manager/internal/http_server/responses"
	"games_manager/internal/repositories"
	"net/http"
	"net/url"
	"slices"
	"strconv"
	"strings"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// BonusGames - обработчик для получения списка игр по бонусу
// принимает параметры:
// device - устройство (desktop или phone)
// lang - язык (например, ru, en и т.д.)
// bonus_id - ID бонуса
// groups - группы игр (например, "Endorphina,Amatic")
// categories - категории игр (например, "slots,table")
// tags - теги игр (например, "top,new")
// per_page - количество игр на странице
// page - номер страницы
// name - имя игры (например, "book of ra")
// Возвращает список игр в формате JSON
// Пример запроса: GET /games/bonus/desktop/ru/1/Endorphina,Amatic/slots,table/top,new/10/1/book%20of%20ra
func BonusGames(w http.ResponseWriter, r *http.Request) {
	device := chi.URLParam(r, "device")
	lang := chi.URLParam(r, "lang")

	// Получаем ID бонуса из URL-параметра
	bonus_id_s, err := url.QueryUnescape(chi.URLParam(r, "bonus_id"))
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}
	bonus_id, err := strconv.Atoi(bonus_id_s)
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}

	// Получаем группы, категории и теги из URL-параметров
	// Пример: "Endorphina,Amatic" -> ["Endorphina", "Amatic"]
	// Пример: "slots,table" -> ["slots", "table"]
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

	// Получаем номер страницы из URL-параметра
	page_str := chi.URLParam(r, "page")
	page, err := strconv.Atoi(page_str)
	if err != nil {
		page = 0
	}

	// Получаем имя игры из URL-параметра
	name, err := url.QueryUnescape(chi.URLParam(r, "name"))
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}
	name = strings.Trim(name, " \"")

	// Получаем менеджер игр для указанного устройства и языка
	manager, found := games.GameManagers[device+"-"+lang]
	if !found {
		resp := responses.MakeGameReponse(responses.StatusError, "Games Manager not found", 0, 0, 0, nil)
		render.JSON(w, r, &resp)
		return
	}

	// Создаем фильтр для поиска игр
	// Фильтр принимает группы, категории, теги и имя игры
	filter := filter.New(filter.Groups(groups), filter.Categories(categories), filter.Tags(tag), filter.Name(name))

	bonus, err := repositories.GetUserBonusRepository().ById(bonus_id)
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}

	// Получаем список игр по бонусу
	games_list, err := manager.GetBonusGames(bonus)
	if err != nil {
		render.PlainText(w, r, err.Error())
		return
	}

	// Фильтруем список игр по заданным критериям
	search_result := filter.FilterGames(games_list)

	total_games := len(search_result)
	last_idx := total_games
	pages := 1

	// Сортируем список игр по имени
	// Если количество игр больше 0, то сортируем
	if total_games > 0 {
		slices.SortFunc(search_result, func(g1, g2 *games.Game) int { return strings.Compare(g1.Name, g2.Name) })
	}

	// Если количество игр больше 0, то обрезаем список по номеру страницы и количеству игр на странице
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

	// Отправляем ответ в формате JSON
	resp := responses.MakeSearchResponse(responses.StatusOk, "", total_games, pages, page, search_result.GetGroups(), search_result)
	render.JSON(w, r, &resp)
}
