package storage

import (
	"context"
	"kbonus/internal/db/models"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/utils/gstructs"
)

type BonusAvailableDeferredStorage struct {
	storage gstructs.TimeoutMap[int64, *models.BonusAvailable]
}

func NewBonusAvailableDeferredStorage(ctx context.Context) *BonusAvailableDeferredStorage {
	st := &BonusAvailableDeferredStorage{
		storage: gstructs.NewTimeoutMap[int64, *models.BonusAvailable](5, ctx),
	}
	st.storage.SetClearFunc(func(id int64, avb *models.BonusAvailable) {

		err := container.GetBonusAvailableRepository().Update(avb)
		if err != nil {
			logger.Get().Error("Failed to save available bonus on timeout", "bonusID", id, "error", err)
		}
	})
	return st
}

func (s *BonusAvailableDeferredStorage) Add(avb *models.BonusAvailable) {
	s.storage.Set(avb.ID, avb)
}
