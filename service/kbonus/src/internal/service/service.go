package service

import (
	"context"
	"fmt"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/service/entities"
	"kbonus/internal/stypes"
	"kbonus/internal/utils/gstructs"
	"sync"
	"time"
)

var (
	ctx              context.Context
	bonuses          gstructs.TimeoutMap[stypes.BonusID, *entities.Bonus]
	userBonuses      gstructs.TimeoutMap[stypes.UserID, *entities.UserBonuses]
	availableBonuses map[int64]*entities.AvailableBonuses
	ubMx             sync.RWMutex // Мьютекс для синхронизации доступа к пользовательским бонусам
	avbMx            sync.RWMutex // Мьютекс для синхронизации доступа к доступным бонусам
)

func MustInit(c context.Context) {
	logger.Get().Info("Initializing service...")

	ctx = c
	bonuses = gstructs.NewTimeoutMap[int64, *entities.Bonus](60*5, ctx)                   // 5 minutes timeout for bonuses
	userBonuses = gstructs.NewTimeoutMap[stypes.UserID, *entities.UserBonuses](60*5, ctx) // 5 minutes timeout for user bonuses
	availableBonuses = make(map[int64]*entities.AvailableBonuses)

	bonuses.SetClearFunc(func(id stypes.BonusID, bonus *entities.Bonus) {
		logger.Get().Info("Unloading bonus", "bonusID", id)
		_, err := bonus.Save()
		if err != nil {
			logger.Get().Error("Failed to save bonus on timeout", "bonusID", id, "error", err)
		}
	})

	err := Reload()
	if err != nil {
		logger.Get().Error("Failed to initialize service", "error", err)
		panic(err)
	}

	ticker := time.NewTicker(1 * time.Minute)
	go func() {
		for {
			select {
			case <-ticker.C:
				logger.DailyLog.Flush()
			case <-ctx.Done():
				ticker.Stop()
				return
			}
		}
	}()

	logger.Get().Info("Service successfully initialized")
}

func Reload() error {
	avbMx.Lock()
	defer avbMx.Unlock()
	ubMx.Lock()
	defer ubMx.Unlock()

	logger.Get().Info("Reloading service...")

	bonuses.Clear()
	userBonuses.Clear()

	bonuses = gstructs.NewTimeoutMap[int64, *entities.Bonus](60*5, ctx)                   // 5 minutes timeout for bonuses
	userBonuses = gstructs.NewTimeoutMap[stypes.UserID, *entities.UserBonuses](60*5, ctx) // 5 minutes timeout for user bonuses
	availableBonuses = make(map[int64]*entities.AvailableBonuses)

	// Загружаем конфигурации валют бонусов
	if err := container.GetBonusCurrencyConfigDataProvider().LoadBonusCurrencyConfigs(); err != nil {
		logger.Get().Error("Failed to load bonus currency configs", "error", err)
		return err
	}
	// Загружаем параметры бонусов
	if err := container.GetBonusParamsDataProvider().Reload(); err != nil {
		logger.Get().Error("Failed to reload bonus params", "error", err)
		return err
	}
	// Загружаем параметры многодневных бонусов
	if err := container.GetMultiDaysBonusPramsDataProvider().Reload(); err != nil {
		logger.Get().Error("Failed to reload multi-days bonus params", "error", err)
		return err
	}
	// Загружаем паттерны бонусов
	if err := container.GetBonusPatternsDataProvider().Reload(); err != nil {
		logger.Get().Error("Failed to reload bonus patterns", "error", err)
		return err
	}
	// Загружаем доступные бонусы
	av_bonuses, err := container.GetBonusAvailableRepository().GetAllAvailable()
	if err != nil {
		logger.Get().Error("Failed to reload available bonuses", "error", err)
		return err
	}
	for _, avb := range av_bonuses {
		av_bonus := &entities.AvailableBonuses{}
		av_bonus.LoadFromModel(avb)
		availableBonuses[avb.ID] = av_bonus
	}
	logger.Get().Info("Service reloaded")
	return nil
}

func ReloadBonusAvailable(id int64) error {
	avbMx.Lock()
	defer avbMx.Unlock()

	logger.Get().Info("Reloading available bonus...")
	delete(availableBonuses, id)
	avb, err := container.GetBonusAvailableRepository().GetByID(uint64(id))
	if err != nil {
		logger.Get().Error("Failed to reload available bonus", "error", err)
		return err
	}
	if avb == nil {
		return fmt.Errorf("available bonus not found")
	}
	av_bonus := &entities.AvailableBonuses{}
	av_bonus.LoadFromModel(avb)
	availableBonuses[id] = av_bonus
	logger.Get().Info("Available bonus reloaded")
	return nil
}

func ReloadBonusIfCached(id stypes.BonusID, user_id stypes.UserID) error {
	ubMx.Lock()
	defer ubMx.Unlock()
	if _, ok := bonuses.Get(id); ok {
		logger.Get().Info("Reloading cached bonus", "bonusID", id)
		bonus, err := entities.LoadBonus(id, 100) // WagerPrc не важен при загрузке
		if err != nil {
			logger.Get().Error("Failed to reload cached bonus", "bonusID", id, "error", err)
			return err
		}
		bonuses.Set(id, bonus) // Обновляем время жизни в кеше
	}
	if ub, ok := userBonuses.Get(user_id); ok {
		ub.AddBonus(id)
	}
	return nil
}

func GetBonus(id stypes.BonusID, WagerPrc int16) (*entities.Bonus, error) {
	if b, ok := bonuses.Get(id); ok {
		return b, nil
	}
	b, err := entities.LoadBonus(id, WagerPrc)
	if err != nil {
		return nil, err
	}
	bonuses.Set(id, b)
	return b, nil
}

func GetBonusAvailable(id stypes.BonusID) (*entities.AvailableBonuses, error) {
	avbMx.RLock()
	defer avbMx.RUnlock()
	if ab, ok := availableBonuses[id]; ok {
		return ab, nil
	}
	return nil, fmt.Errorf("available bonus not found")
}

func AddBonusToCache(bonus *entities.Bonus) {
	bonuses.Set(bonus.GetID(), bonus)
}

func GetUserBonuses(userID stypes.UserID) (*entities.UserBonuses, error) {
	ubMx.Lock()
	if ub, ok := userBonuses.Get(userID); ok {
		ubMx.Unlock()
		return ub, nil
	}
	ub := entities.NewUserBonuses(userID)
	userBonuses.Set(userID, ub)
	ubMx.Unlock()
	return ub, nil
}

func GetAvailableBonusesParams(userID stypes.UserID, currency_id int8) ([]stypes.BonusAvailableParams, error) {
	avbMx.RLock()
	defer avbMx.RUnlock()
	params := []stypes.BonusAvailableParams{}
	for _, ab := range availableBonuses {
		if !ab.IsActive() {
			delete(availableBonuses, ab.GetID())
			continue
		}
		if ab.IsUserAllowed(userID) {
			if ab.GetCurrencyID() == 0 || currency_id == 0 || ab.GetCurrencyID() != currency_id {
				params = append(params, ab.GetParams())
			}
		}
	}
	return params, nil
}

func Stop() {
	// Здесь должна быть логика остановки сервиса
	// Например, закрытие соединений, отписка от событий и т.д.
	bonuses.Clear()
	userBonuses.Clear()
	logger.Get().Info("Service stopped")
}
