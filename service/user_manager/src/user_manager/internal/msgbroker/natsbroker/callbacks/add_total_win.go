package callbacks

import (
	"encoding/json"
	"fmt"
	"log/slog"
	"user-manager/internal/logger"
	"user-manager/internal/server/requests"
	"user-manager/internal/service/usecases"

	"github.com/nats-io/nats.go"
)

func AddTotalWin(msg *nats.Msg) {

	var req requests.BalanceAddRequest

	if err := json.Unmarshal(msg.Data, &req); err != nil {
		logger.Get().Error("Error unmarshalling AddTotalWin money request", slog.String("error", err.Error()))
		// Отправляем ответ с ошибкой
		sendErrorResponse(msg, fmt.Errorf("error: unable to parse request"))
		return
	}

	resp, err := usecases.AddTotalWin(int64(req.UserId), int(req.CurrencyID), req.Amount)
	if err != nil {
		sendErrorResponse(msg, fmt.Errorf("error: %s", err.Error()))
		logger.Get().Error("AddTotalWin: error adding money", slog.Int64("user_id", int64(req.UserId)), slog.Int("currency_id", int(req.CurrencyID)))
		return
	}

	// Отправляем ответ с информацией о балансе
	responseData, err := json.Marshal(resp)
	if err != nil {
		logger.Get().Error("AddTotalWin: error marshalling response", slog.String("error", err.Error()))
		sendErrorResponse(msg, fmt.Errorf("error: unable to create response"))
		return
	}
	if err := msg.Respond(responseData); err != nil {
		logger.Get().Error("AddTotalWin: error sending response", slog.String("error", err.Error()))
		return
	}
}
