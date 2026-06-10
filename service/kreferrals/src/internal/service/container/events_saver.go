package container

import "kreferrals/internal/db/models"

type EventsSaver interface {
	// SaveReferralEvent сохраняет событие реферала в базе данных.
	SaveReferralEvent(event *models.ReferralEvent) error
}
