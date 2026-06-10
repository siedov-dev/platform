// Пакет bonus реализует бизнес-логику работы с бонусами пользователя.
package bonus

import (
	"errors"
	"fmt"
	"log/slog"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/repository"
	"user-manager/internal/service/bonus/actualizers"
	betwagerhandlers "user-manager/internal/service/bonus/bet_wager_handlers"
	bonushandlers "user-manager/internal/service/bonus/bonus_handlers"
	extfshandlers "user-manager/internal/service/bonus/ext_fs_handlers"
	"user-manager/internal/stypes"
)

// Bonus — основная структура для работы с бонусом пользователя.
// Содержит данные о бонусе, обработчики и вспомогательные объекты.
type Bonus struct {
	UserBonus   *models.UserBonus      // Модель бонуса пользователя из БД
	actualizer  stypes.BonusActualizer // Актуализатор состояния бонуса
	GWallet     stypes.RealMoneyWallet // Кошелек пользователя для реальных денег
	WagerPrc    int16                  // Процент вейджера
	handler     stypes.BonusHandler    // Обработчик бонуса
	BackupState models.UserBonus       // Резервная копия состояния бонуса
	BetTrID     string                 // Идентификатор транзакции ставки
	wagerer     stypes.BonusBetWagerer // Обработчик ставок для бонуса
}

// LoadBonus загружает бонус по ID, инициализирует обработчики и актуализатор.
// Возвращает структуру Bonus или ошибку.
func LoadBonus(id int64, w stypes.RealMoneyWallet, WagerPrc int16) (*Bonus, error) {
	b := &Bonus{}
	ub, err := repository.GetUserBonusRepository().GetByID(id)
	if err != nil {
		logger.Get().Error("Load bonus", slog.String("err", err.Error()))
		return nil, err
	}
	b.UserBonus = ub
	b.GWallet = w
	b.WagerPrc = WagerPrc

	b.handler, err = getBonusHandler(b)
	if err != nil {
		return nil, err
	}

	b.wagerer = betwagerhandlers.GetBetWagerer(b.UserBonus, w)

	b.actualizer, err = actualizers.GetActualizer(ub, w)
	return b, err
}

// GetBonusHandler возвращает обработчик бонуса.
func (b *Bonus) GetBonusHandler() stypes.BonusHandler {
	return b.handler
}

// GetBetWagerer возвращает обработчик ставок для бонуса.
// Используется для бонусов отыгрываемых на реальные деньги.
// Эти бонусы не используются в данный момент.
func (b *Bonus) GetBetWagerer() stypes.BonusBetWagerer {
	return b.wagerer
}

// IsActive проверяет, активен ли бонус.
func (b *Bonus) IsActive() bool {
	return b.UserBonus.Status == stypes.STATUS_ACTIVE
}

// Actualize актуализирует состояние бонуса.
// Возвращает сумму выигрыша при завешении бонуса.
func (b *Bonus) Actualize() (win float64) {
	return b.actualizer.Actualize()
}

// Save сохраняет текущее состояние бонуса в репозитории.
func (b *Bonus) Save() error {
	return repository.GetUserBonusRepository().Update(b.UserBonus)
}

// GetStatusPad возвращает статус бонуса для отображения.
func (b *Bonus) GetStatusPad() stypes.BonusStatusPad {
	return b.actualizer.GetStatus()
}

// GetExtFsHandler возвращает обработчик внешних free spins для указанной игровой системы.
func (b *Bonus) GetExtFsHandler(game_system string) (stypes.BonusExternalFsHandler, error) {
	switch game_system {
	case "sleg":
		return extfshandlers.NewSlegExtFsHandler(b.UserBonus), nil
	case "pplay":
		return extfshandlers.NewPPlayExtFsHandler(b.UserBonus), nil
	case "qt":
		return extfshandlers.NewQTExtFsHandler(b.UserBonus), nil
	}
	return nil, errors.New("game system not operable")
}

// getBonusHandler выбирает и возвращает обработчик бонуса в зависимости от его типа.
func getBonusHandler(b *Bonus) (stypes.BonusHandler, error) {
	switch b.UserBonus.Type {
	case stypes.TYPE_NON_DEPOSIT:
		return bonushandlers.NewNoBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_FILTHY_FS_WASH_OFF:
		return bonushandlers.NewNoBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_SUPER_FREE_SPINS:
		return bonushandlers.NewFsTotalWinBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_SUPER_FREE_SPINS_PLAYBACK:
		return bonushandlers.NewFsTotalWinBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_FILTHY_FS:
		return bonushandlers.NewFsTotalWinBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_BEZDEP:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_DEPOSIT_WAGER:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_DEPOSIT_PRIZE:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_DEPOSIT_FFS_TNT:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_NON_DEPOSIT_WAGER:
		return bonushandlers.NewNonDepWagerHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_CHOOSE_WAGER_FS2:
		return bonushandlers.NewFsTotalWinBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_PLUS_MINUS:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_PLUS_MINUS_DEP:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_SHARED_MONEY:
		return bonushandlers.NewSharedMoneyBonusHanlder(b.UserBonus, b.WagerPrc, b.GWallet), nil
	case stypes.TYPE_SHARED_MONEY_FS:
		return bonushandlers.NewSharedMoneyBonusHanlder(b.UserBonus, b.WagerPrc, b.GWallet), nil
	case stypes.TYPE_PPLAY_SMART_FS:
		return bonushandlers.NewSmartBonusHanlder(b.UserBonus, b.WagerPrc, b.GWallet), nil
	case stypes.TYPE_SMART_WAGER:
		return bonushandlers.NewSmartBonusHanlder(b.UserBonus, b.WagerPrc, b.GWallet), nil
	case stypes.TYPE_PPLAY_SFS_DYNAMIC:
		return bonushandlers.NewSmartBonusHanlder(b.UserBonus, b.WagerPrc, b.GWallet), nil
	case stypes.TYPE_PS_SFS_DYNAMIC:
		return bonushandlers.NewSmartBonusHanlder(b.UserBonus, b.WagerPrc, b.GWallet), nil
	case stypes.TYPE_SLEG_SMART:
		return bonushandlers.NewSmartBonusHanlder(b.UserBonus, b.WagerPrc, b.GWallet), nil
	case stypes.TYPE_PS_SMART:
		return bonushandlers.NewSmartBonusHanlder(b.UserBonus, b.WagerPrc, b.GWallet), nil
	case stypes.TYPE_SLEG_FS_MENU:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_PP_FS_MENU:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_PS_FS_MENU:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_SLEG_FS:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_PS_FS:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_SLEG_NONDEP:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_PP_NONDEP:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_PS_NON_DEP:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_NON_DEP:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_QT_FS_WAGER:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	case stypes.TYPE_QT_SMART_FS:
		return bonushandlers.NewSmartBonusHanlder(b.UserBonus, b.WagerPrc, b.GWallet), nil
	case stypes.TYPE_MULTI_DAYS:
		return bonushandlers.NewDefaultBonusHanlder(b.UserBonus, b.WagerPrc), nil
	}
	return nil, fmt.Errorf("no bonus handler for type %v", b.UserBonus.Type)
}
