package models

import "time"

// IpInfoDBModel определяет модель для хранения информации об IP-адресе,
// включая географические данные и информацию об интернет-провайдере.
type IpInfoDBModel struct {
	IP          string     `json:"ip" db:"ip"`                     // IP-адрес
	Continent   string     `json:"continent" db:"continent"`       // Континент
	Country     string     `json:"country" db:"country"`           // Страна, соответствующая IP-адресу
	CountryCode string     `json:"country_code" db:"country_code"` // Код страны (два символа)
	Region      string     `json:"region" db:"region"`             // Регион
	City        string     `json:"city" db:"city"`                 // Город
	Lat         string     `json:"lat" db:"lat"`                   // Широта
	Long        string     `json:"long" db:"long"`                 // Долгота
	Currency    string     `json:"currency" db:"currency"`         // Валюта
	ISP         string     `json:"isp" db:"isp"`                   // Интернет-провайдер
	Org         string     `json:"org" db:"org"`                   // Организация
	CreatedAt   *time.Time `json:"created_at" db:"created_at"`     // Дата создания записи
	UpdatedAt   *time.Time `json:"updated_at" db:"updated_at"`     // Дата обновления записи
	BlockTo     *time.Time `json:"block_to" db:"block_to"`         // Дата окончания блокировки
	Whitelist   bool       `json:"whitelist" db:"whitelist"`       // Флаг, указывающий, является ли IP-адрес в белом списке
}
