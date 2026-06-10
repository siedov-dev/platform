package usecases

import (
	"kbonus/internal/service"
	"kbonus/internal/service/entities"
)

func GetFirstActiveBonus(userID int64, currencyID int8) (*entities.Bonus, error) {
	// Получаем список активных бонусов пользователя
	bonuses, err := service.GetUserBonuses(userID)
	if err != nil {
		return nil, err
	}

	// Ищем первый активный бонус
	for _, bonusID := range bonuses.GetBonusIDs() {
		bonus, err := service.GetBonus(bonusID, 100) // 100 - процент вейджеринга по умолчанию
		if err != nil {
			continue
		}
		if bonus.IsActive() && (currencyID == 0 || int8(bonus.GetCurrencyID()) == currencyID) {
			return bonus, nil // Возвращаем информацию о первом активном бонусе
		}
	}

	return nil, nil // Нет активных бонусов
}
