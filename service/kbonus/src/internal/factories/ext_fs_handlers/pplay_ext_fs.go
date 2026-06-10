package extfshandlers

import (
	"database/sql"
	"encoding/json"
	"errors"
	"fmt"
	"kbonus/internal/db/models"
	"kbonus/internal/fsapi/pplayapi"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"log/slog"
	"strconv"
	"strings"
	"sync"
	"time"
)

// NewPPlayExtFsHandler создает новый обработчик внешних free spins для PragmaticPlay.
// Принимает указатель на бонус пользователя и возвращает реализацию интерфейса BonusExternalFsHandler.
func NewPPlayExtFsHandler(bonus *models.UserBonus) stypes.BonusExternalFsHandler {
	return &PPlayExtFsHandler{
		bonus: bonus,
	}
}

// PPlayExtFsHandler обработчик внешних free spins для PragmaticPlay.
// Реализует интерфейс BonusExternalFsHandler.
type PPlayExtFsHandler struct {
	bonus *models.UserBonus
	mut   sync.RWMutex
}

// ExternFreeSpinBet вызывается при ставке free spin.
func (s *PPlayExtFsHandler) ExternFreeSpinBet(amount float64) (float64, error) {
	s.mut.Lock()
	defer s.mut.Unlock()
	if s.bonus.SpinsAvailable > 1 {
		s.bonus.SpinsAvailable -= 1
	}
	return s.bonus.MoneyAvailable, nil
}

// ExternFreeSpinWin вызывается при выигрыше во free spin.
func (s *PPlayExtFsHandler) ExternFreeSpinWin(amount float64) (float64, error) {
	s.mut.Lock()
	defer s.mut.Unlock()

	s.bonus.MoneyAvailable += amount
	s.bonus.TotalWin += amount
	return s.bonus.MoneyAvailable, nil
}

// ExternFreeSpinsCompleted вызывается при завершении всех free spins.
func (s *PPlayExtFsHandler) ExternFreeSpinsCompleted(amount float64) (float64, error) {
	s.mut.Lock()
	defer s.mut.Unlock()

	// Сбросить количество доступных спинов и общий bet
	s.bonus.SpinsAvailable = -1
	s.bonus.TotalBet = 0
	// Пересчитать сумму для отыгрыша
	s.bonus.WagerToComplete = s.bonus.MoneyAvailable * float64(s.bonus.Multiplier)

	// Сбросить ограничения по играм для бонуса
	if dropGamesBonuses[s.bonus.Type] {
		s.bonus.AllowedGames = sql.NullString{}
		s.bonus.GameID = sql.NullString{}
		s.bonus.GameProvider = sql.NullString{}
		s.bonus.GamesLimited = false
	}

	// Пересчитать максимальный выигрыш для бонуса
	if recalcMaxWinAfterFsBonuses[s.bonus.Type] {
		max_win := s.bonus.GetMaxXWin()
		if max_win > 0 {
			s.bonus.MaxWin = sql.NullFloat64{
				Float64: s.bonus.MoneyAvailable * max_win,
				Valid:   true,
			}
		}
	}

	// Записать статистику по бонусу
	now := time.Now()

	stat := models.BonusStatistic{
		BonusID:      int64(s.bonus.ID),
		BonusType:    int16(s.bonus.Type),
		PatternID:    s.bonus.PatternID.Int64,
		UserID:       int64(s.bonus.UserID),
		Action:       stypes.ACTION_FS_DONE,
		Amount:       s.bonus.MoneyAvailable,
		Currency:     int8(s.bonus.Currency),
		StatusBefore: stypes.STATUS_ACTIVE,
		Status:       s.bonus.Status,
		CreatedAt:    &now,
		UpdatedAt:    &now,
	}

	// Записать данные в статистику
	data, err := json.Marshal(map[string]float64{
		"money": s.bonus.MoneyAvailable,
		"wager": s.bonus.WagerToComplete,
	})

	if err != nil {
		logger.Get().Error("PPlayExtFsHandler", slog.String("err", err.Error()))
	} else {
		datas := string(data)
		stat.Data = &datas
	}
	container.GetBonusStatisticRepository().Insert(&stat)

	return s.bonus.MoneyAvailable, nil
}

func (s *PPlayExtFsHandler) ExternFreeSpinsCancel(bonus_id string) error {
	s.mut.Lock()
	defer s.mut.Unlock()
	pplayapi.CancelVFRB(bonus_id)
	pplayapi.CancelFRB(bonus_id)
	return nil
}

func (s *PPlayExtFsHandler) ExternFreeSpinsCreate(bonus_id string) error {
	s.mut.Lock()
	defer s.mut.Unlock()
	// Определяем переменную для хранения игр бонуса
	var bonusGames string
	if s.bonus.GameID.Valid && s.bonus.GameID.String != "" {
		bonusGames = s.bonus.GameID.String
	} else if !s.bonus.AllowedGames.Valid || s.bonus.AllowedGames.String != "" {
		return errors.New("cannot create free spins for bonus with no games set")
	} else {
		gamesAllowed := make(map[string][]string)
		err := json.Unmarshal([]byte(s.bonus.AllowedGames.String), &gamesAllowed)
		if err != nil {
			return err
		}
		if len(gamesAllowed["pragplay"]) == 0 {
			return errors.New("cannot create free spins for bonus with no games set")
		}
		bonusGames = strings.Join(gamesAllowed["pragplay"], ",")
	}
	maxBet := 0.0
	jsonParams := make(map[string]any)
	if err := json.Unmarshal([]byte(s.bonus.JSONParams.String), &jsonParams); err != nil {
		return err
	}
	if val, ok := jsonParams["max_bet"]; ok {
		switch mb := val.(type) {
		case float64:
			maxBet = mb
		case int:
			maxBet = float64(mb)
		case string:
			m, err := strconv.ParseFloat(mb, 64)
			if err != nil {
				return errors.New("invalid max_bet value")
			}
			maxBet = m
		default:
			return errors.New("invalid max_bet value")
		}
	}

	playerId := strconv.Itoa(int(s.bonus.UserID)) + s.bonus.GetCurrencyName()
	var errResp struct {
		Error       string `json:"error"`
		Description string `json:"description"`
	}

	if maxBet > 0 {
		resp, err := pplayapi.CreatePlayerVFRB(
			playerId,
			s.bonus.GetCurrencyName(),
			bonusGames,
			fmt.Sprintf("%d", s.bonus.SpinsAvailable),
			fmt.Sprintf("%d", s.bonus.ID),
			s.bonus.ActiveTo.Time.Unix(),
			maxBet,
		)
		if err != nil {
			return err
		}
		logger.Get().Info("PPlayExtFsHandler ExternFreeSpinsCreate VFRB", slog.String("response", resp))
		if err = json.Unmarshal([]byte(resp), &errResp); err != nil {
			return err
		}
		if errResp.Error != "" && errResp.Error != "0" {
			return errors.New(errResp.Description)
		}
	} else {
		resp, err := pplayapi.CreateFRB(
			playerId,
			s.bonus.GetCurrencyName(),
			bonusGames,
			fmt.Sprintf("%d", s.bonus.SpinsAvailable),
			fmt.Sprintf("%d", s.bonus.ID),
			s.bonus.ActiveTo.Time.Unix(),
		)
		if err != nil {
			return err
		}
		logger.Get().Info("PPlayExtFsHandler ExternFreeSpinsCreate FRB", slog.String("response", resp))
		if err = json.Unmarshal([]byte(resp), &errResp); err != nil {
			return err
		}
		if errResp.Error != "" && errResp.Error != "0" {
			return errors.New(errResp.Description)
		}
	}

	return nil
}
