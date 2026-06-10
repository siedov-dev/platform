package container

import (
	"kbonus/internal/db/models"
	"kbonus/internal/stypes"
)

var (
	bonusRepository                UserBonusRepository
	bonusCurrencyConfigRepository  BonusCurrencyConfigRepository
	bonusMenuParamsRepository      BonusMenuParamsRepository
	bonusMultiDaysParamsRepository BonusMultiDaysParamsRepository
	bonusStatisticRepository       BonusStatisticRepository
	bonusTransactionRepository     BonusTransactionRepository
	bonusPatternsRepository        BonusPatternsRepository
	bonusAvailableRepository       BonusAvailableRepository
)

// UserBonusRepository определяет интерфейс для операций с бонусами пользователей в хранилище данных.
type UserBonusRepository interface {

	// GetByID извлекает информацию о бонусе пользователя из хранилища по заданному идентификатору.
	GetByID(id stypes.BonusID) (*models.UserBonus, error)

	// GetByUserId извлекает все бонусы пользователя по его идентификатору.
	// Возвращает срез структур UserBonus и ошибку, если операция завершилась неудачно.
	GetByUserId(id stypes.UserID) ([]models.UserBonus, error)

	// Update обновляет информацию о заданном бонусе пользователя в хранилище данных.
	Update(bonus *models.UserBonus) error

	// Insert добавляет новую запись о бонусе пользователя в хранилище данных.
	Insert(bonus *models.UserBonus) (int64, error)

	// GetActiveByUserId извлекает все активные бонусы пользователя по его идентификатору.
	GetActiveByUserId(id stypes.UserID) ([]models.UserBonus, error)

	// GetInactiveByUserId извлекает все завершенные бонусы пользователя по его идентификатору.
	GetCompletedByUserId(id stypes.UserID, limit int) ([]models.UserBonus, error)
}

type BonusCurrencyConfigRepository interface {
	// GetByCurrency извлекает конфигурацию бонусов для заданной валюты.
	GetConfigs() ([]*models.BonusCurrencyConfig, error)
}

type BonusMenuParamsRepository interface {
	// GetAllParams извлекает все параметры бонусного меню из хранилища данных.
	// Возвращает срез структур BonusMenuParam и ошибку, если операция завершилась неудачно.
	GetAllParams() ([]*models.BonusMenuParam, error)
}

type BonusMultiDaysParamsRepository interface {
	// GetAllParams извлекает все параметры многодневных бонусов из хранилища данных.
	// Возвращает срез структур BonusMultiDaysParam и ошибку, если операция завершилась неудачно.
	GetAllParams() ([]*models.BonusMultiDaysParam, error)
}

// BonusStatisticRepository предоставляет методы для работы с данными статистики бонусов.
// Позволяет добавлять новую запись в хранилище.
type BonusStatisticRepository interface {
	// Insert добавляет записи о статистике бонуса в хранилище.
	Insert(bss ...*models.BonusStatistic) error
}

// BonusTransactionRepository интерфейс для работы с транзакциями бонусов пользователей.
// Метод Insert добавляет новую запись о транзакции и возвращает её идентификатор или ошибку.
// Используется для записи транзакции о выигрыше в бонусе.
type BonusTransactionRepository interface {
	Insert(tr *models.BonusTransaction) (int64, error)
}

// BonusPatternsRepository интерфейс для работы с паттернами бонусов.
type BonusPatternsRepository interface {
	// GetAllPatterns извлекает все паттерны бонусов из хранилища данных.
	GetAllPatterns() ([]*models.BonusPattern, error)
}

type BonusAvailableRepository interface {
	// GetAllAvailable извлекает все активные доступные бонусы из хранилища данных.
	GetAllAvailable() ([]*models.BonusAvailable, error)
	GetByID(id uint64) (*models.BonusAvailable, error)
	Update(ba *models.BonusAvailable) error
	Delete(id uint64) error
	Insert(ba *models.BonusAvailable) (uint64, error)
}

// GetUserBonusRepository возвращает репозиторий для работы с бонусами пользователей.
// Если репозиторий не инициализирован, вызывает панику.
func GetUserBonusRepository() UserBonusRepository {
	if bonusRepository == nil {
		panic("UserBonusRepository is not initialized")
	}
	return bonusRepository
}

// SetBonusRepository устанавливает репозиторий для работы с бонусами пользователей.
// Используется для инициализации репозитория в контейнере зависимостей.
func SetBonusRepository(repo UserBonusRepository) {
	bonusRepository = repo
}

// GetBonusCurrencyConfigRepository возвращает репозиторий для работы с конфигурациями бонусов по валютам.
// Если репозиторий не инициализирован, вызывает панику.
func GetBonusCurrencyConfigRepository() BonusCurrencyConfigRepository {
	if bonusCurrencyConfigRepository == nil {
		panic("BonusCurrencyConfigRepository is not initialized")
	}
	return bonusCurrencyConfigRepository
}

// SetBonusCurrencyConfigRepository устанавливает репозиторий для работы с конфигурациями бонусов по валютам.
// Используется для инициализации репозитория в контейнере зависимостей.
func SetBonusCurrencyConfigRepository(repo BonusCurrencyConfigRepository) {
	bonusCurrencyConfigRepository = repo
}

// GetBonusMenuParamsRepository возвращает репозиторий для работы с параметрами бонусного меню.
// Если репозиторий не инициализирован, вызывает панику.
func GetBonusMenuParamsRepository() BonusMenuParamsRepository {
	if bonusMenuParamsRepository == nil {
		panic("BonusMenuParamsRepository is not initialized")
	}
	return bonusMenuParamsRepository
}

// SetBonusMenuParamsRepository устанавливает репозиторий для работы с параметрами бонусного меню.
// Используется для инициализации репозитория в контейнере зависимостей.
func SetBonusMenuParamsRepository(repo BonusMenuParamsRepository) {
	bonusMenuParamsRepository = repo
}

// GetBonusMultiDaysParamsRepository возвращает репозиторий для работы с параметрами многодневных бонусов.
// Если репозиторий не инициализирован, вызывает панику.
func GetBonusMultiDaysParamsRepository() BonusMultiDaysParamsRepository {
	if bonusMultiDaysParamsRepository == nil {
		panic("BonusMultiDaysParamsRepository is not initialized")
	}
	return bonusMultiDaysParamsRepository
}

// SetBonusMultiDaysParamsRepository устанавливает репозиторий для работы с параметрами многодневных бонусов.
// Используется для инициализации репозитория в контейнере зависимостей.
func SetBonusMultiDaysParamsRepository(repo BonusMultiDaysParamsRepository) {
	bonusMultiDaysParamsRepository = repo
}

func GetBonusStatisticRepository() BonusStatisticRepository {
	if bonusStatisticRepository == nil {
		panic("BonusStatisticRepository is not initialized")
	}
	return bonusStatisticRepository
}
func SetBonusStatisticRepository(repo BonusStatisticRepository) {
	bonusStatisticRepository = repo
}

func GetBonusTransactionRepository() BonusTransactionRepository {
	if bonusTransactionRepository == nil {
		panic("BonusTransactionRepository is not initialized")
	}
	return bonusTransactionRepository
}
func SetBonusTransactionRepository(repo BonusTransactionRepository) {
	bonusTransactionRepository = repo
}

func GetBonusPatternsRepository() BonusPatternsRepository {
	if bonusPatternsRepository == nil {
		panic("BonusPatternsRepository is not initialized")
	}
	return bonusPatternsRepository
}
func SetBonusPatternsRepository(repo BonusPatternsRepository) {
	bonusPatternsRepository = repo
}

func GetBonusAvailableRepository() BonusAvailableRepository {
	if bonusAvailableRepository == nil {
		panic("BonusAvailableRepository is not initialized")
	}
	return bonusAvailableRepository
}
func SetBonusAvailableRepository(repo BonusAvailableRepository) {
	bonusAvailableRepository = repo
}
