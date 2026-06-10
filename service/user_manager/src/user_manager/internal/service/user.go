package service

import (
	"errors"
	"sync"
	"time"
	"user-manager/internal/db/models"
	"user-manager/internal/service/container"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"
	"user-manager/pkg/gstructs"
)

// User Структура пользователя, содержащая все данные и состояние игрока
type User struct {
	dbUser                  models.M_User                                 // Данные пользователя из БД
	wallets                 map[currency.CurrencyID]*Wallet               // Кошельки пользователя для разных валют
	lastBet                 models.GameTransaction                        // Последняя ставка пользователя
	lastTransactions        gstructs.DLinkedList[*models.GameTransaction] // Список последних транзакций
	lastRefundsTransactions gstructs.DLinkedList[*models.GameTransaction] // Список последних возвратов
	currentGameSession      *GameSession                                  // Текущая игровая сессия
	events                  gstructs.DLinkedList[UserEvent]               // Очередь событий
	id                      stypes.UserID                                 // ID пользователя
	isLoaded                bool                                          // Флаг, указывающий, загружены ли данные пользователя
	mut                     sync.RWMutex                                  // Общий мьютекс структуры
}

func NewUser(id stypes.UserID) *User {
	return &User{
		id:                      id,
		wallets:                 make(map[currency.CurrencyID]*Wallet, 1),
		lastTransactions:        gstructs.DLinkedList[*models.GameTransaction]{},
		lastRefundsTransactions: gstructs.DLinkedList[*models.GameTransaction]{},
		events:                  gstructs.DLinkedList[UserEvent]{},
		isLoaded:                false,
	}
}

// Load Загружает данные пользователя из БД по его ID
func (u *User) Load(id stypes.UserID) error {
	if u == nil {
		u = NewUser(id)
	}

	u.lastTransactions.SetSizeLimit(20)
	u.lastRefundsTransactions.SetSizeLimit(5)

	db_user, err := container.GetUserRepository().GetByID(int64(id))
	if err != nil {
		return err
	}
	u.dbUser = *db_user

	wallets, err := container.GetWalletsRepository().GetByUserID(int64(id))
	if err != nil {
		return err
	}
	for _, w := range wallets {
		wl := new(Wallet)
		wl.LoadFromModel(&w)
		u.wallets[currency.CurrencyID(w.CurrencyID)] = wl
	}

	//events queue limit
	u.events.SetSizeLimit(20)
	u.isLoaded = true

	return nil
}

// GetWallet Получает кошелек пользователя для указанной валюты. Если кошелек не существует - создает новый
func (u *User) GetWallet(curr currency.CurrencyID) (*Wallet, error) {
	if u == nil {
		return nil, errors.New("user is nil")
	}
	u.mut.Lock() // Захватываем write lock сразу
	defer u.mut.Unlock()
	err := u.checkLoaded()
	if err != nil {
		return nil, err
	}
	if wallet, ok := u.wallets[curr]; ok {
		return wallet, nil
	}

	if wallet, ok := u.wallets[curr]; ok {
		return wallet, nil
	}

	//try load wallet
	wallet, err := container.GetWalletsRepository().GetByUserIDCurrID(int64(u.dbUser.ID), int8(curr))
	if err == nil {
		u.wallets[curr] = &Wallet{}
		u.wallets[curr].LoadFromModel(wallet)
		return u.wallets[curr], nil
	}
	//create wallet
	err = container.GetWalletsRepository().Create(int64(u.dbUser.ID), int8(curr))
	if err != nil {
		return nil, err
	}
	wallet, err = container.GetWalletsRepository().GetByUserIDCurrID(int64(u.dbUser.ID), int8(curr))
	if err != nil {
		return nil, err
	}
	u.wallets[curr] = &Wallet{}
	u.wallets[curr].LoadFromModel(wallet)
	return u.wallets[curr], nil
}

// GetWallets Возвращает все кошельки пользователя
func (u *User) GetWallets() map[currency.CurrencyID]*Wallet {
	u.mut.RLock()
	defer u.mut.RUnlock()
	err := u.checkLoaded()
	if err != nil {
		return nil
	}
	return u.wallets
}

// SetLastBetTransaction Устанавливает последнюю ставку пользователя
func (u *User) SetLastBetTransaction(tr *models.GameTransaction) {
	u.mut.Lock()
	defer u.mut.Unlock()
	err := u.checkLoaded()
	if err != nil {
		return
	}

	u.lastBet = *tr
}

// GetLastBetTransaction Получает последнюю ставку пользователя
func (u *User) GetLastBetTransaction() *models.GameTransaction {
	u.mut.RLock()
	defer u.mut.RUnlock()
	err := u.checkLoaded()
	if err != nil {
		return nil
	}

	return &u.lastBet
}

// GetGameSession Получает текущую игровую сессию пользователя
func (u *User) GetGameSession() *GameSession {
	u.mut.RLock()
	defer u.mut.RUnlock()
	err := u.checkLoaded()
	if err != nil {
		return nil
	}
	return u.currentGameSession
}

// SetGameSession Устанавливает текущую игровую сессию пользователя
func (u *User) SetGameSession(s *GameSession) {
	u.mut.Lock()
	defer u.mut.Unlock()
	err := u.checkLoaded()
	if err != nil {
		return
	}
	u.currentGameSession = s
}

// CloseGameSession Закрывает текущую игровую сессию и сохраняет статистику
func (u *User) CloseGameSession() (*GameSession, error) {
	gs := u.GetGameSession()
	u.SetGameSession(nil)
	if gs == nil {
		return nil, errors.New("no active game session")
	}
	w, err := u.GetWallet(gs.GameStartData.CurrencyID)
	if err != nil {
		return nil, err
	}
	tm := time.Now()

	gs.Stats.LogoutDate = &tm
	gs.Stats.LogoutBalance = w.GetBalance()
	gs.Stats.BalanceDiff = gs.Stats.LogoutBalance - gs.Stats.LoginBalance
	gs.Save()

	return gs, nil
}

// Save Сохраняет данные пользователя в БД
func (u *User) Save() {
	u.mut.Lock()
	defer u.mut.Unlock()

	for _, w := range u.wallets {
		w.Save()
	}
}

// AddEvent Добавляет событие в очередь событий пользователя
func (u *User) AddEvent(e UserEvent) {
	err := u.checkLoaded()
	if err != nil {
		return
	}
	u.events.Push(e)
}

// GetEvents Получает список всех событий пользователя
func (u *User) GetEvents() []UserEvent {
	u.checkLoaded()
	return u.events.ToSlice()
}

// PullEvents Получает и удаляет все события из очереди
func (u *User) PullEvents() []UserEvent {
	u.checkLoaded()
	return u.events.PopSlice()
}

func (u *User) GetID() stypes.UserID {
	if u == nil {
		return 0
	}
	u.mut.RLock()
	defer u.mut.RUnlock()
	return stypes.UserID(u.dbUser.ID)
}

func (u *User) GetDbUser() *models.M_User {
	if u == nil {
		return nil
	}
	u.mut.RLock()
	defer u.mut.RUnlock()
	err := u.checkLoaded()
	if err != nil {
		return nil
	}
	return &u.dbUser
}

// Unload Выгружает все данные пользователя из памяти
func (u *User) Unload() {
	u.events.Clear()
	u.lastTransactions.Clear()
	u.lastRefundsTransactions.Clear()

	u.wallets = nil
	u.currentGameSession = nil
}

func (u *User) checkLoaded() error {
	if !u.isLoaded {
		return u.Load(u.id)
	}
	return nil
}

func (u *User) GetCurrentGameSession() *GameSession {
	if u == nil {
		return nil
	}
	u.mut.RLock()
	defer u.mut.RUnlock()
	err := u.checkLoaded()
	if err != nil {
		return nil
	}
	return u.currentGameSession
}

func (u *User) GetLastTransactions() *gstructs.DLinkedList[*models.GameTransaction] {
	u.mut.RLock()
	defer u.mut.RUnlock()
	u.checkLoaded()
	return &u.lastTransactions
}

func (u *User) GetLastRefundsTransactions() *gstructs.DLinkedList[*models.GameTransaction] {
	u.mut.RLock()
	defer u.mut.RUnlock()
	u.checkLoaded()
	return &u.lastRefundsTransactions
}

func (u *User) GetLogin() string {
	u.mut.RLock()
	defer u.mut.RUnlock()
	err := u.checkLoaded()
	if err != nil {
		return ""
	}
	return u.dbUser.Login.String
}
