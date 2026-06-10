package container

import (
	"kipinfo/internal/db/models"
)

// ipDataProviders - список провайдеров данных IP.
// Он используется для получения информации о IP-адресах из различных источников.
// Каждый провайдер должен реализовать интерфейс IPDataProvider.
var ipDataProviders map[string]IPDataProvider = map[string]IPDataProvider{}

// IPDataEnrichmentProviders - список провайдеров данных IP для расширения информации.
// Он используется для получения дополнительной информации о IP-адресах из различных источников.
// Каждый провайдер должен реализовать интерфейс IPDataProvider.
var IPDataEnrichmentProviders map[string]IPDataProvider = map[string]IPDataProvider{}

// SetIPDataProviders устанавливает список провайдеров данных IP.
func SetIPDataProviders(providers ...IPDataProvider) {
	for _, provider := range providers {
		ipDataProviders[provider.GetId()] = provider
	}
}

// SetIPDataEnrichmentProviders устанавливает список провайдеров данных IP для расширения информации.
func SetIPDataEnrichmentProviders(providers ...IPDataProvider) {
	for _, provider := range providers {
		IPDataEnrichmentProviders[provider.GetId()] = provider
	}
}

// GetIPDataProviders возвращает список провайдеров данных IP.
func GetIPDataProviders() map[string]IPDataProvider {
	return ipDataProviders
}

// GetIPDataEnrichmentProviders возвращает список провайдеров данных IP для расширения информации.
func GetIPDataEnrichmentProviders() map[string]IPDataProvider {
	return IPDataEnrichmentProviders
}

// IPDataProvider - интерфейс для получения информации о IP-адресах.
// Он определяет метод GetIPInfo, который должен быть реализован каждым провайдером.
type IPDataProvider interface {
	// GetIPInfo получает информацию о IP-адресе.
	// Возвращает структуру IpInfoDBModel с информацией о IP-адресе.
	GetIPInfo(ip string, model models.IpInfoDBModel) (models.IpInfoDBModel, error)
	// GetId возвращает идентификатор провайдера.
	// Возвращает строку с идентификатором провайдера.
	GetId() string
}
