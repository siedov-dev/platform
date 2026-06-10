package service

import (
	"context"
	"crypto/hmac"
	"crypto/sha256"
	"encoding/hex"
	"errors"
	"log/slog"
	"net/http"
	"strconv"
	"sync"
	"time"
	"user-manager/internal/config"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/service/container"
	"user-manager/internal/stypes"
	"user-manager/pkg/gstructs"
)

const (
	GameModeReal       = 0 // Режим игры на реальные средства
	GameModeBonus      = 1 // Режим игры на бонус
	GameModeTournament = 2 // Не используется
)

var key string = ""

func init() {
	// Инициализация сервиса
	// Установка ключа подписи запросов из конфигурации
	key = config.Get().Kraken.Key
}

var (
	Users                   gstructs.TimeoutMap[stypes.UserID, *User] // Кэш пользователей с таймаутом
	mut                     sync.RWMutex                              // Мьютекс для синхронизации доступа
	Transactions            *TransactionStorage                       // Хранилище транзакций
	Currencies              gstructs.DLinkedList[models.UserCurrency] // Список поддерживаемых валют
	DefferWalletPersistence gstructs.TimeoutMap[int64, *Wallet]       // Отложенное сохранение кошельков
	// DefferBonusPersistence  gstructs.TimeoutMap[int64, stypes.IBonus]  // Отложенное сохранение бонусов
	DefferLastActiveUpdate gstructs.TimeoutMap[int, time.Time]        // Отложенное обновление времени последней активности
	PPlayAuthTokens        gstructs.TimeoutMap[string, stypes.UserID] // Токены авторизации PPlay
)

// MustInit инициализирует сервис
// При ошибке инициализации происходит паника
// Параметры:
//   - ctx: глобальный контекст
func MustInit(ctx context.Context) {
	Transactions = NewTransactionStorage(ctx)
	// Сохраняем пользователей в течение 10 минут после каждого обращения
	Users = gstructs.NewTimeoutMap[stypes.UserID, *User](600, ctx)
	Users.SetClearFunc(func(userId stypes.UserID, user *User) {
		logger.Get().Info("User unload", "user_id", userId)
		user.CloseGameSession()
		user.Save()
		user.Unload()
	})
	// Обновление последней активности
	Users.SetUpdateValueFunc(func(userId stypes.UserID, user *User) {
		DefferLastActiveUpdate.Set(int(userId), time.Now())
	})

	// Сохранение кошелька через 2 секунды после изменения
	DefferWalletPersistence = gstructs.NewTimeoutMap[int64, *Wallet](2, ctx)
	DefferWalletPersistence.SetClearFunc(func(id int64, w *Wallet) {
		logger.Get().Info("saving wallet", "id", id)
		err := w.Save()
		if err != nil {
			logger.Get().Error("saving wallet error!", slog.String("err", err.Error()))
		}
	})
	// Отложить операцию сохранение не чаще 20 раз
	DefferWalletPersistence.SetPostponeLimit(20)
	// // Сохранение бонуса через 2 секунды после изменения
	// DefferBonusPersistence = gstructs.NewTimeoutMap[int64, stypes.IBonus](2, ctx)
	// DefferBonusPersistence.SetClearFunc(func(id int64, b stypes.IBonus) {
	// 	logger.Get().Info("saving bonus", "id", id)
	// 	err := b.Save()
	// 	if err != nil {
	// 		logger.Get().Error("saving bonus error!", slog.String("err", err.Error()))
	// 	}
	// })
	// // Не чаще 10 раз
	// DefferBonusPersistence.SetPostponeLimit(10)

	// Отложенное сохранение последней активности на 2 сек
	DefferLastActiveUpdate = gstructs.NewTimeoutMap[int, time.Time](2, ctx)
	DefferLastActiveUpdate.SetClearFunc(func(userId int, dt time.Time) {
		logger.Get().Info("saving last active", "user_id", userId)
		err := container.GetUserRepository().UpdateLastActive(userId, dt)
		if err != nil {
			logger.Get().Error("saving last active error!", slog.String("err", err.Error()))
		}
	})
	// Не чаще 10 раз
	DefferLastActiveUpdate.SetPostponeLimit(10)

	// Загрузка валют
	db_currencies, err := container.GetWalletsRepository().LoadUserCurrencies()
	if err != nil {
		logger.Get().Error("error loading currencies", slog.String("err", err.Error()))
		panic(err.Error())
	}
	Currencies.Push(db_currencies...)

	// Инициализация временного хранилища токенов авторизации PragmaticPlay
	// Токен храниться 30 секунд
	PPlayAuthTokens = gstructs.NewTimeoutMap[string, stypes.UserID](30, ctx)
}

// Shutdown сохраняет всех пользователей и транзакции.
// Вызывается при завершении работы сервиса
func Shutdown() {
	Transactions.Persist()
	DefferWalletPersistence.Clear()
	DefferLastActiveUpdate.Clear()
	// DefferBonusPersistence.Clear()
	Users.Clear()
}

func IsUserOnline(userId stypes.UserID) bool {
	mut.RLock()
	defer mut.RUnlock()
	_, ok := Users.Get(userId)
	return ok
}

// GetUser возвращает пользователя из кэша или загружает его из БД
// Параметры:
//   - userId: ID пользователя
//
// Возвращает:
//   - *User: указатель на объект пользователя
//   - error: ошибка при загрузке пользователя
func GetUser(userId stypes.UserID) (*User, error) {
	mut.RLock()
	if user, ok := Users.Get(userId); ok {
		mut.RUnlock()
		return user, nil
	}
	mut.RUnlock()

	//if no user found - write lock and load
	mut.Lock()
	defer mut.Unlock()
	//check again for user
	if user, ok := Users.Get(userId); ok {
		return user, nil
	}
	user := NewUser(userId)

	// add user to cache
	Users.Set(userId, user)

	return user, nil
}

// GetUserTemp используется для получения пользователя без кэширования.
// Используется для редких операций, таких как установка TotalBet или реферальных денег,
// чтобы пользователь не оставался в памяти надолго.
//
//	Параметры:
//	 - userId: ID пользователя
//
//	 Возвращает:
//	  - *User: указатель на объект пользователя
//	  - error: ошибка при загрузке пользователя
func GetUserTemp(userId stypes.UserID) (*User, error) {
	mut.RLock()
	if user, ok := Users.Get(userId); ok {
		mut.RUnlock()
		return user, nil
	}
	mut.RUnlock()
	// Если пользователя нет в кеше - загружаем
	user := NewUser(userId)
	err := user.Load(userId)
	if err != nil {
		return nil, err
	}

	return user, nil
}

// SignRequest подписывает запрос с помощью HMAC-SHA256
//
//	Параметры:
//	 - method: метод запроса
//	 - data: данные для подписи
//	 - qtime: время запроса
//
// Возвращает:
//   - string: подпись запроса в hex формате
func SignRequest(method string, data string, qtime string) string {
	// calc HMAC-SHA256
	h := hmac.New(sha256.New, []byte(key))
	h.Write([]byte(method))
	h.Write([]byte(data))
	h.Write([]byte(qtime))
	return hex.EncodeToString(h.Sum(nil))
}

// CheckRequestSign проверяет подпись запроса.
//
//	Параметры:
//	 - r: HTTP запрос
//	 - method: метод запроса
//	 - body: тело запроса
//
//	Возвращает:
//	 - error: ошибка проверки подписи
func CheckRequestSign(r *http.Request, method string, body string) error {
	logger.Get().Info(method, slog.String("request", string(body)))
	sign := r.Header.Get("X-sign")
	qtime := r.Header.Get("X-Time")
	rtime, err := strconv.ParseInt(qtime, 10, 64)
	if err != nil {
		return err
	}
	c_time := time.Now().Unix()
	if rtime < (c_time-5) || rtime > (c_time+1) {
		return errors.New("wrong request time")
	}
	logger.Get().Info(method, slog.String("time", qtime))
	// logger.Get().Info(method, slog.String("sign", sign))

	xsign := SignRequest(method, body, qtime)
	// logger.Get().Info(method, slog.String("xsign", xsign))

	if sign != xsign {
		logger.Get().Error(method, slog.String("err", "Wrong sign"))
		return errors.New("wrong sign")
	}
	return nil
}
