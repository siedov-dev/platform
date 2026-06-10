package extfshandlers

import (
	"database/sql"
	"encoding/json"
	"errors"
	"fmt"
	"kbonus/internal/db/models"
	"kbonus/internal/fsapi/slegapi"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"log/slog"
	"net/http"
	"strconv"
	"sync"
	"time"
)

// NewSlegExtFsHandler создает новый обработчик внешних free spins для игровой системы Sleg.
func NewSlegExtFsHandler(bonus *models.UserBonus) stypes.BonusExternalFsHandler {
	return &SlegExtFsHandler{
		bonus: bonus,
	}
}

// SlegExtFsHandler реализует интерфейс обработки внешних free spins для Sleg.
// Использует мьютекс для потокобезопасной работы с бонусом.
type SlegExtFsHandler struct {
	bonus *models.UserBonus // Ссылка на бонус пользователя
	mut   sync.RWMutex      // Мьютекс для защиты данных бонуса
}

// ExternFreeSpinBet вызывается при ставке free spin.
// Возвращает доступную сумму бонуса.
func (s *SlegExtFsHandler) ExternFreeSpinBet(amount float64) (float64, error) {
	s.mut.Lock()
	defer s.mut.Unlock()
	if s.bonus.SpinsAvailable > 1 {
		s.bonus.SpinsAvailable -= 1
	}
	return s.bonus.MoneyAvailable, nil
}

// ExternFreeSpinWin вызывается при выигрыше во free spin.
// Увеличивает сумму доступных средств и общий выигрыш по бонусу.
func (s *SlegExtFsHandler) ExternFreeSpinWin(amount float64) (float64, error) {
	s.mut.Lock()
	defer s.mut.Unlock()

	s.bonus.MoneyAvailable += amount
	s.bonus.TotalWin += amount
	return s.bonus.MoneyAvailable, nil
}

// ExternFreeSpinsCompleted вызывается при завершении всех free spins.
// Обновляет состояние бонуса, пересчитывает wager, снимает ограничения по играм и сохраняет статистику.
func (s *SlegExtFsHandler) ExternFreeSpinsCompleted(amount float64) (float64, error) {
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
func (s *SlegExtFsHandler) ExternFreeSpinsCancel(bonus_id string) error {
	s.mut.Lock()
	defer s.mut.Unlock()
	_, code, err := slegapi.FreeSpinsCancel(bonus_id)
	if err != nil {
		return fmt.Errorf("error canceling free spins: %w", err)
	}
	if code != http.StatusOK {
		return fmt.Errorf("unexpected status code %d while canceling free spins", code)
	}
	return nil
}

func (s *SlegExtFsHandler) ExternFreeSpinsCreate(bonus_id string) error {
	s.mut.Lock()
	defer s.mut.Unlock()
	// Определяем переменную для хранения игр бонуса
	var bonusGame string
	if s.bonus.GameID.Valid && s.bonus.GameID.String != "" {
		bonusGame = s.bonus.GameID.String
	} else if !s.bonus.AllowedGames.Valid || s.bonus.AllowedGames.String != "" {
		return errors.New("cannot create free spins for bonus with no games set")
	} else {
		gamesAllowed := make(map[string][]string)
		err := json.Unmarshal([]byte(s.bonus.AllowedGames.String), &gamesAllowed)
		if err != nil {
			return err
		}
		if len(gamesAllowed["sleg"]) == 0 {
			return errors.New("cannot create free spins for bonus with no games set")
		}
		bonusGame = gamesAllowed["sleg"][0]
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
	playerId := "bonus" + strconv.Itoa(int(s.bonus.UserID)) + s.bonus.GetCurrencyName()

	activeTo := int(s.bonus.ActiveTo.Time.Unix())
	_, code, err := slegapi.FreeSpinsSet(
		bonus_id,
		bonusGame,
		playerId,
		s.bonus.SpinsAvailable,
		s.bonus.GetCurrencyName(),
		&activeTo,
		maxBet,
	)
	if err != nil {
		return fmt.Errorf("error creating free spins: %w", err)
	}
	if code != http.StatusOK {
		return fmt.Errorf("unexpected status code %d while creating free spins", code)
	}
	return nil
}
