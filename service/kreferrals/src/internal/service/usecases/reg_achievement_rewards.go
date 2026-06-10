package usecases

import "kreferrals/internal/service"

func GetAchievementRewards(currency string) map[uint64]float64 {
	rewards := service.GetAchievementRewards()
	resp := make(map[uint64]float64)

	for id, reward := range rewards {
		if reward[currency] > 0 {
			resp[id] = reward[currency]
		}
	}

	return resp
}
