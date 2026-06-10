package usecases

import (
	"kreferrals/internal/db/models"
	"kreferrals/internal/service/container"
)

func GetReferralEvents(ref_id uint64, offset uint64, limit uint64) (events []*models.ReferralEvent, total_rows uint64, err error) {
	total, err := container.GetReferralEventRepository().GetReferralEventsCount(ref_id)
	if err != nil {
		return nil, 0, err
	}
	events, err = container.GetReferralEventRepository().GetReferralEvents(ref_id, offset, limit)
	if err != nil {
		return nil, 0, err
	}
	return events, total, nil
}
