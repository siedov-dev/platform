package handlers

import (
	"games_manager/internal/games"
	"games_manager/internal/http_server/responses"
	"net/http"
	"strconv"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// ProviderGamesInSegment - обработчик для получения списка игр по сегменту
// принимает параметры:
// device - устройство (desktop или phone)
// lang - язык (например, ru, en и т.д.)
// segment - ID сегмента
// Возвращает список игр в формате JSON
// Пример запроса: GET /games/segment/desktop/ru/1
func ProviderGamesInSegment(w http.ResponseWriter, r *http.Request) {
	device := chi.URLParam(r, "device")
	lang := chi.URLParam(r, "lang")
	segmentID := chi.URLParam(r, "segment")
	segment_id, err := strconv.ParseUint(segmentID, 10, 64)
	if err != nil {
		resp := responses.MakeGameReponse(responses.StatusNotFound, "Wrong segment ID", 0, 0, 0, nil)
		render.JSON(w, r, &resp)
		return
	}

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

	manager, found := games.GameManagers[device+"-"+lang]
	if !found {
		resp := responses.MakeGameReponse(responses.StatusError, "Games Manager not found", 0, 0, 0, nil)
		render.JSON(w, r, &resp)
		return
	}
	games := manager.GetGamesBySegmentID(segment_id)
	if len(games) == 0 {
		resp := responses.MakeGameReponse(responses.StatusNotFound, "No games in this segment", 0, 0, 0, nil)
		render.JSON(w, r, &resp)
		return
	}

	total_games := len(games)
	last_idx := total_games
	pages := 1

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
		games = games[i_st:i_e]
	}
	resp := responses.MakeSearchResponse(responses.StatusOk, "", total_games, pages, page, games.GetGroups(), games)
	render.JSON(w, r, &resp)
}
