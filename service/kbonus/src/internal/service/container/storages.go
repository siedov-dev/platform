package container

import "kbonus/internal/db/models"

var (
	bonusStatisticStorage         BonusStatisticStorage
	bonusAvailableDeferredStorage BonusAvailableDeferredStorage
	bonusSegmentsStorage          BonusSegmentsStorage
)

type BonusStatisticStorage interface {
	Add(tr *models.BonusStatistic)
	Close()
}
type BonusAvailableDeferredStorage interface {
	Add(avb *models.BonusAvailable)
}

type BonusSegmentsStorage interface {
	AddBonusToSegmentIds(bonusID int64, segmentIDs []int64) error
	AddBonusToSegmentName(bonusID int64, segmentName string) error
	GetBonusAvailableSegmentIds(bonusId int64) ([]int64, error)
}

func SetBonusStatisticStorage(storage BonusStatisticStorage) {
	bonusStatisticStorage = storage
}
func GetBonusStatisticStorage() BonusStatisticStorage {
	if bonusStatisticStorage == nil {
		panic("BonusStatisticStorage is not initialized")
	}
	return bonusStatisticStorage
}

// SetBonusAvailableDeferredStorage инициализирует хранилище отложенных бонусов.
func SetBonusAvailableDeferredStorage(storage BonusAvailableDeferredStorage) {
	bonusAvailableDeferredStorage = storage
}

// GetBonusAvailableDeferredStorage возвращает хранилище отложенных бонусов.
func GetBonusAvailableDeferredStorage() BonusAvailableDeferredStorage {
	if bonusAvailableDeferredStorage == nil {
		panic("BonusAvailableDeferredStorage is not initialized")
	}
	return bonusAvailableDeferredStorage
}

func GetBonusSegmentsStorage() BonusSegmentsStorage {
	if bonusSegmentsStorage == nil {
		panic("BonusSegmentsStorage is not initialized")
	}
	return bonusSegmentsStorage
}
func SetBonusSegmentsStorage(storage BonusSegmentsStorage) {
	bonusSegmentsStorage = storage
}
