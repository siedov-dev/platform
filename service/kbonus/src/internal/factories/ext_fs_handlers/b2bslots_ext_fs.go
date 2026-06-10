package extfshandlers

import (
	"database/sql"
	"encoding/json"
	"kbonus/internal/db/models"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"log/slog"
	"sync"
	"time"
)

// NewB2BSlotsExtFsHandler создает новый обработчик внешних free spins для игровой системы B2BSlots.
func NewB2BSlotsExtFsHandler(bonus *models.UserBonus) stypes.BonusExternalFsHandler {
	return &B2BSlotsExtFsHandler{
		bonus: bonus,
	}
}

// B2BSlotsExtFsHandler реализует интерфейс обработки внешних free spins для B2BSlots.
// Использует мьютекс для потокобезопасной работы с бонусом.
type B2BSlotsExtFsHandler struct {
	bonus *models.UserBonus // Ссылка на бонус пользователя
	mut   sync.RWMutex      // Мьютекс для защиты данных бонуса
}

// ExternFreeSpinBet вызывается при ставке free spin.
// Возвращает доступную сумму бонуса.
func (s *B2BSlotsExtFsHandler) ExternFreeSpinBet(amount float64) (float64, error) {
	s.mut.Lock()
	defer s.mut.Unlock()
	if s.bonus.SpinsAvailable > 1 {
		s.bonus.SpinsAvailable -= 1
	}
	return s.bonus.MoneyAvailable, nil
}

// ExternFreeSpinWin вызывается при выигрыше во free spin.
// Увеличивает сумму доступных средств и общий выигрыш по бонусу.
func (s *B2BSlotsExtFsHandler) ExternFreeSpinWin(amount float64) (float64, error) {
	s.mut.Lock()
	defer s.mut.Unlock()

	s.bonus.MoneyAvailable += amount
	s.bonus.TotalWin += amount
	return s.bonus.MoneyAvailable, nil
}

// ExternFreeSpinsCompleted вызывается при завершении всех free spins.
// Обновляет состояние бонуса, пересчитывает wager, снимает ограничения по играм и сохраняет статистику.
func (s *B2BSlotsExtFsHandler) ExternFreeSpinsCompleted(amount float64) (float64, error) {
	s.mut.Lock()
	defer s.mut.Unlock()

	// Сбросить количество доступных спинов и общий bet
	s.bonus.SpinsAvailable = -1
	s.bonus.TotalBet = 0
	// Пересчитать сумму для отыгрыша
	s.bonus.WagerToComplete = s.bonus.MoneyAvailable * float64(s.bonus.Multiplier)

	// Если требуется, снять ограничения по играм для бонуса
	if dropGamesBonuses[s.bonus.Type] {
		s.bonus.AllowedGames = sql.NullString{}
		s.bonus.GameID = sql.NullString{}
		s.bonus.GameProvider = sql.NullString{}
		s.bonus.GamesLimited = false
	}

	// Если требуется, пересчитать максимальный выигрыш после завершения free spins
	if recalcMaxWinAfterFsBonuses[s.bonus.Type] {
		max_win := s.bonus.GetMaxXWin()
		if max_win > 0 {
			s.bonus.MaxWin = sql.NullFloat64{
				Float64: s.bonus.MoneyAvailable * max_win,
				Valid:   true,
			}
		}
	}

	now := time.Now()

	// Формируем статистику по завершению free spins
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
	// Сохраняем дополнительные данные в статистику (доступные деньги и wager)
	data, err := json.Marshal(map[string]float64{
		"money": s.bonus.MoneyAvailable,
		"wager": s.bonus.WagerToComplete,
	})

	if err != nil {
		logger.Get().Error("SlegExtFsHandler", slog.String("err", err.Error()))
	} else {
		datas := string(data)
		stat.Data = &datas
	}
	// Сохраняем статистику в репозитории
	container.GetBonusStatisticRepository().Insert(&stat)

	return s.bonus.MoneyAvailable, nil
}

// ExternFreeSpinsCancel вызывается при отмене free spins.
func (s *B2BSlotsExtFsHandler) ExternFreeSpinsCancel(bonus_id string) error {
	return nil
}

func (s *B2BSlotsExtFsHandler) ExternFreeSpinsCreate(bonus_id string) error {
	return nil
}
