package entities

import (
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"slices"
	"sync"
)

type UserBonuses struct {
	userID           stypes.UserID    // Идентификатор пользователя
	bonuses          []stypes.BonusID // Список доступных бонусов пользователя по ID бонуса
	completedBonuses []stypes.BonusID // Список завершенных бонусов пользователя по ID бонуса
	mx               sync.RWMutex     // Мьютекс для безопасного доступа к спискам бонусов
	loaded           bool             // Флаг, указывающий, были ли загружены бонусы пользователя
}

func NewUserBonuses(userID stypes.UserID) *UserBonuses {
	return &UserBonuses{
		userID:           userID,
		bonuses:          make([]stypes.BonusID, 0),
		completedBonuses: make([]stypes.BonusID, 0),
	}
}

func (ub *UserBonuses) GetBonusIDs() []stypes.BonusID {
	ub.LoadIfNotLoaded()
	ub.mx.RLock()
	defer ub.mx.RUnlock()
	return ub.bonuses
}

func (ub *UserBonuses) GetCompletedBonuses() []stypes.BonusID {
	ub.LoadIfNotLoaded()
	ub.mx.RLock()
	defer ub.mx.RUnlock()
	return ub.completedBonuses
}

func (ub *UserBonuses) GetUserID() stypes.UserID {
	return ub.userID
}

func (ub *UserBonuses) GetBonusCount() int {
	ub.LoadIfNotLoaded()
	ub.mx.RLock()
	defer ub.mx.RUnlock()
	return len(ub.bonuses)
}

func (ub *UserBonuses) AddBonus(bonusID stypes.BonusID) {
	ub.LoadIfNotLoaded()
	ub.mx.Lock()
	defer ub.mx.Unlock()
	if slices.Contains(ub.bonuses, bonusID) {
		return
	}
	ub.bonuses = append(ub.bonuses, bonusID)
}

func (ub *UserBonuses) RemoveBonus(bonusID stypes.BonusID) {
	ub.mx.Lock()
	defer ub.mx.Unlock()
	for i, id := range ub.bonuses {
		if id == bonusID {
			ub.bonuses = append(ub.bonuses[:i], ub.bonuses[i+1:]...)
			break
		}
	}
}

func (ub *UserBonuses) AddCompletedBonus(bonusID stypes.BonusID) {
	ub.mx.Lock()
	defer ub.mx.Unlock()
	for _, id := range ub.completedBonuses {
		if id == bonusID {
			return
		}
	}
	ub.completedBonuses = append(ub.completedBonuses, bonusID)
}

func (ub *UserBonuses) LoadIfNotLoaded() {
	ub.mx.Lock()
	defer ub.mx.Unlock()
	if !ub.loaded {
		av_bonuses, err := container.GetUserBonusRepository().GetActiveByUserId(ub.userID)
		if err != nil {
			logger.Get().Error("Failed to load user bonuses", "userID", ub.userID, "error", err)
			return
		}
		for _, bonus := range av_bonuses {
			ub.bonuses = append(ub.bonuses, bonus.ID)
		}
		completed, err := container.GetUserBonusRepository().GetCompletedByUserId(ub.userID, 10)
		if err != nil {
			logger.Get().Error("Failed to load completed user bonuses", "userID", ub.userID, "error", err)
		} else {
			for _, bonus := range completed {
				ub.completedBonuses = append(ub.completedBonuses, bonus.ID)
			}
		}
		ub.loaded = true
	}
}
