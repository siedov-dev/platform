package handlers

import (
	"encoding/json"
	"io"
	"log/slog"
	"net/http"
	slegresponses "user-manager/internal/gamesystems/slotegrator/sleg_responses"
	"user-manager/internal/logger"
	"user-manager/internal/server/requests"
	"user-manager/internal/server/responses"
	"user-manager/internal/service"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"

	"github.com/go-chi/render"
)

// StartGame обрабатывает запрос на начало игры.
// Проверяет подпись запроса, создает игровую сессию и устанавливает начальный баланс
func StartGame(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	log := logger.Get()
	method := "/startGame"
	// Читаем тело запроса
	body, err := io.ReadAll(r.Body)
	defer r.Body.Close()
	if err != nil {
		log.Error("StartGame", slog.String("err", err.Error()))
		w.WriteHeader(http.StatusInternalServerError)
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Проверяем подпись запроса
	if err := service.CheckRequestSign(r, method, string(body)); err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	log.Info("StartGame", slog.String("body", string(body)))
	// Разбираем JSON запрос
	var sr requests.StartGameRequest
	err = json.Unmarshal(body, &sr)
	if err != nil {
		log.Error("StartGame", slog.String("err", err.Error()))
		w.WriteHeader(http.StatusInternalServerError)
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Получаем пользователя по ID
	user, err := service.GetUser(stypes.UserID(sr.UserID))
	if err != nil {
		log.Error("sleg", slog.Int64("user not found", sr.UserID))
		render.JSON(w, r, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err))
	}
	// Получаем валюту по ID
	curr, err := currency.GetName(sr.GameData.CurrencyID)
	if err != nil {
		log.Error("sleg", slog.Int64("wrogn currency id", int64(sr.GameData.CurrencyID)))
		render.JSON(w, r, slegresponses.MakeErrorResponse("INTERNAL_ERROR", err))
	}

	// Закрываем текущую сессию и создаем новую игровую сессию
	user.CloseGameSession()
	gs, err := user.GetCurrentGameSession().CreateGameSession(user.GetID(), curr, sr, sr.GameData)
	if err != nil {
		log.Error("StartGame", slog.String("err", err.Error()))
		w.WriteHeader(http.StatusInternalServerError)
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}
	// Получаем кошелек пользователя для указанной валюты
	wallet, err := user.GetWallet(sr.GameData.CurrencyID)
	if err != nil {
		log.Error("StartGame", slog.String("err", err.Error()))
		w.WriteHeader(http.StatusInternalServerError)
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}
	// Устанавливаем начальный баланс, сохраняем сессию и отправляем успешный ответ
	gs.Stats.LoginBalance = wallet.GetBalance()

	user.SetGameSession(gs)
	user.GetGameSession().Save()

	render.JSON(w, r, responses.MakeOkResponse())
}
