package usecases

import (
	"encoding/json"
	"kreferrals/internal/db/models"
	"kreferrals/internal/logger"
	"kreferrals/internal/service"
	"kreferrals/internal/service/container"
)

func MoneySpent(ref_id uint64, user_id uint64, amount float64, currency string, currency_id int, fingerprint *string) {
	// Получаем ID пользователя по реферальному коду
	ref_user_id, err := service.GetUserIDByRefID(ref_id)
	if err != nil {
		logger.Get().Error("Error getting user ID by referral code", "error", err.Error())
		return
	}
	if ref_user_id == 0 {
		logger.Get().Info("Referral code not found", "ref_id", ref_id)
		return // Реферальный код не найден
	}

	config := service.GetUserConfig(ref_user_id)
	if config == nil {
		logger.Get().Info("No user config found for user ID", "user_id", user_id)
		return // Конфигурация пользователя не найдена
	}

	// Рассчитываем сумму, которая будет начислена на накопительный реферальный кошелек
	ref_amount := float64(config.GGRPrc) * amount / 100
	logger.Get().Info("Money spent amount for user ID", "user_id", user_id, "ref_amount", ref_amount, "currency", currency)
	if ref_amount != 0 {
		err := container.GetRefWallet().ChangeAccumulativeBalance(ref_user_id, ref_amount, currency)
		if err != nil {
			logger.Get().Error("Error changing referral balance", "ref_user_id", ref_user_id, "error", err.Error())
			return
		}
	}

	data := map[string]any{"amount": amount, "currency": currency, "ref_amount": ref_amount}
	jsonData, err := json.Marshal(data)
	if err != nil {
		logger.Get().Error("Error marshalling referral event data", "error", err.Error())
		return
	}
	var sjsonData string = string(jsonData)
	event := &models.ReferralEvent{
		UserID:      &user_id,
		RefID:       ref_id, // Идентификатор реферала, к которому относится событие
		Type:        int(models.ReferralEventTypeMoneySpent),
		Data:        &sjsonData,
		Amount:      amount,       // Сумма, связанная с событием
		Currency:    &currency_id, // Идентификатор валюты события
		Profit:      ref_amount,   // Прибыль, связанная с событием
		CreatedAt:   nil,          // Используем текущее время по умолчанию
		UpdatedAt:   nil,          // Используем текущее время по умолчанию
		Status:      0,            // Статус события
		ConfigID:    &config.ID,   // Может быть NULL, если не используется
		Fingerprint: fingerprint,  // Отпечаток пользователя, связанный с событием
	}
	if err := container.GetEventsSaver().SaveReferralEvent(event); err != nil {
		logger.Get().Error("Error saving referral event", "user_id", user_id, "error", err.Error())
		return
	}
}
