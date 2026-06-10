package stypes

// UserID представляет собой уникальный идентификатор пользователя в системе.
type UserID = int64

// IWallet представляет интерфейс для работы с кошельком, обеспечивая основные операции с балансом и валютой.
type IWallet interface {
	// GetCurrencyName возвращает строку, представляющую название валюты, используемой в текущем кошельке.
	GetCurrencyName() string
	// GetBalance возвращает текущий баланс кошелька как число с плавающей точкой в формате float64.
	GetBalance() float64
	// GetCents возвращает текущий баланс кошелька в минимальных единицах (например, центы) как целое число типа int64.
	GetCents() int64
	// AddMoney добавляет указанную сумму к балансу кошелька и возвращает новый баланс в формате float64.
	AddMoney(amount float64) float64
	// AddCents добавляет указанную сумму в минимальных единицах (например, центы) к балансу и возвращает новый баланс.
	AddCents(amount int64) int64
	// RemMoney уменьшает баланс кошелька на указанную сумму и возвращает новый баланс. Возвращает ошибку при недостатке средств.
	RemMoney(amount float64) (float64, error)
	// RemCents удаляет указанное количество центов с баланса. Возвращает новый баланс в центах или ошибку, если операция невозможна.
	RemCents(amount int64) (int64, error)
	// Save сохраняет текущее состояние кошелька в хранилище. Возвращает ошибку, если операция сохранения не удалась.
	Save() error
}

// LockedMoneyWallet предоставляет интерфейс для работы с "замороженной" для отыгрыша частью средств в кошельке.
// Используется для операций добавления, удаления, получения и установки этой суммы.
type LockedMoneyWallet interface {
	GetLockedMoney() float64
	AddLockedMoney(amount float64) float64
	RemLockedMoney(amount float64) float64
	SetLockedMoney(amount float64)
}

// BetWinTotals представляет интерфейс для работы с тоталами ставок и выигрышей.
// Позволяет получать и изменять тоталы ставок и выигрышей.
type BetWinTotals interface {
	GetTotalBet() float64
	AddTotalBet(amount float64)
	GetTotalWin() float64
	AddTotalWin(amount float64)
}

// GameWallet представляет интерфейс, объединяющий функционал работы с кошельком и управления ставками и выигрышами.
type GameWallet interface {
	IWallet
	BetWinTotals
}

// RealMoneyWallet объединяет функционал игровых кошельков и управление замороженными средствами.
// Является расширением интерфейсов GameWallet и LockedMoneyWallet.
type RealMoneyWallet interface {
	GameWallet
	LockedMoneyWallet
}
