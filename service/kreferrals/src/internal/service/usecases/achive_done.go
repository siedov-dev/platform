package usecases

import (
	"encoding/json"
	"kreferrals/internal/db/models"
	"kreferrals/internal/logger"
	"kreferrals/internal/service"
	"kreferrals/internal/service/container"
)

func AchiveDone(ref_id uint64, user_id uint64, ach_id uint64, currency string, currency_id int, fingerprint *string) {
	ref_user_id, err := service.GetUserIDByRefID(ref_id)
	if err != nil {
		logger.Get().Error("Error getting user ID by referral code", "error", err.Error())
		return
	}
	if ref_user_id == 0 {
		logger.Get().Info("Referral code not found", "ref_id", ref_id)
		return // Реферальный код не найден
	}

	reward := service.GetAchievementReward(ach_id, currency)

	logger.Get().Info("First deposit amount for user ID", "user_id", user_id, "reward", reward, "currency", currency)
	if reward == 0 {
		logger.Get().Info("No reward for achievement", "ach_id", ach_id, "currency", currency)
		return // Награда за достижение равна 0, ничего не делаем
	}
	err = container.GetRefWallet().ChangeRefBalance(ref_user_id, reward, currency)
	if err != nil {
		logger.Get().Error("Error changing referral balance", "ref_user_id", ref_user_id, "error", err.Error())
		return
	}

	data := map[string]any{"ach_id": ach_id, "currency": currency}
	jsonData, err := json.Marshal(data)
	if err != nil {
		logger.Get().Error("Error marshalling referral event data", "error", err.Error())
	}
	var sjsonData string = string(jsonData)

	event := &models.ReferralEvent{
		UserID:      &user_id,
		RefID:       ref_id, // Идентификатор реферала, к которому относится событие
		Type:        int(models.ReferralEventTypeAchievementCompleted),
		Data:        &sjsonData,
		Amount:      0,            // Сумма, связанная с событием (может быть 0 для достижений)
		Currency:    &currency_id, // Идентификатор валюты события
		Profit:      reward,       // Прибыль, связанная с событием
		CreatedAt:   nil,          // Используем текущее время по умолчанию
		UpdatedAt:   nil,          // Используем текущее время по умолчанию
		Status:      0,            // Статус события (например, 0 - ожидает обработки)
		Fingerprint: fingerprint,  // Отпечаток пользователя, связанный с событием
	}
	if err := container.GetEventsSaver().SaveReferralEvent(event); err != nil {
		logger.Get().Error("Error saving referral event", "user_id", user_id, "error", err.Error())
		return
	}
}
