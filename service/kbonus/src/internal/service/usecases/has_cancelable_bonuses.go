package usecases

func HasCancelableOnPayout(userID int64) (bool, error) {
	// Получаем список всех бонусов пользователя
	bonuses, err := GetUserBonuses(userID, 0) // 0 означает, что не фильтруем по валюте
	if err != nil {
		return false, err
	}

	// Проверяем, есть ли среди бонусов с возможностью отмены
	for _, bonus := range bonuses {
		if bonus.GetBonusInfo().IsCancelebleOnPayout {
			return true, nil
		}
	}

	return false, nil // Если не найдено ни одного бонуса с возможностью отмены
}
