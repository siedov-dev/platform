package usecases

import (
	"encoding/json"
	"kreferrals/internal/db/models"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/container"
)

// TransferRefMoney переводит реферальные деньги на кошелек пользователя.
func TransferRefMoney(user_id uint64, currency string, currency_id int) (transferred float64, err error) {

	referral, err := container.GetUserReferralRepository().GetUserReferralByUserID(user_id)
	if err != nil {
		logger.Get().Error("Error getting user referral", "user_id", user_id, "error", err.Error())
		return 0, err
	}
	if referral == nil {
		logger.Get().Info("Referral not found for user", "user_id", user_id)
		return 0, nil // Реферальный код не найден
	}
	ref_id := referral.ID // Идентификатор реферала, которому относится событие

	transferred, err = container.GetRefWallet().TransferRefMoney(user_id, currency)
	if err != nil {
		logger.Get().Error("Error changing referral balance", "user_id", user_id, "error", err.Error())
		return
	}

	logger.Get().Info("TransferRefMoney", "user_id", user_id, "currency", currency, "amount", transferred, "transferred", transferred)

	data := map[string]any{
		"user_id":     user_id,
		"currency":    currency,
		"transferred": transferred,
	}
	jsonData, err := json.Marshal(data)
	if err != nil {
		logger.Get().Error("Error marshalling transfer data", "error", err.Error())
	} else {
		var sjsonData string = string(jsonData)
		event := &models.ReferralEvent{
			UserID:    &user_id,
			RefID:     ref_id, // Идентификатор реферала, которому относится событие
			Type:      int(models.ReferralEventTypeReferralMoneyTransferred),
			Data:      &sjsonData,
			Amount:    transferred,  // Сумма переведенных средств
			Currency:  &currency_id, // Идентификатор валюты события
			CreatedAt: nil,          // Используем текущее время по умолчанию
			UpdatedAt: nil,          // Используем текущее время по умолчанию
			Status:    0,            // Статус события (например, 0 - ожидает обработки)
			ConfigID:  nil,          // Может быть NULL, если не используется
		}
		if err := container.GetEventsSaver().SaveReferralEvent(event); err != nil {
			logger.Get().Error("Error saving user registered event", "user_id", user_id, "error", err.Error())
		}
	}

	return transferred, nil
}
