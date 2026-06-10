package dataproviders

import (
	"errors"
	"kbonus/internal/db/models"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"sync"
)

type CurrencyConfigDataProviderImpl struct {
	bonusCurrencyConfigs map[int8]*models.BonusCurrencyConfig
	mx                   sync.RWMutex // Защита от конкурентного доступа
}

func NewCurrencyConfigDataProviderImpl() container.CurrencyConfigDataProvider {
	return &CurrencyConfigDataProviderImpl{
		bonusCurrencyConfigs: make(map[int8]*models.BonusCurrencyConfig),
	}
}

func (r *CurrencyConfigDataProviderImpl) LoadBonusCurrencyConfigs() error {
	r.mx.Lock()
	defer r.mx.Unlock()
	configs, err := container.GetBonusCurrencyConfigRepository().GetConfigs()
	if err != nil {
		logger.Get().Error("Failed to load bonus currency configs", "error", err)
		return err
	}
	if configs == nil {
		logger.Get().Info("No bonus currency configs found")
		return errors.New("no bonus currency configs found")
	}
	r.bonusCurrencyConfigs = make(map[int8]*models.BonusCurrencyConfig)
	for _, config := range configs {
		if config == nil {
			logger.Get().Warn("Found nil bonus currency config, skipping")
			continue
		}
		r.bonusCurrencyConfigs[config.Currency] = config
	}
	logger.Get().Info("Bonus currency configs loaded")
	return nil
}

func (r *CurrencyConfigDataProviderImpl) GetBonusCurrencyConfig(currency int8) (*models.BonusCurrencyConfig, error) {
	r.mx.RLock()
	defer r.mx.RUnlock()
	if config, exists := r.bonusCurrencyConfigs[currency]; exists {
		return config, nil
	}
	logger.Get().Error("Bonus currency config not found", "currency", currency)
	return nil, errors.New("bonus currency config not found")
}
