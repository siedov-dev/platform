package tournament

import (
	"ktournaments/internal/db/models"
	"ktournaments/internal/logger"
	"ktournaments/internal/service/container"
	"log/slog"
	"slices"
	"sync"
	"time"
)

// TournamentValues - структура для хранения значений (очков) турнира
type TournamentValues struct {
	Values       map[int]*models.TournamentValueModel // Значения турнира
	tournamentID uint64                               // ID турнира
	logins       map[int]string                       // Логины пользователей
	mx           sync.RWMutex                         // Мьютекс
}

// Load - загрузка значений (очков) турнира из базы данных
func (t *TournamentValues) Load(tid uint64) (*TournamentValues, error) {
	// Инициализация
	tv := &TournamentValues{}
	tv.tournamentID = tid

	tv.Values = make(map[int]*models.TournamentValueModel)
	tv.logins = make(map[int]string)

	// Получаем значения (очки) турнира из базы данных
	vals, err := container.GetValueRepository().GetTournamentValues(tid)
	if err != nil {
		return nil, err
	}
	// Загружаем значения (очки) турнира
	for _, v := range vals {
		tv.Values[v.UserID] = &v
		// Получаем логин пользователя
		login, err := container.GetLoginsRepository().GetUserLogin(v.UserID)
		if err != nil {
			logger.Get().Error("tournament values", slog.String("cant get user login", err.Error()))
		}
		tv.logins[v.UserID] = login
	}
	return tv, nil
}

// AddPointsAndSpinsCount - добавление очков пользователю
// Если пользователь уже имеет значение, то обновляем его; иначе создаем новое
func (t *TournamentValues) AddPointsAndSpinsCount(userId int, points float32, spinsCount int32) {
	t.mx.Lock()
	defer t.mx.Unlock()

	// Получаем текущую дату
	now := time.Now()

	// Если пользователь уже имеет значение, то обновляем его
	if _, ok := t.Values[userId]; ok {
		t.Values[userId].Value += points
		t.Values[userId].SpinsCount += spinsCount
		t.Values[userId].UpdatedAt = &now
		_, err := container.GetValueSaver().Save(t.Values[userId])
		if err != nil {
			logger.Get().Error("Failed to save tournament value", slog.String("error", err.Error()))
			return
		}
	} else {
		// Создаем новое значение
		t.Values[userId] = &models.TournamentValueModel{
			UserID:       userId,
			Value:        points,
			TournamentID: t.tournamentID,
			SpinsCount:   spinsCount, // Инициализируем количество спинов
			CreatedAt:    &now,
			UpdatedAt:    &now,
		}
		// Сохраняем новое значение
		id, err := container.GetValueSaver().Save(t.Values[userId])
		if err != nil {
			logger.Get().Error("Failed to insert tournament value", slog.String("error", err.Error()))
			return
		}
		t.Values[userId].ID = id
		// Получаем логин пользователя
		login, err := container.GetLoginsRepository().GetUserLogin(userId)
		if err != nil {
			logger.Get().Error("tournament values", slog.String("cant get user login", err.Error()))
		}
		t.logins[userId] = login
	}
}

// Winner - структура победителя
type Winner struct {
	User_id    int     `json:"user_id"`
	Points     float32 `json:"points"`
	SpinsCount int32   `json:"spins_count"` // Количество спинов
	Login      string  `json:"login"`
}

// Winners - мапа победителей
// Ключ - место в таблице призов
// Значение - победитель
type Winners map[int]Winner

// GetWinners - получение таблицы победителей
func (t *TournamentValues) GetWinners(limit int) Winners {
	t.mx.RLock()

	// Инициализация
	winners := make(Winners)

	// Инициализация массива значений
	values := make([]struct {
		user_id     int
		points      float32
		spins_count int32
		updatedAt   time.Time
	}, 0, len(t.Values))

	// Загружаем все значения
	for _, v := range t.Values {
		values = append(values, struct {
			user_id     int
			points      float32
			spins_count int32
			updatedAt   time.Time
		}{
			user_id:     v.UserID,
			points:      v.Value,
			spins_count: v.SpinsCount,
			updatedAt:   *v.UpdatedAt,
		})
	}
	t.mx.RUnlock()

	// Сортировка значений по очкам
	slices.SortFunc(values, func(a, b struct {
		user_id     int
		points      float32
		spins_count int32
		updatedAt   time.Time
	}) int {
		if a.points > b.points {
			return -1
		}
		if a.points < b.points {
			return 1
		}
		return int(a.updatedAt.Sub(b.updatedAt))
	})

	// Формируем таблицу победителей
	for i, v := range values {
		if limit > 0 && i == limit {
			break
		}
		winners[i+1] = Winner{
			User_id:    v.user_id,
			Points:     v.points,
			SpinsCount: v.spins_count,
			Login:      t.logins[v.user_id],
		}
	}
	return winners
}

// GetUserPosition - получение позиции пользователя в турнире
// Если пользователь не найден, то возвращает 0 место
//
// Параметры:
// - user_id: id of the user
//
// Возвращает:
// - postion: место пользователя в турнире
// - winner: структура победителя
func (t *TournamentValues) GetUserPosition(user_id int) (position int, winner Winner) {
	winners := t.GetWinners(0)
	for i, w := range winners {
		if w.User_id == user_id {
			return i, w
		}
	}
	return 0, Winner{}
}
