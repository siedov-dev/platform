package entities

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"kbonus/internal/db/models"
	"kbonus/internal/logger"
	"kbonus/internal/service/container"
	"kbonus/internal/stypes"
	"kbonus/internal/utils/currencyid"
	"log/slog"
	"strconv"
	"sync"
	"time"
)

// Bonus — основная структура для работы с бонусом пользователя.
// Содержит данные о бонусе, обработчики и вспомогательные объекты.
type Bonus struct {
	UserBonus    *models.UserBonus        // Модель бонуса пользователя из БД
	actualizer   stypes.BonusActualizer   // Актуализатор состояния бонуса
	GWallet      stypes.RealMoneyWallet   // Кошелек пользователя для реальных денег
	WagerPrc     int16                    // Процент вейджера
	handler      stypes.BonusHandler      // Обработчик бонуса
	BackupState  models.UserBonus         // Резервная копия состояния бонуса
	BetTrID      string                   // Идентификатор транзакции ставки
	wagerer      stypes.BonusBetWagerer   // Обработчик ставок для бонуса
	infoProvider stypes.BonusInfoProvider // Провайдер информации о бонусе
	mx           sync.RWMutex
}

// LoadBonus загружает бонус по ID, инициализирует обработчики и актуализатор.
// Возвращает структуру Bonus или ошибку.
func LoadBonus(id int64, WagerPrc int16) (*Bonus, error) {
	b := &Bonus{}
	ub, err := container.GetUserBonusRepository().GetByID(id)
	if err != nil {
		logger.Get().Error("Load bonus", slog.String("err", err.Error()))
		return nil, err
	}
	wallet := container.GetRealMoneyWalletFactory().GetMoneyWallet(ub.UserID, ub.Currency)
	b.UserBonus = ub
	b.BackupState = *ub // Сохраняем резервную копию состояния бонуса
	b.GWallet = wallet
	b.WagerPrc = WagerPrc

	b.handler, err = getBonusHandler(b)
	if err != nil {
		return nil, err
	}

	b.wagerer = container.GetBetWagererFactory().GetBetWagerer(b.UserBonus, wallet)

	b.actualizer, err = container.GetBonusActualizerFactory().GetActualizer(ub, wallet)
	b.infoProvider = container.GetBonusInfoProviderFactory().GetBonusInfoProvider(ub)
	return b, err
}

func NewBonusFromModel(ub *models.UserBonus, WagerPrc int16) (*Bonus, error) {
	b := &Bonus{}
	b.UserBonus = ub
	b.BackupState = *ub
	wallet := container.GetRealMoneyWalletFactory().GetMoneyWallet(ub.UserID, ub.Currency)
	b.GWallet = wallet
	b.WagerPrc = WagerPrc

	handler, err := getBonusHandler(b)
	if err != nil {
		return nil, err
	}
	b.handler = handler

	b.wagerer = container.GetBetWagererFactory().GetBetWagerer(b.UserBonus, wallet)

	b.actualizer, err = container.GetBonusActualizerFactory().GetActualizer(ub, wallet)
	b.infoProvider = container.GetBonusInfoProviderFactory().GetBonusInfoProvider(ub)
	return b, err
}

// UpdateModel обновляет модель бонуса внутри структуры Bonus.
func (b *Bonus) UpdateModel(ub *models.UserBonus) (*Bonus, error) {
	b.mx.Lock()
	defer b.mx.Unlock()
	if ub == nil {
		return nil, fmt.Errorf("user bonus model is nil")
	}
	if b.UserBonus == nil {
		return nil, fmt.Errorf("current user bonus model is nil")
	}
	if b.UserBonus.ID != ub.ID {
		return nil, fmt.Errorf("bonus ID mismatch: current %d, new %d", b.UserBonus.ID, ub.ID)
	}
	b.UserBonus = ub

	wallet := container.GetRealMoneyWalletFactory().GetMoneyWallet(ub.UserID, ub.Currency)
	b.GWallet = wallet

	handler, err := getBonusHandler(b)
	if err != nil {
		return nil, err
	}
	b.handler = handler

	b.wagerer = container.GetBetWagererFactory().GetBetWagerer(b.UserBonus, wallet)

	b.actualizer, err = container.GetBonusActualizerFactory().GetActualizer(ub, wallet)
	b.infoProvider = container.GetBonusInfoProviderFactory().GetBonusInfoProvider(ub)
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
// Возвращает сумму выигрыша при завершении бонуса.
func (b *Bonus) Actualize() (win float64) {
	b.mx.Lock()
	defer b.mx.Unlock()
	return b.actualizer.Actualize()
}

// Save сохраняет текущее состояние бонуса в репозитории.
func (b *Bonus) Save() (stypes.BonusID, error) {
	b.mx.Lock()
	defer b.mx.Unlock()
	return b.save()
}

// Save сохраняет текущее состояние бонуса в репозитории.
func (b *Bonus) save() (stypes.BonusID, error) {
	if b.UserBonus == nil {
		return 0, nil // Если бонус не загружен, ничего не делаем
	}
	if b.UserBonus.UserID == 0 {
		return 0, fmt.Errorf("bonus user ID is zero")
	}
	// Если бонус не сохранен или состояние бонуса изменилось, сохраняем его
	if b.UserBonus.ID == 0 || *b.UserBonus != b.BackupState {
		now := time.Now()
		if b.UserBonus.ID == 0 {
			id, err := container.GetUserBonusRepository().Insert(b.UserBonus)
			if err != nil {
				return 0, err
			}
			b.UserBonus.ID = id
			b.UserBonus.CreatedAt = sql.NullTime{Time: now, Valid: true}
			b.UserBonus.UpdatedAt = sql.NullTime{Time: now, Valid: true}
			// Если бонус только что создан, сохраняем его ID и время создания
			// и записываем статистику по бонусу

			// Записываем статистику по бонусу
			stat := models.BonusStatistic{
				BonusID:      int64(b.UserBonus.ID),
				BonusType:    int16(b.UserBonus.Type),
				PatternID:    b.UserBonus.PatternID.Int64,
				UserID:       int64(b.UserBonus.UserID),
				Action:       stypes.ACTION_CREATED,
				Amount:       b.UserBonus.MoneyPaidOut,
				Currency:     int8(b.UserBonus.Currency),
				StatusBefore: stypes.STATUS_NOT_ACTIVATED,
				Status:       b.UserBonus.Status,
				CreatedAt:    &now,
				UpdatedAt:    &now,
			}
			// Записываем статистику по бонусу
			container.GetBonusStatisticStorage().Add(&stat)
		} else {
			b.UserBonus.UpdatedAt = sql.NullTime{Time: now, Valid: true}
			err := container.GetUserBonusRepository().Update(b.UserBonus)
			if err != nil {
				return 0, err
			}
		}
		b.BackupState = *b.UserBonus
	}
	return b.UserBonus.ID, nil
}

// GetStatusPad возвращает статус бонуса для отображения.
func (b *Bonus) GetStatusPad() stypes.BonusStatusPad {
	b.mx.RLock()
	defer b.mx.RUnlock()
	return b.actualizer.GetStatus()
}

// GetExtFsHandler возвращает обработчик внешних free spins для указанной игровой системы.
func (b *Bonus) GetExtFsHandler(game_system string) (stypes.BonusExternalFsHandler, error) {
	return container.GetExtFsHandlersFactory().GetExtFsHandler(game_system, b.UserBonus)
}

// getBonusHandler выбирает и возвращает обработчик бонуса в зависимости от его типа.
func getBonusHandler(b *Bonus) (stypes.BonusHandler, error) {
	return container.GetBonusHandlersFactory().GetBonusHandler(b.UserBonus, b.WagerPrc, b.GWallet)
}

func (b *Bonus) GetUserID() stypes.UserID {
	b.mx.RLock()
	defer b.mx.RUnlock()
	return b.UserBonus.UserID
}
func (b *Bonus) GetID() stypes.BonusID {
	b.mx.RLock()
	defer b.mx.RUnlock()
	return b.UserBonus.ID
}
func (b *Bonus) GetCurrencyID() currencyid.CurrencyID {
	b.mx.RLock()
	defer b.mx.RUnlock()
	return currencyid.CurrencyID(b.UserBonus.Currency)
}

func (b *Bonus) GetType() int8 {
	b.mx.RLock()
	defer b.mx.RUnlock()
	return b.UserBonus.Type
}

func (b *Bonus) GetPatternID() int64 {
	b.mx.RLock()
	defer b.mx.RUnlock()
	return b.UserBonus.PatternID.Int64
}

func (b *Bonus) GetBonusInfo() stypes.BonusInfo {
	return b.infoProvider.GetBonusInfo()
}

// SetAllowedGames устанавливает список разрешенных игр из JSON-строки.
// Если задан один провайдер и одна игра, проставляет GameProvider и GameID.
func (b *Bonus) SetAllowedGames(games *string) {
	b.mx.Lock()
	defer b.mx.Unlock()
	if games == nil {
		return
	}

	// Ожидается JSON вида: {"provider": ["game1", "game2", ...], ...}
	var m map[string][]string
	if err := json.Unmarshal([]byte(*games), &m); err != nil || len(m) == 0 {
		return
	}

	b.UserBonus.AllowedGames = sql.NullString{String: *games, Valid: true}
	b.UserBonus.GamesLimited = true

	// Один провайдер и одна игра — проставляем прямые параметры.
	if len(m) == 1 {
		for provider, ids := range m {
			if len(ids) == 1 {
				b.UserBonus.GameProvider = sql.NullString{String: provider, Valid: true}
				b.UserBonus.GameID = sql.NullString{String: ids[0], Valid: true}
			}
			break
		}
	}
}

func (b *Bonus) CancelBonus() {
	b.mx.Lock()
	defer b.mx.Unlock()
	b.actualizer.CancelBonus()
	b.save()
}

func (b *Bonus) Activate(value float64) error {
	b.mx.Lock()
	defer b.mx.Unlock()
	activator, err := container.GetBonusActivatorFactory().GetBonusActivator(b.UserBonus, b.GWallet)
	if err != nil {
		return err
	}
	return activator.ActivateBonus(value)
}

func (b *Bonus) Pause() error {
	b.mx.Lock()
	defer b.mx.Unlock()
	if b.UserBonus.Status != stypes.STATUS_ACTIVE && b.UserBonus.Status != stypes.STATUS_WAITING_FOR_PAYMENT {
		return fmt.Errorf("bonus not active")
	}
	if b.UserBonus.Status == stypes.STATUS_ACTIVE && b.UserBonus.SpinsAvailable > 0 {
		return fmt.Errorf("cannot pause bonus with available free spins")
	}
	// Переводим бонус в статус приостановлен
	if b.UserBonus.Status == stypes.STATUS_WAITING_FOR_PAYMENT {
		b.UserBonus.Status = stypes.STATUS_PAUSE_PAYMENT
	} else {
		b.UserBonus.Status = stypes.STATUS_PAUSE
	}
	_, err := b.save()

	now := time.Now()

	stat := models.BonusStatistic{
		BonusID:      int64(b.UserBonus.ID),
		BonusType:    int16(b.UserBonus.Type),
		PatternID:    b.UserBonus.PatternID.Int64,
		UserID:       int64(b.UserBonus.UserID),
		Action:       stypes.ACTION_PAUSE,
		Amount:       b.UserBonus.MoneyAvailable,
		Currency:     int8(b.UserBonus.Currency),
		StatusBefore: b.UserBonus.Status,
		Status:       stypes.STATUS_PAUSE,
		CreatedAt:    &now,
		UpdatedAt:    &now,
	}

	container.GetBonusStatisticStorage().Add(&stat)
	return err
}

func (b *Bonus) Resume() error {
	b.mx.Lock()
	defer b.mx.Unlock()
	if b.UserBonus.Status != stypes.STATUS_PAUSE && b.UserBonus.Status != stypes.STATUS_PAUSE_PAYMENT {
		return fmt.Errorf("bonus not paused")
	}
	// Переводим бонус в статус активен
	if b.UserBonus.Status == stypes.STATUS_PAUSE_PAYMENT {
		b.UserBonus.Status = stypes.STATUS_WAITING_FOR_PAYMENT
	} else {
		b.UserBonus.Status = stypes.STATUS_ACTIVE
	}
	_, err := b.save()

	now := time.Now()

	stat := models.BonusStatistic{
		BonusID:      int64(b.UserBonus.ID),
		BonusType:    int16(b.UserBonus.Type),
		PatternID:    b.UserBonus.PatternID.Int64,
		UserID:       int64(b.UserBonus.UserID),
		Action:       stypes.ACTION_RESUME,
		Amount:       b.UserBonus.MoneyAvailable,
		Currency:     int8(b.UserBonus.Currency),
		StatusBefore: stypes.STATUS_PAUSE,
		Status:       b.UserBonus.Status,
		CreatedAt:    &now,
		UpdatedAt:    &now,
	}

	container.GetBonusStatisticStorage().Add(&stat)
	return err
}

func (b *Bonus) ConfirmPayment(value float64, currency_id int8) error {
	b.mx.Lock()
	defer b.mx.Unlock()
	if b.UserBonus.Currency != currency_id {
		return fmt.Errorf("currency mismatch: bonus %d, payment %d", b.UserBonus.Currency, currency_id)
	}
	activator, err := container.GetBonusActivatorFactory().GetBonusActivator(b.UserBonus, b.GWallet)
	if err != nil {
		return err
	}
	return activator.ConfirmPayment(value)
}

func (b *Bonus) OnDeposit(value float64, currency_id int8) error {
	b.mx.Lock()
	defer b.mx.Unlock()
	if b.UserBonus.Currency != currency_id {
		return fmt.Errorf("currency mismatch: bonus %d, payment %d", b.UserBonus.Currency, currency_id)
	}
	activator, err := container.GetBonusActivatorFactory().GetBonusActivator(b.UserBonus, b.GWallet)
	if err != nil {
		return err
	}
	return activator.OnDeposit(value)
}

func (b *Bonus) GetEnableAfterPatternIds() ([]int64, error) {
	b.mx.RLock()
	defer b.mx.RUnlock()
	if !b.UserBonus.EnableAfter.Valid || b.UserBonus.EnableAfter.String == "" {
		return []int64{}, nil
	}
	jsonIds := []any{}
	err := json.Unmarshal([]byte(b.UserBonus.EnableAfter.String), &jsonIds)
	if err != nil {
		return []int64{}, err
	}
	ids := make([]int64, 0, len(jsonIds))
	for _, id := range jsonIds {
		if idFloat, ok := id.(float64); ok {
			ids = append(ids, int64(idFloat))
		} else if idInt64, ok := id.(int64); ok {
			ids = append(ids, idInt64)
		} else if idStr, ok := id.(string); ok {
			if idInt, err := strconv.ParseInt(idStr, 10, 64); err == nil {
				ids = append(ids, idInt)
			} else {
				return []int64{}, err
			}
		} else {
			return []int64{}, fmt.Errorf("unsupported id type: %T", id)
		}
	}
	return ids, nil
}

func (b *Bonus) GetJsonParam(key string) (any, bool) {
	b.mx.RLock()
	defer b.mx.RUnlock()
	return b.UserBonus.GetJsonParam(key)
}

func (b *Bonus) TransferBonusMoney() (transferred float64, err error) {
	b.mx.Lock()
	defer b.mx.Unlock()
	return b.actualizer.TransferBonusMoney()
}

func (b *Bonus) SetWagerPrc(wagerPrc int16) {
	b.mx.Lock()
	defer b.mx.Unlock()
	b.WagerPrc = wagerPrc
}

func (b *Bonus) Drop() {
	b.mx.Lock()
	defer b.mx.Unlock()
	b.UserBonus.MoneyAvailable = 0
}

func (b *Bonus) GetBalance() float64 {
	b.mx.RLock()
	defer b.mx.RUnlock()
	return b.GetBonusHandler().GetBalance()
}

func (b *Bonus) GetBalanceCents() int64 {
	b.mx.RLock()
	defer b.mx.RUnlock()
	return b.GetBonusHandler().GetCents()
}

func (b *Bonus) AddMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64) {
	b.mx.Lock()
	defer b.mx.Unlock()
	return b.GetBonusHandler().AddMoney(amount)
}

func (b *Bonus) RemMoney(amount float64) (balance float64, real_amount float64, bonus_amount float64, err error) {
	b.mx.Lock()
	defer b.mx.Unlock()
	return b.GetBonusHandler().RemMoney(amount)
}

func (b *Bonus) ExternFreeSpinBet(amount float64, provider string) (float64, error) {
	b.mx.Lock()
	defer b.mx.Unlock()
	if provider == "" {
		if b.UserBonus.GameProvider.Valid {
			provider = b.UserBonus.GameProvider.String
		} else {
			return 0, fmt.Errorf("game provider not set for bonus %d", b.UserBonus.ID)
		}
	}
	h, err := b.GetExtFsHandler(provider)
	if err != nil {
		return 0, err
	}
	return h.ExternFreeSpinBet(amount)
}

func (b *Bonus) ExternFreeSpinWin(amount float64, provider string) (float64, error) {
	b.mx.Lock()
	defer b.mx.Unlock()
	if provider == "" {
		if b.UserBonus.GameProvider.Valid {
			provider = b.UserBonus.GameProvider.String
		} else {
			return 0, fmt.Errorf("game provider not set for bonus %d", b.UserBonus.ID)
		}
	}
	h, err := b.GetExtFsHandler(provider)
	if err != nil {
		return 0, err
	}
	return h.ExternFreeSpinWin(amount)
}

func (b *Bonus) ExternFreeSpinsCompleted(amount float64, provider string) (float64, error) {
	b.mx.Lock()
	defer b.mx.Unlock()
	if provider == "" {
		if b.UserBonus.GameProvider.Valid {
			provider = b.UserBonus.GameProvider.String
		} else {
			return 0, fmt.Errorf("game provider not set for bonus %d", b.UserBonus.ID)
		}
	}
	h, err := b.GetExtFsHandler(provider)
	if err != nil {
		return 0, err
	}
	return h.ExternFreeSpinsCompleted(amount)
}

func (b *Bonus) OnUserBet(amount float64) {
	b.mx.Lock()
	defer b.mx.Unlock()
	if b.wagerer != nil {
		b.wagerer.OnUserBet(amount)
	}
}

func (b *Bonus) OnUserWin(amount float64) {
	b.mx.Lock()
	defer b.mx.Unlock()
	if b.wagerer != nil {
		b.wagerer.OnUserWin(amount)
	}
}
