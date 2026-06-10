package repositories

import (
	"kipinfo/internal/db"
	"kipinfo/internal/db/models"
	"kipinfo/internal/service/container"
	"time"
)

var _ container.IpInfoRepository = &DBIpInfoRepository{}

func NewDBIpInfoRepository() *DBIpInfoRepository {
	return &DBIpInfoRepository{}
}

// DBIpInfoRepository - реализация репозитория для работы с информацией о IP-адресах в базе данных.
// Он реализует интерфейс IpInfoRepository из контейнера.
// Этот репозиторий отвечает за получение, обновление и вставку информации о IP-адресах в базу данных.
type DBIpInfoRepository struct {
}

func (r *DBIpInfoRepository) GetIPInfo(ip string) (models.IpInfoDBModel, error) {
	db := db.Instance()
	var ipInfo models.IpInfoDBModel
	err := db.Get(&ipInfo, "SELECT * FROM ip_info WHERE ip = ?", ip)
	if err != nil {
		return models.IpInfoDBModel{}, err
	}
	return ipInfo, nil
}

func (r *DBIpInfoRepository) UpdateIPInfo(ipInfo models.IpInfoDBModel) error {
	db := db.Instance()
	now := time.Now()
	ipInfo.UpdatedAt = &now
	_, err := db.NamedExec("UPDATE ip_info SET continent = :continent, country = :country, country_code = :country_code, region = :region, city = :city, `lat` = :lat, `long` = :long, currency = :currency, isp = :isp, org = :org, updated_at = :updated_at, block_to = :block_to, whitelist = :whitelist WHERE ip = :ip", ipInfo)
	if err != nil {
		return err
	}
	return nil
}

func (r *DBIpInfoRepository) InsertIPInfo(ipInfo models.IpInfoDBModel) error {
	db := db.Instance()
	_, err := db.NamedExec("INSERT INTO ip_info (ip, continent, country, country_code, region, city, `lat`, `long`, currency, isp, org, created_at, updated_at) VALUES (:ip, :continent, :country, :country_code, :region, :city, :lat, :long, :currency, :isp, :org, :created_at, :updated_at)", ipInfo)
	if err != nil {
		return err
	}
	return nil
}
