package dataproviders

import (
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
	"sync"
)

type MultiDaysBonusPramsDataProviderImpl struct {
	params map[int]*models.BonusMultiDaysParam
	mx     sync.RWMutex
}

func NewMultiDaysBonusPramsDataProviderImpl() container.MultiDaysBonusPramsDataProvider {
	return &MultiDaysBonusPramsDataProviderImpl{}
}

func (p *MultiDaysBonusPramsDataProviderImpl) GetParamsForDay(day int) *models.BonusMultiDaysParam {
	p.mx.RLock()
	defer p.mx.RUnlock()

	if param, exists := p.params[day]; exists {
		return param
	}
	return nil // Параметр не найден для данного дня
}

func (p *MultiDaysBonusPramsDataProviderImpl) Reload() error {
	p.mx.Lock()
	defer p.mx.Unlock()
	p.params = make(map[int]*models.BonusMultiDaysParam)

	params, err := container.GetBonusMultiDaysParamsRepository().GetAllParams()
	if err != nil {
		return err
	}
	for _, param := range params {
		p.params[param.Day] = param
	}
	return nil
}
