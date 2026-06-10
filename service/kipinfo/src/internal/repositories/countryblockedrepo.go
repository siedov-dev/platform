package repositories

import (
	"kipinfo/internal/db"
	"kipinfo/internal/db/models"
	"kipinfo/internal/service/container"
)

var _ container.CountryBlockedRepository = &CountryBlockedRepo{}

// CountryBlockedRepo - структура репозитория для работы с заблокированными странами.
// Она реализует интерфейс CountryBlockedRepository из контейнера.
// Этот репозиторий отвечает за получение информации о заблокированных странах из базы данных.
type CountryBlockedRepo struct {
}

func NewCountryBlockedRepository() *CountryBlockedRepo {
	return &CountryBlockedRepo{}
}

func (c *CountryBlockedRepo) GetCountryBlocked() ([]models.CountryBlockedDBModel, error) {
	countryBlocked := []models.CountryBlockedDBModel{}
	db := db.Instance()
	err := db.Select(&countryBlocked, "SELECT country_code FROM ip_country_blocks")
	if err != nil {
		return nil, err
	}

	return countryBlocked, nil
}
