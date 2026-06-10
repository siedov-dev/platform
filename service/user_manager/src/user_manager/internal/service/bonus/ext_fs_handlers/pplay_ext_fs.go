package extfshandlers

import (
	"database/sql"
	"encoding/json"
	"log/slog"
	"sync"
	"time"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/repository"
	"user-manager/internal/stypes"
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
	repository.GetBonusStatisticRepository().Insert(&stat)

	return s.bonus.MoneyAvailable, nil
}
