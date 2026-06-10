package container

import "kbonus/internal/db/models"

var (
	bonusParamsDataProvider BonusParamsDataProvider
	// bonusCurrencyConfigDataProvider хранит провайдер данных для конфигураций бонусов по валютам.
	// Используется для загрузки и получения конфигураций бонусов по валютам.
	bonusCurrencyConfigDataProvider CurrencyConfigDataProvider
	// bonusMultiDaysParamsDataProvider хранит провайдер данных для многодневных бонусов.
	// Используется для получения параметров многодневных бонусов.
	bonusMultiDaysParamsDataProvider MultiDaysBonusPramsDataProvider
	// bonusPatternsDataProvider хранит провайдер данных для паттернов бонусов.
	bonusPatternsDataProvider BonusPatternsDataProvider
)

// BonusParamsDataProvider определяет интерфейс для работы с параметрами бонусов.
// Используется для получения параметров бонусов в зависимости от типа, валюты и других критериев.
type BonusParamsDataProvider interface {
	// FindParamInRange ищет параметр бонуса для MinValue <= value <= MaxVale.
	FindParamInRange(bonusType int8, currency int8, value float64) (*models.BonusMenuParam, error)
	// GetParamByKey извлекает параметр бонуса по ключу minValue.
	GetParamByKey(bonusType int8, currency int8, key float64) (*models.BonusMenuParam, error)
	// GetLowestMinValue извлекает минимальное значение для заданного типа бонуса и валюты.
	GetLowestMinValue(bonusType int8, currency int8) (float64, error)
	// GetParamsByBonusTypeAndCurrency извлекает параметры бонуса по заданному типу и валюте.
	GetParamsByBonusTypeAndCurrency(bonusType int8, currency int8) ([]*models.BonusMenuParam, error)
	// Reload перезагружает данные провайдера.
	// Используется для обновления данных в случае изменений в конфигурации бонусов.
	Reload() error
}

// CurrencyConfigDataProvider определяет интерфейс для работы с конфигурациями бонусов по валютам.
// Используется для получения конфигураций бонусов в зависимости от валюты.
type CurrencyConfigDataProvider interface {
	// LoadBonusCurrencyConfigs загружает конфигурации бонусов по валютам.
	LoadBonusCurrencyConfigs() error
	// GetBonusCurrencyConfig извлекает конфигурацию бонуса по заданной валюте.
	GetBonusCurrencyConfig(currency int8) (*models.BonusCurrencyConfig, error)
}

type MultiDaysBonusPramsDataProvider interface {
	GetParamsForDay(day int) *models.BonusMultiDaysParam
	Reload() error
}

type BonusPatternsDataProvider interface {
	GetBonusPattern(id int64) (*models.BonusPattern, error)
	GetRegPattern(currency string, pType string, num int) (*models.BonusPattern, error)
	GetPatternByName(name string) (*models.BonusPattern, error)
	GetRegPatternIds(currency string) ([]int64, error)
	GetRegFsPatternIds(currency string) ([]int64, error)
	GetRegNonDepPatternIds(currency string) ([]int64, error)
	Reload() error
}

// GetMultiDaysBonusPramsDataProvider возвращает провайдер данных для многодневных бонусов.
// Если провайдер не инициализирован, вызывает панику.
func GetMultiDaysBonusPramsDataProvider() MultiDaysBonusPramsDataProvider {
	if bonusMultiDaysParamsDataProvider == nil {
		panic("MultiDaysBonusPramsDataProvider is not initialized")
	}
	return bonusMultiDaysParamsDataProvider
}

// SetMultiDaysBonusPramsDataProvider устанавливает провайдер данных для многодневных бонусов.
// Используется для инициализации провайдера в контейнере зависимостей.
func SetMultiDaysBonusPramsDataProvider(provider MultiDaysBonusPramsDataProvider) {
	bonusMultiDaysParamsDataProvider = provider
}

// SetBonusParamsDataProvider устанавливает провайдер данных для параметров бонусов.
// Используется для инициализации провайдера в контейнере зависимостей.
func SetBonusParamsDataProvider(provider BonusParamsDataProvider) {
	bonusParamsDataProvider = provider
}

// GetBonusParamsDataProvider возвращает провайдер данных для параметров бонусов.
// Если провайдер не инициализирован, вызывает панику.
func GetBonusParamsDataProvider() BonusParamsDataProvider {
	if bonusParamsDataProvider == nil {
		panic("BonusParamsDataProvider is not initialized")
	}
	return bonusParamsDataProvider
}

// GetBonusCurrencyConfigDataProvider возвращает провайдер данных для конфигураций бонусов по валютам.
// Если провайдер не инициализирован, вызывает панику.
func GetBonusCurrencyConfigDataProvider() CurrencyConfigDataProvider {
	if bonusCurrencyConfigDataProvider == nil {
		panic("BonusCurrencyConfigDataProvider is not initialized")
	}
	return bonusCurrencyConfigDataProvider
}

// SetBonusCurrencyConfigDataProvider устанавливает провайдер данных для конфигураций бонусов по валютам.
// Используется для инициализации провайдера в контейнере зависимостей.
func SetBonusCurrencyConfigDataProvider(provider CurrencyConfigDataProvider) {
	bonusCurrencyConfigDataProvider = provider
}

func GetBonusPatternsDataProvider() BonusPatternsDataProvider {
	if bonusPatternsDataProvider == nil {
		panic("BonusPatternsDataProvider is not initialized")
	}
	return bonusPatternsDataProvider
}
func SetBonusPatternsDataProvider(provider BonusPatternsDataProvider) {
	bonusPatternsDataProvider = provider
}
