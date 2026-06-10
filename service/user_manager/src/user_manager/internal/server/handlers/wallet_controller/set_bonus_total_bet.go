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

func SetTotalBet(w http.ResponseWriter, r *http.Request) {
	method := "/wallet/setTotalBet"
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

	var req requests.SetAmountRequest
	err = json.Unmarshal(body, &req)
	if err != nil {
		logger.Get().Error(method, slog.String("err", err.Error()))
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, err))
		return
	}

	user, err := service.GetUser(stypes.UserID(req.UserId))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNotFound, errors.New("user not found")))
		logger.Get().Error(method+": user not found", slog.Int("id", int(req.UserId)))
		return
	}
	wallet, err := user.GetWallet(currency.CurrencyID(req.CurrencyID))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("can't get wallet")))
		logger.Get().Error(method+": can't get wallet", slog.Int("currency", int(req.CurrencyID)))
		return
	}

	wallet.SetTotalBet(req.Amount)
	err = wallet.Save()
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("add money error")))
		logger.Get().Error(method+": add money error", slog.String("err", err.Error()))
		return
	}

	resp := responses.MakeOkResponse()
	logger.Get().Info(method+":", slog.String("resp", fmt.Sprintf("%v", resp)))

	render.JSON(w, r, resp)
}
