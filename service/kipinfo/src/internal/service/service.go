// Пакет service содержит бизнес-логику приложения
// и взаимодействует с репозиториями для получения и обновления данных.
// Он также управляет кэшированием данных и блокировкой IP-адресов.
package service

import (
	"context"
	"kipinfo/internal/db/models"
	"kipinfo/internal/service/container"
	"kipinfo/pkg/gstructs"
	"log"
	"sync"
	"time"
)

// cachedIPS хранит кэшированные данные о IP-адресах
// с использованием TimeoutMap для автоматического удаления устаревших записей.
var cachedIPS gstructs.TimeoutMap[string, *models.IpInfoDBModel]

// blockedCountries хранит список заблокированных стран
// с использованием карты для быстрого доступа по коду страны.
// Ключ - код страны, значение - true (страна заблокирована).
// Используется для проверки блокировки IP-адресов по стране.
// Коды стран хранятся в формате ISO 3166-1 alpha-2 (например, "US" для США).
var blockedCountries map[string]bool

// mt используется для синхронизации доступа к кэшу.
var mt sync.Mutex

// MustInit инициализирует кэш IP-адресов и загружает список заблокированных стран.
// Вызывается при запуске приложения для подготовки кэширования и блокировки IP-адресов.
func MustInit(ctx context.Context) {
	// Initialize the IP info cache with a timeout of 60 seconds
	cachedIPS = gstructs.NewTimeoutMap[string, *models.IpInfoDBModel](60, ctx)
	// Initialize the blocked countries map
	err := ReloadBlockedCountries()
	if err != nil {
		panic(err)
	}
}

// ReloadBlockedCountries загружает список заблокированных стран из репозитория
// и обновляет кэшированную карту blockedCountries.
// Вызывается при инициализации приложения и при необходимости обновления списка стран.
func ReloadBlockedCountries() error {
	// Reload the blocked countries from the repository
	countries, err := container.GetCountryBlockedRepository().GetCountryBlocked()
	if err != nil {
		log.Printf("Error reloading blocked countries: %v", err)
		return err
	}
	bc := make(map[string]bool)
	for _, country := range countries {
		bc[country.CountryCode] = true
	}
	blockedCountries = bc
	return nil
}

// GetIPInfo получает информацию о IP-адресе.
// Сначала проверяет кэшированные данные, если они есть.
// Если данные не найдены в кэше, запрашивает информацию из репозитория.
// Если данные не найдены в репозитории, запрашивает информацию у провайдеров IP-данных
// и сохраняет их в репозитории.
func GetIPInfo(ip string) (models.IpInfoDBModel, error) {
	mt.Lock()
	// Провеяем наличии ip кеше
	if cachedIP, ok := cachedIPS.Get(ip); ok {
		mt.Unlock()
		return *cachedIP, nil
	}
	ipInfo := models.IpInfoDBModel{}
	cachedIPS.Set(ip, &ipInfo) // Ставим пустой объект в кэш, чтобы избежать повторных запросов
	// Разблокируем мьютекс, чтобы другие горутины могли работать с кэшем
	mt.Unlock()

	// Загружаем информацию о IP-адресе
	// Грузим данные из базы
	ipInfo, err := container.GetIPInfoRepository().GetIPInfo(ip)
	if err != nil {
		log.Printf("Error getting IP info from repository: %v", err)
		// Если нету в базе берем данные из ip сервисов
		ipInfo = GetIpInfoNoCache(ip)
		// Сохраняем в базе
		err := container.GetIPInfoRepository().InsertIPInfo(ipInfo)
		if err != nil {
			log.Printf("Error inserting IP info into repository: %v", err)
		}
	}

	// Сохраняем в кеш
	cachedIPS.Set(ip, &ipInfo)

	return ipInfo, nil
}

// GetIpInfoNoCache получает информацию о IP-адресе без использования кэша.
// Используется, когда необходимо получить актуальную информацию
func GetIpInfoNoCache(ip string) models.IpInfoDBModel {
	now := time.Now()
	ip_info := models.IpInfoDBModel{}
	ip_info.IP = ip
	ip_info.CreatedAt = &now
	ip_info.UpdatedAt = &now
	// Основные провайдеры данных IP
	// Получаем данный от первого успешного провайдера
	for id, provider := range container.GetIPDataProviders() {
		var err error
		log.Printf("Getting IP info from provider %s", id)
		ip_info, err = provider.GetIPInfo(ip, ip_info)
		if err != nil {
			log.Printf("Error getting IP info from provider %s: %v", id, err)
			continue
		}
		break
	}
	// Уточняем данные провайдерами данных IP для более точной/расширенной информации
	for id, provider := range container.GetIPDataEnrichmentProviders() {
		var err error
		log.Printf("Getting IP info from enrichment provider %s", id)
		ip_info, err = provider.GetIPInfo(ip, ip_info)
		if err != nil {
			log.Printf("Error getting IP info from provider %s: %v", id, err)
		}
	}
	return ip_info
}

// BlockTo блокирует IP-адрес до указанной даты.
// Если IP-адрес уже заблокирован, обновляет дату блокировки.
// Если IP-адрес не найден, возвращает ошибку.
// Возвращает обновленную информацию о IP-адресе.
// Используется для управления блокировкой IP-адресов в системе.
// Параметры:
// - ip: IP-адрес, который нужно заблокировать.
// - block_to: Дата, до которой IP-адрес должен быть заблокирован.
// Возвращает структуру IpInfoDBModel с обновленной информацией о блокировке IP-адреса.
func BlockTo(ip string, block_to time.Time) (models.IpInfoDBModel, error) {
	ipInfo, err := GetIPInfo(ip)
	if err != nil {
		return models.IpInfoDBModel{}, err
	}
	now := time.Now()
	ipInfo.UpdatedAt = &now
	ipInfo.BlockTo = &block_to
	// Update the IP info in the repository
	err = container.GetIPInfoRepository().UpdateIPInfo(ipInfo)
	if err != nil {
		log.Printf("Error updating IP info in repository: %v", err)
		return models.IpInfoDBModel{}, err
	}
	// Update the cached IP info
	cachedIPS.Set(ip, &ipInfo)
	return ipInfo, nil
}

// UnBlock разблокирует IP-адрес.
// Если IP-адрес не найден, возвращает ошибку.
// Возвращает обновленную информацию о IP-адресе.
// Используется для управления разблокировкой IP-адресов в системе.
// Параметры:
// - ip: IP-адрес, который нужно разблокировать.
// Возвращает структуру IpInfoDBModel с обновленной информацией о разблокировке IP-адреса.
func UnBlock(ip string) (models.IpInfoDBModel, error) {
	ipInfo, err := GetIPInfo(ip)
	if err != nil {
		return models.IpInfoDBModel{}, err
	}
	now := time.Now()
	ipInfo.UpdatedAt = &now
	ipInfo.BlockTo = nil
	// Update the IP info in the repository
	err = container.GetIPInfoRepository().UpdateIPInfo(ipInfo)
	if err != nil {
		log.Printf("Error updating IP info in repository: %v", err)
		return models.IpInfoDBModel{}, err
	}
	// Update the cached IP info
	cachedIPS.Set(ip, &ipInfo)
	return ipInfo, nil
}

// IsBlocked проверяет, заблокирован ли IP-адрес.
// Если IP-адрес заблокирован, возвращает true.
func IsBlocked(ip string) (bool, error) {
	ipInfo, err := GetIPInfo(ip)
	if err != nil {
		return false, err
	}
	if ipInfo.BlockTo != nil {
		if time.Now().Before(*ipInfo.BlockTo) {
			return true, nil
		} else {
			ipInfo.BlockTo = nil
			ipInfo.UpdatedAt = &time.Time{}
			err = container.GetIPInfoRepository().UpdateIPInfo(ipInfo)
			if err != nil {
				log.Printf("Error updating IP info in repository: %v", err)
				return false, err
			}
			// Update the cached IP info
			cachedIPS.Set(ip, &ipInfo)
			return false, nil
		}
	}
	return false, nil
}

// IsCountryBlocked проверяет, заблокирована ли страна по коду страны.
// Если страна заблокирована, возвращает true.
// Используется для проверки блокировки IP-адресов по стране.
func IsCountryBlocked(ip string) (bool, error) {
	ipInfo, err := GetIPInfo(ip)
	if err != nil {
		return false, err
	}
	if _, ok := blockedCountries[ipInfo.CountryCode]; ok {
		return true, nil
	}
	return false, nil
}

// IsIPWhitelisted проверяет, находится ли IP-адрес в белом списке.
func IsIPWhitelisted(ip string) (bool, error) {
	ipInfo, err := GetIPInfo(ip)
	if err != nil {
		return false, err
	}
	if ipInfo.Whitelist {
		return true, nil
	}
	return false, nil
}

// SetIPWhitelist устанавливает флаг белого списка для IP-адреса.
func SetIPWhitelist(ip string, whitelist bool) (models.IpInfoDBModel, error) {
	ipInfo, err := GetIPInfo(ip)
	if err != nil {
		return models.IpInfoDBModel{}, err
	}
	now := time.Now()
	ipInfo.UpdatedAt = &now
	ipInfo.Whitelist = whitelist
	// Update the IP info in the repository
	err = container.GetIPInfoRepository().UpdateIPInfo(ipInfo)
	if err != nil {
		log.Printf("Error updating IP info in repository: %v", err)
		return models.IpInfoDBModel{}, err
	}
	// Update the cached IP info
	cachedIPS.Set(ip, &ipInfo)
	return ipInfo, nil
}
