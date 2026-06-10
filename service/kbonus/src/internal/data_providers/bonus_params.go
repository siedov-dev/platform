package dataproviders

import (
	"kbonus/internal/db/models"
	"kbonus/internal/service/container"
	"sync"
)

type BonusParamsDataProviderImpl struct {
	params map[int8]map[int8][]*models.BonusMenuParam
	mx     sync.RWMutex // Защита от конкурентного доступа
}

func NewBonusParamsDataProviderImpl() container.BonusParamsDataProvider {
	return &BonusParamsDataProviderImpl{
		params: make(map[int8]map[int8][]*models.BonusMenuParam),
	}
}

// Reload загружает все параметры бонусного меню из хранилища данных и обновляет внутреннее состояние.
func (p *BonusParamsDataProviderImpl) Reload() error {
	p.mx.Lock()
	defer p.mx.Unlock()
	params, err := container.GetBonusMenuParamsRepository().GetAllParams()
	if err != nil {
		return err
	}

	p.params = make(map[int8]map[int8][]*models.BonusMenuParam)
	for _, param := range params {
		if _, exists := p.params[param.BonusType]; !exists {
			p.params[param.BonusType] = make(map[int8][]*models.BonusMenuParam)
		}
		p.params[param.BonusType][param.Currency] = append(p.params[param.BonusType][param.Currency], param)
	}

	return nil
}

// FindParamInRange ищет параметр бонуса, для которого заданное значение value попадает в диапазон [MinValue, MaxValue(MinValue след. параметра)].
// Возвращает первый найденный параметр, соответствующий диапазону, или nil, если не найдено подходящего параметра.
func (p *BonusParamsDataProviderImpl) FindParamInRange(bonusType int8, currency int8, value float64) (*models.BonusMenuParam, error) {
	p.mx.RLock()
	defer p.mx.RUnlock()
	var currParam *models.BonusMenuParam
	if params, exists := p.params[bonusType][currency]; exists {
		for _, param := range params {
			if param.MinValue.Valid && value >= param.MinValue.Float64 {
				if !param.MaxValue.Valid || (param.MaxValue.Valid && value <= param.MaxValue.Float64) {
					if currParam == nil || (param.MinValue.Float64 > currParam.MinValue.Float64) {
						currParam = param
					}
				}
			}
		}
	}
	return currParam, nil // Возвращаем nil, если не найдено подходящего параметра
}

// GetParamByKey ищет параметр бонуса по ключу MinValue для определенного типа бонуса и валюты.
// Возвращает первый найденный параметр, соответствующий ключу, или nil, если не найдено подходящего параметра.
func (p *BonusParamsDataProviderImpl) GetParamByKey(bonusType int8, currency int8, key float64) (*models.BonusMenuParam, error) {
	p.mx.RLock()
	defer p.mx.RUnlock()
	if params, exists := p.params[bonusType][currency]; exists {
		for _, param := range params {
			if param.MinValue.Valid && param.MinValue.Float64 == key {
				return param, nil
			}
		}
	}
	return nil, nil // Возвращаем nil, если не найдено подходящего параметра
}

// GetLowestMinValue возвращает наименьшее значение MinValue среди всех параметров для заданного типа бонуса и валюты.
// Если нет подходящих параметров, возвращает 0.
// Используется для определения минимально возможной суммы для бонуса.
func (p *BonusParamsDataProviderImpl) GetLowestMinValue(bonusType int8, currency int8) (float64, error) {
	p.mx.RLock()
	defer p.mx.RUnlock()
	// Проверяем, есть ли параметры для данного типа бонуса и валюты
	if params, exists := p.params[bonusType][currency]; exists {
		var lowest float64
		found := false
		for _, param := range params {
			if param.MinValue.Valid {
				if !found || param.MinValue.Float64 < lowest {
					lowest = param.MinValue.Float64
					found = true
				}
			}
		}
		if found {
			return lowest, nil
		}
	}
	return 0, nil // Возвращаем 0, если нет подходящих параметров
}

func (p *BonusParamsDataProviderImpl) GetParamsByBonusTypeAndCurrency(bonusType int8, currency int8) ([]*models.BonusMenuParam, error) {
	p.mx.RLock()
	defer p.mx.RUnlock()
	if params, exists := p.params[bonusType][currency]; exists {
		return params, nil
	}
	return nil, nil
}
