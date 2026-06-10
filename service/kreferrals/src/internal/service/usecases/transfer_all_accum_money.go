package usecases

import (
	"encoding/json"
	"fmt"
	"kreferrals/internal/db/models"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/container"
)

// TransferAllAccumMoney переводит все накопленные средства всех рефералов
func TransferAllAccumMoney() error {
	currentPosition := int64(0)
	limit := int64(500)
	for {
		userReferrals, err := container.GetUserReferralRepository().GetUserReferrals(currentPosition, limit)
		logger.Get().Info("TransferAllAccumMoney", "userfererrals count", len(userReferrals))
		if err != nil {
			return err
		}
		for _, userReferral := range userReferrals {
			logger.Get().Info("TransferAllAccumMoney", "userReferral", *userReferral.UserID)
			transferred, err := container.GetRefWallet().TransferAllAccumulatedMoney(*userReferral.UserID)
			if err != nil {
				return err
			}
			if transferred.Error != "" {
				return fmt.Errorf("error transferring accumulated money: %s", transferred.Error)
			}
			for currency, amount := range transferred.Amounts {
				logger.Get().Info("TransferAllAccumMoney", "user_id", *userReferral.UserID, "currency", currency, "amount", amount)
				data := map[string]any{
					"user_id":     userReferral.UserID,
					"currency":    currency,
					"transferred": transferred,
				}
				jsonData, err := json.Marshal(data)
				if err != nil {
					logger.Get().Error("Error marshalling transfer data", "error", err.Error())
				} else {
					var sjsonData string = string(jsonData)
					event := &models.ReferralEvent{
						UserID:    userReferral.UserID,
						RefID:     userReferral.ID, // Идентификатор реферала, которому относится событие
						Type:      int(models.ReferralEventTypeAccumulationMoneyTransferred),
						Data:      &sjsonData,
						Amount:    amount, // Сумма переведенных средств
						Profit:    0,
						Currency:  &currency, // Идентификатор валюты события
						CreatedAt: nil,       // Используем текущее время по умолчанию
						UpdatedAt: nil,       // Используем текущее время по умолчанию
						Status:    0,         // Статус события (например, 0 - ожидает обработки)
						ConfigID:  nil,       // Может быть NULL, если не используется
					}
					if err := container.GetEventsSaver().SaveReferralEvent(event); err != nil {
						logger.Get().Error("Error saving TransferAllAccumMoney event", "user_id", userReferral.UserID, "error", err.Error())
					}
				}
			}
		}
		if len(userReferrals) < int(limit) {
			break
		}
		currentPosition += limit
	}
	return nil
}
