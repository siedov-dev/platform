package usecases

import "kbonus/internal/service/container"

func BonusSegmentAddToIds(bonusID int64, segmentIDs []int64) error {
	return container.GetBonusSegmentsStorage().AddBonusToSegmentIds(bonusID, segmentIDs)
}

func BonusSegmentAddToName(bonusID int64, segmentName string) error {
	return container.GetBonusSegmentsStorage().AddBonusToSegmentName(bonusID, segmentName)
}
