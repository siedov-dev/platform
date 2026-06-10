package models

import (
	"time"
)

// TournamentModel - модель турнира.
// Содержит информацию о турнире (тип, название, статус, время начала и окончания ...)
type TournamentModel struct {
	ID                   uint64     `db:"id" json:"id"`                                                         // ID турнира
	Type                 int        `db:"type" json:"type"`                                                     // Тип турнира (не используется)
	Name                 string     `db:"name" json:"name"`                                                     // Название турнира
	Status               int        `db:"status" json:"status"`                                                 // Статус турнира (0 - не активен, 1 - активен, 2 - завершен, 3 - выданы награды)
	StartTime            time.Time  `db:"start_time" json:"start_time"`                                         // Время начала турнира
	EndTime              time.Time  `db:"end_time" json:"end_time"`                                             // Время окончания турнира
	PrizePlaces          int        `db:"prize_places" json:"-"`                                                // Количество призовых мест (не используется)
	WinPrc               int        `db:"win_prc" json:"-"`                                                     // Процент победителя (не используется)
	GameType             int        `db:"game_type" json:"game_type"`                                           // Тип игры (1 - мультипликатор, 2 - ставка * x, 3 - выигрыш * x, 4 - выигрыш / k)
	GameParams           string     `db:"game_params" json:"-"`                                                 // Параметры игры (не используется)
	Bank                 float64    `db:"bank" json:"-"`                                                        // Банк турнира (не используется)
	BuyinCost            float64    `db:"buyin_cost" json:"-"`                                                  // Стоимость байина (не используется)
	RebuyCost            float64    `db:"rebuy_cost" json:"-"`                                                  // Стоимость ребая (не используется)
	RebuyCount           int        `db:"rebuy_count" json:"-"`                                                 // Количество ребаев (не используется)
	BuyinBankPrc         int        `db:"buyin_bank_prc" json:"-"`                                              // Процент банка от байина (не используется)
	RebuyBankPrc         int        `db:"rebuy_bank_prc" json:"-"`                                              // Процент банка от ребая (не используется)
	PlayerList           *string    `db:"player_list" json:"player_list,omitempty"`                             // Список игроков (не используется)
	AllowedLevels        *string    `db:"allowed_levels" json:"allowed_levels,omitempty"`                       // Список уровней (не используется)
	GameList             *string    `db:"game_list" json:"game_list,omitempty"`                                 // Список игр
	Params               string     `db:"params" json:"params"`                                                 // Параметры турнира
	TournamentBonusGroup *int       `db:"tournament_bonus_group_id" json:"tournament_bonus_group_id,omitempty"` // ID группы призовых итемов (SLC item) (было бонусов)
	CreatedAt            *time.Time `db:"created_at" json:"created_at,omitempty"`                               // Время создания
	UpdatedAt            *time.Time `db:"updated_at" json:"updated_at,omitempty"`                               // Время обновления
	EdRebuys             int        `db:"ed_rebuys" json:"-"`                                                   // Не используется
	PenaltyPoints        int        `db:"penalty_points" json:"-"`                                              // Не используется
	FreeRebuys           int        `db:"free_rebuys" json:"-"`                                                 // Не используется
	HallID               int        `db:"hall_id" json:"-"`                                                     // Не используется
	CurrencyID           int        `db:"currency" json:"currency"`                                             // Валюта
	IsTest               bool       `db:"is_test" json:"is_test"`                                               // Тестовый режим
	TileImg              string     `db:"tile_img" json:"tile_img"`                                             // Изображение для плитки
	MobileImg            string     `db:"mobile_img" json:"mobile_img"`                                         // Изображение для мобильного
	DesktopImg           string     `db:"desktop_img" json:"desktop_img"`                                       // Изображение для десктопа
	CabinetDImg          string     `db:"cabinet_d_img" json:"cabinet_d_img"`                                   // Изображение для кабинета десктоп
	CabinetMImg          string     `db:"cabinet_m_img" json:"cabinet_m_img"`                                   // Изображение для кабинета мобильное
	ReqConfirm           bool       `db:"req_confirm" json:"req_confirm"`                                       // Не используется
	Conditions           string     `db:"conditions" json:"conditions"`                                         // Условия турнира
	AgreementReq         bool       `db:"aggr_req" json:"aggr_req"`                                             // Требуется согласие на условия
	PrizeFund            string     `db:"prize_fund" json:"prize_fund"`                                         // Строка призового фонда по языкам (json)
	PrizePage            string     `db:"prize_page" json:"prize_page"`                                         // Страница призы по языкам
	SpinsRequired        int32      `db:"spins_required" json:"spins_required"`                                 // Количество спинов, необходимых для получениея призов
}

// Статусы турнира
const (
	STATUS_NOT_ACTIVE int = iota // не активен
	STATUS_ACTIVE                // активен
	STATUS_DONE                  // завершен, но не выданы награды
	STATUS_COMPLETED             // завершен и выданы награды
)
