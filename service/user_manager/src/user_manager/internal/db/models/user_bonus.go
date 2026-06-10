package models

import (
	"database/sql"
	"encoding/json"
	"errors"
	"strconv"
)

// UserBonus представляет структуру данных, описывающую бонусы пользователя и их состояние.
type UserBonus struct {
	ID                   uint            `json:"id" db:"id"`                                         // ID представляет уникальный идентификатор бонуса пользователя в базе данных.
	UserID               uint            `json:"user_id" db:"user_id"`                               // UserID представляет уникальный идентификатор пользователя.
	Type                 int8            `json:"type" db:"type"`                                     // Type тип бонуса. 1 - Deposit, 2 - Non-deposit, 5 - Plus/Minus...
	MoneyAvailable       float64         `json:"money_available" db:"money_available"`               // MoneyAvailable представляет сумму бонусных средств, доступных для использования.
	MoneyPaidOut         float64         `json:"money_paid_out" db:"money_paid_out"`                 // MoneyPaidOut указывает сумму, которая была выплачена в рамках бонуса.
	TotalBet             float64         `json:"total_bet" db:"total_bet"`                           // TotalBet представляет общую сумму ставок, сделанных пользователем в рамках текущего бонуса.
	TotalBetForPayout    float64         `json:"total_bet_for_payout" db:"total_bet_for_payout"`     // TotalBetForPayout представляет сумму ставки, необходимую для выполнения условий получения выплаты (Используется в некоторых бонусах и может применяться для других нужд).
	TotalWin             float64         `json:"total_win" db:"total_win"`                           // TotalWin представляет общую сумму выигрыша по бонусу (Сумму выигрышных ставок).
	WagerToComplete      float64         `json:"wager_to_complete" db:"wager_to_complete"`           // WagerToComplete представляет сумму ставок, необходимую для завершения выполнения условий бонуса.
	WagerToPayout        float64         `json:"wager_to_payout" db:"wager_to_payout"`               // WagerToPayout Используется в некоторых бонусах для своих условий.
	MultiplierToComplete float64         `json:"multiplier_to_complete" db:"multiplier_to_complete"` // MultiplierToComplete представляет множитель, необходимый для выполнения условий бонуса. Используется в некоторых бонусах для своих условий.
	MultiplierToPayout   float64         `json:"multiplier_to_payout" db:"multiplier_to_payout"`     // MultiplierToPayout определяет множитель, необходимый для выплаты бонуса после выполнения условий. Используется в некоторых бонусах для своих условий.
	RealMoneySpent       float64         `json:"real_money_spent" db:"real_money_spent"`             // RealMoneySpent Может использоваться некоторыми бонусами для своих нужд.
	Status               int8            `json:"status" db:"status"`                                 // Status представляет текущий статус бонуса пользователя.
	SpinsTotal           int             `json:"spins_total" db:"spins_total"`                       // SpinsTotal представляет общее количество игровых вращений по бонусу.
	SpinsAvailable       int             `json:"spins_available" db:"spins_available"`               // SpinsAvailable представляет количество доступных спинов для пользователя.
	CreatedAt            sql.NullTime    `json:"created_at" db:"created_at"`                         // CreatedAt указывает время создания бонуса пользователя.
	UpdatedAt            sql.NullTime    `json:"updated_at" db:"updated_at"`                         // UpdatedAt содержит время последнего обновления записи о бонусе.
	ActiveTo             sql.NullTime    `json:"active_to" db:"active_to"`                           // ActiveTo указывает время завершения бонуса.
	TotalBetAfter        float64         `json:"total_bet_after" db:"total_bet_after"`               // TotalBetAfter используется бонусами для своих нужд.
	CompletedAt          sql.NullTime    `json:"completed_at" db:"completed_at"`                     // CompletedAt указывает время завершения бонуса; может быть nil, если бонус ещё не завершён. Некоторые бонусы могут использовать поле для других целей (Multidays bonus)
	MoneyToActivate      string          `json:"money_to_activate" db:"money_to_activate"`           // MoneyToActivate представляет сумму, необходимую для активации бонуса.
	Multiplier           float32         `json:"multiplier" db:"multiplier"`                         // Multiplier Чаще всего размер вейджера.
	HoursActive          int             `json:"hours_active" db:"hours_active"`                     // HoursActive указывает количество часов, в течение которых бонус остается активным. Используется в некоторых при создании для расчета поля ActiveTo.
	MaxWin               sql.NullFloat64 `json:"max_win" db:"max_win"`                               // MaxWin представляет максимальный возможный выигрыш, связанный с бонусом, может быть null.
	Tries                int             `json:"tries" db:"tries"`                                   // Tries представляет количество попыток, доступных или использованных пользователем в рамках бонуса. (Использовался для бонусов с перезапуском). В Multidays используется для указания текущего дня.
	HallID               int             `json:"hall_id" db:"hall_id"`                               // HallID не используется
	Currency             int             `json:"currency" db:"currency"`                             // Currency ид валюты бонуса
	PatternID            sql.NullInt64   `json:"pattern_id" db:"pattern_id"`                         // PatternID ид паттерна бонуса, из которого создан бонус
	PatternStatus        int             `json:"pattern_status" db:"pattern_status"`                 // PatternStatus не используется
	BonusPrizes          sql.NullString  `json:"bonus_prizes" db:"bonus_prizes"`                     // BonusPrizes не используется
	GivenPrizes          sql.NullString  `json:"given_prizes" db:"given_prizes"`                     // GivenPrizes не используется
	EnableAfter          sql.NullString  `json:"enable_after" db:"enable_after"`                     // EnableAfter Указывает, что бонус может быть доступен только после завершения другого типа бонуса
	GamesLimited         bool            `json:"games_limited" db:"games_limited"`                   // Признак того, что у бонуса ограничен список доступных игр (не используется)
	AllowedGames         sql.NullString  `json:"allowed_games" db:"allowed_games"`                   // AllowedGames указывает JSON список игр, доступных в рамках бонуса, может быть пустым.
	JSONParams           sql.NullString  `json:"json_params" db:"json_params"`                       // JSONParams представляет дополнительные параметры в формате JSON, используется для хранения специфичных данных.
	CreatorType          int             `json:"creator_type" db:"creator_type"`                     // CreatorType определяет тип создателя бонуса для идентификации источника создания (промокод, доступные бонусы, ...).
	CreatorID            sql.NullInt64   `json:"creator_id" db:"creator_id"`                         // CreatorID представляет идентификатор создателя бонуса, может быть NULL, если создатель не указан.
	GameProvider         sql.NullString  `json:"game_provider" db:"game_provider"`                   // GameProvider Если бонус лимитирован одной игрой указывается игровая система.
	GameID               sql.NullString  `json:"game_id" db:"game_id"`                               // GameID Если бонус лимитирован одной игрой указывается ид игры.
	Title                sql.NullString  `json:"title" db:"title"`                                   // Title представляет заголовок бонуса, может быть NULL и используется для отображения названия.
	ImgURL               sql.NullString  `json:"img_url" db:"img_url"`                               // ImgURL представляет URL изображения, связанного с бонусом, может быть null, если изображение не задано.
	Marker               int             `json:"marker" db:"marker"`                                 // Marker представляет идентификатор или метку, используемую для идентификации бонуса или его состояния.
}

// BonusGames представляет тип данных map, где ключ — строка (игровая система), а значение — срез строк (ид игр).
// Используется для хранения допустимых игр.
type BonusGames map[string][]string

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
