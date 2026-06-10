package callbacks

import (
	"kbonus/internal/db/models"
	"kbonus/internal/stypes"
)

type BonusData struct {
	Attributes models.UserBonus `json:"attributes"`
	Meta       stypes.BonusInfo `json:"meta,omitzero"` // Информация о бонусе
}

type UserIDRequest struct {
	UserID int64 `json:"user_id"`
}

type UserIDCurrencyIDRequest struct {
	UserID     int64 `json:"user_id"`
	CurrencyID int8  `json:"currency_id"`
}

type GetBonusRequest struct {
	BonusID int64 `json:"bonus_id"`
}

type GetBonusByPatternIDRequest struct {
	UserID    int64 `json:"user_id"`
	PatternID int64 `json:"pattern_id"`
}

type CreateFromBonusAvailable struct {
	BonusID    int64 `json:"bonus_id"`
	UserID     int64 `json:"user_id"`
	CurrencyID int8  `json:"currency_id"`
}

type CancelFromBonusAvailable struct {
	BonusID int64 `json:"bonus_id"`
	UserID  int64 `json:"user_id"`
}

type BonusIDCurrencyIDRequest struct {
	BonusID    int64 `json:"bonus_id"`
	CurrencyID int8  `json:"currency_id"`
}

type ActivateBonusRequest struct {
	BonusID int64   `json:"bonus_id"`
	Value   float64 `json:"value"`
}

type ConfirmPaymentRequest struct {
	BonusID    int64   `json:"bonus_id"`
	Amount     float64 `json:"amount"`
	CurrencyID int8    `json:"currency_id"`
}

type GetUserBonusesRequest struct {
	UserID     int64 `json:"user_id"`
	CurrencyID int64 `json:"currency_id"` // 0 - all currencies
}

type GenericResponse struct {
	Code  int    `json:"cd"`
	Error string `json:"err"` // Сообщение об ошибке, если есть
}

type GetUserBonusesResponse struct {
	Bonuses []BonusData `json:"bonuses"` // Список бонусов пользователя
	Code    int         `json:"cd"`      // Код ответа
	Error   string      `json:"err"`     // Сообщение об ошибке, если есть
}

type GetBonusResponse struct {
	Attributes models.UserBonus `json:"attributes,omitzero"`
	Meta       stypes.BonusInfo `json:"meta,omitzero"` // Информация о бонусе
	Code       int              `json:"cd"`            // Код ответа
	Error      string           `json:"err"`           // Сообщение об ошибке, если есть
}

type GetAvailableBonusCountResponse struct {
	Count int    `json:"count"` // Количество доступных бонусов
	Code  int    `json:"cd"`    // Код ответа
	Error string `json:"err"`   // Сообщение об ошибке, если есть
}

type BoolResponse struct {
	Code  int    `json:"cd"`
	Data  bool   `json:"data"`
	Error string `json:"error,omitempty"`
}

type StatusResponse struct {
	Code  int    `json:"cd"`
	Error string `json:"error,omitempty"`
}

type BonusTypeAndCurrencyRequest struct {
	BonusType  int8 `json:"bonus_type"`
	CurrencyID int8 `json:"currency_id"`
}

type BonusParamsResponse struct {
	Params []*models.BonusMenuParam `json:"params"`
	Code   int                      `json:"cd"`
	Error  string                   `json:"err,omitempty"`
}
type BonusParamResponse struct {
	Param *models.BonusMenuParam `json:"param"`
	Code  int                    `json:"cd"`
	Error string                 `json:"err,omitempty"`
}

type FindBonusParamInRangeRequest struct {
	BonusType  int8    `json:"bonus_type"`
	CurrencyID int8    `json:"currency_id"`
	MinValue   float64 `json:"min_value"`
}

type FindBonusParamInRangeResponse struct {
	Param *models.BonusMenuParam `json:"param"`
	Code  int                    `json:"cd"`
	Error string                 `json:"err,omitempty"`
}

type GetParamByKeyRequest struct {
	BonusType  int8    `json:"bonus_type"`
	CurrencyID int8    `json:"currency_id"`
	Key        float64 `json:"key"`
}

type GetParamByKeyResponse struct {
	Param *models.BonusMenuParam `json:"param"`
	Code  int                    `json:"cd"`
	Error string                 `json:"err,omitempty"`
}

type ParamsLowestMinValueRequest struct {
	BonusType  int8 `json:"bonus_type"`
	CurrencyID int8 `json:"currency_id"`
}

type ParamsLowestMinValueResponse struct {
	Value float64 `json:"value"`
	Code  int     `json:"cd"`
	Error string  `json:"err,omitempty"`
}

type GetPatternResponse struct {
	Attributes models.BonusPattern `json:"attributes"`
	Code       int                 `json:"cd"`
	Error      string              `json:"err"`
}

type GetRegPatternIdsResponse struct {
	IDs   []int64 `json:"ids"`
	Code  int     `json:"cd"`
	Error string  `json:"err"`
}

type BonusBalanceInCentsResponse struct {
	Balance int64  `json:"balance"`
	Code    int    `json:"cd"`
	Error   string `json:"err,omitempty"`
}

type MultidaysMenuParamsResponse struct {
	Param *models.BonusMultiDaysParam `json:"param"`
	Code  int                         `json:"cd"`
	Error string                      `json:"err,omitempty"`
}

type TransferBonusMoneyResponse struct {
	Attributes models.UserBonus `json:"attributes"`
	Meta       stypes.BonusInfo `json:"meta"`   // Информация о бонусе
	Amount     float64          `json:"amount"` // Сумма перевода
	Code       int              `json:"cd"`     // Код ответа
	Error      string           `json:"err"`    // Сообщение об ошибке, если есть
}

type BonusDataRequest struct {
	Attributes models.UserBonus `json:"attributes"`
}

type GetBonusGameRequest struct {
	BonusID  int64 `json:"bonus_id"`
	WagerPrc int16 `json:"wager_prc"`
}

type BonusGameMoneyRequest struct {
	BonusID  int64   `json:"bonus_id"`
	WagerPrc int16   `json:"wager_prc"`
	Amount   float64 `json:"amount"`
}

type BonusGameExtFsMoneyRequest struct {
	BonusID  int64   `json:"bonus_id"`
	WagerPrc int16   `json:"wager_prc"`
	Amount   float64 `json:"amount"`
	Provider string  `json:"provider"`
}

type BonusBalanceResponse struct {
	Balance float64 `json:"balance"`
	Code    int     `json:"cd"`
	Error   string  `json:"err,omitempty"`
}

type BonusMoneyChangeResponse struct {
	Balance     float64 `json:"balance"`
	RealAmount  float64 `json:"real_amount"`
	BonusAmount float64 `json:"bonus_amount"`
	Code        int     `json:"cd"`
	Error       string  `json:"err,omitempty"`
}

type BonusActualizeResponse struct {
	Win   float64 `json:"win"`
	Code  int     `json:"cd"`
	Error string  `json:"err,omitempty"`
}
