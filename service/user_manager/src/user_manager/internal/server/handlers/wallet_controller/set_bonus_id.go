package walletcontroller

import (
	"encoding/json"
	"errors"
	"fmt"
	"io"
	"log/slog"
	"net/http"
	"user-manager/internal/logger"
	"user-manager/internal/server/requests"
	"user-manager/internal/server/responses"
	"user-manager/internal/service"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"

	"github.com/go-chi/render"
)

// SetBonusId устанавливает идентификатор бонуса модифицирующего отображение баланса для кошелька пользователя.
// Принимает HTTP запрос с данными пользователя, валюты и ID бонуса.
// Возвращает успешный ответ или ошибку.
func SetBonusId(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	// Путь метода API
	method := "/wallet/setBonusId"
	// Читаем тело запроса
	body, err := io.ReadAll(r.Body)
	if err != nil {
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}
	if err := service.CheckRequestSign(r, method, string(body)); err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Парсим JSON запрос в структуру
	var req requests.SetBonusIdRequest
	err = json.Unmarshal(body, &req)
	if err != nil {
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Получаем пользователя по ID
	user, err := service.GetUser(stypes.UserID(req.UserId))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNotFound, errors.New("user not found")))
		logger.Get().Error(method+": user not found", slog.Int("id", int(req.UserId)))
		return
	}
	// Получаем кошелек пользователя для указанной валюты
	wallet, err := user.GetWallet(currency.CurrencyID(req.CurrencyID))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("can't get wallet")))
		logger.Get().Error(method+": can't get wallet", slog.Int("currency", int(req.CurrencyID)))
		return
	}

	// Устанавливаем ID бонуса и сохраняем изменения
	wallet.SetBonusId(req.BonusID)
	err = wallet.Save()
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("set bonus id error: "+err.Error())))
		logger.Get().Error(method+": set bonus id error", slog.String("err", err.Error()))
		return
	}

	// Формируем ответ
	resp := responses.MakeOkResponse()
	logger.Get().Info(method+":", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
