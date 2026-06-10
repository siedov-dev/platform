package extfshandlers

import (
	"database/sql"
	"encoding/json"
	"errors"
	"kbonus/internal/db/models"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"log/slog"
	"strconv"
	"sync"
	"time"
)

// NewMockExtFsHandler создает новый обработчик внешних free spins для игровой системы Qt.
// Принимает указатель на бонус пользователя и возвращает реализацию интерфейса BonusExternalFsHandler.
func NewMockExtFsHandler(bonus *models.UserBonus) stypes.BonusExternalFsHandler {
	return &MockExtFsHandler{
		bonus: bonus,
	}
}

// MockExtFsHandler обработчик внешних free spins для игровой системы Qt.
// Реализует интерфейс BonusExternalFsHandler.
type MockExtFsHandler struct {
	bonus *models.UserBonus
	mut   sync.RWMutex
}

// ExternFreeSpinBet вызывается при ставке free spin.
// Возвращает доступную сумму бонуса.
func (s *MockExtFsHandler) ExternFreeSpinBet(amount float64) (float64, error) {
	s.mut.Lock()
	defer s.mut.Unlock()
	return s.bonus.MoneyAvailable, nil
}

// ExternFreeSpinWin вызывается при выигрыше во free spin.
// Увеличивает сумму доступных средств и общий выигрыш по бонусу.
func (s *MockExtFsHandler) ExternFreeSpinWin(amount float64) (float64, error) {
	s.mut.Lock()
	defer s.mut.Unlock()

	s.bonus.MoneyAvailable += amount
	s.bonus.TotalWin += amount
	return s.bonus.MoneyAvailable, nil
}

// ExternFreeSpinsCompleted вызывается при завершении всех free spins.
// Сбрасывает количество доступных спинов и общий bet, пересчитывает сумму для отыгрыша.
func (s *MockExtFsHandler) ExternFreeSpinsCompleted(amount float64) (float64, error) {
	s.mut.Lock()
	defer s.mut.Unlock()

	s.bonus.SpinsAvailable = -1
	s.bonus.TotalBet = 0
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
	data, err := json.Marshal(map[string]float64{
		"money": s.bonus.MoneyAvailable,
		"wager": s.bonus.WagerToComplete,
	})

	if err != nil {
		logger.Get().Error("MockExtFsHandler", slog.String("err", err.Error()))
	} else {
		datas := string(data)
		stat.Data = &datas
	}

	// Записать данные в статистику
	container.GetBonusStatisticRepository().Insert(&stat)

	return s.bonus.MoneyAvailable, nil
}

func (s *MockExtFsHandler) ExternFreeSpinsCancel(bonus_id string) error {
	logger.Get().Warn("MockExtFsHandler ExternFreeSpinsCancel called, but no action taken", slog.String("bonus_id", bonus_id))
	return nil
}

func (s *MockExtFsHandler) ExternFreeSpinsCreate(bonus_id string) error {
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
	logger.Get().Warn("MockExtFsHandler ExternFreeSpinsCreate called, but no action taken", slog.String("bonus_id", bonus_id), slog.Float64("max_bet", maxBet))
	return nil
}
