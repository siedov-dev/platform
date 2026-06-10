package usecases

import (
	"kreferrals/internal/db/models"
	"kreferrals/internal/logger"
	"kreferrals/internal/service"
	"kreferrals/internal/service/container"
)

func UserRegistered(ref_id uint64, user_id uint64, fingerprint *string) {
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

	event := &models.ReferralEvent{
		UserID:      &user_id,
		RefID:       ref_id, // Идентификатор реферала, которому относится событие
		Type:        int(models.ReferralEventTypeRegister),
		Data:        nil,
		CreatedAt:   nil,         // Используем текущее время по умолчанию
		UpdatedAt:   nil,         // Используем текущее время по умолчанию
		Status:      0,           // Статус события (например, 0 - ожидает обработки)
		ConfigID:    nil,         // Может быть NULL, если не используется
		Fingerprint: fingerprint, // Отпечаток пользователя, связанный с событием
	}
	if err := container.GetEventsSaver().SaveReferralEvent(event); err != nil {
		logger.Get().Error("Error saving user registered event", "user_id", user_id, "error", err.Error())
		return
	}
}
