package models

import (
	"database/sql"
	"encoding/json"
	"errors"
	"kbonus/internal/logger"
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
	"log/slog"
	"strconv"
)

// UserBonus представляет бонус пользователя в системе.
// Он содержит информацию о типе бонуса, доступных деньгах, статусе и других
// параметрах, связанных с бонусом.
type UserBonus struct {
	ID                   stypes.BonusID  `db:"id" json:"id"`                                         // Уникальный идентификатор бонуса
	UserID               stypes.UserID   `db:"user_id" json:"user_id"`                               // Идентификатор пользователя, которому принадлежит бонус
	Type                 int8            `db:"type" json:"type"`                                     // Тип бонуса
	MoneyAvailable       float64         `db:"money_available" json:"money_available"`               // Доступные деньги для бонуса
	MoneyPaidOut         float64         `db:"money_paid_out" json:"money_paid_out"`                 // Выплаченные деньги по бонусу
	TotalBet             float64         `db:"total_bet" json:"total_bet"`                           // Общая сумма ставок по бонусу
	TotalBetForPayout    float64         `db:"total_bet_for_payout" json:"total_bet_for_payout"`     // Общая сумма ставок для выплаты (не используется)
	TotalWin             float64         `db:"total_win" json:"total_win"`                           // Общая сумма выигрышей по бонусу
	WagerToComplete      float64         `db:"wager_to_complete" json:"wager_to_complete"`           // Ставка для завершения бонуса
	WagerToPayout        float64         `db:"wager_to_payout" json:"wager_to_payout"`               // Ставка для выплаты бонуса
	MultiplierToComplete float64         `db:"multiplier_to_complete" json:"multiplier_to_complete"` // Множитель для завершения бонуса
	MultiplierToPayout   float64         `db:"multiplier_to_payout" json:"multiplier_to_payout"`     // Множитель для выплаты бонуса
	RealMoneySpent       float64         `db:"real_money_spent" json:"real_money_spent"`             // Реальные деньги, потраченные на бонус (не используется)
	Status               int8            `db:"status" json:"status"`                                 // Статус бонуса (активен, завершен и т.д.)
	SpinsTotal           int             `db:"spins_total" json:"spins_total"`                       // Общее количество спинов по бонусу
	SpinsAvailable       int             `db:"spins_available" json:"spins_available"`               // Доступное количество спинов по бонусу
	ActiveTo             sql.NullTime    `db:"active_to" json:"active_to"`                           // Дата и время, до которого бонус активен
	TotalBetAfter        float64         `db:"total_bet_after" json:"total_bet_after"`               // Общая сумма ставок после активации бонуса (не используется)
	CompletedAt          sql.NullTime    `db:"completed_at" json:"completed_at"`                     // Дата и время завершения бонуса
	MoneyToActivate      string          `db:"money_to_activate" json:"money_to_activate"`           // Сумма денег, необходимая для активации бонуса
	Multiplier           float32         `db:"multiplier" json:"multiplier"`                         // Множитель бонуса
	HoursActive          int             `db:"hours_active" json:"hours_active"`                     // Количество часов, в течение которых бонус активен
	MaxWin               sql.NullFloat64 `db:"max_win" json:"max_win"`                               // Максимальный выигрыш по бонусу
	Tries                int             `db:"tries" json:"tries"`                                   // Количество попыток использования бонуса, так же используется для других целей, например активный день многодневного бонуса
	HallID               int             `db:"hall_id" json:"hall_id"`                               // Идентификатор зала, к которому относится бонус (не используется)
	Currency             int8            `db:"currency" json:"currency"`                             // Идентификатор валюты, в которой начислен бонус
	PatternID            sql.NullInt64   `db:"pattern_id" json:"pattern_id"`                         // Идентификатор шаблона бонуса, если применимо
	PatternStatus        int             `db:"pattern_status" json:"pattern_status"`                 // Статус шаблона бонуса (не используется)
	BonusPrizes          sql.NullString  `db:"bonus_prizes" json:"bonus_prizes"`                     // не используется, но может содержать информацию о призах, связанных с бонусом
	GivenPrizes          sql.NullString  `db:"given_prizes" json:"given_prizes"`                     // Список призов, выданных по бонусу (не используется)
	EnableAfter          sql.NullString  `db:"enable_after" json:"enable_after"`                     // Указывает тип бонусов после завершения которых бонус будет доступен
	GamesLimited         bool            `db:"games_limited" json:"games_limited"`                   // Указывает, ограничен ли бонус по играм (0 - нет, 1 - да) (не используется)
	AllowedGames         sql.NullString  `db:"allowed_games" json:"allowed_games"`                   // Список игр, в которых можно использовать бонус
	JSONParams           sql.NullString  `db:"json_params" json:"json_params"`                       // Дополнительные параметры бонуса в формате JSON
	CreatorType          int             `db:"creator_type" json:"creator_type"`                     // Тип создателя бонуса (например, промокод, доступные бонусы и т.д.)
	CreatorID            sql.NullInt64   `db:"creator_id" json:"creator_id"`                         // Идентификатор создателя бонуса (например, ID промокода)
	GameProvider         sql.NullString  `db:"game_provider" json:"game_provider"`                   // Провайдер игры, к которому относится бонус
	GameID               sql.NullString  `db:"game_id" json:"game_id"`                               // Идентификатор игры, к которой относится бонус
	Title                sql.NullString  `db:"title" json:"title"`                                   // Заголовок бонуса
	ImgURL               sql.NullString  `db:"img_url" json:"img_url"`                               // URL изображения бонуса
	Marker               int             `db:"marker" json:"marker"`                                 // Маркер бонуса
	CreatedAt            sql.NullTime    `db:"created_at" json:"created_at"`                         // Дата и время создания бонуса
	UpdatedAt            sql.NullTime    `db:"updated_at" json:"updated_at"`                         // Дата и время последнего обновления бонуса
	WagerGamesSegmentID  sql.NullInt64   `db:"wager_games_segment_id" json:"wager_games_segment_id"` // Идентификатор сегмента игр для отыгрыша
}

// BonusGames представляет тип данных map, где ключ — строка (игровая система), а значение — срез строк (ид игр).
// Используется для хранения допустимых игр.
type BonusGames map[string][]string

func (b *UserBonus) GetCurrencyName() string {
	name, err := currencyid.CurrencyID(b.Currency).Name()
	if err != nil {
		logger.Get().Error("UserBonus.GetCurrencyName", slog.String("err", err.Error()))
		return ""
	}
	return name
}

// GetGames возвращает список разрешенных игр (BonusGames) или ошибку, если данные игр недоступны или некорректны.
func (b *UserBonus) GetGames() (BonusGames, error) {
	var bonus_games BonusGames
	if !b.AllowedGames.Valid {
		return nil, errors.New("empty allowed games")
	}

	err := json.Unmarshal([]byte(b.AllowedGames.String), &bonus_games)
	if err != nil {
		return nil, err
	}

	return bonus_games, nil
}

// GetMaxXWin возвращает максимальное значение выигрыша (max_x_win) из JSONParams структуры UserBonus.
// Если данные отсутствуют или некорректны, возвращается 0.
func (b *UserBonus) GetMaxXWin() float64 {
	if b.JSONParams.Valid {
		var dat map[string]any
		err := json.Unmarshal([]byte(b.JSONParams.String), &dat)
		if err != nil {
			return 0
		}
		if val, ok := dat["max_x_win"]; ok {
			if val, ok := val.(float64); ok {
				return val
			}
			if val, ok := val.(string); ok {
				parseFloat, err := strconv.ParseFloat(val, 64)
				if err == nil {
					return parseFloat
				}
			}
		}
	}
	return 0
}

func (b *UserBonus) GetJsonParam(key string) (any, bool) {
	if !b.JSONParams.Valid || b.JSONParams.String == "" {
		return nil, false
	}
	var params map[string]any
	if err := json.Unmarshal([]byte(b.JSONParams.String), &params); err != nil {
		return nil, false
	}
	value, ok := params[key]
	return value, ok
}

func (b *UserBonus) SetJsonParam(key string, value any) error {
	var params map[string]any
	if b.JSONParams.Valid && b.JSONParams.String != "" {
		if err := json.Unmarshal([]byte(b.JSONParams.String), &params); err != nil {
			return err
		}
	} else {
		params = make(map[string]any)
	}
	params[key] = value
	data, err := json.Marshal(params)
	if err != nil {
		logger.Get().Error("UserBonus.SetJsonParam", slog.String("err", err.Error()))
		return err
	}
	b.JSONParams = sql.NullString{String: string(data), Valid: true}
	return nil
}

// Перечисление типов бонусов
const (
	TYPE_NON_DEPOSIT               = 2
	TYPE_FREE_SPINS                = 3
	TYPE_PLUS_MINUS                = 5
	TYPE_INSURANCE                 = 6
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
	TYPE_PPLAY_FS                  = 30
	TYPE_PPLAY_SMART_FS            = 31
	TYPE_QT_FS_WAGER               = 32
	TYPE_QT_SMART_FS               = 33
	TYPE_SLEG_FS                   = 34
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

// Перечисление статусов бонусов
const (
	STATUS_NOT_ACTIVATED         = 0  // Ожидание активации
	STATUS_ACTIVE                = 1  // Активен
	STATUS_DONE                  = 2  // Завершён
	STATUS_CANCELED              = 3  // Отменен
	STATUS_PENDING               = 4  // Ожидает
	STATUS_EXPIRED               = 5  // Истёк
	STATUS_WIN                   = 6  // Выигран
	STATUS_WAITING_FOR_PAYMENT   = 7  // Ожидает оплаты
	STATUS_WAITING_FOR_AGREEMENT = 8  // Ожидает активации
	STATUS_FILTHY                = 9  // Отмывочный (не используется)
	STATUS_WAITING_FOR_LOSE      = 10 // Ожидает проигрыша
	STATUS_LOCKED                = 11 // Заблокирован
	STATUS_BUY_REQUIRED          = 12 // Требует покупки (не используется)
	STATUS_ERROR                 = 13 // Ошибка
	STATUS_DEPOSIT_LISTEN        = 14 // Требует уведомления об оплате
)
