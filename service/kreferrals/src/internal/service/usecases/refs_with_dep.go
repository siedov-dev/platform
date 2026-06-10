package usecases

import "kreferrals/internal/service/container"

func GetReferralUsersCountwithDeps(ref_id uint64) (uint64, error) {
	return container.GetReferralMUsersRepository().GetReferralUsersCountwithDeps(ref_id)
}
