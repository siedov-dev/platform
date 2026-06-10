package usecases

import (
	"kreferrals/internal/service/container"
)

func GetReferralUsersCount(ref_id uint64) (uint64, error) {
	return container.GetReferralMUsersRepository().GetReferralUsersCount(ref_id)
}
