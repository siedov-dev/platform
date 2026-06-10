package requests

import (
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"
)

// StartGameRequest представляет структуру запроса для начала игровой сессии.
// Содержит информацию о пользователе, игре, провайдере, группе, режиме и сессии.
type StartGameRequest struct {
	UserID     int64           `json:"user_id"`          // UserID представляет идентификатор пользователя.
	GameId     string          `json:"game_id"`          // GameId представляет идентификатор игры.
	ProviderId string          `json:"game_provider_id"` // ProviderId представляет идентификатор провайдера игры (игровой системы).
	GameGroup  string          `json:"game_group"`       // GameGroup представляет группу, к которой принадлежит игра, для классификации или логической группировки.
	GameData   stypes.GameData `json:"game_data"`        // GameData представляет структурированные данные об игре.
	GameMode   int             `json:"game_mode"`        // GameMode представляет режим игры, например, бонусный или реальный, и используется для определения логики игровой сессии.
	SessionId  string          `json:"session_id"`       // SessionId представляет уникальный идентификатор игровой сессии, используемый для идентификации и отслеживания сессии.
}

// BalanceRequest представляет запрос на получение баланса пользователя в определенной валюте.
type BalanceRequest struct {
	UserId     stypes.UserID       `json:"user_id"`  // UserId содержит идентификатор пользователя в системе.
	CurrencyID currency.CurrencyID `json:"currency"` // CurrencyID указывает валюту, для которой требуется получить баланс.
}

// BalancesRequest представляет собой структуру запроса для получения балансов пользователя.
type BalancesRequest struct {
	UserId stypes.UserID `json:"user_id"` // UserID представляет идентификатор пользователя.
}

// BalanceAddRequest представляет собой структуру для запроса на добавление баланса.
// Содержит идентификатор пользователя, валюты и сумму для добавления.
type BalanceAddRequest struct {
	UserId     stypes.UserID       `json:"user_id"`  // UserID представляет идентификатор пользователя.
	CurrencyID currency.CurrencyID `json:"currency"` // CurrencyID обозначает идентификатор валюты, используемый для указания типа валюты в запросе.
	Amount     float64             `json:"amount"`   // Amount обозначает количество добавляемых денежных средств в валюте, идентификатор которой указан в поле CurrencyID.
}

// SetActiveRequest представляет собой структуру запроса для установки активности кошелька.
type SetActiveRequest struct {
	UserId     stypes.UserID       `json:"user_id"`  // UserID представляет идентификатор пользователя.
	CurrencyID currency.CurrencyID `json:"currency"` // CurrencyID обозначает идентификатор валюты, используемый для указания типа валюты в запросе.
	Active     bool                `json:"active"`   // Active указывает, активен ли кошелек, и используется для управления статусом активности.
}

// SetBonusIdRequest представляет запрос на установку идентификатора бонуса, модифицирующего отображение баланса валюты.
type SetBonusIdRequest struct {
	UserId     stypes.UserID       `json:"user_id"`  // UserID представляет идентификатор пользователя.
	CurrencyID currency.CurrencyID `json:"currency"` // CurrencyID обозначает идентификатор валюты, используемый для указания типа валюты в запросе.
	BonusID    int64               `json:"bonus_id"` // BonusID представляет идентификатор бонуса, применяемого к отображению баланса валюты.
}

// GetBonusIdRequest представляет собой структуру запроса для получения ID бонуса идентификатора бонуса, модифицирующего отображение баланса валюты.
type GetBonusIdRequest struct {
	UserId     stypes.UserID       `json:"user_id"`  // UserID представляет идентификатор пользователя.
	CurrencyID currency.CurrencyID `json:"currency"` // CurrencyID обозначает идентификатор валюты, используемый для указания типа валюты в запросе.
}

// GetWalletValueRequest представляет собой структуру запроса для получения значения определенного кошелька пользователя (тоталы).
// Содержит идентификатор пользователя и идентификатор валюты, по которой выполняется запрос.
type GetWalletValueRequest struct {
	UserId     stypes.UserID       `json:"user_id"`  // UserID представляет идентификатор пользователя.
	CurrencyID currency.CurrencyID `json:"currency"` // CurrencyID обозначает идентификатор валюты, используемый для указания типа валюты в запросе.
}

// SetAmountRequest используется для установки значений в кошельках (тоталы).
type SetAmountRequest struct {
	UserId     stypes.UserID       `json:"user_id"`  // UserID представляет идентификатор пользователя.
	CurrencyID currency.CurrencyID `json:"currency"` // CurrencyID обозначает идентификатор валюты, используемый для указания типа валюты в запросе.
	Amount     float64             `json:"amount"`
}

// SetPPlayToken представляет собой структуру для задания токена PragmaticPlay необходимого для авторизации при запуске игры.
// Поле Token содержит строку, представляющую токен аутентификации.
// Поле UserId хранит уникальный идентификатор пользователя типа stypes.UserID.
type SetPPlayToken struct {
	Token  string        `json:"token"`   // Token представляет собой строку для хранения токена аутентификации.
	UserId stypes.UserID `json:"user_id"` // UserID представляет идентификатор пользователя.
}

// EventRequest представляет собой запрос на добавление события для определенного пользователя.
type EventRequest struct {
	UserId  stypes.UserID `json:"user_id"` // UserID представляет идентификатор пользователя.
	Event   string        `json:"event"`   // Event представляет собой строку, описывающую событие, которое нужно добавить для пользователя.
	Payload string        `json:"payload"` // Payload представляет собой дополнительную информацию или данные, связанные с событием, которые могут быть полезны для его обработки или анализа.
}

// UserIDRequest представляет собой структуру для запроса, содержащего идентификатор пользователя.
type UserIDRequest struct {
	UserId stypes.UserID `json:"user_id"` // UserID представляет идентификатор пользователя.
}
