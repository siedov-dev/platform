package models

// CountryBlockedDBModel определяет модель для хранения информации о заблокированных странах
type CountryBlockedDBModel struct {
	CountryCode string `json:"country_code" db:"country_code"` // CountryCode двухбуквенный код страны
}
