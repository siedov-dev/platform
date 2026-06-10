package handlers

import (
	"errors"
	"fmt"
	"log/slog"
	"net/http"
	"regexp"
	"strconv"
	"user-manager/internal/gamesystems/slotegrator"
	slegresponses "user-manager/internal/gamesystems/slotegrator/sleg_responses"
	"user-manager/internal/logger"
	"user-manager/internal/service"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"

	"github.com/go-chi/render"
)

// Sleg обрабатывает запросы от системы Slotegrator
// Поддерживает следующие действия через параметр action:
// - balance: получение баланса игрока
// - bet: обработка ставки
// - win: обработка выигрыша
// - refund: возврат ставки
func Sleg(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	log := logger.Get()
	sleg := slotegrator.Make()

	// Получаем основные параметры запроса
	action := r.PostFormValue("action")          // тип операции
	player_id := r.PostFormValue("player_id")    // идентификатор игрока
	game_currency := r.PostFormValue("currency") // валюта игры
	isBonusGame := false
	var UserId string
	var currencyName string

	log.Info("sleg", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	log.Info("sleg", slog.String("action", action))
	log.Info("sleg", slog.String("request", fmt.Sprintf("%v\n", r.PostForm)))
	log.Info("sleg", slog.String("headers", fmt.Sprintf("%v\n", r.Header)))

	w.Header().Del("Content-Type") // Удаляем Content-Type, чтобы избежать конфликта с render.JSON
	w.Header().Set("Content-Type", "application/json")

	if !sleg.CheckSign(r) {
		w.WriteHeader(http.StatusOK)
		log.Error("sleg", slog.String("wrong sign", r.Header.Get("X-Sign")))
		render.JSON(w, r, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("wrong sign")))
		return
	}

	// Разбираем ID игрока на составляющие:
	// - признак бонусной игры
	// - числовой идентификатор пользователя
	// - код валюты (опционально)
	re := regexp.MustCompile(`^(bonus)?([0-9]+)([a-zA-Z]{3,5})?$`)

	match := re.FindStringSubmatch(player_id)
	if match != nil {
		isBonusGame = match[1] != ""
		UserId = match[2]
		if match[3] != "" {
			currencyName = match[3]
		} else {
			currencyName = game_currency
		}
	}

	if currencyName == "USD" {
		currencyName = "USDT"
	}

	user_id, err := strconv.ParseInt(UserId, 10, 64)
	if err != nil {
		log.Error("sleg", slog.String("cannot convert user id", UserId))
		render.JSON(w, r, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err))
		return
	}

	// Получаем пользователя
	user, err := service.GetUser(stypes.UserID(user_id))
	if err != nil {
		log.Error("sleg", slog.String("user not found", UserId))
		render.JSON(w, r, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err))
		return
	}

	// Получаем игровую сессию
	// gs := user.GetGameSession()
	// if gs == nil || gs.Stats.SessionID == nil {
	// 	log.Error("sleg", slog.String("no game session", UserId))
	// 	render.JSON(w, r, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("no game session found")))
	// 	return
	// }
	// session_id := r.PostFormValue("session_id")
	// if *gs.Stats.SessionID != session_id {
	// 	log.Error("sleg", slog.String("wrong game session", UserId))
	// 	render.JSON(w, r, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("wrong game session")))
	// 	return
	// }

	// Ид валюты
	curr, err := currency.GetCurrencyID(currencyName)
	if err != nil {
		log.Error("sleg", slog.String("wrong currency name", currencyName))
		render.JSON(w, r, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err))
		return
	}

	// Обработка различных типов действий:
	// - balance: получение баланса
	// - bet: обработка ставки
	// - win: начисление выигрыша
	// - refund: возврат ставки
	switch action {
	case "balance":
		resp, err := sleg.Balance(user, isBonusGame, curr)
		if err != nil {
			switch e := err.(type) {
			case slegresponses.ErrorResponse:
				log.Error("sleg", slog.String(e.GetCode(), e.Error()))
				render.JSON(w, r, e)
			default:
				log.Error("sleg", slog.String("balance error", err.Error()))
				render.JSON(w, r, slegresponses.MakeErrorResponse("ERROR", err))
			}
			return
		}
		render.JSON(w, r, resp)
		return
	case "bet":
		resp, err := sleg.Bet(r, user, isBonusGame, curr)
		if err != nil {
			switch e := err.(type) {
			case slegresponses.ErrorResponse:
				log.Error("sleg", slog.String(e.GetCode(), e.Error()))
				render.JSON(w, r, e)
			default:
				log.Error("sleg", slog.String("bet error", err.Error()))
				render.JSON(w, r, slegresponses.MakeErrorResponse("ERROR", err))
			}
			return
		}
		log.Info("sleg", slog.String("bet resp", fmt.Sprintf("%v", resp)))
		render.JSON(w, r, resp)
		return

	case "win":
		resp, err := sleg.Win(r, user, isBonusGame, curr)
		if err != nil {
			switch e := err.(type) {
			case slegresponses.ErrorResponse:
				log.Error("sleg", slog.String(e.GetCode(), e.Error()))
				render.JSON(w, r, e)
			default:
				log.Error("sleg", slog.String("win error", err.Error()))
				render.JSON(w, r, slegresponses.MakeErrorResponse("ERROR", err))
			}
			return
		}
		render.JSON(w, r, resp)
		log.Info("sleg", slog.String("win resp", fmt.Sprintf("%v", resp)))
		return

	case "refund":
		resp, err := sleg.Refund(r, user, isBonusGame, curr)
		if err != nil {
			switch e := err.(type) {
			case slegresponses.ErrorResponse:
				log.Error("sleg", slog.String(e.GetCode(), e.Error()))
				render.JSON(w, r, e)
			default:
				log.Error("sleg", slog.String("refund error", err.Error()))
				render.JSON(w, r, slegresponses.MakeErrorResponse("ERROR", err))
			}
			return
		}
		render.JSON(w, r, resp)
		log.Info("sleg", slog.String("refund resp", fmt.Sprintf("%v", resp)))
		return

	case "rollback":
		resp, err := sleg.Rollback(r, user, isBonusGame, curr)
		if err != nil {
			switch e := err.(type) {
			case slegresponses.ErrorResponse:
				log.Error("sleg", slog.String(e.GetCode(), e.Error()))
				render.JSON(w, r, e)
			default:
				log.Error("sleg", slog.String("rollback error", err.Error()))
				render.JSON(w, r, slegresponses.MakeErrorResponse("ERROR", err))
			}
			return
		}
		render.JSON(w, r, resp)
		log.Info("sleg", slog.String("rollback resp", fmt.Sprintf("%v", resp)))
		return

	default:
		log.Error("sleg", slog.String("unknown action", action))
		render.JSON(w, r, slegresponses.MakeErrorResponse("INTERNAL_ERROR", errors.New("unknown action")))
		return
	}

}
