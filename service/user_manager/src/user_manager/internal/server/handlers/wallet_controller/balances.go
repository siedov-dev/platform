package walletcontroller

import (
	"errors"
	"fmt"
	"log/slog"
	"net/http"
	"strconv"
	"user-manager/internal/logger"
	"user-manager/internal/server/responses"
	"user-manager/internal/service"
	"user-manager/internal/stypes"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/render"
)

// Balances обрабатывает HTTP-запрос для получения балансов кошельков пользователя и возвращает JSON-ответ.
// Использует user_id из URL, загружает данные пользователя, формирует и возвращает ответ с информацией о балансе.
// В случае ошибок возвращает соответствующий JSON-ответ с кодом состояния и описанием ошибки.
func Balances(w http.ResponseWriter, r *http.Request) {
	defer r.Body.Close()
	u_id_p := chi.URLParam(r, "user_id")
	log := logger.Get()
	log.Info("Balances:", slog.String("request", r.RequestURI), slog.String("rip", r.RemoteAddr))
	u_id, err := strconv.Atoi(u_id_p)
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusInternalServerError, errors.New("user id error")))
		logger.Get().Error("Balances: wrong user id", slog.String("id", u_id_p))
		return
	}

	user, err := service.GetUser(stypes.UserID(u_id))
	if err != nil {
		render.JSON(w, r, responses.MakeErrorResponse(http.StatusNotFound, errors.New("user not found")))
		logger.Get().Error("Balances: user not found", slog.Int("id", u_id))
		return
	}

	wallets := user.GetWallets()
	resp := responses.MakeBalancesResponse(wallets)
	resp.Active = user.GetDbUser().CurrencyId

	logger.Get().Info("Balances:", slog.String("resp", fmt.Sprintf("%v", resp)))
	render.JSON(w, r, resp)
}
