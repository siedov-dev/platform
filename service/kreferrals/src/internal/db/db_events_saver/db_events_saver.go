package dbeventssaver

import (
	"kreferrals/internal/db/models"
	"kreferrals/internal/service/container"
)

var _ container.EventsSaver = (*DBEventsSaver)(nil)

type DBEventsSaver struct {
}

func NewDBEventsSaver() *DBEventsSaver {
	return &DBEventsSaver{}
}

// SaveReferralEvent сохраняет событие реферала в базе данных.
func (s *DBEventsSaver) SaveReferralEvent(event *models.ReferralEvent) error {
	repo := container.GetReferralEventRepository()
	if repo == nil {
		panic("ReferralEventRepository is not set")
	}
	return repo.CreateReferralEvent(event)
}
