package usecases

import (
	"encoding/json"
	"kreferrals/internal/db/models"
	"kreferrals/internal/logger"
	"kreferrals/internal/service/container"
)

// RefClick обрабатывает переход по реферальному коду и сохраняет событие в базе данных.
func RefClick(ref_code string, referer string, fingerprint *string) {
	referral, err := container.GetUserReferralRepository().GetUserReferralByRefCode(ref_code)
	if err != nil {
		logger.Get().Error("Error getting referral by code", "ref_code", ref_code, "error", err)
		return
	}

	if referral == nil {
		logger.Get().Info("Referral code not found", "ref_code", ref_code)
		return
	}
	ref_id := referral.ID
	if ref_id == 0 {
		logger.Get().Info("Referral ID is zero", "ref_code", ref_code)
		return
	}

	if referral.UserID == nil {
		logger.Get().Info("Referral user ID is nil", "ref_code", ref_code)
		return
	}

	data := map[string]any{"ref_id": ref_id, "referer": referer}
	jsonData, err := json.Marshal(data)
	if err != nil {
		logger.Get().Error("Error marshalling user click event data", "error", err.Error())
	}
	var sjsonData string = string(jsonData)
	event := &models.ReferralEvent{
		UserID:      nil,
		RefID:       ref_id, // Идентификатор реферала, которому относится событие
		Type:        int(models.ReferralEventTypeClick),
		Data:        &sjsonData,
		CreatedAt:   nil,         // Используем текущее время по умолчанию
		UpdatedAt:   nil,         // Используем текущее время по умолчанию
		Status:      0,           // Статус события (например, 0 - ожидает обработки)
		ConfigID:    nil,         // Может быть NULL, если не используется
		Fingerprint: fingerprint, // Отпечаток пользователя, связанный с событием
	}
	if err := container.GetEventsSaver().SaveReferralEvent(event); err != nil {
		logger.Get().Error("Error saving user click event", "ref_id", ref_id, "error", err.Error())
	}
}
