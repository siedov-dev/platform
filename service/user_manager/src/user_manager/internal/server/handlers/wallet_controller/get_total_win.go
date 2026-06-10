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

// Обработчик для получения общей суммы выигрыша пользователя
// Принимает ID пользователя и ID валюты
func GetTotalWin(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()
	method := "/wallet/getTotalWin"
	// Читаем тело запроса
	body, err := io.ReadAll(r.Body)
	if err != nil {
		// Ошибка при чтении тела запроса
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}
	// Проверяем подпись запроса
	if err := service.CheckRequestSign(r, method, string(body)); err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Десериализуем запрос
	var req requests.GetWalletValueRequest
	err = json.Unmarshal(body, &req)
	if err != nil {
		// Ошибка при разборе JSON
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	// Получаем данные пользователя
	user, err := service.GetUserTemp(stypes.UserID(req.UserId))
	if err != nil {
		// Пользователь не найден
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNotFound, errors.New("user not found")))
		logger.Get().Error(method+": user not found", slog.Int("id", int(req.UserId)))
		return
	}
	// Получаем кошелек пользователя в указанной валюте
	wallet, err := user.GetWallet(currency.CurrencyID(req.CurrencyID))
	if err != nil {
		// Ошибка при получении кошелька
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("can't get wallet")))
		logger.Get().Error(method+": can't get wallet", slog.Int("currency", int(req.CurrencyID)))
		return
	}

	// Формируем и отправляем ответ с общей суммой выигрыша
	resp := responses.MakeAmountResponse(wallet.GetTotalWin(), wallet.GetCurrencyName(), wallet.DbWallet.CurrencyID)
	logger.Get().Info(method+":", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
