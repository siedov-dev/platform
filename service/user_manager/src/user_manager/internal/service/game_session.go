package service

import (
	"errors"
	"log/slog"
	"sync"
	"time"
	"user-manager/internal/db/models"
	"user-manager/internal/logger"
	"user-manager/internal/server/requests"
	"user-manager/internal/service/container"
	"user-manager/internal/stypes"
	"user-manager/internal/stypes/currency"
)

// GameSession структура для хранения информации об игровой сессии
type GameSession struct {
	Stats         models.UserGameStats // Хранит статистику текущей игровой сессии пользователя
	GameStartData stypes.GameData      // Содержит исходные данные при старте игровой сессии
	activeBonus   stypes.IBonus        // Текущий активированный бонус в игровой сессии
	BetWagerBonus stypes.IBonus        // Бонус, применяемый к ставкам в игре
	mut           sync.RWMutex         // Мьютекс для обеспечения потокобезопасности при работе с данными
}

// CreateGameSession создает новую игровую сессию
//
//	Параметры:
//		 - user_id - уникальный идентификатор пользователя в системе
//		 - curr - код валюты для игровой сессии
//		 - srq - структура с параметрами запроса на создание игровой сессии
//		 - gd - структура с начальными данными игры
//
//	 Возвращает:
//	  - *GameSession - указатель на созданную игровую сессию
//	  - error - ошибка при создании сессии
func (gs *GameSession) CreateGameSession(user_id int64, curr string, srq requests.StartGameRequest, gd stypes.GameData) (*GameSession, error) {
	gs = &GameSession{}
	now := time.Now()
	var session_id string = srq.SessionId

	// Инициализация основных параметров игровой сессии
	gs.Stats.SessionID = &session_id // Устанавливаем ID сессии
	gs.Stats.UserID = user_id        // Устанавливаем ID пользователя
	gs.Stats.Currency = curr         // Устанавливаем валюту
	gs.Stats.GameID = &gd.GameID     // Устанавливаем ID игры
	gs.Stats.Group = &srq.GameGroup  // Устанавливаем группу игры
	gs.Stats.System = &gd.System     // Устанавливаем систему
	gs.Stats.CreatedAt = &now        // Устанавливаем время создания
	gs.Stats.UpdatedAt = &now        // Устанавливаем время обновления
	gs.Stats.LoginDate = &now        // Устанавливаем время входа
	gs.GameStartData = gd            // Сохраняем начальные данные игры

	user, err := GetUser(stypes.UserID(user_id))
	if err != nil {
		return nil, err
	}
	c, err := currency.GetCurrencyID(curr)
	if err != nil {
		return nil, err
	}
	uw, err := user.GetWallet(c)
	if err != nil {
		return nil, err
	}

	// Загрузка бонуса для бонусного режима игры
	if srq.GameMode == GameModeBonus && gs.GameStartData.BonusID > 0 {
		logger.Get().Info("CreateGameSession", slog.Int64("loading bonus id", gs.GameStartData.BonusID))
		gs.activeBonus, err = container.GetBonusFactory().LoadBonus(gs.GameStartData.BonusID, uw, gs.GameStartData.WagerPrc)
		if err != nil {
			logger.Get().Error("CreateGameSession", slog.String("err", err.Error()))
			return nil, err
		}
		gs.Stats.BonusID = &gs.GameStartData.BonusID
	}

	// Загрузка бонусов для реального режима игры (эти бонусы больше не используются)
	if srq.GameMode == GameModeReal && gs.GameStartData.FilthyBonusID > 0 {
		logger.Get().Info("CreateGameSession", slog.Int64("loading filthy  bet wager bonus id", gs.GameStartData.FilthyBonusID))
		gs.BetWagerBonus, err = container.GetBonusFactory().LoadBonus(gs.GameStartData.FilthyBonusID, uw, gs.GameStartData.WagerPrc)
		if err != nil {
			gs.BetWagerBonus = nil
			logger.Get().Error("CreateGameSession", slog.String("err", err.Error()))
		}
	} else if srq.GameMode == GameModeReal && gs.GameStartData.NonDepBonusID > 0 {
		logger.Get().Info("CreateGameSession", slog.Int64("loading non dep bet wager bonus id", gs.GameStartData.NonDepBonusID))
		gs.BetWagerBonus, err = container.GetBonusFactory().LoadBonus(gs.GameStartData.NonDepBonusID, uw, gs.GameStartData.WagerPrc)
		if err != nil {
			gs.BetWagerBonus = nil
			logger.Get().Error("CreateGameSession", slog.String("err", err.Error()))
		}
	}

	return gs, nil
}

// Save сохраняет или обновляет текущую статистику игровой сессии в базе данных
// При первом сохранении создается новая запись
// При последующих вызовах обновляет существующую запись
func (gs *GameSession) Save() {
	if gs == nil {
		logger.Get().Error("Save game stats", slog.String("err", "no game stats to save"))
		return
	}

	now := time.Now()
	gs.Stats.UpdatedAt = &now

	if gs.Stats.ID == 0 {
		id, err := container.GetGameSessionRepository().Insert(&gs.Stats)
		if err != nil {
			logger.Get().Error("Insert game stats", slog.String("error", err.Error()))
		}
		gs.Stats.ID = id
	} else {
		err := container.GetGameSessionRepository().Update(&gs.Stats)
		if err != nil {
			logger.Get().Error("update game stats", slog.String("error", err.Error()))
		}
	}
}

// AddTotalBet добавляет сумму к общей ставке
// Увеличивает значения FullTotalBet, RealTotalBet и TotalBet на указанную сумму
// amount - сумма, которую нужно добавить к общей ставке
func (gs *GameSession) AddTotalBet(amount float64) {
	gs.mut.Lock()
	defer gs.mut.Unlock()

	gs.Stats.FullTotalBet += amount
	gs.Stats.RealTotalBet += amount
	gs.Stats.TotalBet += amount
}

// AddTotalWin добавляет сумму к общему выигрышу
// Увеличивает значения FullTotalWin, RealTotalWin и TotalWin на указанную сумму
// amount - сумма выигрыша, которую нужно добавить
func (gs *GameSession) AddTotalWin(amount float64) {
	gs.mut.Lock()
	defer gs.mut.Unlock()

	gs.Stats.FullTotalWin += amount
	gs.Stats.RealTotalWin += amount
	gs.Stats.TotalWin += amount
}

// AddBonusBet добавляет бонусную ставку к общей сумме ставок
// Увеличивает только значение FullTotalBet на величину бонусной ставки
// amount - сумма бонусной ставки
func (gs *GameSession) AddBonusBet(amount float64) {
	gs.mut.Lock()
	defer gs.mut.Unlock()

	gs.Stats.FullTotalBet += amount
}

// AddBonusWin добавляет бонусный выигрыш к общей сумме выигрышей
// Увеличивает только значение FullTotalWin на величину бонусного выигрыша
// amount - сумма бонусного выигрыша
func (gs *GameSession) AddBonusWin(amount float64) {
	gs.mut.Lock()
	defer gs.mut.Unlock()

	gs.Stats.FullTotalWin += amount
}

// SetRealWin устанавливает сумму реального выигрыша
// Увеличивает значение RealTotalWin на указанную сумму
// amount - сумма реального выигрыша
func (gs *GameSession) SetRealWin(amount float64) {
	gs.mut.Lock()
	defer gs.mut.Unlock()

	gs.Stats.RealTotalWin += amount
}

func (gs *GameSession) GetActiveBonus() (b stypes.IBonus, err error) {
	// загружаем бонус каждый раз, т.к. бонус мог измениться в другой сессии
	user, err := GetUser(stypes.UserID(gs.Stats.UserID))
	if err != nil {
		return nil, err
	}
	c, err := currency.GetCurrencyID(gs.Stats.Currency)
	if err != nil {
		return nil, err
	}
	uw, err := user.GetWallet(c)
	if err != nil {
		return nil, err
	}
	// Загрузка бонуса для бонусного режима игры
	if gs.GameStartData.BonusID > 0 {
		b, err = container.GetBonusFactory().LoadBonus(gs.GameStartData.BonusID, uw, gs.GameStartData.WagerPrc)
		if err != nil {
			return nil, err
		}
		return b, nil
	}

	return nil, errors.New("no active bonus")
}

func (gs *GameSession) GetBetWagerBonus() (b stypes.IBonus, err error) {
	// Загрузка бонусов для реального режима игры (эти бонусы больше не используются)
	// загружаем бонус каждый раз, т.к. бонус мог измениться в другой сессии
	user, err := GetUser(stypes.UserID(gs.Stats.UserID))
	if err != nil {
		return nil, err
	}
	c, err := currency.GetCurrencyID(gs.Stats.Currency)
	if err != nil {
		return nil, err
	}
	uw, err := user.GetWallet(c)
	if err != nil {
		return nil, err
	}
	if gs.GameStartData.FilthyBonusID > 0 {
		b, err = container.GetBonusFactory().LoadBonus(gs.GameStartData.FilthyBonusID, uw, gs.GameStartData.WagerPrc)
		if err != nil {
			b = nil
			logger.Get().Error("CreateGameSession", slog.String("err", err.Error()))
		}
		return b, nil
	} else if gs.GameStartData.NonDepBonusID > 0 {
		logger.Get().Info("CreateGameSession", slog.Int64("loading non dep bet wager bonus id", gs.GameStartData.NonDepBonusID))
		b, err = container.GetBonusFactory().LoadBonus(gs.GameStartData.NonDepBonusID, uw, gs.GameStartData.WagerPrc)
		if err != nil {
			b = nil
			logger.Get().Error("CreateGameSession", slog.String("err", err.Error()))
		}
		return b, nil
	}
	return nil, errors.New("no bet wager bonus")
}

// DropSessionBonus сбрасывает активный бонус игровой сессии
func (gs *GameSession) DropSessionBonus() {
	gs.mut.Lock()
	defer gs.mut.Unlock()
	gs.activeBonus = nil
	gs.Stats.BonusID = nil
}
