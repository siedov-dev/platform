package service

import (
	"context"
	"errors"
	"ktournaments/internal/db/models"
	"ktournaments/internal/logger"
	"ktournaments/internal/service/container"
	"ktournaments/internal/service/tournament"
	"log/slog"
	"sync"
	"time"
)

// PendingTournaments - турниры в ожидании
var PendingTournaments map[uint64]*tournament.Tournament

// ActiveTournaments - активные турниры
var ActiveTournaments map[uint64]*tournament.Tournament

// CompletedTournaments - завершенные турниры
var CompletedTournaments map[uint64]*tournament.Tournament

// PendingAwardTournaments - турниры в ожидании наград
var PendingAwardTournaments map[uint64]*tournament.Tournament

// Tournaments - все турниры
var Tournaments map[uint64]*tournament.Tournament

// TournamentMut - мьютекс для турниров
var TournamentMut = &sync.RWMutex{}

// ticker - тикер для проверки турниров
var ticker *time.Ticker

// MustInit - инициализация сервиса
func MustInit(ctx context.Context) {
	// Загрузка турниров
	LoadTournaments()
	// Запуск тикера для проверки турниров
	ticker = time.NewTicker(1 * time.Minute)
	go func(ctx context.Context) {
		for {
			select {
			case <-ctx.Done():
				ticker.Stop()
				return
			case t := <-ticker.C:
				_ = t
				checkTournaments()
				logger.Get().Info("check tournaments")
				logger.DailyLog.Flush()
			}
		}
	}(ctx)
}

// LoadTournaments - загрузка турниров
func LoadTournaments() {

	TournamentMut.Lock()
	defer TournamentMut.Unlock()

	// Инициализация мап турниров
	PendingTournaments = make(map[uint64]*tournament.Tournament)
	ActiveTournaments = make(map[uint64]*tournament.Tournament)
	CompletedTournaments = make(map[uint64]*tournament.Tournament)
	PendingAwardTournaments = make(map[uint64]*tournament.Tournament)
	Tournaments = make(map[uint64]*tournament.Tournament)

	// Получение активных турниров
	tnts, err := container.GetTournamentRepository().GetTournamentsByStatus(models.STATUS_ACTIVE)
	if err != nil {
		logger.Get().Error("Failed to get active tournaments", slog.String("error", err.Error()))
	}
	// Загрузка активных турниров
	for _, t := range tnts {
		at := &tournament.Tournament{}
		err := at.LoadFromModel(t)
		if err != nil {
			logger.Get().Error("Failed to load tournament model", slog.String("error", err.Error()))
			continue
		}
		ActiveTournaments[t.ID] = at
		Tournaments[t.ID] = at
	}

	// Получение турниров в ожидании
	tnts, err = container.GetTournamentRepository().GetTournamentsByStatus(models.STATUS_NOT_ACTIVE)
	if err != nil {
		logger.Get().Error("Failed to get pending tournaments", slog.String("error", err.Error()))
	}
	// Загрузку турниров в ожидании
	for _, t := range tnts {
		at := &tournament.Tournament{}
		err := at.LoadFromModel(t)
		if err != nil {
			logger.Get().Error("Failed to load tournament model", slog.String("error", err.Error()))
			continue
		}
		PendingTournaments[t.ID] = at
		Tournaments[t.ID] = at
	}

	// Получение последних 10 завершенных турниров
	tnts, err = container.GetTournamentRepository().GetCompletedTournaments(10)
	if err != nil {
		logger.Get().Error("Failed to get completed tournaments", slog.String("error", err.Error()))
	}
	// Загрузка последних 10 завершенных турниров
	for _, t := range tnts {
		at := &tournament.Tournament{}
		err := at.LoadFromModel(t)
		if err != nil {
			logger.Get().Error("Failed to load tournament model", slog.String("error", err.Error()))
			continue
		}
		CompletedTournaments[t.ID] = at
		Tournaments[t.ID] = at
	}

	logger.Get().Info("Active tournaments loaded", slog.Int("count", len(ActiveTournaments)))
}

// checkTournaments - проверка статуса турниров и обновление их статуса в базе данных
// Перемещает турниры из ожидания в активные и из активных в завершенные
// на основе их времени начала и окончания
func checkTournaments() {
	TournamentMut.Lock()
	defer TournamentMut.Unlock()

	// Проверка турниров в ожидании
	for id, t := range PendingTournaments {
		if t.Model.StartTime.Before(time.Now()) {
			t.Model.Status = models.STATUS_ACTIVE
			ActiveTournaments[id] = t
			delete(PendingTournaments, id)
			container.GetTournamentRepository().UpdateTournament(&t.Model)
		}
	}

	// Проверка активных турниров
	for id, t := range ActiveTournaments {
		if t.Model.EndTime.Before(time.Now()) {
			t.Model.Status = models.STATUS_COMPLETED
			CompletedTournaments[id] = t
			PendingAwardTournaments[id] = t
			delete(ActiveTournaments, id)
			container.GetTournamentRepository().UpdateTournament(&t.Model)
		}
	}
}

// ReloadTournament - перезагрузка турнира
func ReloadTournament(id uint64) error {
	// Блокировка для изменения
	TournamentMut.Lock()
	defer TournamentMut.Unlock()

	// Сохранение текущих значений в базу данных
	container.GetValueSaver().Flush()

	// Загрузка турнира из базы данных
	tm, err := container.GetTournamentRepository().GetTournamentByID(id)
	if err != nil {
		return err
	}
	t := &tournament.Tournament{}
	err = t.LoadFromModel(*tm)
	if err != nil {
		return err
	}

	// Удаление старого турнира, если он уже был загружен
	DeleteTournament(id)

	// Помещение в соответствующий контейнер
	switch t.Model.Status {
	case models.STATUS_ACTIVE:
		ActiveTournaments[id] = t
	case models.STATUS_NOT_ACTIVE:
		PendingTournaments[id] = t
	case models.STATUS_COMPLETED:
		PendingTournaments[id] = t
		CompletedTournaments[id] = t
	default:
		CompletedTournaments[id] = t
	}
	Tournaments[id] = t

	return nil
}

// SetTournamentState - установка статуса турнира
func SetTournamentState(id uint64, state int) error {
	// Блокировка для изменения
	TournamentMut.Lock()
	defer TournamentMut.Unlock()

	// Поиск турнира
	t, ok := Tournaments[id]
	if !ok {
		return errors.New("tournament not found")
	}
	// Если статус турнира совпадает с новым статусом, то ничего не делаем
	if t.Model.Status == state {
		return nil
	}

	// Установка нового статуса
	t.Model.Status = state

	// Обновление статуса турнира в базе данных
	err := container.GetTournamentRepository().UpdateTournament(&t.Model)
	if err != nil {
		return err
	}

	// Удаление старого турнира из мап турниров
	DeleteTournament(id)

	// Помещение в соответствующую мап турнира
	switch t.Model.Status {
	case models.STATUS_ACTIVE:
		ActiveTournaments[id] = t
	case models.STATUS_NOT_ACTIVE:
		PendingTournaments[id] = t
	case models.STATUS_COMPLETED:
		PendingTournaments[id] = t
		CompletedTournaments[id] = t
	default:
		CompletedTournaments[id] = t
	}
	Tournaments[id] = t

	return nil
}

// DeleteTournament - удаление турнира из мап турниров
func DeleteTournament(id uint64) {
	delete(ActiveTournaments, id)
	delete(PendingTournaments, id)
	delete(CompletedTournaments, id)
	delete(PendingAwardTournaments, id)
	delete(Tournaments, id)
}
