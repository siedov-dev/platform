package callbacks

import (
	"encoding/json"
	"fmt"
	"log/slog"
	"user-manager/internal/logger"
	"user-manager/internal/server/responses"
	"user-manager/internal/service"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"

	"github.com/nats-io/nats.go"
)

type ChangeBonusMoneyRequest struct {
	UserID   int64   `json:"user_id"`  // ID пользователя, которому добавляются реферальные средства
	Amount   float64 `json:"amount"`   // Сумма реферальных средств, которые нужно добавить/убрать (может быть отрицательной для уменьшения)
	Currency string  `json:"currency"` // Валюта, в которой указана сумма
}

func ChangeBonusMoney(msg *nats.Msg) {
	// Обработчик изменения накопительных реферальных средств
	// msg.Data содержит данные запроса, которые должны быть в формате JSON

	var req ChangeBonusMoneyRequest
	if err := json.Unmarshal(msg.Data, &req); err != nil {
		logger.Get().Error("Error unmarshalling add ref money request", slog.String("error", err.Error()))
		// Отправляем ответ с ошибкой
		sendErrorResponse(msg, fmt.Errorf("error: unable to parse request"))
		return
	}
	// Логируем запрос
	logger.Get().Info("Received add ref money request",
		slog.Int64("user_id", req.UserID),
		slog.Float64("amount", req.Amount),
		slog.String("currency", req.Currency),
	)

	// Получение данных пользователя по ID
	user, err := service.GetUserTemp(stypes.UserID(req.UserID))
	if err != nil {
		sendErrorResponse(msg, fmt.Errorf("error: user not found"))
		logger.Get().Error("ChangeBonusMoney: user not found", slog.Int64("id", req.UserID))
		return
	}

	curr_id, err := currency.GetCurrencyID(req.Currency)
	if err != nil {
		// Отправляем ответ с ошибкой, если валюта не найдена
		sendErrorResponse(msg, fmt.Errorf("error: currency not found"))
		logger.Get().Error("ChangeBonusMoney: currency not found", slog.String("currency", req.Currency))
		return
	}

	// Получение кошелька пользователя для указанной валюты
	wallet, err := user.GetWallet(currency.CurrencyID(curr_id))
	if err != nil {
		// Отправляем ответ с ошибкой, если кошелек не найден
		sendErrorResponse(msg, fmt.Errorf("error: wallet not found for user %d in currency %s", req.UserID, req.Currency))
		logger.Get().Error("ChangeBonusMoney: wallet not found", slog.Int64("user_id", req.UserID), slog.String("currency", req.Currency))
		return
	}

	// Изменение реферальных средств и сохранение изменений
	if req.Amount < 0 {
		wallet.RemBonusMoney(-req.Amount)
	} else if req.Amount > 0 {
		wallet.AddBonusMoney(req.Amount)
	}
	err = wallet.Save()
	if err != nil {
		// Отправляем ответ с ошибкой, если не удалось сохранить изменения в кошельке
		sendErrorResponse(msg, fmt.Errorf("error: unable to save wallet changes"))
		logger.Get().Error("ChangeBonusMoney: unable to save wallet changes", slog.String("error", err.Error()))
		return
	}

	// Формирование и отправка ответа с обновленной информацией о реферальных средствах
	resp := responses.MakeAmountResponse(wallet.GetReferralMoney(), req.Currency, int8(curr_id))
	logger.Get().Info("ChangeBonusMoney:", slog.String("resp", fmt.Sprintf("%v", resp)))
	// Отправляем ответ в формате JSON
	respJSON, err := json.Marshal(resp)
	if err != nil {
		logger.Get().Error("Error marshalling add ref money response", slog.String("error", err.Error()))
		// Отправляем ответ с ошибкой, если не удалось сериализовать ответ
		if err := msg.Respond([]byte("error: unable to create response")); err != nil {
			logger.Get().Error("Error responding to add ref money", slog.String("error", err.Error()))
		}
		return
	}
	// Отправляем ответ
	if err := msg.Respond(respJSON); err != nil {
		logger.Get().Error("Error responding to add ref money", slog.String("error", err.Error()))
		return
	}
}
