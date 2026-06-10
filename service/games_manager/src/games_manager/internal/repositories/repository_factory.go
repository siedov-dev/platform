// Пакет для создания репозиториев
// и предоставления интерфейсов для работы с ними
// Пакет содержит интерфейсы для работы с репозиториями
// и функции для получения конкретных реализаций репозиториев
package repositories

import "games_manager/internal/db/models"

// DisabledGamesRepository - интерфейс для работы с отключенными играми
// Он содержит метод GetDisabledGames, который возвращает список отключенных игр
type DisabledGamesRepository interface {
	GetDisabledGames() ([]models.DisabledGame, error)
}

// GameConfigRepository - интерфейс для работы с конфигурациями игр
// Он содержит метод GetGameConfigs, который возвращает список игр
// Он используется для получения игр из базы данных
type GameConfigRepository interface {
	GetGameConfigs() ([]models.GameConfig, error)
}

// LandingGamesRepository - интерфейс для работы с играми на лендинге
// Он содержит метод GetLandingGames, который возвращает список игр на лендинге
type LandingGamesRepository interface {
	GetLandingGames() ([]models.LandingGames, error)
}

// UserBonusRepository - интерфейс для работы с бонусами пользователей
// Он содержит метод ById, который возвращает бонус пользователя по его ID
// Он используется для получения информации о бонусах пользователей из базы данных
// и выборе игр для бонусов
type UserBonusRepository interface {
	ById(id int) (*models.UserBonus, error)
}

// BonusDisabledCIDsRepository - интерфейс для работы с играми по умолчанию по типу бонуса
// Переделан для работы в режиме игр по умолчанию
type BonusDisabledCIDsRepository interface {
	GetDisabledGamesCIDs() ([]models.BonusDisabledGames, error)
}

// GropPriorityRepository - интерфейс для работы с приоритетами групп игр
// Он содержит метод GetPriorities, который возвращает список приоритетов групп игр
// Он используется для получения приоритетов групп игр из базы данных
// для последующей сортировки
type GropPriorityRepository interface {
	GetPriorities() ([]models.GameGroupPriority, error)
}

// ProviderNamesRepository - интерфейс для работы с новыми именами провайдеров
// Он содержит метод GetAll, который возвращает список всех новых имен провайдеров
type ProviderNamesRepository interface {
	GetAll() ([]*models.ProviderNames, error)
}

// GameSegmentRepository - интерфейс для работы с сегментами игр
// Он содержит метод GetGameSegments, который возвращает карту сегментов игр
// ключ - ID сегмента, значение - список CID игр, принадлежащих этому сегменту
type GameSegmentRepository interface {
	GetGameSegments() (map[uint64][]string, error)
}

// Реализация интерфейсов репозиториев
func GetGameConfigsRepository() GameConfigRepository {
	return GameConfigRepo{}
}

func GetDisabledGamesRepository() DisabledGamesRepository {
	return DisabledGamesRepo{}
}

func GetLanfingGamesRepository() LandingGamesRepository {
	return LandingGamesRepo{}
}

func GetUserBonusRepository() UserBonusRepository {
	return UserBonusRepo{}
}

func GetBonusDisabledCIDsRepository() BonusDisabledCIDsRepository {
	return DisabledBonusGamesRepo{}
}

func GetGropPriorityRepository() GropPriorityRepository {
	return GroupPriorytyRepo{}
}
func GetProviderNamesRepository() ProviderNamesRepository {
	return ProviderNamesRepo{}
}
func GetGameSegmentRepository() GameSegmentRepository {
	return GameSegmentRepo{}
}
