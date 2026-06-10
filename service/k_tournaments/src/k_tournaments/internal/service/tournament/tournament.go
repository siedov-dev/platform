package tournament

import (
	"encoding/json"
	"fmt"
	"ktournaments/internal/db/models"
	"ktournaments/internal/service/container"
	"ktournaments/internal/service/tournament/gamehandlers"
	"strconv"
	"sync"
	"time"
)

// Tournament - структура турнира
type Tournament struct {
	Model        models.TournamentModel              `json:"model"`         // Модель турнира
	Values       *TournamentValues                   `json:"-"`             // Значения турнира
	Games        map[string]map[string]bool          `json:"-"`             // Игры
	GamesLimited bool                                `json:"games_limited"` // Флаг ограничения игр
	MinBets      map[string]float64                  `json:"min_bets"`      // Минимальные ставки
	GameHanlder  gamehandlers.TournamentGame         `json:"-"`             // Обработчик игры
	Prizes       map[int]models.TournamentPrizeModel `json:"-"`             // Призы
	PrizePlaces  uint32                              `json:"prize_places"`  // Количество призов
	PrizeBank    map[string]float64                  `json:"prize_bank"`    // Призовой фонд
	mt           sync.RWMutex                        `json:"-"`             // Мьютекс
	Conditions   map[string]string                   `json:"conditions"`    // Описания турнира
}

// LoadFromModel - загрузка турнира из модели
func (t *Tournament) LoadFromModel(m models.TournamentModel) error {
	t.mt.Lock()
	defer t.mt.Unlock()

	// инициализация
	t.Model = m
	t.Games = make(map[string]map[string]bool)
	t.MinBets = make(map[string]float64)
	t.PrizeBank = make(map[string]float64)

	// создание обработчика игры
	gamehandler, err := gamehandlers.NewTournamentGame(m.GameType, m.GameParams)
	if err != nil {
		return fmt.Errorf("error creating game handler: %v", err)
	}
	t.GameHanlder = gamehandler

	// загрузка значений (очков) турнира
	tv, err := t.Values.Load(m.ID)
	if err != nil {
		return fmt.Errorf("error getting tournament values: %v", err)
	}
	t.Values = tv

	if (t.Model.GameList != nil) && (*t.Model.GameList != "") {
		// загрузка игр из формата:
		// {
		// 	"game_system": ["game_id1", "game_id2", "game_id3"],
		// 	"game_system2": ["game_id1", "game_id2", "game_id3"]
		// }
		var temp map[string][]string

		// парсинг JSON
		err = json.Unmarshal([]byte(*t.Model.GameList), &temp)
		if err != nil {
			return fmt.Errorf("error parsing game list: %v", err)
		}

		// конвертация в map[string]map[string]bool
		for key, values := range temp {
			t.Games[key] = make(map[string]bool)
			for _, value := range values {
				t.Games[key][value] = true
			}
		}
		// установка флага ограничения игр
		t.GamesLimited = true
	}
	// загрузка минимальных ставок
	var params map[string]any
	if t.Model.Params != "" {

		// парсинг JSON
		err = json.Unmarshal([]byte(t.Model.Params), &params)
		if err != nil {
			return fmt.Errorf("error parsing params: %v", err)
		}

		// конвертация в map[string]float64
		for key, value := range params {
			// если ключ min_bets, то загрузка минимальных ставок
			// из формата:
			// {
			// 	"currency": "1.0",
			// 	"currency2": "2.0"
			// }
			if key == "min_bets" {
				if v, ok := value.(map[string]any); ok {
					for k, v := range v {
						if val, ok := v.(float64); ok {
							t.MinBets[k] = val
						}
						if val, ok := v.(string); ok {
							t.MinBets[k], err = strconv.ParseFloat(val, 64)
							if err != nil {
								return err
							}
						}
					}
				}
			}
		}
	}

	// загрузка призов
	if m.TournamentBonusGroup == nil {
		return fmt.Errorf("error getting tournament bonus group: %v", err)
	}
	t.Prizes = make(map[int]models.TournamentPrizeModel)
	// получение призовых итемов
	pr, err := container.GetPrizeRepository().GetByGroupID(uint64(*m.TournamentBonusGroup))
	if err != nil {
		return fmt.Errorf("error getting tournament prizes: %v", err)
	}
	for _, prize := range pr {
		t.Prizes[prize.Place] = prize
	}

	// загрузка призового фонда
	var amounts map[string]string
	for _, prize := range t.Prizes {
		if prize.CurrencyAmounts != nil {
			err = json.Unmarshal([]byte(*prize.CurrencyAmounts), &amounts)
			if err != nil {
				return fmt.Errorf("error parsing currency amounts: %v", err)
			}
			for k, v := range amounts {
				a, err := strconv.ParseFloat(v, 64)
				if err != nil {
					return fmt.Errorf("error parsing currency amount: %v", err)
				}
				t.PrizeBank[k] += a
			}
		}
		t.PrizePlaces++
	}

	// загрузка описаний турнира
	t.Conditions = make(map[string]string)
	cond := make(map[string]map[string]string)
	if t.Model.Conditions != "" {
		err = json.Unmarshal([]byte(t.Model.Conditions), &cond)
		if err != nil {
			return fmt.Errorf("error parsing conditions: %v", err)
		}
		for lang, v := range cond {
			if descr, ok := v["descr"]; ok {
				t.Conditions[lang] = descr
			}
		}
	}

	return nil
}

func (t *Tournament) ProcessSpin(user_id int, currency string, game_system string, game_id string, bet float64, win float64, dt time.Time) bool {
	t.mt.Lock()
	defer t.mt.Unlock()

	// Проверить является ли турнир активным
	if t.Model.Status != models.STATUS_ACTIVE {
		return false
	}
	// Проверка времени турнира
	if t.Model.EndTime.Before(dt) {
		return false
	}
	if t.Model.StartTime.After(dt) {
		return false
	}

	// Проверка принятия участия в турнире
	if t.Model.AgreementReq {
		// Если требуется согласие пользователя, проверяем, что пользователь участвует в турнире
		if _, ok := t.Values.Values[user_id]; !ok {
			return false
		}
	}

	// Проверка доступности игры
	if t.GamesLimited {
		if _, ok := t.Games[game_system]; !ok {
			return false
		}
		if _, ok := t.Games[game_system][game_id]; !ok {
			return false
		}
	}

	// Проверка минимальной ставки
	if t.MinBets != nil {
		if minBet, ok := t.MinBets[currency]; ok {
			if bet < minBet {
				return false
			}
		}
	}

	// Получение очков за спин
	points := t.GameHanlder.WinPoints(int64(user_id), currency, bet, win)

	// Логирование спина турнира
	container.GetSpinLogger().LogSpin(models.TournamentSpinLog{
		TournamentID: t.Model.ID,
		UserID:       user_id,
		Currency:     currency,
		GameSystem:   game_system,
		GameID:       game_id,
		Bet:          bet,
		Win:          win,
		Points:       points,
		CreatedAt:    dt,
		UpdatedAt:    dt,
	})
	if points == 0 {
		return true
	}

	// Добавление очков и к-ва спинов в значения турнира
	t.Values.AddPointsAndSpinsCount(user_id, points, 1)
	return true
}

// Используется для проверки доступности турнира для игры
func (t *Tournament) IsAvailable(user_id int, currency string, game_system string, game_id string, test_user bool) bool {
	t.mt.RLock()
	defer t.mt.RUnlock()

	// check test mode
	if t.Model.IsTest && !test_user {
		return false
	}

	// check the tournament is active
	if t.Model.Status != models.STATUS_ACTIVE {
		return false
	}
	dt := time.Now()
	// check the tournament times
	if t.Model.EndTime.Before(dt) {
		return false
	}
	if t.Model.StartTime.After(dt) {
		return false
	}

	// if user agreemnt required
	if t.Model.AgreementReq {
		// check if user is playing (agreed to play)
		if _, ok := t.Values.Values[user_id]; !ok {
			return false
		}
	}

	// check the game is allowed
	if t.GamesLimited {
		if _, ok := t.Games[game_system]; !ok {
			return false
		}
		if _, ok := t.Games[game_system][game_id]; !ok {
			return false
		}
	}

	return true
}
