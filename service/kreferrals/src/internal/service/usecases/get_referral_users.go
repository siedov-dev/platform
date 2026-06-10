package usecases

import (
	"kreferrals/internal/service/container"
)

// GetReferralUsers возвращает список пользователей с их прибылью, приглашенных рефералом с указанным ref_id.
// Параметры offset и limit используются для пагинации результатов.
// Возвращает список пользователей, их валюту и прибыль, если она есть.
// Также возвращает общее количество записей, приглашенных рефералом.
func GetReferralUsers(ref_id uint64, offset uint64, limit uint64) (users []*container.ReferralMUser, total_rows uint64, err error) {
	total, err := container.GetReferralMUsersRepository().GetReferralUsersCountWithProfit(ref_id)
	if err != nil {
		return nil, 0, err
	}
	users, err = container.GetReferralMUsersRepository().GetReferralUsers(ref_id, offset, limit)
	if err != nil {
		return nil, 0, err
	}
	return users, total, nil
}
