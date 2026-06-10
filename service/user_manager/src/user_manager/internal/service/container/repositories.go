package container

import (
	"time"
	"user-manager/internal/db/models"
)

var (
	userBonusRepo  UserBonusRepository
	bonusStatRepo  BonusStatisticRepository
	bonusTransRepo BonustransactionRepository
	gameSessRepo   GameSessionRepository
	gameTransRepo  GameTransactionRepository
	userRepo       UserRepository
	walletsRepo    WalletsRepository
)

// UserBonusRepository определяет интерфейс для операций с бонусами пользователей в хранилище данных.
type UserBonusRepository interface {

	// GetByID извлекает информацию о бонусе пользователя из хранилища по заданному идентификатору.
	//
	// Параметры:
	//   - id: идентификатор бонуса.
	// Возвращает:
	//   - Указатель на структуру UserBonus, содержащую данные бонуса.
	//   - Ошибку, если загрузка данных не удалась.
	GetByID(id int64) (*models.UserBonus, error)

	// Update обновляет информацию о заданном бонусе пользователя в хранилище данных.
	//
	// Параметры:
	//   - bonus: указатель на структуру UserBonus, содержащую обновленные данные бонуса.
	// Возвращает:
	//   - Ошибку, если обновление не удалось.
	Update(bonus *models.UserBonus) error

	// Insert добавляет новую запись о бонусе пользователя в хранилище данных.
	//
	// Параметры:
	//   - bonus: указатель на структуру UserBonus, содержащую данные нового бонуса.
	// Возвращает:
	//   - Идентификатор новой записи.
	//   - Ошибку, если операция добавления не удалась.
	Insert(bonus *models.UserBonus) (int64, error)
}

// BonusStatisticRepository предоставляет методы для работы с данными статистики бонусов.
// Позволяет добавлять новую запись в хранилище.
type BonusStatisticRepository interface {
	// Insert добавляет запись о статистике бонуса в хранилище.
	// Возвращает идентификатор новой записи или ошибку в случае сбоя.
	Insert(bs *models.BonusStatistic) (int64, error)
}

// BonustransactionRepository интерфейс для работы с транзакциями бонусов пользователей.
// Метод Insert добавляет новую запись о транзакции и возвращает её идентификатор или ошибку.
// Используется для записи транзакции о выигрыше в бонусе.
type BonustransactionRepository interface {
	Insert(tr *models.BonusTransaction) (int64, error)
}

// GameSessionRepository определяет интерфейс для работы с сущностью UserGameStats.
// Предоставляет методы для получения, обновления и добавления записей в базе данных.
type GameSessionRepository interface {

	// GetByID возвращает объект UserGameStats по указанному идентификатору id или ошибку, если запись не найдена.
	GetByID(id int64) (*models.UserGameStats, error)
	// Update обновляет существующую запись UserGameStats в базе данных и возвращает ошибку при сбое.
	Update(bonus *models.UserGameStats) error
	// Insert добавляет новую запись UserGameStats в базу данных и возвращает идентификатор записи или ошибку.
	Insert(bonus *models.UserGameStats) (int64, error)
}

// GameTransactionRepository предоставляет методы для работы с игровыми транзакциями в хранилище данных.
// GetByID возвращает транзакцию по её ID.
// GetByTrID возвращает транзакцию по её идентификатору транзакции.
// Insert выполняет вставку одной или нескольких транзакций в хранилище.
type GameTransactionRepository interface {
	// GetByID возвращает игровую транзакцию из хранилища по её уникальному идентификатору.
	GetByID(id int64) (transaction *models.GameTransaction, err error)
	// GetByTrID возвращает игровую транзакцию из хранилища по заданному идентификатору транзакции.
	GetByTrID(id int64) (transaction *models.GameTransaction, err error)
	// Insert вставляет одну или несколько транзакций в хранилище данных. Возвращает ошибку в случае неуспешной операции.
	Insert(tr ...*models.GameTransaction) error
}

// UserRepository предоставляет интерфейс для работы с данными пользователей в хранилище.
// Содержит методы для получения, обновления и работы с последней активностью пользователя.
type UserRepository interface {
	// GetByID получает пользователя из хранилища по указанному идентификатору id.
	// Возвращает указатель на найденного пользователя или ошибку, если пользователь не найден.
	GetByID(id int64) (user *models.M_User, err error)
	// Update обновляет данные указанного пользователя в базе данных.
	// Принимает указатель на структуру модели пользователя для обновления.
	// Возвращает ошибку, если операция обновления не выполнена успешно.
	Update(user *models.M_User) (err error)
	// UpdateLastActive обновляет время последней активности пользователя в хранилище.
	// Принимает идентификатор пользователя user_id и время активности dt.
	// Возвращает ошибку, если операция обновления времени последней активности завершилась неудачно.
	UpdateLastActive(user_id int, dt time.Time) (err error)
}

// WalletsRepository предоставляет интерфейс для работы с кошельками пользователей в системе.
// Обеспечивает доступ к методам для получения, обновления, создания данных о кошельках и их валютах.
type WalletsRepository interface {

	// GetByID возвращает кошелек пользователя по его уникальному идентификатору.
	// Параметры:
	// - id: уникальный идентификатор кошелька.
	// Возвращает указатель на структуру UserWallet и ошибку, если запрос выполнить не удалось.
	GetByID(id int64) (wallet *models.UserWallet, err error)
	// GetByUserID возвращает список кошельков пользователя по его уникальному идентификатору.
	// Параметры:
	// - user_id: уникальный идентификатор пользователя.
	// Возвращает список структур UserWallet и ошибку, если запрос не выполнен.
	GetByUserID(user_id int64) (wallet []models.UserWallet, err error)
	// GetByUserIDCurrID возвращает кошелек пользователя по его ID и ID валюты.
	// Параметры:
	// - user_id: уникальный идентификатор пользователя.
	// - curr: идентификатор валюты.
	// Возвращает указатель на структуру UserWallet и ошибку, если запрос не выполнен.
	GetByUserIDCurrID(user_id int64, curr int8) (wallet *models.UserWallet, err error)
	// Update обновляет данные указанного кошелька пользователя в базе данных.
	Update(wallet *models.UserWallet) (err error)
	// Create создает новый кошелек для пользователя с указанной валютой. Возвращает ошибку в случае неудачи.
	Create(user_id int64, currency int8) (err error)
	// LoadUserCurrencies загружает список валют пользователей из базы данных и возвращает его.
	LoadUserCurrencies() ([]models.UserCurrency, error)
}

func GetUserBonusRepository() UserBonusRepository {
	mx.RLock()
	defer mx.RUnlock()
	if userBonusRepo == nil {
		panic("no bonus repository set")
	}
	return userBonusRepo
}

func SetUserBonusRepository(repo UserBonusRepository) {
	mx.Lock()
	defer mx.Unlock()
	userBonusRepo = repo
}

func GetBonusStatisticRepository() BonusStatisticRepository {
	mx.RLock()
	defer mx.RUnlock()
	if bonusStatRepo == nil {
		panic("no bonus statistic repository set")
	}
	return bonusStatRepo
}

func SetBonusStatisticRepository(repo BonusStatisticRepository) {
	mx.Lock()
	defer mx.Unlock()
	bonusStatRepo = repo
}

func GetBonusTransactionRepository() BonustransactionRepository {
	mx.RLock()
	defer mx.RUnlock()
	if bonusTransRepo == nil {
		panic("no bonus transaction repository set")
	}
	return bonusTransRepo
}

func SetBonusTransactionRepository(repo BonustransactionRepository) {
	mx.Lock()
	defer mx.Unlock()
	bonusTransRepo = repo
}

func GetGameSessionRepository() GameSessionRepository {
	mx.RLock()
	defer mx.RUnlock()
	if gameSessRepo == nil {
		panic("no game session repository set")
	}
	return gameSessRepo
}

func SetGameSessionRepository(repo GameSessionRepository) {
	mx.Lock()
	defer mx.Unlock()
	gameSessRepo = repo
}

func GetTransactionRepository() GameTransactionRepository {
	mx.RLock()
	defer mx.RUnlock()
	if gameTransRepo == nil {
		panic("no game transaction repository set")
	}
	return gameTransRepo
}

func SetGameTransactionRepository(repo GameTransactionRepository) {
	mx.Lock()
	defer mx.Unlock()
	gameTransRepo = repo
}

func GetUserRepository() UserRepository {
	mx.RLock()
	defer mx.RUnlock()
	if userRepo == nil {
		panic("no user repository set")
	}
	return userRepo
}

func SetUserRepository(repo UserRepository) {
	mx.Lock()
	defer mx.Unlock()
	userRepo = repo
}

func GetWalletsRepository() WalletsRepository {
	mx.RLock()
	defer mx.RUnlock()
	if walletsRepo == nil {
		panic("no wallets repository set")
	}
	return walletsRepo
}

func SetWalletsRepository(repo WalletsRepository) {
	mx.Lock()
	defer mx.Unlock()
	walletsRepo = repo
}
