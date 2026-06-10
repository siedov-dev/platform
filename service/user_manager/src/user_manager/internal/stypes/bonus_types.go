package stypes

import "user-manager/internal/db/models"

// Типы бонусов
const (
	TYPE_NON_DEPOSIT               = 2
	TYPE_FREE_SPINS                = 3
	TYPE_PLUS_MINUS                = 5
	TYPE_INSURANCE                 = 6 // Процент возврата за проигрыш в игре. deprecated
	TYPE_SUPER_FREE_SPINS          = 10
	TYPE_SUPER_FREE_SPINS_PLAYBACK = 11
	TYPE_SUPER_FS_CASHBACK         = 12
	TYPE_SUPER_FS_CASHBACK_PB      = 13
	TYPE_FILTHY_FS                 = 14
	TYPE_FILTHY_FS_WASH_OFF        = 15
	TYPE_WEEKLY_CASH_BACK          = 16
	TYPE_BEZDEP                    = 17
	TYPE_FREE_SPINS_WAGER          = 21
	TYPE_EVENT_CASH_BACK           = 22
	TYPE_DEPOSIT_WAGER             = 23
	TYPE_DEPOSIT_PRIZE             = 24
	TYPE_DEPOSIT_FFS_TNT           = 25
	TYPE_NON_DEPOSIT_WAGER         = 26
	TYPE_CHOOSE_WAGER_FS2          = 27
	TYPE_SMART_WAGER               = 28
	TYPE_TOURNAMENT_WIN            = 29
	TYPE_PPLAY_FS                  = 30 //49
	TYPE_PPLAY_SMART_FS            = 31
	TYPE_QT_FS_WAGER               = 32
	TYPE_QT_SMART_FS               = 33
	TYPE_SLEG_FS                   = 34 //49
	TYPE_SLEG_SMART                = 35
	TYPE_PP_NONDEP                 = 36
	TYPE_SLEG_NONDEP               = 37
	TYPE_PP_FS_MENU                = 38
	TYPE_SLEG_FS_MENU              = 39
	TYPE_CASHBACK_MONEY            = 40
	TYPE_SWISS_FS_WAGER            = 41
	TYPE_PPLAY_SFS_DYNAMIC         = 42
	TYPE_SLEG_SFS_DYNAMIC          = 43
	TYPE_MULTI_DAYS                = 44
	TYPE_PLUS_MINUS_DEP            = 45
	TYPE_SHARED_MONEY              = 46
	TYPE_SHARED_MONEY_FS           = 47
	TYPE_WELCOME_BONUS_PACK        = 48
	TYPE_PS_FS                     = 49
	TYPE_PS_FS_MENU                = 50
	TYPE_PS_SFS_DYNAMIC            = 51
	TYPE_PS_SMART                  = 52
	TYPE_PS_NON_DEP                = 53
	TYPE_NON_DEP                   = 54
)

// Статусы бонусов
const (
	STATUS_NOT_ACTIVATED         = 0
	STATUS_ACTIVE                = 1
	STATUS_DONE                  = 2
	STATUS_CANCELED              = 3
	STATUS_PENDING               = 4
	STATUS_EXPIRED               = 5
	STATUS_WIN                   = 6
	STATUS_WAITING_FOR_PAYMENT   = 7
	STATUS_WAITING_FOR_AGREEMENT = 8
	STATUS_FILTHY                = 9
	STATUS_WAITING_FOR_LOSE      = 10
	STATUS_LOCKED                = 11
	STATUS_BUY_REQUIRED          = 12
	STATUS_ERROR                 = 13
	STATUS_DEPOSIT_LISTEN        = 14
)

// Action бонуса для записи истории бонусов
const (
	ACTION_CREATED  = 0
	ACTION_ACTIVATE = 1
	ACTION_CANCEL   = 2
	ACTION_DONE     = 3
	ACTION_FAIL     = 4
	ACTION_WIN      = 5
	ACTION_DEPOSIT  = 6
	ACTION_EXPIRED  = 7
	ACTION_FS_DONE  = 8
)

// BonusHandler представляет интерфейс, обеспечивающий обработку ставок/спинов в бонусе.
// Используется для работы с бонусной логикой, обеспечивая доступ к операциям с балансом и подсчетам ставок/выигрышей.
type BonusHandler interface {
	// GetCurrencyName возвращает строку, представляющую название валюты, используемой в текущем бонусе.
	GetCurrencyName() string
	// GetBalance возвращает текущий баланс бонуса как число с плавающей точкой в формате float64.
	GetBalance() float64
	// AddMoney добавляет указанную сумму к балансу бонуса и возвращает новый баланс, сумму добавленную на реальный счет
	// и сумму добавленную на бонусный счет.
	AddMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64)
	// RemMoney уменьшает баланс бонуса на указанную сумму и возвращает новый баланс. Возвращает ошибку при недостатке средств.
	RemMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64, err error)
	// Save сохраняет текущее состояние бонуса в хранилище. Возвращает ошибку, если операция сохранения не удалась.
	Save() error
}

// BonusActualizer представляет интерфейс для актуализации бонуса, включая расчет выигрыша и получение статуса бонуса.
// Actualize определяет состояние бонуса, вычисляет и возвращает значение выигрыша после актуализации бонуса.
// GetStatus возвращает текущий статус бонуса в виде структуры BonusStatusPad.
// GetProgressHint возвращает строку, описывающую прогресс выполнения бонуса.
type BonusActualizer interface {
	Actualize() (win float64)
	GetStatus() BonusStatusPad
	GetProgressHint() string
}

// BonusStatusPad представляет структуру для отображения информации о статусе бонуса.
// Title содержит заголовок статусной панели бонуса.
// Value хранит значение, связанное с текущим статусом бонуса.
// Progress отображает информацию о прогрессе выполнения бонуса.
// ShowPad определяет, должна ли отображаться панель со статусом бонуса.
// BonusStatus содержит числовое представление текущего состояния бонуса.
// Используется для отображения "плашки" бонуса во время игры
type BonusStatusPad struct {
	Title       string `json:"title"`
	Value       string `json:"value"`
	Progress    string `json:"progress"`
	ShowPad     bool   `json:"show_pad"`
	BonusStatus int    `json:"bonus_status"`
}

// BonusExternalFsHandler представляет интерфейс для обработки внешних фриспинов (на стороне игровых провайдеров).
// Определяет методы для ставок, выигрышей и завершения фриспинов.
type BonusExternalFsHandler interface {
	ExternFreeSpinBet(amount float64) (float64, error)
	ExternFreeSpinWin(amount float64) (float64, error)
	ExternFreeSpinsCompleted(amount float64) (float64, error)
}

// BonusBetWagerer представляет интерфейс для обработки ставок и выигрышей пользователя для бонусов, работающих на реальные средства.
// Содержит методы для учета изменений при ставке (OnUserBet) и при выигрыше (OnUserWin).
// На данный момент такие бонусы не используются
type BonusBetWagerer interface {
	OnUserBet(amount float64)
	OnUserWin(amount float64)
}

type IBonus interface {
	BonusHandler
	BonusBetWagerer
	Actualize() (win float64)
	GetExtFsHandler(game_system string) (BonusExternalFsHandler, error)
	GetID() int64
	IsActive() bool
	SetBetTransactionID(tr_id string)
	GetBetTransactionID() string
	SetBackupState(bonus *models.UserBonus)
	GetBackupState() *models.UserBonus
	RestoreState()
	GetUserBonus() *models.UserBonus
	GetSpinsAvailable() int
	GetSpinsTotal() int
	GetSpinsMade() int
	GetFsMaxBet() (float64, error)
}
