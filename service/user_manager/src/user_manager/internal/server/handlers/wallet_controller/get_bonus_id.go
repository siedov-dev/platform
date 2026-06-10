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

// GetBonusId обрабатывает HTTP запрос для получения идентификатора бонуса кошелька пользователя
// модифицирующего отображение баланса кошелька
func GetBonusId(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()

	// Путь метода API
	method := "/wallet/getBonusId"
	// Чтение тела запроса
	body, err := io.ReadAll(r.Body)
	if err != nil {
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}
	// Проверка подписи запроса
	if err := service.CheckRequestSign(r, method, string(body)); err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	var req requests.GetBonusIdRequest
	err = json.Unmarshal(body, &req)
	if err != nil {
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Получение информации о пользователе
	user, err := service.GetUser(stypes.UserID(req.UserId))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNotFound, errors.New("user not found")))
		logger.Get().Error(method+": user not found", slog.Int("id", int(req.UserId)))
		return
	}
	// Получение кошелька пользователя для указанной валюты
	wallet, err := user.GetWallet(currency.CurrencyID(req.CurrencyID))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("can't get wallet")))
		logger.Get().Error(method+": can't get wallet", slog.Int("currency", int(req.CurrencyID)))
		return
	}

	// Ответ [responses.WalletBonusResponse]
	resp := responses.MakeWalletBonusResponseResponse(wallet.GetBonusId())
	logger.Get().Info(method+":", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
