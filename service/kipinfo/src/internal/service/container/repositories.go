package container

import "kipinfo/internal/db/models"

var ipInfoRepository IpInfoRepository
var countryBlockedRepository CountryBlockedRepository

// IpInfoRepository интерфейс, который определяет методы для взаимодействия с базой данных информации о IP-адресах.
type IpInfoRepository interface {
	GetIPInfo(ip string) (models.IpInfoDBModel, error)
	UpdateIPInfo(ipInfo models.IpInfoDBModel) error
	InsertIPInfo(ipInfo models.IpInfoDBModel) error
}

// SetIPInfoRepository устанавливает репозиторий информации о IP-адресах.
func SetIPInfoRepository(repo IpInfoRepository) {
	ipInfoRepository = repo
}

// GetIPInfoRepository возвращает репозиторий информации о IP-адресах.
func GetIPInfoRepository() IpInfoRepository {
	return ipInfoRepository
}

// CountryBlockedRepository интерфейс, который определяет методы для взаимодействия с базой данных заблокированных стран.
type CountryBlockedRepository interface {
	GetCountryBlocked() ([]models.CountryBlockedDBModel, error)
}

// SetCountryBlockedRepository устанавливает репозиторий заблокированных стран.
func SetCountryBlockedRepository(repo CountryBlockedRepository) {
	countryBlockedRepository = repo
}

// GetCountryBlockedRepository возвращает репозиторий заблокированных стран.
func GetCountryBlockedRepository() CountryBlockedRepository {
	return countryBlockedRepository
}
