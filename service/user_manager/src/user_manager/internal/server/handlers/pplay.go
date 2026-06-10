package handlers

import (
	"fmt"
	"log/slog"
	"net/http"
	"user-manager/internal/gamesystems/pragplay"
	pplayresponses "user-manager/internal/gamesystems/pragplay/pplay_responses"
	"user-manager/internal/logger"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// PPlay обрабатывает запросы от системы PragmaticPlay
// Поддерживает различные игровые операции через параметр method
func PPlay(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	log := logger.Get()
	method := chi.URLParam(r, "method")
	pplay := pragplay.Make()

	hash := r.PostFormValue("hash")

	log.Info("pplay", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	log.Info("pplay", slog.String("PostForm", fmt.Sprintf("%v", r.PostForm)))
	log.Info("pplay", slog.String("method", fmt.Sprintf("%v", method)))

	postData := make(map[string]string)
	for key, values := range r.PostForm {
		postData[key] = values[0]
	}

	// проверяем корректность хэша
	// если хэш не совпадает, возвращаем ошибку
	if hash != pplay.CalcHash(postData) {
		log.Error("pplay", slog.String("wrong hash", hash))
		render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(5, "Invalid hash code"))
		return
	}
	// обработка различных методов API:
	// authenticate.html - аутентификация игрока
	// balance.html - получение баланса
	// bet.html - обработка ставки
	// result.html - обработка выигрыша
	// refund.html - возврат ставки
	// bonusWin.html - начисление бонуса
	// jackpotWin.html - выигрыш джекпота
	// endRound.html - завершение раунда
	// promoWin.html - промо выигрыш
	// session/expired - истечение сессии
	switch method {
	case "authenticate.html":
		pplay.Authenticate(w, r)
		return
	case "balance.html":
		pplay.Balance(w, r)
		return
	case "bet.html":
		pplay.Bet(w, r)
		return
	case "result.html":
		pplay.Win(w, r)
		return
	case "refund.html":
		pplay.Refund(w, r)
		return
	case "bonusWin.html":
		pplay.BonusWin(w, r)
		return
	case "jackpotWin.html":
		pplay.JackpotWin(w, r)
		return
	case "endRound.html":
		pplay.EndRound(w, r)
		return
	case "promoWin.html":
		pplay.PromoWin(w, r)
		return
	case "session/expired":
		pplay.SessionExpired(w, r)
		return
	}
	log.Error("pplay", slog.String("unknown method", method))
	render.JSON(w, r, pplayresponses.MakePPlayErrorResponse(120, "Unknown method"))
}
