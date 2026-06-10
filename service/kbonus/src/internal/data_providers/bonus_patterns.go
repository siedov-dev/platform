package dataproviders

import (
	"errors"
	"fmt"
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
	"sync"
)

type bonusPatternsDataproviderImpl struct {
	patterns map[int64]*models.BonusPattern
	mx       sync.RWMutex
}

func NewBonusPatternsDataProviderImpl() container.BonusPatternsDataProvider {
	return &bonusPatternsDataproviderImpl{
		patterns: make(map[int64]*models.BonusPattern),
	}
}

func (b *bonusPatternsDataproviderImpl) GetBonusPattern(id int64) (*models.BonusPattern, error) {
	b.mx.RLock()
	defer b.mx.RUnlock()

	pattern, exists := b.patterns[id]
	if !exists {
		return nil, errors.New("bonus pattern not found")
	}
	return pattern, nil
}

func (b *bonusPatternsDataproviderImpl) GetRegPattern(currency string, pType string, num int) (*models.BonusPattern, error) {
	b.mx.RLock()
	defer b.mx.RUnlock()

	patternName := fmt.Sprintf("bonus.%s_%d_%s", pType, num, currency)
	return b.getPatternByName(patternName)
}

func (b *bonusPatternsDataproviderImpl) GetPatternByName(name string) (*models.BonusPattern, error) {
	b.mx.RLock()
	defer b.mx.RUnlock()
	return b.getPatternByName(name)
}

func (b *bonusPatternsDataproviderImpl) getPatternByName(name string) (*models.BonusPattern, error) {
	for _, pattern := range b.patterns {
		if pattern.Name == name {
			return pattern, nil
		}
	}
	return nil, errors.New("bonus pattern not found")
}

func (b *bonusPatternsDataproviderImpl) GetRegPatternIds(currency string) ([]int64, error) {
	b.mx.RLock()
	defer b.mx.RUnlock()

	var ids []int64
	for i := 1; i <= 3; i++ {
		patternName := fmt.Sprintf("bonus.dep_bonus_%d_%s", i, currency)
		pattern, err := b.getPatternByName(patternName)
		if err == nil {
			ids = append(ids, pattern.ID)
		}
	}
	if len(ids) == 0 {
		return nil, errors.New("no registration bonus patterns found")
	}
	return ids, nil
}

func (b *bonusPatternsDataproviderImpl) GetRegFsPatternIds(currency string) ([]int64, error) {
	b.mx.RLock()
	defer b.mx.RUnlock()

	var ids []int64
	for i := 1; i <= 3; i++ {
		patternName := fmt.Sprintf("bonus.fd_fs_%d_%s", i, currency)
		pattern, err := b.getPatternByName(patternName)
		if err == nil {
			ids = append(ids, pattern.ID)
		}
	}
	if len(ids) == 0 {
		return nil, errors.New("no registration bonus patterns found")
	}
	return ids, nil
}

func (b *bonusPatternsDataproviderImpl) GetRegNonDepPatternIds(currency string) ([]int64, error) {
	b.mx.RLock()
	defer b.mx.RUnlock()

	var ids []int64
	for i := 4; i <= 5; i++ {
		patternName := fmt.Sprintf("bonus.fd_fs_%d_%s", i, currency)
		pattern, err := b.getPatternByName(patternName)
		if err == nil {
			ids = append(ids, pattern.ID)
		}
	}
	if len(ids) == 0 {
		return nil, errors.New("no registration bonus patterns found")
	}
	return ids, nil
}

func (b *bonusPatternsDataproviderImpl) Reload() error {
	b.mx.Lock()
	defer b.mx.Unlock()

	b.patterns = make(map[int64]*models.BonusPattern)
	allPatterns, err := container.GetBonusPatternsRepository().GetAllPatterns()
	if err != nil {
		return err
	}
	for _, pattern := range allPatterns {
		b.patterns[pattern.ID] = pattern
	}
	return nil
}
