package storage

import (
	"fmt"
	"kbonus/internal/db"
	"kbonus/internal/service/container"
	"sync"
)

func NewBonusSegmentsStorage() container.BonusSegmentsStorage {
	return &BonusSegmentsStorage{}
}

type BonusSegmentsStorage struct {
	mx sync.Mutex
}

func (s *BonusSegmentsStorage) AddBonusToSegmentIds(bonusID int64, segmentIDs []int64) error {
	s.mx.Lock()
	defer s.mx.Unlock()

	return s.addBonusToSegmentIds(bonusID, segmentIDs)
}

func (s *BonusSegmentsStorage) addBonusToSegmentIds(bonusID int64, segmentIDs []int64) error {
	db := db.Instance()
	sql := `INSERT INTO bonus_segment_user_bonus (user_bonus_id, bonus_segment_id) VALUES `

	for i, segmentID := range segmentIDs {
		if i > 0 {
			sql += `, `
		}
		sql += fmt.Sprintf(`(%d, %d)`, bonusID, segmentID)
	}

	_, err := db.Exec(sql)
	return err
}

func (s *BonusSegmentsStorage) AddBonusToSegmentName(bonusID int64, segmentName string) error {
	s.mx.Lock()
	defer s.mx.Unlock()

	db := db.Instance()

	segmentID := s.segmentExists(segmentName)
	if segmentID == 0 {
		sql := `INSERT INTO bonus_segments (name) VALUES (?)`

		res, err := db.Exec(sql, segmentName)
		if err != nil {
			return err
		}

		segmentID, err = res.LastInsertId()
		if err != nil {
			return err
		}
	}

	return s.addBonusToSegmentIds(bonusID, []int64{segmentID})
}

func (s *BonusSegmentsStorage) GetBonusAvailableSegmentIds(bonusId int64) ([]int64, error) {
	db := db.Instance()
	sql := `SELECT bonus_segment_id FROM bonus_segment_bonus_available WHERE bonus_available_id = ?`
	rows, err := db.Query(sql, bonusId)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	var segmentIds []int64
	for rows.Next() {
		var segmentId int64
		if err := rows.Scan(&segmentId); err != nil {
			return nil, err
		}
		segmentIds = append(segmentIds, segmentId)
	}
	return segmentIds, nil
}

func (s *BonusSegmentsStorage) segmentExists(segmentName string) int64 {
	db := db.Instance()
	sql := `SELECT id FROM bonus_segments WHERE name = ?`
	var id int64
	err := db.QueryRow(sql, segmentName).Scan(&id)
	if err != nil {
		return 0
	}
	return id
}
